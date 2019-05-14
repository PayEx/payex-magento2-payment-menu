<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\Order;
use PayEx\PaymentMenu\Model\OrderFactory;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;

class PaymentFailedObserver implements ObserverInterface
{
    protected $checkoutSession;

    protected $paymentOrderRepo;

    protected $magentoOrderRepo;

    /** @var OrderFactory  */
    protected $orderFactory;

    /** @var Config $config */
    protected $config;

    /**
     * PaymentFailedObserver constructor.
     * @param Session $checkoutSession
     * @param OrderRepository $paymentOrderRepo
     * @param MagentoOrderRepository $magentoOrderRepo
     * @param OrderFactory $orderFactory
     * @param Config $config
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $paymentOrderRepo,
        MagentoOrderRepository $magentoOrderRepo,
        OrderFactory $orderFactory,
        Config $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentOrderRepo = $paymentOrderRepo;
        $this->magentoOrderRepo = $magentoOrderRepo;
        $this->orderFactory = $orderFactory;
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return void
     * @throws AlreadyExistsException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        $requestBody = $observer->getData('requestBody');
        $origin = $requestBody->origin;
        $messageId = $requestBody->messageId;
        $details = $requestBody->details;

        $order = $this->checkoutSession->getLastRealOrder();
        $order->setState(\Magento\Sales\Model\Order::STATE_HOLDED);
        $this->magentoOrderRepo->save($order);

        /** @var Order $paymentOrder */
        $paymentOrder = $this->orderFactory->create();
        $paymentOrder->setState(\Magento\Sales\Model\Order::STATE_HOLDED);
        $this->paymentOrderRepo->save($paymentOrder);
    }
}
