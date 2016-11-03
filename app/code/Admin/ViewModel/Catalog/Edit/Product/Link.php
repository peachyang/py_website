<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

use Seahinet\Admin\ViewModel\Catalog\Grid\Product;
use Seahinet\Catalog\Model\Collection\Product as Collection;
use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;

class Link extends Product
{

    protected $action = [];
    protected $type = '';
    protected $activeIds = null;
    protected $bannedFields = ['id', 'linktype', 'attribute_set', 'product_type'];

    public function __construct()
    {
        $this->setTemplate('admin/catalog/product/link');
    }

    public function getType()
    {
        return $this->type ?: $this->getQuery('linktype');
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->query = [];
        return $this;
    }

    public function getOrderByUrl($attr)
    {
        $query = $this->getQuery();
        if (isset($query['asc'])) {
            if ($query['asc'] == $attr) {
                unset($query['asc']);
                $query['desc'] = $attr;
            } else {
                $query['asc'] = $attr;
            }
        } else if (isset($query['desc'])) {
            if ($query['desc'] == $attr) {
                unset($query['desc']);
                $query['asc'] = $attr;
            } else {
                $query['desc'] = $attr;
            }
        } else {
            $query['asc'] = $attr;
        }
        return $this->getAdminUrl('catalog_product/list/?linktype=' . $this->getType() . '&' . http_build_query($query));
    }

    protected function prepareColumns($columns = [])
    {
        $user = (new Segment('admin'))->get('user');
        return \Seahinet\Admin\ViewModel\Eav\Grid::prepareColumns([
                    'store_id' => ($user->getStore() ? [
                'type' => 'hidden',
                'value' => $user->getStore()->getId(),
                'use4sort' => false,
                'use4filter' => false
                    ] : [
                'type' => 'select',
                'options' => (new Store)->getSourceArray(),
                'label' => 'Store'
                    ]),
                    'name' => [
                        'label' => 'Name',
                        'type' => 'text'
                    ],
                    'sku' => [
                        'label' => 'SKU',
                        'type' => 'text'
                    ]
        ]);
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->getSelect()->where->notEqualTo('id', $this->getRequest()->getQuery('id'));
        return parent::prepareCollection(new Collection);
    }

    public function getActiveIds()
    {
        if (is_null($this->activeIds)) {
            $collection = (new Model)->setId($this->getRequest()->getQuery('id'))
                    ->getLinkedProducts($this->getType());
            $this->activeIds = [];
            if (count($collection)) {
                foreach ($collection->toArray() as $item) {
                    $this->activeIds[] = $item['id'];
                }
            }
        }
        return $this->activeIds;
    }

}
