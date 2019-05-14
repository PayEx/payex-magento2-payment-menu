<?php

namespace PayEx\PaymentMenu\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use PayEx\PaymentMenu\Api\Data\OrderInterface;

interface OrderRepositoryInterface
{
    /**
     * @param int $entityId
     * @return \PayEx\PaymentMenu\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * @param int $orderId
     * @return \PayEx\PaymentMenu\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderId($orderId);

    /**
     * @param \PayEx\PaymentMenu\Api\Data\OrderInterface $order
     * @return \PayEx\PaymentMenu\Api\Data\OrderInterface
     */
    public function save(OrderInterface $order);

    /**
     * @param \PayEx\PaymentMenu\Api\Data\OrderInterface $order
     * @return void
     */
    public function delete(OrderInterface $order);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PayEx\PaymentMenu\Api\Data\OrderSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
