<?php

namespace Seahinet\Admin\Controller\Dataflow;

use PHPExcel;
use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Controller\AuthActionController;

class ProductController extends AuthActionController
{

    protected $writer = [
        'csv' => '\PHPExcel_Writer_CSV',
        'xls' => '\PHPExcel_Writer_Excel5',
        'xlsx' => '\PHPExcel_Writer_Excel2007',
        'odt' => '\PHPExcel_Writer_OpenDocument'
    ];
    protected $mime = [
        'csv' => 'text/comma-separated-values',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'odt' => 'application/vnd.oasis.opendocument.spreadsheet'
    ];

    public function importAction()
    {
        return $this->getLayout('dataflow_product_import');
    }

    public function templateAction()
    {
        $data = $this->getRequest()->getQuery();
        if (!isset($data['language_id'])) {
            $data['language_id'] = Bootstrap::getLanguage()->getId();
        }
        $format = isset($data['format']) && isset($this->writer[$data['format']]) ? $data['format'] : 'csv';
        $name = 'template-' . $data['language_id'] . gmdate('Y-m-d-H-i-s') . '.' . $format;
        if (!file_exists(BP . 'var/export/' . $name)) {
            $attributes = new Attribute;
            $attributes->withLabel($data['language_id'])
                    ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                    ->where(['eav_entity_type.code' => Product::ENTITY_TYPE])
                    ->order('eav_attribute.id');
            $excel = new PHPExcel;
            $excel->setActiveSheetIndex(0);
            $sheet = $excel->getActiveSheet();
            $col = 65;
            foreach ($attributes as $attribute) {
                $sheet->setCellValue(chr($col++) . '1', $attribute['label']);
            }
            header('Content-Type: ' . $this->mime[$format] . '; charset=UTF-8');
            header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
            header('Pragma: public');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Disposition: inline; filename="' . $name . '"');
            $className = $this->writer[$format];
            $writer = new $className($excel);
            $writer->save(BP . 'var/export/' . $name);
        }
        echo file_get_contents(BP . 'var/export/' . $name);
        die();
    }

}
