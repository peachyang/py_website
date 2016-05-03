<?php

namespace Seahinet\Lib\Model\Email;

use Seahinet\Lib\Model\AbstractModel;
use Swift_Message;
use Swift_Mime_SimpleMessage;

class Template extends AbstractModel
{

    protected function _construct()
    {
        $this->init('email_template', 'id', ['id', 'code', 'subject', 'content']);
        $this->withLanguage('email_template_language', 'template_id');
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzencode($this->storage['content']);
        parent::beforeSave();
    }

    protected function afterLoad()
    {
        parent::afterLoad();
        $data = @gzdecode($this->storage['content']);
        if ($data !== false) {
            $this->storage['content'] = $data;
        }
    }

    /**
     * @param Swift_Mime_SimpleMessage $message
     * @return Swift_Mime_SimpleMessage
     */
    public function injectMessage(Swift_Mime_SimpleMessage $message, array $vars = [])
    {
        if ($this->isLoaded) {
            $message->setSubject($this->offsetGet('subject'));
            $content = $this->offsetGet('content');
            if (!empty($vars)) {
                $content = str_replace(array_keys($vars), array_values($vars), $content);
            }
            $message->setBody($content);
        }
        return $message;
    }

    /**
     * @return Swift_Message
     */
    public function getMessage($vars = [])
    {
        $message = new Swift_Message();
        return $this->injectMessage($message, $vars);
    }

}
