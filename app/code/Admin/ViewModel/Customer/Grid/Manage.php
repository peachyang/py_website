<?php

namespace Seahinet\Admin\ViewModel\Customer\Grid;

use Seahinet\Admin\ViewModel\Eav\Grid as PGrid;
use Seahinet\Customer\Model\Collection\Customer as Collection;

class Manage extends PGrid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getDeleteAction'];
    protected $messAction = ['getExportAction'];
    protected $translateDomain = 'customer';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getExportAction()
    {
        return '<a href="javascript:void(0);" onclick="var id=\'\';$(\'.grid .table [type=checkbox][value]:checked\').each(function(){id+=$(this).val()+\',\';});location.href=\'' .
                $this->getAdminUrl('dataflow_customer/export/?id=') . '\'+id.replace(/\,$/,\'\');" title="' . $this->translate('Export') .
                '"><span>' . $this->translate('Export') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/customer_manage/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/customer_manage/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareCollection($collection = null)
    {
        if (!$this->getQuery('desc')) {
            $this->query['desc'] = 'created_at';
        }
        return parent::prepareCollection(new Collection);
    }

}
