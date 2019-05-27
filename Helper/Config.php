<?php

namespace PayEx\PaymentMenu\Helper;

use Magento\Store\Model\Store;
use PayEx\Core\Helper\Config as CoreConfig;
use PayEx\PaymentMenu\Model\Ui\ConfigProvider;

class Config extends CoreConfig
{
    const XML_CONFIG_GROUP = 'payment_menu';

    protected $moduleDependencies = [
        'PayEx_Client',
        'PayEx_Checkout'
    ];

    /**
     * Get the order status that should be set on orders that have been processed by PayEx
     *
     * @param Store|int|string|null  $store
     *
     * @return string
     */
    public function getProcessedOrderStatus($store = null)
    {
        return $this->getPaymentValue('order_status', ConfigProvider::CODE, $store);
    }
}
