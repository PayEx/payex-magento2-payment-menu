<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;
use PayEx\Client\Model\Service;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;

class PaymentCreatedObserver implements ObserverInterface
{
    /** @var Session  */
    protected $checkoutSession;

    /** @var OrderRepository  */
    protected $paymentOrderRepo;

    /** @var Service  */
    protected $service;

    /** @var MagentoOrderRepository  */
    protected $magentoOrderRepo;

    /** @var Config $config */
    protected $config;

    /**
     * PaymentCreatedObserver constructor.
     * @param Session $checkoutSession
     * @param OrderRepository $paymentOrderRepo
     * @param Service $service
     * @param MagentoOrderRepository $magentoOrderRepo
     * @param Config $config
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $paymentOrderRepo,
        Service $service,
        MagentoOrderRepository $magentoOrderRepo,
        Config $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentOrderRepo = $paymentOrderRepo;
        $this->service = $service;
        $this->magentoOrderRepo = $magentoOrderRepo;
        $this->config = $config;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }
    }
}
