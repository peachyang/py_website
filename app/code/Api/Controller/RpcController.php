<?php

namespace Seahinet\Api\Controller;

use Exception;
use Seahinet\Api\Model\Api\ClassMap;
use Seahinet\Lib\Controller\AbstractController;
use SoapFault;

class RpcController extends AbstractController
{

    protected $type = 'json';

    /**
     * {@inhertdoc}
     */
    public function dispatch($request = null, $routeMatch = null)
    {
        $response = $this->getResponse();
        if (!isset($_SERVER['HTTPS'])) {
            return $response->withStatus(403, 'SSL required');
        }
        return parent::dispatch($request, $routeMatch);
    }

    protected function getRawPost()
    {
        $data = $this->getRequest()->getPost();
        if (is_object($data)) {
            $this->type = 'xml';
            $result = ['jsonrpc' => '2.0', 'id' => 1];
            $result['method'] = @$data->xpath('/methodCall/methodName')[0]->__toString();
            $result['params'] = [];
            foreach ($data->xpath('/methodCall/params/param/value') as $param) {
                $child = $param->children()[0];
                $result['params'][] = $child->getName() === 'base64' ? base64_decode($child->__toString()) : $child->__toString();
            }
            $data = $result;
        }
        return $data;
    }

    protected function prepareRequest()
    {
        $data = $this->getRawPost();
        if (empty($data['jsonrpc']) || $data['jsonrpc'] !== '2.0' ||
                empty($data['id']) ||
                empty($data['method']) || !is_string($data['method'])) {
            throw new Exception('Invalid Request');
        }
        return $data;
    }

    protected function response($result)
    {
        if ($this->type === 'xml') {
            if (isset($result['error'])) {
                $result = '<?xml version="1.0"?><methodResponse>
<fault><value><struct><member><name>faultCode</name>
               <value><int>' . $result['error']['code'] . '</int></value>
</member><member><name>faultString</name>
               <value><string>' . $result['error']['message'] . '</string></value>
</member></struct></value></fault></methodResponse>';
            } else {
                $type = gettype($result['result']);
                if ($type === 'integer') {
                    $type = 'int';
                } else if ($type === 'boolean') {
                    $result['result'] = (int) $result['result'];
                }
                $result = '<?xml version="1.0"?><methodResponse>
<params><param><value><' . $type . '>' .
                        $result['result']
                        . '</' . $type . '></value></param></params></methodResponse>';
            }
        }
        return $result;
    }

    public function indexAction()
    {
        try {
            $data = $this->prepareRequest();
            $classMap = new ClassMap;
            $result = call_user_func_array([$classMap, $data['method']], $data['params']);
            if ($result instanceof SoapFault) {
                $result = [
                    'jsonrpc' => '2.0',
                    'id' => $data['id'],
                    'error' => ['code' => $result->getCode() === 'Server' ? '-32000' : '-32600', 'message' => $result->getMessage()]
                ];
            } else {
                $result = [
                    'jsonrpc' => '2.0',
                    'id' => $data['id'],
                    'result' => is_bool($result) ? ($result ? 'true' : 'false') : $result
                ];
            }
        } catch (Exception $e) {
            $result = [
                'jsonrpc' => '2.0',
                'id' => $data['id'] ?? null,
                'error' => ['code' => -32600, 'message' => $e->getMessage()]
            ];
        }
        return $this->response($result);
    }

}
