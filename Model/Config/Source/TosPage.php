<?php

namespace PayEx\PaymentMenu\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

class TosPage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $res = [
            [
                'value' => '',
                'label' => __('Please select')
            ]
        ];

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED);

        foreach ($collection as $page) {
            $data['value'] = $page->getData('identifier');
            $data['label'] = $page->getData('title');
            $res[] = $data;
        }

        return $res;
    }
}
