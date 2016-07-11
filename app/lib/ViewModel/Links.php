<?php

namespace Seahinet\Lib\ViewModel;

class Links extends Template
{

    protected $links = [];
    protected $class = '';

    public function __construct()
    {
        $this->setTemplate('page/links');
    }

    /**
     * Set class of links list
     * 
     * @param string|array $class
     * @return Link
     */
    public function setClass($class)
    {
        if (is_array($class)) {
            $class = implode(' ', $class);
        }
        $this->class = ' ' . $class;
        return $this;
    }

    /**
     * Get class of links list
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Add a link to the list
     * 
     * @param array $links
     * @return Links
     */
    public function addLink(array $links)
    {
        $this->links[] = $links;
        return $this;
    }

    /**
     * Get all the links
     * 
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set all the links
     * 
     * @param array $links
     * @return Links
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
        return $this;
    }

}
