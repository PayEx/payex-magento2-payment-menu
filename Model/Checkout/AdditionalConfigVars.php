<?php

namespace PayEx\PaymentMenu\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use PayEx\PaymentMenu\Helper\Config as ConfigHelper;

class AdditionalConfigVars implements ConfigProviderInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Resolver
     */
    protected $locale;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    public function __construct(
        Resolver $locale,
        UrlInterface $urlBuilder,
        ConfigHelper $configHelper
    ) {
        $this->locale = $locale;
        $this->urlBuilder = $urlBuilder;
        $this->configHelper = $configHelper;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'PayEx_PaymentMenu' => [
                'isEnabled' => $this->configHelper->isActive(),
                'culture' => str_replace('_', '-', $this->locale->getLocale()),
                'onPaymentCancel' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentCancel'),
                'onPaymentCapture' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentCapture'),
                'onPaymentCompleted' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentCompleted'),
                'onPaymentCreated' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentCreated'),
                'onPaymentError' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentError'),
                'onPaymentFailed' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentFailed'),
                'onPaymentMenuInstrumentSelected' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentMenuInstrumentSelected'),
                'onPaymentReversal' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentReversal'),
                'onPaymentToS' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnPaymentToS'),
                'onUpdated' => $this->urlBuilder->getUrl('PayExPaymentMenu/Index/OnUpdated')
            ]
        ];
    }
}
