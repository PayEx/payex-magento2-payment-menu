<?php

namespace PayEx\PaymentMenu\Helper;

use Magento\Braintree\Model\LocaleResolver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Header\Logo as HeaderLogo;
use Magento\Quote\Model\Quote as MageQuote;
use Magento\Store\Model\StoreManagerInterface;
use PayEx\Api\Client\Client as ApiClient;
use PayEx\Api\Service\Paymentorder\Resource\Collection\ItemsCollection;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderCampaignInvoice;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderCreditCard;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderInvoice;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderObject;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderPayeeInfo;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderPayer;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderSwish;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderUrl;
use PayEx\Api\Service\Paymentorder\Resource\Request\Paymentorder as PaymentorderRequestResource;
use PayEx\Client\Helper\Config as ClientConfig;
use PayEx\PaymentMenu\Helper\Config as PaymentMenuConfig;
use PayEx\PaymentMenu\Model\QuoteFactory;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;

class Paymentorder
{
    /**
     * @var CheckoutSession $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var PageHelper
     */
    protected $pageHelper;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var HeaderLogo
     */
    protected $headerLogo;

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @var ClientConfig $clientConfig
     */
    protected $clientConfig;

    /**
     * @var PaymentMenuConfig $paymentMenuConfig
     */
    protected $paymentMenuConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Resolver $localeResolver
     */
    protected $localeResolver;

    /**
     * @var QuoteFactory $quoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteRepository $quoteRepository
     */
    protected $quoteRepository;

    public function __construct(
        CheckoutSession $checkoutSession,
        StoreManagerInterface $storeManager,
        PageHelper $pageHelper,
        UrlInterface $urlInterface,
        HeaderLogo $headerLogo,
        ApiClient $apiClient,
        ClientConfig $clientConfig,
        PaymentMenuConfig $paymentMenuConfig,
        ScopeConfigInterface $scopeConfig,
        LocaleResolver $localeResolver,
        QuoteFactory $quoteFactory,
        QuoteRepository $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->pageHelper = $pageHelper;
        $this->urlInterface = $urlInterface;
        $this->headerLogo = $headerLogo;
        $this->apiClient = $apiClient;
        $this->clientConfig = $clientConfig;
        $this->paymentMenuConfig = $paymentMenuConfig;
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Creates Paymentorder Object
     *
     * @param string|null $consumerProfileRef
     * @return PaymentorderObject
     * @throws NoSuchEntityException
     */
    public function createPaymentorderObject($consumerProfileRef = null)
    {
        /** @var MageQuote $mageQuote */
        $mageQuote = $this->checkoutSession->getQuote();

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        $currency = $store->getCurrentCurrency()->getCode();

        $totalAmount = $mageQuote->getGrandTotal() * 100;

        if ($mageQuote->isVirtual()) {
            $vatAmount = $mageQuote->getBillingAddress()->getTaxAmount() * 100;
        }

        if (!isset($vatAmount)) {
            $vatAmount = $mageQuote->getShippingAddress()->getTaxAmount() * 100;
        }

        $urlData = $this->createUrlObject();
        $payeeInfo = $this->createPayeeInfoObject();

        /**
         * Optional payment method specific stuff
         *
         * $paymentOrderItems = $this->createItemsObject();
         */

        $storeName = $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );

        $paymentOrder = new PaymentorderRequestResource();
        $paymentOrder->setOperation('Purchase')
            ->setCurrency($currency)
            ->setAmount($totalAmount)
            ->setVatAmount($vatAmount)
            ->setDescription($storeName . ' ' . __('Purchase'))
            ->setUserAgent($this->apiClient->getUserAgent())
            ->setLanguage($this->getLanguage())
            ->setGeneratePaymentToken(false)
            ->setDisablePaymentMenu(false)
            ->setUrls($urlData)
            ->setPayeeInfo($payeeInfo);

        if (isset($paymentorderItems) && ($paymentorderItems instanceof ItemsCollection)) {
            $paymentOrder->setItems($paymentorderItems);
        }

        if ($consumerProfileRef) {
            $payer = new PaymentorderPayer();
            $payer->setConsumerProfileRef($consumerProfileRef);
            $paymentOrder->setPayer($payer);
        }

        $paymentOrderObject = new PaymentorderObject();
        $paymentOrderObject->setPaymentorder($paymentOrder);

        return $paymentOrderObject;
    }

    /**
     * @return PaymentorderUrl
     */
    public function createUrlObject()
    {
        $mageBaseUrl = $this->urlInterface->getBaseUrl();
        $mageCompleteUrl = $this->urlInterface->getUrl('checkout/onepage/success');
        $mageCancelUrl = $this->urlInterface->getUrl('checkout/cart');

        $baseUrlParts = parse_url($mageBaseUrl);

        $urlData = new PaymentorderUrl();
        $urlData->setHostUrls([$baseUrlParts['scheme'] . '://' . $baseUrlParts['host']])
            ->setCompleteUrl($mageCompleteUrl)
            ->setCancelUrl($mageCancelUrl);

        if ($tosPageId = $this->paymentMenuConfig->getValue('tos_page')) {
            $urlData->setTermsOfService($this->pageHelper->getPageUrl($tosPageId));
        }

        if ($logoSrcUrl = $this->headerLogo->getLogoSrc()) {
            $urlData->setLogoUrl($logoSrcUrl);
        }

        return $urlData;
    }

    /**
     * @return PaymentorderPayeeInfo
     */
    public function createPayeeInfoObject()
    {
        $payeeInfo = new PaymentorderPayeeInfo();
        $payeeInfo->setPayeeId($this->clientConfig->getValue('payee_id'))
            ->setPayeeReference($this->generateRandomString(30));

        return $payeeInfo;
    }

    public function createItemsObject()
    {
        $item = [];

        if ($creditCard = $this->createCreditCardObject()) {
            $item['credit_card'] = $creditCard;
        }

        if ($invoice = $this->createinvoiceObject()) {
            $item['invoice'] = $invoice;
        }

        if ($campaignInvoice = $this->createCampaignInvoiceObject()) {
            $item['campaign_invoice'] = $campaignInvoice;
        }

        if ($swish = $this->createSwishObject()) {
            $item['swish'] = $swish;
        }

        if (count($item) == 0) {
            return null;
        }

        $paymentorderItems = new ItemsCollection();
        $paymentorderItems->addItem($item);

        return $paymentorderItems;
    }

    /**
     * @return PaymentorderCreditCard
     */
    public function createCreditCardObject()
    {
        $creditCard = new PaymentorderCreditCard();
        $creditCard->setNo3DSecure(false)
            ->setNo3DSecureForStoredCard(false)
            ->setRejectCardNot3DSecureEnrolled(false)
            ->setRejectCreditCards(false)
            ->setRejectDebitCards(false)
            ->setRejectConsumerCards(false)
            ->setRejectCorporateCards(false)
            ->setRejectAuthenticationStatusA(false)
            ->setRejectAuthenticationStatusU(false);

        return $creditCard;
    }

    /**
     * @return PaymentorderInvoice
     */
    public function createInvoiceObject()
    {
        $invoice = new PaymentorderInvoice();
        $invoice->setFeeAmount(1900);

        return $invoice;
    }

    /**
     * @return PaymentorderCampaignInvoice
     */
    public function createCampaignInvoiceObject()
    {
        $campaignInvoice = new PaymentorderCampaignInvoice();
        $campaignInvoice->setCampaignCode('Campaign1')
            ->setFeeAmount(2900);

        return $campaignInvoice;
    }

    public function createSwishObject()
    {
        $swish = new PaymentorderSwish();
        $swish->setEnableEcomOnly(false);

        return $swish;
    }

    /**
     * @param $response
     * @throws AlreadyExistsException
     * @throws \Exception
     */
    public function saveQuoteToDB($response)
    {
        /** @var MageQuote $mageQuote */
        $mageQuote = $this->checkoutSession->getQuote();

        // Gets row from payex_quotes by matching quote_id
        // Otherwise, Creates a new record
        try {
            $quote = $this->quoteRepository->getByQuoteId($mageQuote->getId());

            // If is_updated field is 0,
            // Then it doesn't update
            if ($response['payment_order']['id'] == $quote->getPaymentOrderId()
                && $quote->getIsUpdated() != 0) {
                return;
            }
        } catch (NoSuchEntityException $e) {
            $quote = $this->quoteFactory->create();
        }

        $quote->setPaymentOrderId($this->getPayExPaymentorderId($response['payment_order']['id']));
        $quote->setDescription($response['payment_order']['description']);
        $quote->setOperation($response['payment_order']['operation']);
        $quote->setState($response['payment_order']['state']);
        $quote->setCurrency($response['payment_order']['currency']);
        $quote->setAmount($response['payment_order']['amount']);
        $quote->setVatAmount($response['payment_order']['vat_amount']);
        $quote->setRemainingCapturingAmount($response['payment_order']['amount']);
        $quote->setRemainingCancellationAmount($response['payment_order']['amount']);
        $quote->setRemainingReversalAmount(0);
        $quote->setPayerToken('');
        $quote->setQuoteId($mageQuote->getId());
        $quote->setIsUpdated(0);
        $quote->setCreatedAt($response['payment_order']['created']);
        $quote->setUpdatedAt($response['payment_order']['updated']);
        $this->quoteRepository->save($quote);
    }

    /**
     * Extracts Id from PayEx Paymentorder Id, ex: 5adc265f-f87f-4313-577e-08d3dca1a26c
     *
     * @param $paymentorderId
     * @return string
     */
    protected function getPayExPaymentorderId($paymentorderId)
    {
        return str_replace('/psp/paymentorders/', '', $paymentorderId);
    }

    /**
     * Generates a random string
     *
     * @param $length
     * @return bool|string
     */
    protected function generateRandomString($length)
    {
        return substr(str_shuffle(md5(time())), 0, $length);
    }

    /**
     * Gets language in PayEx supported format, ex: nb-No
     *
     * @return string
     */
    protected function getLanguage()
    {
        return str_replace('_', '-', $this->localeResolver->getLocale());
    }
}
