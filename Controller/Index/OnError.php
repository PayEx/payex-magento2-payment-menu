<?php

namespace PayEx\PaymentMenu\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\Manager as EventManager;

use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config as ConfigHelper;

class OnError extends PaymentActionAbstract
{
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventManager $eventManager,
        ConfigHelper $configHelper,
        Logger $logger
    ) {
        parent::__construct($context, $resultJsonFactory, $eventManager, $configHelper, $logger);

        $this->setEventName('error');
        $this->setEventArgs(['origin', 'messageId', 'details']);
        $this->setEventMethod([$this, 'logError']);
    }

    /**
     * @param $origin
     * @param $messageId
     * @param $details
     */
    public function logError($origin, $messageId, $details)
    {
        $this->logger->error(sprintf("PayEx Payment Error [%s]: %s", $messageId, $details));
    }
}
