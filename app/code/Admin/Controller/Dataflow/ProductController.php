<?php

namespace Seahinet\Admin\Controller\Dataflow;

use Exception;
use PHPExcel;
use Seahinet\Catalog\Model\Product;
use Seahinet\Catalog\Model\Product\Type;
use Seahinet\Dataflow\Exception\InvalidCellException;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Http\Stream;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Language;
use Seahinet\Lib\Model\Store;
use Seahinet\Lib\Session\Segment;

class ProductController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;

    protected $writer = [
        'csv' => '\PHPExcel_Writer_CSV',
        'xls' => '\PHPExcel_Writer_Excel5',
        'xlsx' => '\PHPExcel_Writer_Excel2007',
        'ods' => '\PHPExcel_Writer_OpenDocument'
    ];
    protected $mime = [
        'csv' => 'text/comma-separated-values',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
    ];
    protected $reader = [
        'csv' => '\PHPExcel_Reader_CSV',
        'xls' => '\PHPExcel_Reader_Excel5',
        'xlsx' => '\PHPExcel_Reader_Excel2007',
        'ods' => '\PHPExcel_Reader_OOCalc'
    ];
    protected $unziper = [
        'gz' => '\gzdecode',
        'bz2' => '\bzdecompress',
        'lzf' => '\lzf_decompress'
    ];
    protected $columns = ['ID', 'Attribute Set', 'Product Type', 'Store'];
    protected $handler = [
        'Attribute Set' => 'getAttributeSet',
        'Product Type' => 'getProductType',
        'Store' => 'getStore'
    ];

    public function importAction()
    {
        return $this->getLayout('dataflow_product_import');
    }

    public function prepareImportAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['zip', 'language_id', 'format', 'truncate', 'skip']);
            $files = $this->getRequest()->getUploadedFile();
            if ($result['error'] === 0 && isset($files['file'])) {
                if (!is_dir(BP . 'var/import/')) {
                    mkdir(BP . 'var/import/', 0750, true);
                }
                $name = 'import-' . $data['language_id'] . gmdate('-Y-m-d-H-i-s.') . $data['format'];
                if ($data['zip']) {
                    $fp = fopen(BP . 'var/import/' . $name, 'wb');
                    fwrite($fp, $this->{unziper[$data['zip']]}($files['file']->getStream()->getContents()));
                    fclose($fp);
                } else {
                    $files['file']->moveTo(BP . 'var/import/' . $name);
                }
                $segment = new Segment('dataflow');
                $segment->set('language_id', $data['language_id'])
                        ->set('format', $data['format'])
                        ->set('truncate', $data['truncate'])
                        ->set('skip', $data['skip'])
                        ->set('file', $name);
                return $this->getLayout('dataflow_product_prepare_import');
            } else {
                return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
            }
        }
        return $this->notFoundAction();
    }

    public function processImportAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            touch(BP . 'maintence');
            $processer = (int) $this->getRequest()->getQuery('processer', 0);
            $segment = new Segment('dataflow');
            try {
                $this->beginTransaction();
                if ($processer === 0 && $segment->get('truncate')) {
                    $tableGateway = $this->getTableGateway('product_entity');
                    $tableGateway->delete(1);
                }
                $reader = new $this->reader[$segment->get('format')];
                $excel = $reader->load(BP . 'var/import/' . $segment->get('file'));
                $skip = (int) $segment->get('skip', 0) + 50 * $processer;
                $sheet = $excel->setActiveSheetIndex(0);
                $attributes = new Attribute;
                $attributes->withLabel($segment->get('language_id'))
                        ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                        ->where(['eav_entity_type.code' => Product::ENTITY_TYPE])
                        ->order('eav_attribute.id');
                $attributes->load();
                $count = 0;
                foreach ($sheet->getRowIterator($skip + 1, $skip + 50) as $row) {
                    $product = new Product($segment->get('language_id'));
                    $col = 0;
                    $flag = true;
                    foreach ($row->getCellIterator() as $cell) {
                        $value = $cell->getValue();
                        if (!is_null($value)) {
                            $product->setData($col < count($this->columns) ?
                                            (isset($this->handler[$this->columns[$col]]) ?
                                                    $this->{$this->handler[$this->columns[$col]]}($value) : $value) :
                                            (in_array($attributes[$col - count($this->columns)]['input'], ['select', 'radio', 'checkbox', 'multiselect']) ?
                                                    $this->getAttributeValue($attributes[$col - count($this->columns)], $value, $segment->get('language_id')) :
                                                    $attributes[$col - count($this->columns)]['code']), $value);
                            $flag = false;
                        }
                        $col ++;
                    }
                    if ($flag) {
                        break;
                    }
                    $product->save([], true);
                    $count ++;
                }
                $this->commit();
                if ($count < 50) {
                    $segment->clear();
                    unlink(BP . 'maintence');
                    return ['finish' => 1, 'message' => $this->translate('Importation complete.')];
                } else {
                    return ['processer' => $processer + 1, 'message' => $this->translate('The %d - %d lines have been imported.', [$skip + 1, $skip + $count])];
                }
            } catch (InvalidCellException $e) {
                unlink(BP . 'maintence');
                $this->rollback();
                return ['message' => $e->getMessage(), 'error' => 1];
            } catch (Exception $e) {
                unlink(BP . 'maintence');
                $this->rollback();
                $this->getContainer()->get('log')->logException($e);
                return ['message' => $e->getMessage(), 'error' => 1];
            }
        }
        return $this->notFoundAction();
    }

    public function templateAction()
    {
        $data = $this->getRequest()->getQuery();
        if (!isset($data['language_id'])) {
            $language = Bootstrap::getLanguage();
            $data['language_id'] = $language->getId();
        } else {
            $language = new Language;
            $language->load($data['language_id']);
        }
        $format = isset($data['format']) && isset($this->writer[$data['format']]) ? $data['format'] : 'csv';
        $name = 'template-' . $data['language_id'] . gmdate('-Y-m-d-H.') . $format;
        if (!file_exists(BP . 'var/export/' . $name)) {
            $attributes = new Attribute;
            $attributes->withLabel($data['language_id'])
                    ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                    ->where(['eav_entity_type.code' => Product::ENTITY_TYPE])
                    ->order('eav_attribute.id');
            $excel = new PHPExcel;
            $sheet = $excel->setActiveSheetIndex(0);
            $col = 0;
            foreach ($this->columns as $label) {
                $sheet->setCellValue($this->getColumn($col++, true) . '1', $this->translate($label, [], null, $language['locale']));
            }
            foreach ($attributes as $attribute) {
                $sheet->setCellValue($this->getColumn($col++, true) . '1', $attribute['label']);
            }
            $className = $this->writer[$format];
            $writer = new $className($excel);
            $writer->save(BP . 'var/export/' . $name);
        }
        return $this->getResponse()->withHeader('Content-Type', $this->mime[$format] . '; charset=UTF-8')
                        ->withHeader('Content-Disposition', 'inline; filename="' . $name . '"')
                        ->withBody(new Stream(fopen(BP . 'var/export/' . $name, 'rb')));
    }

    protected function getColumn($dec)
    {
        $result = chr(65 + ($dec % 26));
        $quotient = floor($dec / 26);
        if ($quotient) {
            return $this->getColumn($quotient) . $result;
        }
        return $result;
    }

    protected function getAttributeValue($attribute, $value, $language)
    {
        if (is_numeric($value)) {
            return [$attribute['code'] => (int) $value];
        }
        if ($attribute['input'] === 'multiselect') {
            $value = explode(',', $value);
        }
        $result = '';
        foreach ((array) $value as $v) {
            $result .= $attribute->getOption($v, $language) . ',';
        }
        return [$attribute['code'] => trim($result, ',')];
    }

    protected function getAttributeSet($value)
    {
        if (is_numeric($value)) {
            return ['attribute_set_id' => (int) $value];
        }
        $set = new Attribute\Set;
        $set->join('eav_entity_type', 'eav_entity_type.id=eav_attribute_set.type_id', [], 'left')
                ->where([
                    'eav_entity_type.code' => Product::ENTITY_TYPE,
                    'name' => $value
        ]);
        if ($set->count()) {
            return ['attribute_set_id' => $set[0]['id']];
        } else {
            throw new InvalidCellException($this->translate('Invalid attribute set name %s', [$value]));
        }
    }

    protected function getProductType($value)
    {
        if (is_numeric($value)) {
            return ['product_type_id' => (int) $value];
        }
        $model = new Type;
        $model->load($value, 'code');
        if ($model->getId()) {
            return ['product_type_id' => $model->getId()];
        } else {
            $model->load($value, 'name');
            if ($model->getId()) {
                return ['product_type_id' => $model->getId()];
            } else {
                throw new InvalidCellException($this->translate('Invalid product type name %s', [$value]));
            }
        }
    }

    protected function getStore($value)
    {
        if (is_numeric($value)) {
            return ['store_id' => (int) $value];
        }
        $model = new Store;
        $model->load($value, 'code');
        if ($model->getId()) {
            return ['store_id' => $model->getId()];
        } else {
            $model->load($value, 'name');
            if ($model->getId()) {
                return ['store_id' => $model->getId()];
            } else {
                throw new InvalidCellException($this->translate('Invalid store name %s', [$value]));
            }
        }
    }

}
