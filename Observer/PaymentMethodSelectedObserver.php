<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Quote\Model\Quote as MageQuote;
use Magento\Store\Model\StoreManagerInterface;
use PayEx\Api\Client\Exception;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderObject;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderPayeeInfo;
use PayEx\Api\Service\Paymentorder\Resource\PaymentorderUrl;
use PayEx\Api\Service\Paymentorder\Resource\Request\Paymentorder;
use PayEx\Client\Exception\ServiceException;
use PayEx\Client\Helper\Config as ClientConfig;
use PayEx\Client\Model\Service;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\QuoteFactory;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;

class PaymentMethodSelectedObserver implements ObserverInterface
{
    /**
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var Service $service
     */
    protected $service;

    /**
     * @var QuoteFactory $quoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteRepository $quoteRepository
     */
    protected $quoteRepository;

    /**
     * @var Resolver $localeResolver
     */
    protected $localeResolver;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var ClientConfig $clientConfig
     */
    protected $clientConfig;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * PaymentMethodSelected constructor.
     * @param Session $checkoutSession
     * @param QuoteFactory $quoteFactory
     * @param QuoteRepository $quoteRepository
     * @param Service $service
     * @param Resolver $localeResolver
     * @param Config $config
     * @param ClientConfig $clientConfig
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     */
    public function __construct(
        Session $checkoutSession,
        QuoteFactory $quoteFactory,
        QuoteRepository $quoteRepository,
        Resolver $localeResolver,
        Service $service,
        Config $config,
        ClientConfig $clientConfig,
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->localeResolver = $localeResolver;
        $this->service = $service;
        $this->config = $config;
        $this->clientConfig = $clientConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }
    }
}
