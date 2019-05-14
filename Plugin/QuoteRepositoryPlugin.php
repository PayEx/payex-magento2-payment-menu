<?php

namespace PayEx\PaymentMenu\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository as MagentoQuoteRepository;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;

class QuoteRepositoryPlugin
{
    /** @var QuoteRepository  */
    protected $quoteRepository;

    /** @var Config $config */
    protected $config;

    /** @var Logger $logger */
    protected $logger;

    /**
     * QuoteRepositoryPlugin constructor.
     * @param QuoteRepository $quoteRepository
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Config $config,
        Logger $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param MagentoQuoteRepository $subject
     * @param null $result
     * @param CartInterface $quote
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        /** @noinspection PhpUnusedParameterInspection */
        MagentoQuoteRepository $subject,
        $result,
        CartInterface $quote
    ) {
        if (!$this->config->isActive()) {
            return;
        }

        try {
            $payexQuote = $this->quoteRepository->getByQuoteId($quote->getId());
            $payexQuote->setIsUpdated(1);

            $this->quoteRepository->save($payexQuote);
        } catch (NoSuchEntityException $e) {
            // No Quote Found
            // Do Nothing
        }
    }
}
