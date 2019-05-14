<?php

namespace PayEx\PaymentMenu\Observer;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteRepository as MagentoQuoteRepository;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use PayEx\Checkin\Model\ConsumerSession;
use PayEx\Client\Model\Service;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\OrderFactory;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;

class AfterPaymentCompletedObserver implements ObserverInterface
{
    /** @var StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @var CustomerFactory $customerFactory */
    protected $customerFactory;

    /** @var CustomerRepositoryInterface $customerRepo */
    protected $customerRepo;

    /** @var Session $checkoutSession */
    protected $checkoutSession;

    /** @var ConsumerSession $consumerSession */
    protected $consumerSession;

    /** @var Service $service */
    protected $service;

    /** @var OrderRepository $orderRepo */
    protected $orderRepo;

    /** @var MagentoOrderRepository $magentoOrderRepo */
    protected $magentoOrderRepo;

    /** @var OrderFactory $orderFactory */
    protected $orderFactory;

    /** @var Quote $magentoQuote */
    protected $magentoQuote;

    /** @var QuoteManagement $quoteManagement */
    protected $quoteManagement;

    /** @var QuoteRepository $quoteRepo */
    protected $quoteRepo;

    /** @var MagentoQuoteRepository $magentoQuoteRepo */
    protected $magentoQuoteRepo;

    /** @var EventManager $eventManager */
    protected $eventManager;

    /** @var Config $config */
    protected $config;

    /** @var Logger $logger */
    protected $logger;

    /**
     * AfterPaymentCompletedObserver constructor.
     * @param Session $checkoutSession
     * @param OrderRepository $orderRepo
     * @param Service $service
     * @param MagentoOrderRepository $magentoOrderRepo
     * @param OrderFactory $orderFactory
     * @param Quote $magentoQuote
     * @param QuoteManagement $quoteManagement
     * @param QuoteRepository $quoteRepository
     * @param EventManager $eventManager
     * @param StoreManagerInterface $storeManager
     * @param ConsumerSession $consumerSession
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepo
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepository $orderRepo,
        Service $service,
        MagentoOrderRepository $magentoOrderRepo,
        OrderFactory $orderFactory,
        Quote $magentoQuote,
        QuoteManagement $quoteManagement,
        QuoteRepository $quoteRepository,
        EventManager $eventManager,
        StoreManagerInterface $storeManager,
        ConsumerSession $consumerSession,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepo,
        Config $config,
        Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->service = $service;
        $this->orderRepo = $orderRepo;
        $this->magentoOrderRepo = $magentoOrderRepo;
        $this->orderFactory = $orderFactory;
        $this->magentoQuote = $magentoQuote;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepo = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->consumerSession = $consumerSession;
        $this->customerFactory = $customerFactory;
        $this->customerRepo = $customerRepo;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        $this->logger->Debug('payex_paymentmenu_after_payment_completed event has been triggered');
    }
}
