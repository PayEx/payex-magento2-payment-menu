<?php

namespace PayEx\PaymentMenu\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\Manager as EventManager;
use PayEx\Core\Logger\Logger;

class OnPaymentToS extends \Magento\Framework\App\Action\Action
{
    /** @var JsonFactory  */
    protected $resultJsonFactory;

    /** @var EventManager  */
    protected $eventManager;

    /** @var Logger  */
    protected $logger;

    /**
     * OnPaymentToS constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EventManager $eventManager
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventManager $eventManager,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $requestBody = json_decode($this->getRequest()->getContent());
        $this->eventManager->dispatch(
            'payex_paymentmenu_before_payment_tos',
            (array) $requestBody
        );

        try {
            $origin = $requestBody->origin;
            $openUrl = $requestBody->OpenUrl;
        } catch (\Exception $exception) {
            $result = $this->resultJsonFactory->create();
            $result->setData(['result' => 'Wrong request body']);
            $result->setHttpResponseCode(400);
            $this->logger->Error('Wrong request body passed to OnPaymentToS', (array) $requestBody);
            return $result;
        }

        $result = $this->resultJsonFactory->create();

        $result->setData(['result' => 'status changed']);
        return $result;
    }
}
