<?php

namespace Seahinet\Admin\ViewModel\Email;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Email\Model\Collection\Queue as Collection;

class Queue extends Grid
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
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/email_queue/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        return [
            'from' => [
                'type' => 'email',
                'label' => 'Mail From',
            ],
            'to' => [
                'type' => 'email',
                'label' => 'Rcpt To',
            ],
            'scheduled_at' => [
                'label' => 'Scheduled time',
                'type' => 'datetime',
            ],
            'status' => [
                'label' => 'Status',
                'sortby' => 'status',
                'type' => 'select',
                'options' => [
                    'Pending',
                    'Success'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'created_at';
        }
        return parent::prepareCollection(new Collection);
    }

}
