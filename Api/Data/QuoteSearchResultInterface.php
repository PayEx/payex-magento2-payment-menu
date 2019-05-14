<?php

namespace PayEx\PaymentMenu\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface QuoteSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \PayEx\PaymentMenu\Api\Data\QuoteInterface[]
     */
    public function getItems();

    /**
     * @param \PayEx\PaymentMenu\Api\Data\QuoteInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
