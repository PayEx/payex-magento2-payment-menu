<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayEx\PaymentMenu\Helper\Config;

class PaymentTosObserver implements ObserverInterface
{
    /** @var Config $config */
    protected $config;

    /**
     * PaymentTosObserver constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        // TODO: Implement execute() method.
    }
}
