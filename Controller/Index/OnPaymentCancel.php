<?php

namespace PayEx\PaymentMenu\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\Manager as EventManager;
use PayEx\Core\Logger\Logger;

class OnPaymentCancel extends \Magento\Framework\App\Action\Action
{
    /** @var JsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /** @var EventManager $eventManager  */
    protected $eventManager;

    /** @var Logger $logger */
    protected $logger;

    /**
     * OnPaymentCancel constructor.
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
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->eventManager->dispatch('payex_paymentmenu_before_payment_cancel');
    }
}
