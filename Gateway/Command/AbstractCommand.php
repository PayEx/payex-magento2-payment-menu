<?php

namespace PayEx\PaymentMenu\Gateway\Command;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use PayEx\Api\Service\Data\ResponseInterface;
use PayEx\Api\Service\Paymentorder\Request\GetPaymentorder as GetPaymentorderRequest;
use PayEx\Api\Service\Resource\Collection\Item\OperationsItem;
use PayEx\Framework\AbstractDataTransferObject;
use PayEx\Api\Service\Resource\Data\ResponseInterface as ResponseResourceInterface;
use PayEx\Client\Model\Service as ClientRequestService;
use PayEx\Client\Helper\Config as ClientConfig;
use PayEx\Core\Exception\PayExException;
use PayEx\Core\Logger\Logger;
use PayEx\PaymentMenu\Api\Data\OrderInterface as PaymentOrderInterface;
use PayEx\PaymentMenu\Api\Data\QuoteInterface as PaymentQuoteInterface;
use PayEx\PaymentMenu\Helper\Config as PaymentMenuConfig;
use PayEx\PaymentMenu\Api\OrderRepositoryInterface as PaymentOrderRepository;
use PayEx\PaymentMenu\Api\QuoteRepositoryInterface as PaymentQuoteRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrderInterface;
use Magento\Sales\Model\OrderRepository as MageOrderRepository;

/**
 * Class AbstractCommand
 *
 * @package PayEx\PaymentMenu\Gateway\Command
 */
abstract class AbstractCommand extends DataObject implements CommandInterface
{
    const GATEWAY_COMMAND_CAPTURE = 'capture';
    const GATEWAY_COMMAND_CANCEL = 'cancel';
    const GATEWAY_COMMAND_REFUND = 'refund';

    const TRANSACTION_ACTION_CAPTURE = 'capture';
    const TRANSACTION_ACTION_CANCEL = 'cancellation';
    const TRANSACTION_ACTION_REFUND = 'reversal';

    /**
     * @var PaymentOrderRepository
     */
    protected $paymentOrderRepo;

    /**
     * @var PaymentQuoteRepository
     */
    protected $paymentQuoteRepo;

    /**
     * @var ClientRequestService
     */
    protected $requestService;

    /**
     * @var MageQuoteRepository
     */
    protected $mageQuoteRepo;

    /**
     * @var MageOrderRepository
     */
    protected $mageOrderRepo;

    /**
     * @var ClientConfig
     */
    protected $clientConfig;

    /**
     * @var PaymentMenuConfig
     */
    protected $paymentMenuConfig;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $cmdTransActionMap = [];

    /**
     * AbstractCommand constructor.
     *
     * @param PaymentOrderRepository $paymentOrderRepo
     * @param PaymentQuoteRepository $paymentQuoteRepo
     * @param ClientRequestService $requestService
     * @param MageQuoteRepository $mageQuoteRepo
     * @param MageOrderRepository $mageOrderRepo
     * @param ClientConfig $clientConfig
     * @param PaymentMenuConfig $paymentMenuConfig
     * @param MessageManager $messageManager
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
        Logger $logger,
        array $data = []
    ) {
        parent::__construct($data);
        $this->paymentOrderRepo = $paymentOrderRepo;
        $this->paymentQuoteRepo = $paymentQuoteRepo;
        $this->requestService = $requestService;
        $this->mageQuoteRepo = $mageQuoteRepo;
        $this->mageOrderRepo = $mageOrderRepo;
        $this->clientConfig = $clientConfig;
        $this->paymentMenuConfig = $paymentMenuConfig;
        $this->messageManager = $messageManager;
        $this->logger = $logger;

        $this->cmdTransActionMap = [
            self::GATEWAY_COMMAND_CAPTURE => self::TRANSACTION_ACTION_CAPTURE,
            self::GATEWAY_COMMAND_CANCEL => self::TRANSACTION_ACTION_CANCEL,
            self::GATEWAY_COMMAND_REFUND => self::TRANSACTION_ACTION_REFUND
        ];
    }

    /**
     * AbstractCommand command
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    abstract public function execute(array $commandSubject);

    /**
     * Get PayEx payment order data
     *
     * @param MagentoOrderInterface|int|string $order
     *
     * @return PaymentOrderInterface|PaymentQuoteInterface|false
     * @throws NoSuchEntityException
     */
    protected function getPayExPaymentData($order)
    {
        if (is_numeric($order)) {
            return $this->paymentOrderRepo->getByOrderId($order);
        }

        if ($order instanceof MagentoOrderInterface) {
            if ($order->getEntityId()) {
                return $this->paymentOrderRepo->getByOrderId($order->getEntityId());
            }
            return $this->paymentQuoteRepo->getByQuoteId($order->getQuoteId());
        }

        $this->logger->error(
            sprintf("Unable to find a PayEx payment matching order:\n%s", print_r($order, true))
        );

        throw new NoSuchEntityException(
            sprintf("Unable to find a PayEx payment matching order %s", $order->getIncrementId())
        );
    }

    /**
     * Get available PayEx payment order operations
     *
     * @param string $paymentOrderId
     *
     * @return \PayEx\Framework\Data\DataObjectCollectionInterface
     * @throws \PayEx\Client\Exception\ServiceException
     */
    protected function getPayExPaymentOperations($paymentOrderId)
    {
        /** @var GetPaymentorderRequest $requestService */
        $requestService = $this->requestService->init('paymentorder', 'getPaymentorder');
        $requestService->setPaymentId($paymentOrderId);

        /** @var ResponseInterface $responseService */
        $responseService = $requestService->send();

        return $responseService->getResponseResource()->getOperations();
    }

    /**
     * Get PayEx payment order resource id
     *
     * @param string $paymentOrderId
     *
     * @return string
     * @throws \PayEx\Client\Exception\ServiceException
     */
    protected function getPayExPaymentResourceId($paymentOrderId)
    {
        $paymentOperations = $this->getPayExPaymentOperations($paymentOrderId);
        $paymentResourceId = $paymentOrderId;

        /** @var OperationsItem $operation */
        foreach ($paymentOperations as $operation) {
            if ($pos = strpos('/psp/paymentorders/', $operation->getHref()) !== false) {
                $paymentResourceId = substr($operation->getHref(), $pos + count($operation->getHref()));
                break;
            }
        }

        return $paymentResourceId;
    }

    /**
     * Get client request service class
     *
     * @param string $service
     * @param string $operation
     * @param AbstractDataTransferObject|null $dataTransferObject
     * @return \PayEx\Api\Service\Data\RequestInterface|string
     * @throws \PayEx\Client\Exception\ServiceException
     */
    protected function getRequestService($service, $operation, $dataTransferObject = null)
    {
        return $this->requestService->init($service, $operation, $dataTransferObject);
    }

    /**
     * Generates a random string
     *
     * @param $length
     * @return bool|string
     */
    protected function generateRandomString($length)
    {
        return substr(str_shuffle(md5(time())), 0, $length);
    }

    /**
     * @param string $command
     * @param string|int|float $amount
     * @param OrderInterface $mageOrder
     * @param PaymentOrderInterface|PaymentQuoteInterface $payExOrder
     * @throws PayExException
     */
    protected function checkRemainingAmount($command, $amount, $mageOrder, $payExOrder)
    {
        $getMethod = 'getRemaining' . ucfirst($this->cmdTransActionMap[$command]) . 'Amount';
        $remainingAmount = (int)call_user_func([$payExOrder, $getMethod]);

        if ($remainingAmount >= ($amount * 100)) {
            return;
        }

        $this->logger->error(
            sprintf(
                "Failed to %s order %s with payex payment order id %s:" .
                "The amount of %s exceeds the remaining %s.",
                $command,
                $mageOrder->getEntityId(),
                $payExOrder->getPaymentOrderId(),
                $amount,
                $remainingAmount
            )
        );

        throw new PayExException(
            new Phrase(
                sprintf(
                    "PayEx %s Error: The amount of %s exceeds the remaining %s.",
                    ucfirst($command),
                    $amount,
                    $remainingAmount
                )
            )
        );
    }

    /**
     * @param string $command
     * @param ResponseResourceInterface $responseResource
     * @param OrderInterface $mageOrder
     * @param PaymentOrderInterface|PaymentQuoteInterface $payExOrder
     * @throws PayExException
     */
    protected function checkResponseResource($command, $responseResource, $mageOrder, $payExOrder)
    {
        if ($responseResource instanceof ResponseResourceInterface) {
            return;
        }

        $this->logger->error(
            sprintf(
                "Failed to %s order %s with payex payment order id %s, response resource:\n%s",
                $command,
                $mageOrder->getEntityId(),
                $payExOrder->getPaymentOrderId(),
                print_r($responseResource, true)
            )
        );

        throw new PayExException(
            new Phrase(
                sprintf(
                    "PayEx %s Error: Failed to parse response for payex payment order %s.",
                    ucfirst($command),
                    $payExOrder->getPaymentOrderId()
                )
            )
        );
    }

    /**
     * @param string $command
     * @param array $responseData
     * @param OrderInterface $mageOrder
     * @param PaymentOrderInterface|PaymentQuoteInterface $payExOrder
     * @throws PayExException
     */
    protected function checkResponseData($command, $responseData, $mageOrder, $payExOrder)
    {
        if (is_array($responseData)
            && isset($responseData[$this->cmdTransActionMap[$command]]['transaction']['state'])
            && $responseData[$this->cmdTransActionMap[$command]]['transaction']['state'] == 'Completed') {
            return;
        }

        $this->logger->error(
            sprintf(
                "Failed to %s order %s with payex payment order id %s, response data:\n%s",
                $command,
                $mageOrder->getEntityId(),
                $payExOrder->getPaymentOrderId(),
                print_r($responseData, true)
            )
        );

        throw new PayExException(
            new Phrase(
                sprintf(
                    "PayEx %s Error: Failed to %s payex payment order %s.",
                    ucfirst($command),
                    $command,
                    $payExOrder->getPaymentOrderId()
                )
            )
        );
    }

    /**
     * @param string $command
     * @param string|int|float $amount
     * @param PaymentOrderInterface|PaymentQuoteInterface $payExOrder
     */
    protected function updateRemainingAmounts($command, $amount, $payExOrder)
    {
        switch ($command) {
            case self::GATEWAY_COMMAND_CAPTURE:
                $payExOrder->setRemainingCapturingAmount($payExOrder->getRemainingCapturingAmount() - ($amount * 100));
                $payExOrder->setRemainingCancellationAmount($payExOrder->getRemainingCapturingAmount());
                $payExOrder->setRemainingReversalAmount($payExOrder->getRemainingReversalAmount() + ($amount * 100));
                break;
            case self::GATEWAY_COMMAND_CANCEL:
                $payExOrder->setRemainingCancellationAmount($payExOrder->getRemainingCancellationAmount() - ($amount * 100));
                $payExOrder->setRemainingCapturingAmount($payExOrder->getRemainingCancellationAmount());
                break;
            case self::GATEWAY_COMMAND_REFUND:
                $payExOrder->setRemainingReversalAmount($payExOrder->getRemainingReversalAmount() - ($amount * 100));
                break;
        }

        $this->paymentOrderRepo->save($payExOrder);
    }
}
