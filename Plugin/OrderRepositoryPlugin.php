<?php

namespace PayEx\PaymentMenu\Plugin;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;
use PayEx\Client\Model\Service;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config;
use PayEx\PaymentMenu\Model\OrderFactory;
use PayEx\PaymentMenu\Model\ResourceModel\OrderRepository;
use PayEx\PaymentMenu\Model\ResourceModel\QuoteRepository;

class OrderRepositoryPlugin
{
    /**
     * @var QuoteRepository $quoteRepository
     */
    protected $quoteRepository;

    /**
     * @var OrderRepository $orderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * @var Service $service
     */
    protected $service;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * OrderRepositoryPlugin constructor.
     * @param QuoteRepository $quoteRepository
     * @param OrderRepository $orderRepository
     * @param OrderFactory $orderFactory
     * @param Service $service
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        OrderRepository $orderRepository,
        OrderFactory $orderFactory,
        Service $service,
        Config $config,
        Logger $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->service = $service;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param MagentoOrderRepository $subject
     * @param OrderInterface $mageOrder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave(
        /** @noinspection PhpUnusedParameterInspection */
        MagentoOrderRepository $subject,
        OrderInterface $mageOrder
    ) {
        if (!$this->config->isActive()) {
            return $mageOrder;
        }

        $payExQuote = $this->quoteRepository->getByQuoteId($mageOrder->getQuoteId());

        try {
            $payExOrder = $this->orderRepository->getByOrderId($mageOrder->getEntityId());
        } catch (\Exception $e) {
            $payExOrder = $this->orderFactory->create();
        }

        $payExOrder->setState($payExQuote->getState());
        $payExOrder->setPaymentOrderId($payExQuote->getPaymentOrderId());
        $payExOrder->setCreatedAt($payExQuote->getCreatedAt());
        $payExOrder->setUpdatedAt($payExQuote->getUpdatedAt());
        $payExOrder->setOperation($payExQuote->getOperation());
        $payExOrder->setCurrency($payExQuote->getCurrency());
        $payExOrder->setAmount($payExQuote->getAmount());
        $payExOrder->setVatAmount($payExQuote->getVatAmount());
        $payExOrder->setRemainingCapturingAmount($payExQuote->getRemainingCapturingAmount());
        $payExOrder->setRemainingCancellationAmount($payExQuote->getRemainingCancellationAmount());
        $payExOrder->setRemainingReversalAmount($payExQuote->getRemainingReversalAmount());
        $payExOrder->setDescription($payExQuote->getDescription());
        $payExOrder->setInitiatingSystemUserAgent($_SERVER['HTTP_USER_AGENT']);
        $payExOrder->setOrderId($mageOrder->getEntityId());

        try {
            $this->orderRepository->save($payExOrder);
        } catch (AlreadyExistsException $e) {
            $this->logger->Error('OrderRepositoryPlugin - AlreadyExistsException: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->Error('OrderRepositoryPlugin - Exception: ' . $e->getMessage());
        }

        return $mageOrder;
    }
}
