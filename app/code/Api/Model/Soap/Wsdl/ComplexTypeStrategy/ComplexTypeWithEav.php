<?php

namespace Seahinet\Api\Model\Soap\Wsdl\ComplexTypeStrategy;

use Seahinet\Api\Exception\Soap as Exception;
use Seahinet\Api\Model\Soap\Wsdl;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Eav\Type;

class ComplexTypeWithEav extends DefaultComplexType
{

    /**
     * {@inheritDoc}
     */
    public function addComplexType($type)
    {
        if (class_exists($type)) {
            return parent::addComplexType($type);
        }
        if (($soapType = $this->scanRegisteredTypes($type)) !== null) {
            return $soapType;
        }
        $entityType = new Type;
        $entityType->load($type, 'code');
        if ($entityType->getId()) {
            $dom = $this->getContext()->toDomDocument();
            $soapTypeName = $this->getContext()->translateType($type);
            $soapType = Wsdl::TYPES_NS . ':' . $soapTypeName;
            $this->getContext()->addType($type, $soapType);
            $complexType = $dom->createElementNS(Wsdl::XSD_NS_URI, 'complexType');
            $complexType->setAttribute('name', $soapTypeName);
            $all = $dom->createElementNS(Wsdl::XSD_NS_URI, 'all');
            $attributes = new Attribute;
            $attributes->columns(['code', 'type', 'is_required'])
                    ->where([
                        'type_id' => $entityType->getId()
                    ])->where->notEqualTo('input', 'password');
            $attributes->load(true, true);
            $element = $dom->createElementNS(Wsdl::XSD_NS_URI, 'element');
            $element->setAttribute('name', 'sessionId');
            $element->setAttribute('type', $this->transformType('string'));
            $all->appendChild($element);
            foreach ($attributes as $attribute) {
                $element = $dom->createElementNS(Wsdl::XSD_NS_URI, 'element');
                $element->setAttribute('name', $attribute['code']);
                $element->setAttribute('type', $this->transformType($attribute['type']));
                if (!$attribute['is_required']) {
                    $element->setAttribute('nillable', 'true');
                }
                $all->appendChild($element);
            }
            $complexType->appendChild($all);
            $this->getContext()->getSchema()->appendChild($complexType);
            return $soapType;
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
                    'Cannot add a complex type %s that is not an object or where '
                    . 'class could not be found in "ComplexTypeWithEav" strategy.', $type
            ));
        }
    }

    protected function transformType($type)
    {
        $context = $this->getContext();
        switch ($type) {
            case 'text':
            case 'varchar':
            case 'datetime':
                return $context->getType('string');
            case 'decimal':
                return $context->getType('float');
            case 'int':
                return $context->getType('int');
            default:
                return $context->getType($type);
        }
    }

}
