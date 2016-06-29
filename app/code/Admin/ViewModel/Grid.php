<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\ViewModel\AbstractViewModel;
use Zend\Db\Sql\Predicate\Like;

class Grid extends AbstractViewModel
{

    protected $count = null;
    protected $action = [];
    protected $messAction = [];
    protected $translateDomain = null;

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
        $condition = $this->getQuery();
        $limit = isset($condition['limit']) ? $condition['limit'] : 20;
        if (isset($condition['page'])) {
            $collection->offset(($condition['page'] - 1) * $limit);
            unset($condition['page']);
        }
        $collection->limit((int) $limit);
        unset($condition['limit']);
        if (isset($condition['asc'])) {
            $collection->order((strpos($condition['asc'], ':') ?
                            str_replace(':', '.', $condition['asc']) :
                            $condition['asc']) . ' ASC');
            unset($condition['asc']);
        } else if (isset($condition['desc'])) {
            $collection->order((strpos($condition['desc'], ':') ?
                            str_replace(':', '.', $condition['desc']) :
                            $condition['desc']) . ' DESC');
            unset($condition['desc']);
        }
        if (!empty($condition)) {
            foreach ($condition as $key => $value) {
                if (trim($value) === '') {
                    unset($condition[$key]);
                } else if (strpos($key, ':')) {
                    if (strpos($value, '%') !== false) {
                        $collection->where(new Like(str_replace(':', '.', $key), $value));
                    } else {
                        $condition[str_replace(':', '.', $key)] = $value;
                    }
                    unset($condition[$key]);
                } else if (strpos($value, '%') !== false) {
                    $collection->where(new Like($key, $value));
                    unset($condition[$key]);
                }
            }
            $collection->where($condition);
        }
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
        $box->setTemplate('admin/renderer/' . $item['type']);
        return $box;
    }

}
