<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PayEx\PaymentMenu\Model\Ui\ConfigProvider;
use PayEx\PaymentMenu\Helper\Config as ConfigHelper;

class PaymentMethodAvailable implements ObserverInterface
{
    protected $configHelper;

    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Event|object $event */
        $event = $observer->getEvent();

        if ($event->getMethodInstance()->getCode() == ConfigProvider::CODE) {
            /** @var object $checkResult */
            $checkResult = $event->getResult();
            $checkResult->setData('is_available', $this->configHelper->isActive());
        }
    }
}
