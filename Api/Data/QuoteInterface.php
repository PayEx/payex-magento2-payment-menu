<?php

namespace PayEx\PaymentMenu\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface QuoteInterface extends ExtensibleDataInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $entityId
     * @return void
     */
    public function setId($entityId);

    /**
     * @return string
     */
    public function getPaymentOrderId();

    /**
     * @param string $paymentOrderId
     * @return void
     */
    public function setPaymentOrderId($paymentOrderId);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getOperation();

    /**
     * @param string $operation
     * @return void
     */
    public function setOperation($operation);

    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $state
     * @return void
     */
    public function setState($state);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency($currency);

    /**
     * @return int
     */
    public function getAmount();

    /**
     * @param int $amount
     * @return void
     */
    public function setAmount($amount);

    /**
     * @return int
     */
    public function getVatAmount();

    /**
     * @param int $vatAmount
     * @return void
     */
    public function setVatAmount($vatAmount);

    /**
     * @return int
     */
    public function getRemainingCapturingAmount();

    /**
     * @param int $amount
     * @return void
     */
    public function setRemainingCapturingAmount($amount);

    /**
     * @return int
     */
    public function getRemainingCancellationAmount();

    /**
     * @param int $amount
     * @return void
     */
    public function setRemainingCancellationAmount($amount);

    /**
     * @return int
     */
    public function getRemainingReversalAmount();

    /**
     * @param int $amount
     * @return void
     */
    public function setRemainingReversalAmount($amount);

    /**
     * @return string
     */
    public function getPayerToken();

    /**
     * @param string $payerToken
     * @return void
     */
    public function setPayerToken($payerToken);

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId($quoteId);

    /**
     * @return int
     */
    public function getIsUpdated();

    /**
     * @param int $isUpdated
     * @return void
     */
    public function setIsUpdated($isUpdated);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt($updatedAt);
}
