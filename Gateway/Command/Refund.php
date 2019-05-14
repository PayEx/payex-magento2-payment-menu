<?php

namespace PayEx\PaymentMenu\Gateway\Command;

use PayEx\Api\Service\Data\ResponseInterface;
use PayEx\Api\Service\Transaction\Resource\Request\Transaction;
use PayEx\Api\Service\Transaction\Resource\Request\TransactionObject;
use PayEx\Client\Model\Service as ClientRequestService;
use PayEx\Client\Helper\Config as ClientConfig;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Helper\Config as PaymentMenuConfig;
use PayEx\PaymentMenu\Api\OrderRepositoryInterface as PaymentOrderRepository;
use PayEx\PaymentMenu\Api\QuoteRepositoryInterface as PaymentQuoteRepository;

use Magento\Sales\Model\Order as MageOrder;
use Magento\Payment\Gateway\Command;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Framework\App\RequestInterface;

/**
 * Class Refund
 *
 * @package PayEx\PaymentMenu\Gateway\Command
 */
class Refund extends AbstractCommand
{
    /**
     * @var RequestInterface|object
     */
    protected $request;

    /**
     * Refund constructor.
     *
     * @param PaymentOrderRepository $paymentOrderRepo
     * @param PaymentQuoteRepository $paymentQuoteRepo
     * @param ClientRequestService $requestService
     * @param MageQuoteRepository $mageQuoteRepo
     * @param MageOrderRepository $mageOrderRepo
     * @param ClientConfig $clientConfig
     * @param PaymentMenuConfig $paymentMenuConfig
     * @param MessageManager $messageManager
     * @param RequestInterface $request
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        PaymentOrderRepository $paymentOrderRepo,
        PaymentQuoteRepository $paymentQuoteRepo,
        ClientRequestService $requestService,
        MageQuoteRepository $mageQuoteRepo,
        MageOrderRepository $mageOrderRepo,
        ClientConfig $clientConfig,
        PaymentMenuConfig $paymentMenuConfig,
        MessageManager $messageManager,
        RequestInterface $request,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct(
            $paymentOrderRepo,
            $paymentQuoteRepo,
            $requestService,
            $mageQuoteRepo,
            $mageOrderRepo,
            $clientConfig,
            $paymentMenuConfig,
            $messageManager,
            $logger,
            $data
        );

        $this->request = $request;
    }

    /**
     * Refund command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \PayEx\Core\Exception\PayExException
     * @throws \PayEx\Client\Exception\ServiceException
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface|object $payment */
        $payment = $commandSubject['payment']->getPayment();
        $amount = $commandSubject['amount'] + 0;

        /** @var MageOrder $order */
        $order = $payment->getOrder();

        $paymentOrder = $this->getPayExPaymentData($order);

        $this->checkRemainingAmount('refund', $amount, $order, $paymentOrder);

        $transaction = new Transaction();
        $transaction->setDescription("Reversing the captured payment")
            ->setAmount($amount * 100)
            ->setVatAmount($order->getBaseTaxAmount() * 100)
            ->setPayeeReference($this->generateRandomString(30));

        $transactionObject = new TransactionObject();
        $transactionObject->setTransaction($transaction);

        $reversalRequest = $this->getRequestService('transaction', 'TransactionReversal', $transactionObject);
        $reversalRequest->setRequestEndpointVars($this->getPayExPaymentResourceId($paymentOrder->getPaymentOrderId()));

        /** @var ResponseInterface $reversalResponse */
        $reversalResponse = $reversalRequest->send();

        $this->checkResponseResource('refund', $reversalResponse->getResponseResource(), $order, $paymentOrder);

        $reversalResponseData = $reversalResponse->getResponseData();

        $this->checkResponseData('refund', $reversalResponseData, $order, $paymentOrder);

        $this->updateRemainingAmounts('refund', $amount, $paymentOrder);

        return null;
    }
}
