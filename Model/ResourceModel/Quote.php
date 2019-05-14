<?php
namespace PayEx\PaymentMenu\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Quote extends AbstractDb
{
    const MAIN_TABLE = 'payex_quotes';
    const ID_FIELD_NAME = 'id';

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
