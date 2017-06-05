<?php

namespace Seahinet\Admin\ViewModel\Cms\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Cms\Model\Collection\Page as Collection;
use Seahinet\Cms\Source\Category;
use Seahinet\Lib\Session\Segment;

class Page extends PGrid
{

    protected $action = [
        'getEditAction' => 'Admin\\Cms\\Page::edit',
        'getDeleteAction' => 'Admin\\Cms\\Page::delete'
    ];
    protected $translateDomain = 'cms';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/cms_page/edit/?id=') . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/cms_page/delete/') . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    protected function prepareColumns()
    {
        return [
            'id' => [
                'label' => 'ID',
            ],
            'category_id' => [
                'type' => 'select',
                'label' => 'Category',
                'use4sort' => false,
                'options' => (new Category)->getSourceArray()
            ],
            'title' => [
                'label' => 'Title',
                'class' => 'text-left'
            ],
            'uri_key' => [
                'label' => 'Uri Key',
                'class' => 'text-left',
                'handler' => function ($value) {
                    return rawurldecode($value);
                }
            ],
            'language' => [
                'label' => 'Language',
                'use4sort' => false,
                'use4filter' => false
            ],
            'status' => [
                'label' => 'Status',
                'sortby' => 'cms_page:status',
                'type' => 'select',
                'options' => [
                    'Disabled',
                    'Enabled'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $user = (new Segment('admin'))->get('user');
        $collection = new Collection;
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        if ($this->getQuery('category_id')) {
            $collection->join('cms_category_page', 'cms_category_page.page_id=cms_page.id', [], 'left');
        }
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'created_at';
        }
        return parent::prepareCollection($collection);
    }

}
