<?php

namespace Seahinet\Admin\ViewModel\Article\Edit\Product;

use Seahinet\Admin\ViewModel\Article\Grid\Product;
use Seahinet\Article\Model\Collection\Product as Collection;
use Seahinet\Article\Model\Product as Model;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;

class Link extends Product
{

    protected $action = [];
    protected $type = '';
    protected $activeIds = null;
    protected $bannedFields = ['id', 'linktype', 'attribute_set'];

    public function __construct()
    {
        $this->setTemplate('admin/article/product/link');
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
        return $this->getAdminUrl('article_product/list/?linktype=' . $this->getType() . '&' . http_build_query($query));
    }

    protected function prepareColumns($columns = [])
    {
        $user = (new Segment('admin'))->get('user');
        return \Seahinet\Admin\ViewModel\Eav\Grid::prepareColumns([
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
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'created_at';
        }
        return parent::prepareCollection($collection);
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
