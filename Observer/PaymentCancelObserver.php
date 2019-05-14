<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayEx\Api\Service\Transaction\Resource\Request\Transaction;
use PayEx\Client\Exception\ServiceException;
use PayEx\Client\Model\Service;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;
use PayEx\PaymentMenu\Helper\Config;

class PaymentCancelObserver implements ObserverInterface
{
    /** @var Session  */
    protected $checkoutSession;

    /** @var Service  */
    protected $service;

    /** @var OrderRepository  */
    protected $orderRepository;

    /** @var QuoteRepository  */
    protected $quoteRepository;

    /** @var EventManager  */
    protected $eventManager;

    /** @var Logger  */
    protected $logger;

    /** @var Config $config */
    protected $config;

    /**
     * PaymentCancelObserver constructor.
     * @param Session $checkoutSession
     * @param OrderRepository $orderRepository
     * @param QuoteRepository $quoteRepository
     * @param Service $service
     * @param EventManager $eventManager
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        Service $service,
        EventManager $eventManager,
        Logger $logger,
        Config $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->service = $service;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     * @throws AlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        $magentoQuote = $this->checkoutSession->getQuote();
        $magentoOrder = $this->checkoutSession->getLastRealOrder();
        try {
            $payexQuote = $this->quoteRepository->getByQuoteId($magentoQuote->getEntityId());
        } catch (NoSuchEntityException $e) {
            $this->logger->Error($e->getLogMessage());
            return;
        }

        $transactionData = new Transaction();
        $transactionData->setDescription('Canceling parts of the total amount');
        $transactionData->setPayeeReference('');

        try {
            $session = $this->service->init('Transaction', 'Transaction', $transactionData);
        } catch (ServiceException $e) {
            $this->logger->Error($e->getMessage());
            return;
        }

        $session->setRequestEndpoint($payexQuote->getPaymentOrderId() . '/cancellations');
        try {
            $response = $session->send()->getResponseData();
            $payexOrder = $this->orderRepository->getByOrderId($magentoOrder->getId());
        } catch (\Exception $e) {
            $this->logger->Error($e->getMessage());
            return;
        }

        $payexOrder->setCreatedAt($response['capture']['transaction']['created']);
        $payexOrder->setUpdatedAt($response['capture']['transaction']['updated']);
        $payexOrder->setState($response['capture']['transaction']['state']);
        $payexOrder->setAmount($response['capture']['transaction']['amount']);
        $payexOrder->setVatAmount($response['capture']['transaction']['vatAmount']);
        $payexOrder->setDescription($response['capture']['transaction']['description']);
        $this->orderRepository->save($payexOrder);
    }
}
