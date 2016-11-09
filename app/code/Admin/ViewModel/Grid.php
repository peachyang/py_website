<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\ViewModel\Template;

class Grid extends Template
{

    use \Seahinet\Lib\Traits\Filter;

    protected $count = null;
    protected $action = [];
    protected $messAction = [];
    protected $translateDomain = null;
    protected $bannedFields = [];

    public function __construct()
    {
        $this->setTemplate('admin/grid');
    }

    /**
     * Get current url
     * 
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->getUri()->withQuery('')->withFragment('')->__toString();
    }

    /**
     * Get operations for each row
     * 
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get operations for multiple rows
     * 
     * @return array
     */
    public function getMessAction()
    {
        return $this->messAction;
    }

    /**
     * Get order by url for each attribute
     * 
     * @param string $attr
     * @return string
     */
    public function getOrderByUrl($attr)
    {
        $query = $this->getQuery();
        if (isset($query['asc'])) {
            if ($query['asc'] == $attr) {
                unset($query['asc']);
                $query['desc'] = $attr;
            } else {
                $query['asc'] = $attr;
            }
        } else if (isset($query['desc'])) {
            if ($query['desc'] == $attr) {
                unset($query['desc']);
                $query['asc'] = $attr;
            } else {
                $query['desc'] = $attr;
            }
        } else {
            $query['asc'] = $attr;
        }
        return $this->getUri()->withQuery(http_build_query($query))->__toString();
    }

    /**
     * Get limit url
     * 
     * @param string $attr
     * @return string
     */
    public function getLimitUrl()
    {
        $query = $this->getQuery();
        unset($query['limit']);
        if (empty($query)) {
            $url = $this->getUri()->withFragment('')->__toString() . '?';
        } else {
            $url = $this->getUri()->withFragment('')->withQuery(http_build_query($query))->__toString() . '&';
        }
        return $url;
    }

    /**
     * Prepare columns/attributes
     * 
     * @return array
     */
    protected function prepareColumns()
    {
        return [];
    }

    /**
     * Handle sql for collection
     * 
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function prepareCollection($collection = null)
    {
        if (is_null($collection)) {
            return [];
        }
        $this->filter($collection, $this->getQuery());
        return $collection;
    }

    /**
     * {@inhertdoc}
     */
    protected function getRendered($template)
    {
        $collection = $this->prepareCollection();
        if ($collection instanceof AbstractCollection) {
            $collection->load();
        }
        $this->setVariable('collection', $collection)
                ->setVariable('attributes', $this->prepareColumns());
        return parent::getRendered($template);
    }

    /**
     * Get translate domain
     * 
     * @return string
     */
    public function getTranslateDomain()
    {
        return $this->translateDomain;
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
        $box->setTemplate('page/renderer/' . (in_array($item['type'], ['multiselect', 'checkbox']) ? 'select' : $item['type']), false);
        return $box;
    }

}
