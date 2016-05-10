<?php

namespace Seahinet\Admin\ViewModel\Email;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Email\Model\Collection\Subscriber as Collection;

class Subscriber extends Grid
{

    protected $deleteUrl = '';
    protected $action = ['getDeleteAction'];
    protected $translateDomain = 'email';

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/email_subscriber/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        return [
            'email' => [
                'label' => 'Email',
            ],
            'status' => [
                'label' => 'Status',
                'sortby' => 'status',
                'type' => 'select',
                'options' => [
                    'Unsubscribed',
                    'Subscribed'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        return parent::prepareCollection($collection);
    }

}
