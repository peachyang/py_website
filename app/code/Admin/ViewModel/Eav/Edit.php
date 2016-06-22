<?php

namespace Seahinet\Admin\ViewModel\Eav;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Eav\Attribute as AttributeModel;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Eav\Attribute\Set;
use Seahinet\Lib\Source\Store;

abstract class Edit extends PEdit
{

    protected $group = false;
    protected $tabs = null;

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function getTabs()
    {
        if (is_null($this->tabs)) {
            $this->tabs = $this->getChild('tabs');
        }
        return $this->tabs;
    }

    protected function prepareElements($columns = [])
    {
        $attributes = new Attribute;
        $languageId = Bootstrap::getLanguage()->getId();
        $model = $this->getVariable('model');
        $attributes->withGroup()
                ->withSet()
                ->withLabel($languageId)
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'right')
                ->order('sort_order, eav_attribute.id')
                ->where(['eav_entity_type.code' => $model::ENTITY_TYPE, 'attribute_set_id' => $this->getQuery('attribute_set', $model['attribute_set_id'])]);
        if ($this->group) {
            $columns = [];
            $attributes->where(['eav_attribute_group.name' => $this->group]);
        } else if (empty($columns)) {
            $user = (new Segment('admin'))->get('user');
            $columns = [
                'id' => [
                    'type' => 'hidden'
                ],
                'csrf' => [
                    'type' => 'csrf'
                ],
                'increment_id' => ($this->getQuery('id') ? [
                    'type' => 'label',
                    'label' => 'Human-Friendly ID'
                        ] : [
                    'type' => 'hidden'
                        ]),
                'attribute_set_id' => [
                    'type' => 'select',
                    'label' => 'Attribute Set',
                    'required' => 'required',
                    'options' => (new Set)->getSourceArray(),
                    'value' => $this->getQuery('attribute_set', $model['attribute_set_id']),
                    'attr' => [
                        'onchange' => 'location.href=\'' . $this->getUri()->withQuery(http_build_query($query = array_diff_key($this->getQuery(), ['attribute_set' => '']))) . (empty($query) ? '?' : '&') . 'attribute_set=\'+this.value;'
                    ]
                ],
                'store_id' => ($user->getStore() ? [
                    'type' => 'hidden',
                    'value' => $user->getStore()->getId()
                        ] : [
                    'type' => 'select',
                    'options' => (new Store)->getSourceArray(),
                    'label' => 'Store',
                    'required' => 'required'
                        ]),
                'status' => [
                    'type' => 'select',
                    'label' => 'Status',
                    'options' => [
                        1 => 'Enabled',
                        0 => 'Disabled'
                    ],
                    'required' => 'required'
                ]
            ];
        }
        $groups = [];
        foreach ($attributes as $attribute) {
            if (!$this->group && !in_array($attribute['attribute_group_id'], $groups)) {
                $this->getTabs()->addTab('attribute_group_' . $attribute['attribute_group_id'], $attribute['attribute_group']);
                $this->addChild('attribute_group_' . $attribute['attribute_group_id'], (new static())->setGroup($attribute['attribute_group']));
                $groups[] = $attribute['attribute_group_id'];
            }
            $columns[$attribute['code']] = [
                'label' => $attribute['label'],
                'type' => $attribute['input'],
                'class' => $attribute['validation'],
                'options' => (new AttributeModel($attribute))->getOptions($languageId)
            ];
            if ($attribute['is_required']) {
                $columns[$attribute['code']]['required'] = 'required';
            }
        }
        return parent::prepareElements($columns);
    }

}
