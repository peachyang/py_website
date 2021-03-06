<?php

namespace Seahinet\Admin\ViewModel\Article\Grid;

use Seahinet\Admin\ViewModel\Eav\Grid as PGrid;
use Seahinet\Article\Model\Collection\Product as Collection;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;

class Product extends PGrid
{

    protected $action = [
        'getEditAction' => 'Admin\\Article\\Product::edit',
        'getDeleteAction' => 'Admin\\Article\\Product::delete'
    ];
    protected $messAction = [
        'getExportAction' => 'Admin\\Dataflow\\Product::export'
    ];
    protected $translateDomain = 'article';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/article_product/edit/?id=') . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/article_product/delete/') . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getExportAction()
    {
        return '<a href="javascript:void(0);" onclick="var id=\'\';$(\'.grid .table [type=checkbox][value]:checked\').each(function(){id+=$(this).val()+\',\';});location.href=\'' .
                $this->getAdminUrl('dataflow_product/export/?id=') . '\'+id.replace(/\,$/,\'\');" title="' . $this->translate('Export') .
                '"><span>' . $this->translate('Export') . '</span></a>';
    }

    protected function prepareColumns($columns = [])
    {
        $user = (new Segment('admin'))->get('user');
        return parent::prepareColumns([
                    'id' => [
                        'label' => 'ID',
                    ],
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
        if (is_null($collection)) {
            $collection = new Collection;
            $user = (new Segment('admin'))->get('user');
            if ($user->getStore()) {
                $collection->where(['store_id' => $user->getStore()->getId()]);
            }
            if (!$this->getQuery('desc')) {
                $this->query['desc'] = 'created_at';
            }
        }
        return parent::prepareCollection($collection);
    }

}
