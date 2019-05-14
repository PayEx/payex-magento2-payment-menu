<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\OrderFactory;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;
use PayEx\Core\Logger\Logger;

class PaymentErrorObserver implements ObserverInterface
{
    /** @var Session  */
    protected $checkoutSession;

    /** @var OrderRepository  */
    protected $paymentOrderRepo;

    /** @var MagentoOrderRepository  */
    protected $magentoOrderRepo;

    /** @var OrderFactory  */
    protected $orderFactory;

    /** @var Config $config */
    protected $config;

    /** @var Logger */
    protected $logger;

    /**
     * PaymentErrorObserver constructor.
     * @param Session $checkoutSession
     * @param OrderRepository $paymentOrderRepo
     * @param MagentoOrderRepository $magentoOrderRepo
     * @param OrderFactory $orderFactory
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $paymentOrderRepo,
        MagentoOrderRepository $magentoOrderRepo,
        OrderFactory $orderFactory,
        Config $config,
        Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentOrderRepo = $paymentOrderRepo;
        $this->magentoOrderRepo = $magentoOrderRepo;
        $this->orderFactory = $orderFactory;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        $requestBody = $observer->getData('requestBody');
        //$origin = $requestBody->origin;
        $messageId = $requestBody->messageId;
        $details = $requestBody->details;

        $this->logger->error(sprintf("PayEx Payment Error [%s]: %s", $messageId, $details));
    }
}
