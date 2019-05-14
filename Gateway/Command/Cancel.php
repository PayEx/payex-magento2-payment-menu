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
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;
use Magento\Framework\App\RequestInterface;

/**
 * Class Cancel
 *
 * @package PayEx\PaymentMenu\Gateway\Command
 */
class Cancel extends AbstractCommand
{
    /**
     * @var RequestInterface|object
     */
    protected $request;

    /**
     * Cancel constructor.
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
     * Cancel command
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

        /** @var MageOrder $order */
        $order = $payment->getOrder();

        $paymentOrder = $this->getPayExPaymentData($order);

        $amount = round(
            $paymentOrder->getRemainingCancellationAmount() / 100,
            PriceCurrencyInterface::DEFAULT_PRECISION
        );

        $this->checkRemainingAmount('cancel', $amount, $order, $paymentOrder);

        $transaction = new Transaction();
        $transaction->setDescription("Cancelling the authorized payment")
            ->setPayeeReference($this->generateRandomString(30));

        $transactionObject = new TransactionObject();
        $transactionObject->setTransaction($transaction);

        $cancelRequest = $this->getRequestService('transaction', 'TransactionCancel', $transactionObject);
        $cancelRequest->setRequestEndpointVars($this->getPayExPaymentResourceId($paymentOrder->getPaymentOrderId()));

        /** @var ResponseInterface $cancelResponse */
        $cancelResponse = $cancelRequest->send();

        $this->checkResponseResource('cancel', $cancelResponse->getResponseResource(), $order, $paymentOrder);

        $cancelResponseData = $cancelResponse->getResponseData();

        $this->checkResponseData('cancel', $cancelResponseData, $order, $paymentOrder);

        $this->updateRemainingAmounts('cancel', $amount, $paymentOrder);

        return null;
    }
}
