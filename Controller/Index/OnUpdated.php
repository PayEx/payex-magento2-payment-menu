<?php

namespace PayEx\PaymentMenu\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PayEx\Checkin\Model\ConsumerSession;
use PayEx\Client\Exception\ServiceException;
use PayEx\Client\Model\Service;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config as ConfigHelper;
use PayEx\PaymentMenu\Helper\Paymentorder as PaymentorderHelper;

class OnUpdated extends Action
{
    /** @var JsonFactory */
    protected $resultJsonFactory;

    /**
     * @var Service $service
     */
    protected $service;

    /**
     * @var ConfigHelper $configHelper
     */
    protected $configHelper;

    /**
     * @var PaymentorderHelper $paymentorderHelper
     */
    protected $paymentorderHelper;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var ConsumerSession $consumerSession
     */
    protected $consumerSession;

    /**
     * @var CookieManagerInterface $cookieManager
     */
    protected $cookieManager;

    /**
     * OnPaymentCapture constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Service $service
     * @param ConfigHelper $configHelper
     * @param PaymentorderHelper $paymentorderHelper
     * @param Logger $logger
     * @param ConsumerSession $consumerSession
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Service $service,
        ConfigHelper $configHelper,
        PaymentorderHelper $paymentorderHelper,
        Logger $logger,
        ConsumerSession $consumerSession,
        CookieManagerInterface $cookieManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->service = $service;
        $this->configHelper = $configHelper;
        $this->paymentorderHelper = $paymentorderHelper;
        $this->logger = $logger;
        $this->consumerSession = $consumerSession;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @return array|bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->configHelper->isActive()) {
            return $this->setResult('Module is not active', 400);
        }

        $consumerProfileRef = $this->cookieManager->getCookie('consumerProfileRef');

        try {
            $paymentOrderObject = $this->paymentorderHelper->createPaymentorderObject($consumerProfileRef);
            $paymentOrderPurchase = $this->service->init('Paymentorder', 'purchase', $paymentOrderObject);
        } catch (ServiceException $e) {
            $this->logger->Error($e->getMessage());
            return $this->setResult($e->getMessage(), 400);
        }

        try {
            /** @var \PayEx\Api\Service\Data\ResponseInterface $response */
            $response = $paymentOrderPurchase->send();
            $responseData = $response->getResponseData();
            $href = $response->getOperationByRel('view-paymentorder', 'href');

            $this->logger->debug('Updating payment order with ID: ' . $responseData['payment_order']['id']);

            $this->paymentorderHelper->saveQuoteToDB($responseData);

            return $this->setResult($href);
        } catch (\Exception $e) {
            $this->logger->Error($e->getMessage());
            return $this->setResult($e->getMessage(), 400);
        }
    }

    /**
     * Sets JSON result
     *
     * @param string $message
     * @param int $httpCode
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function setResult($message = '', $httpCode = 200)
    {
        $result = $this->resultJsonFactory->create();
        $result->setData([$message]);
        $result->setHttpResponseCode($httpCode);

        return $result;
    }
}
