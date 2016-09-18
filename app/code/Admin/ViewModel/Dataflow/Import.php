<?php

namespace Seahinet\Admin\ViewModel\Dataflow;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Dataflow\Source\Compression;
use Seahinet\Lib\Source\Language;

class Import extends Edit
{

    protected $hasUploadingFile = true;

    public function getSaveUrl()
    {
        return $this->getAdminUrl($this->getVariable('save_url'));
    }

    public function getTitle()
    {
        return $this->getVariable('title');
    }

    public function getAdditionalButtons()
    {
        return '<button type="button" onclick="window.open(\'' . $this->getAdminUrl($this->getVariable('tmpl_url')) . '?\'+$(\'[name=language_id],[name=format]\').serialize());" class="btn btn-theme">' . $this->translate('Download Template') . '</a>';
    }

    protected function prepareElements($columns = [])
    {
        return [
            'csrf' => [
                'type' => 'csrf'
            ],
            'limit' => [
                'type' => 'label',
                'label' => 'Warning',
                'value' => $this->translate('Your server PHP settings allow you to upload files not more than %s at a time. Please modify post_max_size (currently is %s) and upload_max_filesize (currently is %s) values in php.ini if you want to upload larger files.', [ini_get('upload_max_filesize'), ini_get('post_max_size'), ini_get('upload_max_filesize')])
            ],
            'zip' => [
                'type' => 'select',
                'label' => 'Compression',
                'required' => 'required',
                'options' => (new Compression)->getSourceArray()
            ],
            'language_id' => [
                'type' => 'select',
                'label' => 'Language',
                'required' => 'required',
                'options' => (new Language)->getSourceArray()
            ],
            'format' => [
                'type' => 'select',
                'label' => 'Format',
                'required' => 'required',
                'options' => [
                    'csv' => 'CSV (.csv)',
                    'xls' => 'Excel5 (.xls)',
                    'xlsx' => 'Excel2007 (.xlsx)',
                    'odt' => 'OpenDocument (.odt)'
                ]
            ],
            'truncate' => [
                'type' => 'select',
                'label' => 'Truncate Data',
                'required' => 'required',
                'options' => [
                    'Yes',
                    'No'
                ],
                'comment' => 'Whether existed data would be truncated before importing or not.'
            ],
            'skip' => [
                'type' => 'tel',
                'label' => 'Skip Row',
                'required' => 'required',
                'value' => 1,
                'comment' => 'Including the table head and imported rows'
            ],
            'file' => [
                'type' => 'file',
                'label' => 'File',
                'required' => 'required'
            ]
        ];
    }

}
