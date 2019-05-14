<?php

namespace PayEx\PaymentMenu\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use PayEx\PaymentMenu\Api\Data\QuoteInterface;

interface QuoteRepositoryInterface
{
    /**
     * @param int $entityId
     * @return \PayEx\PaymentMenu\Api\Data\QuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * @param int $quoteId
     * @return \PayEx\PaymentMenu\Api\Data\QuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByQuoteId($quoteId);

    /**
     * @param \PayEx\PaymentMenu\Api\Data\QuoteInterface $quote
     * @return \PayEx\PaymentMenu\Api\Data\QuoteInterface
     */
    public function save(QuoteInterface $quote);

    /**
     * @param \PayEx\PaymentMenu\Api\Data\QuoteInterface $quote
     * @return void
     */
    public function delete(QuoteInterface $quote);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PayEx\PaymentMenu\Api\Data\QuoteSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
