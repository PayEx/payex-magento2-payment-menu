<?php

namespace PayEx\PaymentMenu\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use PayEx\PaymentMenu\Api\Data\OrderInterface;

class Order extends AbstractExtensibleModel implements OrderInterface
{
    const ID = 'id';
    const PAYMENT_ORDER_ID = 'payment_order_id';
    const DESCRIPTION = 'description';
    const OPERATION = 'operation';
    const STATE = 'state';
    const CURRENCY = 'currency';
    const AMOUNT = 'amount';
    const VAT_AMOUNT = 'vat_amount';
    const REMAINING_CAPTURE_AMOUNT = 'remaining_capture_amount';
    const REMAINING_CANCELLATION_AMOUNT = 'remaining_cancellation_amount';
    const REMAINING_REVERSAL_AMOUNT = 'remaining_cancellation_amount';
    const INITIATING_SYSTEM_USER_AGENT = 'initiating_system_user_agent';
    const ORDER_ID = 'order_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Order::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function setId($orderId)
    {
        $this->setData(self::ID, $orderId);
    }

    /**
     * @return string
     */
    public function getPaymentOrderId()
    {
        return $this->_getData(self::PAYMENT_ORDER_ID);
    }

    /**
     * @param string $paymentOrderId
     * @return void
     */
    public function setPaymentOrderId($paymentOrderId)
    {
        $this->setData(self::PAYMENT_ORDER_ID, $paymentOrderId);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_getData(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->_getData(self::OPERATION);
    }

    /**
     * @param string $operation
     * @return void
     */
    public function setOperation($operation)
    {
        $this->setData(self::OPERATION, $operation);
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->_getData(self::STATE);
    }

    /**
     * @param string $state
     * @return void
     */
    public function setState($state)
    {
        $this->setData(self::STATE, $state);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->_getData(self::CURRENCY);
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency($currency)
    {
        $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->_getData(self::AMOUNT);
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @return int
     */
    public function getVatAmount()
    {
        return $this->_getData(self::VAT_AMOUNT);
    }

    /**
     * @param int $vatAmount
     * @return void
     */
    public function setVatAmount($vatAmount)
    {
        $this->setData(self::VAT_AMOUNT, $vatAmount);
    }

    /**
     * @return int
     */
    public function getRemainingCapturingAmount()
    {
        return $this->_getData(self::REMAINING_CAPTURE_AMOUNT);
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setRemainingCapturingAmount($amount)
    {
        $this->setData(self::REMAINING_CAPTURE_AMOUNT, $amount);
    }

    /**
     * @return int
     */
    public function getRemainingCancellationAmount()
    {
        return $this->_getData(self::REMAINING_CANCELLATION_AMOUNT);
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setRemainingCancellationAmount($amount)
    {
        $this->setData(self::REMAINING_CANCELLATION_AMOUNT, $amount);
    }

    /**
     * @return int
     */
    public function getRemainingReversalAmount()
    {
        return $this->_getData(self::REMAINING_REVERSAL_AMOUNT);
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setRemainingReversalAmount($amount)
    {
        $this->setData(self::REMAINING_REVERSAL_AMOUNT, $amount);
    }

    /**
     * @return string
     */
    public function getInitiatingSystemUserAgent()
    {
        return $this->_getData(self::INITIATING_SYSTEM_USER_AGENT);
    }

    /**
     * @param string $userAgent
     * @return void
     */
    public function setInitiatingSystemUserAgent($userAgent)
    {
        $this->setData(self::INITIATING_SYSTEM_USER_AGENT, $userAgent);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->_getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
