<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;

class Edit extends AbstractViewModel
{

    public function __construct()
    {
        $this->setTemplate('admin/edit');
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit' : 'Add';
    }

    public function getSaveUrl()
    {
        return $this->getAdminUrl($this->getVariable('save_url'));
    }

    protected function getRendered()
    {
        $this->setVariables([
            'elements' => $this->prepareElements(),
            'title' => $this->getTitle()
        ]);
        return parent::getRendered();
    }

    protected function prepareElements($columns = [])
    {
        $model = $this->getVariable('model');
        if (empty($columns)) {
            $tableColumns = $model->getColumns();
            $values = $model->getArrayCopy();
            foreach ($tableColumns as $column) {
                $columns[$column] = [
                    'type' => 'text',
                    'value' => isset($values[$column]) ? $values[$column] : '',
                    'label' => $column
                ];
            }
        } else {
            $values = $model->getArrayCopy();
            foreach ($columns as $key => $column) {
                $columns[$key]['value'] = isset($values[$key]) ? $values[$key] : '';
            }
        }
        return $columns;
    }

    public function getAttrs($attrs = [])
    {
        $result = '';
        if (!empty($attrs)) {
            foreach ($attrs as $key => $value) {
                $result .= $key . '="' . $value . '" ';
            }
        }
        return $result;
    }

}
