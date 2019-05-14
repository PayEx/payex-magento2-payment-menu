<?php

namespace PayEx\PaymentMenu\Helper;

use Magento\Store\Model\Store;
use PayEx\Core\Helper\Config as CoreConfig;
use PayEx\PaymentMenu\Model\Ui\ConfigProvider;

class Config extends CoreConfig
{
    const XML_CONFIG_GROUP = 'payment_menu';

    /**
     * Get the order status that should be set on orders that have been processed by Klarna
     *
     * @param Store|int|string|null  $store
     *
     * @return string
     */
    public function getProcessedOrderStatus($store = null)
    {
        return $this->getPaymentValue('order_status', ConfigProvider::CODE, $store);
    }

    /**
     * Overrides the default isActive() so that we get the value from the payment method scope
     *
     * @param Store|int|string|null  $store
     *
     * @return bool
     */
    public function isActive($store = null)
    {
        return $this->getPaymentValue('active', ConfigProvider::CODE, $store);
    }
}
