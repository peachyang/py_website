<?php

namespace Seahinet\Email\Model;

use Seahinet\Lib\Model\AbstractModel;
use Swift_Message;
use Swift_Mime_SimpleMessage;
use Zend\Db\TableGateway\TableGateway;

class Template extends AbstractModel
{

    protected function _construct()
    {
        $this->init('email_template', 'id', ['id', 'code', 'subject', 'content']);
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzencode($this->storage['content']);
        $this->beginTransaction();
        parent::beforeSave();
    }

    protected function afterSave()
    {
        if (isset($this->storage['language_id'])) {
            $tableGateway = new TableGateway('email_template_language', $this->getContainer()->get('dbAdapter'));
            $tableGateway->delete(['template_id' => $this->getId()]);
            foreach ($this->storage['language_id'] as $language_id) {
                $tableGateway->insert(['template_id' => $this->getId(), 'language_id' => $language_id]);
            }
        }
        parent::afterSave();
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left');
        $select->join('core_language', 'email_template_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
        parent::beforeLoad($select);
    }

    protected function afterLoad($result = [])
    {
        parent::afterLoad($result);
        if (isset($result[0])) {
            $language = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
            }
            $this->storage['language'] = $language;
        }
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
