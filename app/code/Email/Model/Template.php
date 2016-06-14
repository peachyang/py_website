<?php

namespace Seahinet\Email\Model;

use Pelago\Emogrifier;
use Seahinet\Lib\Model\AbstractModel;
use Swift_Message;
use Swift_Mime_SimpleMessage;
use Zend\Db\TableGateway\TableGateway;

class Template extends AbstractModel
{

    use \Seahinet\Lib\Traits\Url,
        \Seahinet\Cms\Traits\Renderer;

    protected function construct()
    {
        $this->init('email_template', 'id', ['id', 'code', 'subject', 'content', 'css']);
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzencode($this->storage['content']);
        $this->storage['css'] = gzencode($this->storage['css']);
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
            $this->storage['language_id'] = array_keys($language);
        }
        $content = @gzdecode($this->storage['content']);
        if ($content !== false) {
            $this->storage['content'] = $content;
        }
        $css = @gzdecode($this->storage['css']);
        if ($css !== false) {
            $this->storage['css'] = $css;
        }
    }

    /**
     * @param Swift_Mime_SimpleMessage $message
     * @return Swift_Mime_SimpleMessage
     */
    public function injectMessage(Swift_Mime_SimpleMessage $message, array $vars = [])
    {
        if ($this->offsetExists('content')) {
            $message->setSubject($this->offsetGet('subject'));
            $content = $this->offsetGet('content');
            $vars += [
                'base_url' => $this->getBaseUrl(),
                'pub_url' => $this->getPubUrl(),
                'res_url' => $this->getResourceUrl()
            ];
            $content = $this->replace($content, $vars);
            if ($content) {
                $css = $this->offsetGet('css');
                $message->setBody(
                        ($css ? (new Emogrifier($content, $css))
                                        ->emogrifyBodyContent() : $content)
                        , 'text/html', 'UTF-8');
            }
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
