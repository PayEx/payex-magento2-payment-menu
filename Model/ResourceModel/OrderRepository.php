<?php

namespace PayEx\PaymentMenu\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use PayEx\PaymentMenu\Api\Data\OrderInterface;
use PayEx\PaymentMenu\Api\Data\OrderSearchResultInterface;
use PayEx\PaymentMenu\Api\Data\OrderSearchResultInterfaceFactory;
use PayEx\PaymentMenu\Api\OrderRepositoryInterface;
use PayEx\PaymentMenu\Model\OrderFactory;
use PayEx\PaymentMenu\Model\Order as OrderModel;
use PayEx\PaymentMenu\Model\ResourceModel\Order as OrderResource;
use PayEx\PaymentMenu\Model\ResourceModel\Order\Collection as OrderCollection;
use PayEx\PaymentMenu\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * OrderRepository constructor.
     * @param OrderFactory $orderFactory
     * @param Order $orderResource
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderSearchResultInterfaceFactory $orderSearchResultInterfaceFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        OrderResource $orderResource,
        OrderCollectionFactory $orderCollectionFactory,
        OrderSearchResultInterfaceFactory $orderSearchResultInterfaceFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->searchResultFactory = $orderSearchResultInterfaceFactory;
        $this->orderResource = $orderResource;
    }

    /**
     * @param int $entityId
     * @return OrderInterface|OrderResource
     * @throws NoSuchEntityException
     */
    public function getById($entityId)
    {
        /** @var OrderModel $order */
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $entityId);
        if (!$order->getId()) {
            throw new NoSuchEntityException(
                __("The order that was requested doesn't exist. Verify the order id and try again.")
            );
        }
        return $order;
    }

    /**
     * @param int $orderId
     * @return OrderInterface|OrderResource
     * @throws NoSuchEntityException
     */
    public function getByOrderId($orderId)
    {
        /** @var OrderModel $order */
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId, 'order_id');
        if (!$order->getId()) {
            throw new NoSuchEntityException(
                __("The order that was requested doesn't exist. Verify the Magento order id and try again.")
            );
        }
        return $order;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(OrderInterface $order)
    {
        /** @var OrderModel $order */
        $this->orderResource->save($order);
        return $order;
    }

    /**
     * @param OrderInterface $order
     * @throws \Exception
     */
    public function delete(OrderInterface $order)
    {
        /** @var OrderModel $order */
        $this->orderResource->delete($order);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return OrderSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var OrderCollection $collection */
        $collection = $this->orderCollectionFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param OrderCollection $collection
     */
    protected function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, OrderCollection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param OrderCollection $collection
     */
    protected function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, OrderCollection $collection)
    {
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            /** @var string $direction */
            $direction = ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param OrderCollection $collection
     */
    protected function addPagingToCollection(SearchCriteriaInterface $searchCriteria, OrderCollection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param OrderCollection $collection
     * @return OrderSearchResultInterface
     */
    protected function buildSearchResult(SearchCriteriaInterface $searchCriteria, OrderCollection $collection)
    {
        /** @var OrderSearchResultInterface $searchResults */
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
