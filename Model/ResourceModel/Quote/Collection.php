<?php

namespace PayEx\PaymentMenu\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'payex_quotes_collection';
    protected $_eventObject = 'quotes_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('PayEx\PaymentMenu\Model\Quote', 'PayEx\PaymentMenu\Model\ResourceModel\Quote');
    }
}
