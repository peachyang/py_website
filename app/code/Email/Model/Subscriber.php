<?php

namespace Seahinet\Email\Model;

use Seahinet\Lib\Model\AbstractModel;

class Subscriber extends AbstractModel
{

    protected function construct()
    {
        $this->init('newsletter_subscriber', 'id', ['id', 'email', 'name', 'language_id', 'status']);
    }

    public function beforeSave()
    {
        $this['code'] = md5(random_bytes(32));
        parent::beforeSave();
    }
    
    public function unsubscribe()
    {
        if ($this->isLoaded || $this->getId()) {
            $this->setData('status', 0)->save();
        }
        return $this;
    }

}
