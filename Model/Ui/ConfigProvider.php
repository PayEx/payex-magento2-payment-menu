<?php

namespace PayEx\PaymentMenu\Model\Ui;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CODE = 'payex_payment_menu';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                ]
            ]
        ];
    }
}
