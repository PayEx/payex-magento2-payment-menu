<?php

namespace PayEx\PaymentMenu\Gateway\Command;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Gateway\Command;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Model\Order as MageOrder;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;
use PayEx\Api\Service\Data\ResponseInterface;
use PayEx\Api\Service\Transaction\Resource\Collection\Item\DescriptionItem;
use PayEx\Api\Service\Transaction\Resource\Collection\Item\VatSummaryItem;
use PayEx\Api\Service\Transaction\Resource\Collection\ItemDescriptionCollection;
use PayEx\Api\Service\Transaction\Resource\Collection\VatSummaryCollection;
use PayEx\Api\Service\Transaction\Resource\Request\Transaction;
use PayEx\Api\Service\Transaction\Resource\Request\TransactionObject;
use PayEx\Client\Helper\Config as ClientConfig;
use PayEx\Client\Model\Service as ClientRequestService;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Api\OrderRepositoryInterface as PaymentOrderRepository;
use PayEx\PaymentMenu\Api\QuoteRepositoryInterface as PaymentQuoteRepository;
use PayEx\PaymentMenu\Helper\Config as PaymentMenuConfig;

/**
 * Class Capture
 *
 * @package PayEx\PaymentMenu\Gateway\Command
 */
class Capture extends AbstractCommand
{
    /**
     * @var RequestInterface|object
     */
    protected $request;

    /**
     * @var Calculation
     */
    protected $calculator;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Capture constructor.
     *
     * @param PaymentOrderRepository $paymentOrderRepo
     * @param PaymentQuoteRepository $paymentQuoteRepo
     * @param ClientRequestService $requestService
     * @param MageQuoteRepository $mageQuoteRepo
     * @param MageOrderRepository $mageOrderRepo
     * @param ClientConfig $clientConfig
     * @param PaymentMenuConfig $paymentMenuConfig
     * @param MessageManager $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param GroupRepositoryInterface $groupRepository
     * @param Calculation $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        PaymentOrderRepository $paymentOrderRepo,
        PaymentQuoteRepository $paymentQuoteRepo,
        ClientRequestService $requestService,
        MageQuoteRepository $mageQuoteRepo,
        MageOrderRepository $mageOrderRepo,
        ClientConfig $clientConfig,
        PaymentMenuConfig $paymentMenuConfig,
        MessageManager $messageManager,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        GroupRepositoryInterface $groupRepository,
        Calculation $calculator,
        PriceCurrencyInterface $priceCurrency,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct(
            $paymentOrderRepo,
            $paymentQuoteRepo,
            $requestService,
            $mageQuoteRepo,
            $mageOrderRepo,
            $clientConfig,
            $paymentMenuConfig,
            $messageManager,
            $logger,
            $data
        );

        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->groupRepository = $groupRepository;
        $this->calculator = $calculator;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Capture command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \PayEx\Core\Exception\PayExException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \PayEx\Client\Exception\ServiceException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface|object $payment */
        $payment = $commandSubject['payment']->getPayment();
        $amount = $commandSubject['amount'] + 0;

        /** @var MageOrder $order */
        $order = $payment->getOrder();

        $invoices = $order->getInvoiceCollection();

        /**
         * The latest invoice will contain only the selected items(and quantities) for the (partial) capture
         * @var Invoice $invoice
         */
        $invoice = $invoices->getLastItem();

        $paymentOrder = $this->getPayExPaymentData($order);

        $this->checkRemainingAmount('capture', $amount, $order, $paymentOrder);

        $itemDescriptions = new ItemDescriptionCollection();
        $vatSummaryRateAmounts = [];

        /** @var InvoiceItem $item */
        foreach ($invoice->getItemsCollection() as $item) {
            $itemTotal = ($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount()) * 100;

            $description = (string)$item->getName();
            if ($item->getBaseDiscountAmount()) {
                $formattedDiscountAmount = $this->priceCurrency->format(
                    $item->getBaseDiscountAmount(),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $order->getStoreId()
                );
                $description .= ' - ' . __('Including') . ' ' . $formattedDiscountAmount . ' ' . __('discount');
            }

            $descriptionItem = new DescriptionItem();
            $descriptionItem->setAmount($itemTotal)
                ->setDescription($description);
            $itemDescriptions->addItem($descriptionItem);

            $rate = (int)$item->getOrderItem()->getTaxPercent();

            if (!isset($vatSummaryRateAmounts[$rate])) {
                $vatSummaryRateAmounts[$rate] = ['amount' => 0, 'vat_amount' => 0];
            }

            $vatSummaryRateAmounts[$rate]['amount'] += $itemTotal;
            $vatSummaryRateAmounts[$rate]['vat_amount'] += $item->getBaseTaxAmount() * 100;
        }

        if (!$order->getIsVirtual() && $order->getBaseShippingInclTax() > 0) {
            $shippingTotal = ($order->getBaseShippingInclTax() - $order->getBaseShippingDiscountAmount()) * 100;

            $description = (string)$order->getShippingDescription();
            if ($order->getBaseShippingDiscountAmount()) {
                $formattedDiscountAmount = $this->priceCurrency->format(
                    $order->getBaseShippingDiscountAmount(),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $order->getStoreId()
                );
                $description .= ' - ' . __('Including') . ' ' . $formattedDiscountAmount . ' ' . __('discount');
            }

            $descriptionItem = new DescriptionItem();
            $descriptionItem->setAmount($shippingTotal)
                ->setDescription($description);
            $itemDescriptions->addItem($descriptionItem);

            $rate = (int)$this->getTaxRate($order);

            if (!isset($vatSummaryRateAmounts[$rate])) {
                $vatSummaryRateAmounts[$rate] = ['amount' => 0, 'vat_amount' => 0];
            }

            $vatSummaryRateAmounts[$rate]['amount'] += $shippingTotal;
            $vatSummaryRateAmounts[$rate]['vat_amount'] += $order->getBaseShippingTaxAmount() * 100;
        }

        $vatSummaries = new VatSummaryCollection();

        foreach ($vatSummaryRateAmounts as $rate => $amounts) {
            $vatSummary = new VatSummaryItem();
            $vatSummary->setAmount($amounts['amount'])
                ->setVatAmount($amounts['vat_amount'])
                ->setVatPercent($rate);
            $vatSummaries->addItem($vatSummary);
        }

        $transaction = new Transaction();
        $transaction->setDescription("Capturing the authorized payment")
            ->setAmount($amount * 100)
            ->setVatAmount($order->getBaseTaxAmount() * 100)
            ->setPayeeReference($this->generateRandomString(30))
            ->setItemDescriptions($itemDescriptions)
            ->setVatSummary($vatSummaries);

        $transactionObject = new TransactionObject();
        $transactionObject->setTransaction($transaction);

        $captureRequest = $this->getRequestService('transaction', 'TransactionCapture', $transactionObject);
        $captureRequest->setRequestEndpointVars($this->getPayExPaymentResourceId($paymentOrder->getPaymentOrderId()));

        /** @var ResponseInterface $captureResponse */
        $captureResponse = $captureRequest->send();

        $this->checkResponseResource('capture', $captureResponse->getResponseResource(), $order, $paymentOrder);

        /** @var array $captureResponseData */
        $captureResponseData = $captureResponse->getResponseData();

        $this->checkResponseData('capture', $captureResponseData, $order, $paymentOrder);

        $this->updateRemainingAmounts('capture', $amount, $paymentOrder);

        return null;
    }

    /**
     * Getting back the tax rate
     *
     * @param MageOrder $order
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTaxRate(MageOrder $order)
    {
        $store = $order->getStore();
        $taxClassId = null;

        $groupId = $order->getCustomerGroupId();
        if ($groupId !== null) {
            $taxClassId = $this->groupRepository->getById($groupId)->getTaxClassId();
        }

        /** @var DataObject|object $request */
        $request = $this->calculator->getRateRequest(
            $order->getShippingAddress(),
            $order->getBillingAddress(),
            $taxClassId,
            $store
        );

        $taxRateId = $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            ScopeInterface::SCOPE_STORES,
            $store
        );

        return $this->calculator->getRate($request->setProductClassId($taxRateId));
    }
}
