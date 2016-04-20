<?php

namespace Seahinet\Lib\ViewModel;

class Breadcrumb extends AbstractViewModel
{

    protected $crumbs = [];
    protected $showLabel = false;
    protected $showHome = false;

    public function __construct()
    {
        $this->setTemplate('page/breadcrumb');
    }

    public function getCacheKey()
    {
        return false;
    }

    public function showLabel($flag = null)
    {
        if (is_bool($flag)) {
            $this->showLabel = $flag;
        }
        return $this->showLabel;
    }

    public function showHome($flag = null)
    {
        if (is_bool($flag)) {
            $this->showHome = $flag;
        }
        return $this->showHome;
    }

    public function getCrumbs()
    {
        return $this->crumbs;
    }

    public function setCrumbs(array $crumbs)
    {
        $this->crumbs = $crumbs;
        return $this;
    }

    public function addCrumb(array $crumbs)
    {
        $this->crumbs[] = $crumbs;
        return $this;
    }

}
