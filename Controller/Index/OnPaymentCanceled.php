<?php

namespace PayEx\PaymentMenu\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\Manager as EventManager;

use PayEx\Api\Service\Transaction\Resource\Request\Transaction;
use PayEx\Core\Logger\Logger;
use PayEx\Client\Model\Service;
use PayEx\PaymentMenu\Helper\Config as ConfigHelper;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;

class OnPaymentCanceled extends PaymentActionAbstract
{
    /** @var Session  */
    protected $checkoutSession;

    /** @var Service  */
    protected $service;

    /** @var OrderRepository  */
    protected $orderRepository;

    /** @var QuoteRepository  */
    protected $quoteRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventManager $eventManager,
        ConfigHelper $configHelper,
        Logger $logger,
        Session $checkoutSession,
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        Service $service
    ) {
        parent::__construct($context, $resultJsonFactory, $eventManager, $configHelper, $logger);

        $this->checkoutSession = $checkoutSession;
        $this->service = $service;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;

        $this->setEventName('payment_canceled');
        $this->setEventMethod([$this, 'cancelPaymentOrder']);
        $this->setEventArgs(['id', 'redirectUrl']);
    }

    /**
     * @param string $paymentId
     * @param string $redirectUrl
     * @throws \Exception
     */
    public function cancelPaymentOrder($paymentId = '', $redirectUrl = '')
    {
        $magentoQuote = $this->checkoutSession->getQuote();
        $magentoOrder = $this->checkoutSession->getLastRealOrder();

        $payexQuote = $this->quoteRepository->getByQuoteId($magentoQuote->getEntityId());

        $transactionData = new Transaction();
        $transactionData->setDescription('Canceling parts of the total amount');
        $transactionData->setPayeeReference('');

        $session = $this->service->init('Transaction', 'Transaction', $transactionData);

        $session->setRequestEndpoint($payexQuote->getPaymentOrderId() . '/cancellations');

        $response = $session->send()->getResponseData();
        $payexOrder = $this->orderRepository->getByOrderId($magentoOrder->getId());

        $payexOrder->setCreatedAt($response['capture']['transaction']['created']);
        $payexOrder->setUpdatedAt($response['capture']['transaction']['updated']);
        $payexOrder->setState($response['capture']['transaction']['state']);
        $payexOrder->setAmount($response['capture']['transaction']['amount']);
        $payexOrder->setVatAmount($response['capture']['transaction']['vatAmount']);
        $payexOrder->setDescription($response['capture']['transaction']['description']);
        $this->orderRepository->save($payexOrder);
    }
}
