<?php
/**
 * This file is part of the Klarna Kco module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace PayEx\PaymentMenu\Gateway\Command;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Command;
use Magento\Sales\Model\Order;

/**
 * Class Initialize
 *
 * @package PayEx\PaymentMenu\Gateway\Command
 */
class Initialize extends AbstractCommand
{
    const TYPE_AUTH = 'authorization';

    /**
     * Initialize command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface|object $payment */
        $payment = $commandSubject['payment']->getPayment();

        /** @var DataObject|object $stateObject */
        $stateObject = $commandSubject['stateObject'];

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $paymentQuote = $this->getPayExPaymentData($order);

        /** @var \Magento\Store\Model\Store $store */
        $store = $order->getStore();

        $state = Order::STATE_PROCESSING;
        $status = $this->paymentMenuConfig->getProcessedOrderStatus($store);

        if (0 >= $order->getGrandTotal()) {
            $state = Order::STATE_NEW;
            $status = $stateObject->getStatus();
        }

        $stateObject->setState($state);
        $stateObject->setStatus($status);

        $stateObject->setIsNotified(false);

        $transactionId = $paymentQuote->getPaymentOrderId();
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setTransactionId($transactionId)->setIsTransactionClosed(0);
        $payment->addTransaction(self::TYPE_AUTH);

        return null;
    }
}
