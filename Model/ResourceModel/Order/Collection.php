<?php

namespace PayEx\PaymentMenu\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'payex_orders_collection';
    protected $_eventObject = 'orders_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('PayEx\Menu\Model\Order', 'PayEx\Menu\Model\ResourceModel\Order');
    }
}
