<?php

namespace PayEx\PaymentMenu\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\Manager as EventManager;

use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config as ConfigHelper;
use PayEx\PaymentMenu\Model\Order;
use PayEx\PaymentMenu\Model\OrderFactory;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;

class OnPaymentFailed extends PaymentActionAbstract
{
    /** @var Session  */
    protected $checkoutSession;

    /** @var OrderRepository */
    protected $paymentOrderRepo;

    /** @var MagentoOrderRepository */
    protected $magentoOrderRepo;

    /** @var OrderFactory  */
    protected $orderFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventManager $eventManager,
        ConfigHelper $configHelper,
        Logger $logger,
        Session $session,
        OrderRepository $orderRepository,
        MagentoOrderRepository $magentoOrderRepo,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context, $resultJsonFactory, $eventManager, $configHelper, $logger);

        $this->checkoutSession = $session;
        $this->paymentOrderRepo = $orderRepository;
        $this->magentoOrderRepo = $magentoOrderRepo;
        $this->orderFactory = $orderFactory;

        $this->setEventName('payment_failed');
        $this->setEventMethod([$this, 'holdPaymentOrder']);
        $this->setEventArgs(['id', 'redirectUrl']);
    }

    public function holdPaymentOrder($paymentId = '', $redirectUrl = '')
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $order->setState(\Magento\Sales\Model\Order::STATE_HOLDED);
        $this->magentoOrderRepo->save($order);

        /** @var Order $paymentOrder */
        $paymentOrder = $this->orderFactory->create();
        $paymentOrder->setState(\Magento\Sales\Model\Order::STATE_HOLDED);
        $this->paymentOrderRepo->save($paymentOrder);
    }
}
