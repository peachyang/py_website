<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\Template;

class Edit extends Template
{

    protected $hasTitle = true;
    protected $hasUploadingFile = false;

    public function __construct()
    {
        $this->setTemplate('admin/edit');
    }

    /**
     * Whether there is uploading file or not
     * To change enctype attribute of form element
     * 
     * @param bool $hasUploadingFile
     * @return Edit
     */
    public function hasUploadingFile($hasUploadingFile = null)
    {
        if (is_bool($hasUploadingFile)) {
            $this->hasUploadingFile = $hasUploadingFile;
            return $this;
        }
        return $this->hasUploadingFile;
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
                    } else if (strpos($key, '[]') && isset($values[substr($key, 0, -2)])) {
                        $columns[$key]['value'] = $values[substr($key, 0, -2)];
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
        $class = empty($item['view_model']) ? '\\Seahinet\\Lib\\ViewModel\\Template' : $item['view_model'];
        $box = new $class;
        $box->setVariables([
            'key' => $key,
            'item' => $item,
            'parent' => $this
        ]);
        $box->setTemplate('page/renderer/' . $item['type']);
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
