<?php

namespace Seahinet\Admin\Controller\Dataflow;

use Seahinet\Catalog\Model\Product;
use Seahinet\Catalog\Model\Product\Type;
use Seahinet\Dataflow\Exception\InvalidCellException;

class ProductController extends AbstractController
{

    const NAME = 'product';

    protected $columns = ['ID', 'Attribute Set', 'Product Type', 'Store'];
    protected $columnsKey = ['id', 'attribute_set_id', 'product_type_id', 'store_id'];
    protected $handler = [
        'Attribute Set' => 'getAttributeSet',
        'Product Type' => 'getProductType',
        'Store' => 'getStore'
    ];

    public function importAction()
    {
        return $this->getLayout('dataflow_product_import');
    }

    public function exportAction()
    {
        return $this->getLayout('dataflow_product_export');
    }

    public function processImportAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->doImport((int) $this->getRequest()->getQuery('p', 0), '\\Seahinet\\Catalog\\Model\\Product', 'product_entity', Product::ENTITY_TYPE);
        }
        return $this->notFoundAction();
    }

    public function processExportAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->doExport((int) $this->getRequest()->getQuery('p', 0), '\\Seahinet\\Catalog\\Model\\Collection\\Product', Product::ENTITY_TYPE);
        }
        return $this->notFoundAction();
    }

    public function templateAction()
    {
        return $this->getTemplate(Product::ENTITY_TYPE);
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

}
