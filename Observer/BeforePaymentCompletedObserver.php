<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;

class BeforePaymentCompletedObserver implements ObserverInterface
{
    /** @var Session  */
    protected $checkoutSession;

    /** @var OrderRepository  */
    protected $orderRepository;

    /** @var EventManager  */
    protected $eventManager;

    /** @var QuoteRepository  */
    protected $quoteRepository;

    /** @var Config $config */
    protected $config;

    /** @var Logger $logger */
    protected $logger;

    /**
     * BeforePaymentCompletedObserver constructor.
     * @param Session $checkoutSession
     * @param OrderRepository $orderRepository
     * @param EventManager $eventManager
     * @param QuoteRepository $quoteRepository
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $orderRepository,
        EventManager $eventManager,
        QuoteRepository $quoteRepository,
        Config $config,
        Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        $this->logger->Debug('payex_paymentmenu_before_payment_completed event has been triggered');
    }
}
