<?php

namespace Seahinet\Admin\ViewModel\Article\Edit;

use Seahinet\Admin\ViewModel\Edit as PEdit;
use Seahinet\Article\Model\Collection\Product\Rating as RatingCollection;
use Seahinet\Article\Source\Product;
use Seahinet\Customer\Source\Customer;
use Seahinet\Lib\Source\Language;

class Review extends PEdit
{

    public function getSaveUrl()
    {
        return $this->getAdminUrl('article_product_review/save/');
    }

    public function getDeleteUrl()
    {
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            return $this->getAdminUrl('article_product_review/delete/');
        }
        return FALSE;
    }

    public function getTitle()
    {
        return $this->getQuery('id') ? 'Edit Review' : 'Add New Review';
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'id' => [
                'label' => 'ID',
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'article_id' => [
                'type' => 'select',
                'label' => 'Product',
                'required' => 'required',
                'options' => (new Product)->getSourceArray()
            ],
            'customer_id' => [
                'type' => 'select',
                'label' => 'Customer',
                'options' => (new Customer)->getSourceArray()
            ],
            'language_id' => [
                'type' => 'select',
                'label' => 'Language',
                'options' => (new Language)->getSourceArray()
            ]
        ];
        $collection = new RatingCollection;
        $model = $this->getVariable('model');
        if ($model && $model->getId()) {
            $collection->join('review_rating', 'review_rating.rating_id=rating.id', ['value'], 'left')
                    ->where(['review_rating.review_id' => $model->getId()]);
        }
        foreach ($collection as $item) {
            $column = [
                'type' => 'radio',
                'label' => $item['title'],
                'options' => [
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5'
                ]
            ];
            if (isset($item['value'])) {
                $column['value'] = (string) (float) $item['value'];
            }
            $columns['rating[' . $item['id'] . ']'] = $column;
        }
        $columns += [
            'subject' => [
                'type' => 'text',
                'label' => 'Subject'
            ],
            'content' => [
                'type' => 'textarea',
                'label' => 'Content'
            ],
            'reply' => [
                'type' => 'textarea',
                'label' => 'Reply'
            ],
            'status' => [
                'type' => 'select',
                'label' => 'Status',
                'required' => 'required',
                'options' => [
                    1 => 'Enabled',
                    0 => 'Disabled'
                ]
            ]
        ];
        return parent::prepareElements($columns);
    }

}
