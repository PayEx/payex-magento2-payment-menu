<?php

namespace PayEx\PaymentMenu\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface OrderSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \PayEx\PaymentMenu\Api\Data\OrderInterface[]
     */
    public function getItems();

    /**
     * @param \PayEx\PaymentMenu\Api\Data\OrderInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
