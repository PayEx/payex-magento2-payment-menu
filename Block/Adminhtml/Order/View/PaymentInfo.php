<?php

namespace PayEx\PaymentMenu\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use PayEx\Api\Service\Paymentorder\Request\CurrentPayment;
use PayEx\Client\Model\Service;
use PayEx\PaymentMenu\Api\OrderRepositoryInterface as PaymentOrderRepository;

class PaymentInfo extends Template
{
    protected $service;

    protected $paymentOrderRepo;

    public function __construct(
        Context $context,
        array $data = [],
        Service $service,
        PaymentOrderRepository $paymentOrderRepo
    ) {
        parent::__construct($context, $data);
        $this->service = $service;
        $this->paymentOrderRepo = $paymentOrderRepo;
    }

    public function getCurrentPayment()
    {
        /** @var CurrentPayment $serviceRequest */
        $serviceRequest = $this->service->init('Paymentorder', 'CurrentPayment');
        $serviceRequest->setRequestEndpoint('/psp/paymentorders/' . $this->getCurrentPaymentId() . '/currentpayment');

        return $serviceRequest->send()->getResponseData();
    }

    public function getCurrentPaymentId()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $paymentOrderData = $this->paymentOrderRepo->getByOrderId($orderId);

        return $paymentOrderData->getPaymentOrderId();
    }
}