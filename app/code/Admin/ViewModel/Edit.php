<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;
use Seahinet\Lib\ViewModel\Template;

class Edit extends AbstractViewModel
{

    protected $hasTitle = true;

    public function __construct()
    {
        $this->setTemplate('admin/edit');
    }

    /**
     * Has form title
     * 
     * @param bool $hasTitle
     * @return Edit|bool
     */
    public function hasTitle($hasTitle = null)
    {
        if (is_bool($hasTitle)) {
            $this->hasTitle = $hasTitle;
            return $this;
        }
        return $this->hasTitle;
    }

    /**
     * Get title for form
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit' : 'Add';
    }

    /**
     * Get saving url for form
     * 
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getAdminUrl($this->getVariable('save_url'));
    }

    /**
     * Get deleting url for current record
     * 
     * @return string|bool
     */
    public function getDeleteUrl()
    {
        return false;
    }

    /**
     * {@inhertdoc}
     */
    protected function getRendered($template)
    {
        $this->setVariables([
            'elements' => $this->prepareElements(),
            'title' => $this->getTitle()
        ]);
        return parent::getRendered($template);
    }

    /**
     * Add value to each column
     * 
     * @param array $columns
     * @return array
     */
    protected function prepareElements($columns = [])
    {
        $model = $this->getVariable('model');
        if ($model) {
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
                    if (!isset($columns[$key]['value'])) {
                        $columns[$key]['value'] = isset($values[$key]) ? $values[$key] : '';
                    }
                }
                if (!empty($values['language']) && isset($columns['language_id[]'])) {
                    $columns['language_id[]']['value'] = array_keys($values['language']);
                }
            }
        }
        return $columns;
    }

    /**
     * Get attributes' HTML code of element
     * 
     * @param array $attrs
     * @return string
     */
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

    /**
     * Get input box for different form elements
     * 
     * @param string $key
     * @param array $item
     * @return Template
     */
    public function getInputBox($key, $item)
    {
        if (empty($item['type'])) {
            return '';
        }
        $box = new Template;
        $box->setVariables([
            'key' => $key,
            'item' => $item,
            'parent' => $this
        ]);
        $box->setTemplate('admin/renderer/' . $item['type']);
        return $box;
    }

    /**
     * Get additional buttons' HTML code
     * 
     * @return string
     */
    public function getAdditionalButtons()
    {
        return '';
    }

}
