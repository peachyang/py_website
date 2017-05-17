<?php

namespace Seahinet\Payment\Model;

use DOMDocument;
use Exception;
use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Bootstrap;
use Seahinet\Log\Model\Collection\Payment as Collection;
use Seahinet\Log\Model\Payment as Model;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Order;
use Seahinet\Sales\Model\Collection\Order\Status;
use Zend\Math\Rand;

class WeChatPay extends AbstractMethod
{

    const METHOD_CODE = 'wechat_pay';

    public function preparePayment($orders)
    {
        $config = $this->getContainer()->get('config');
        $tradeType = Bootstrap::isMobile() ? (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessager') === false ? 'JSAPI' : 'MWEB') : 'NATIVE';
        $params = [
            'appid' => $config['payment/wechat_pay/app_id'],
            'mch_id' => $config['payment/wechat_pay/mch_id'],
            'notify_url' => $this->getBaseUrl('payment/notify/'),
            'nonce_str' => Rand::getString(30),
            'body' => Bootstrap::getMerchant()->offsetGet('name'),
            'out_trade_no' => '',
            'total_fee' => 0,
            'spbill_create_ip' => $_SERVER['X-REAL-IP'] ?? $_SERVER['REMOTE_ADDR'],
            'trade_type' => $tradeType,
            'product_id' => ''
        ];
        if ($tradeType === 'MWEB') {
            $params['mweb_url'] = $this->getBaseUrl('payment/return/');
        }
        $ids = [];
        $logs = [];
        $currency = new Currency;
        $currency->load('CNY', 'code');
        foreach ($orders as $order) {
            $ids[] = $order['increment_id'];
            if ($order->offsetGet('currency') !== 'CNY') {
                $total = $currency->convert($order->offsetGet('base_total'));
            } else {
                $total = (float) $order->offsetGet('total');
            }
            $params['total_fee'] += $total;
            $log = new Model;
            $log->setData(['order_id' => $order->getId()]);
            $logs[] = $log;
        }
        sort($ids);
        $params['total_fee'] = (int) ($params['total_fee'] * 100);
        $params['product_id'] = md5(implode(',', $ids));
        $params['out_trade_no'] = md5(implode('', $ids));
        $params['sign'] = $this->getSign($params);
        $query = http_build_query($params);
        foreach ($logs as $log) {
            $log->setData([
                'trade_id' => $params['out_trade_no'],
                'params' => $query,
                'is_request' => 1,
                'method' => __CLASS__
            ])->save();
        }
        $result = $this->request($config['payment/wechat_pay/gateway'] . 'pay/unifiedorder', $params);
        if ($result === false) {
            throw new Exception('An error detected.');
        }
        $segment = new Segment('payment');
        if (($codeUrl = $result->getElementsByTagName('code_url')) && $codeUrl->length) {
            $segment->set('wechatpay', [$tradeType, $codeUrl->item(0)->nodeValue, $params['out_trade_no']]);
        } else if (($redirect = $result->getElementsByTagName('mweb_url')) && $redirect->length) {
            return $redirect->item(0)->nodeValue;
        } else {
            $segment->set('wechatpay', [$tradeType, $result === false ? false : true, $params['out_trade_no']]);
        }
        return $this->getBaseUrl('payment/wechat/');
    }

    public function check($id)
    {
        if (!$id) {
            return false;
        }
        $config = $this->getContainer()->get('config');
        $params = [
            'appid' => $config['payment/wechat_pay/app_id'],
            'mch_id' => $config['payment/wechat_pay/mch_id'],
            'out_trade_no' => $id,
            'nonce_str' => Rand::getString(30)
        ];
        $params['sign'] = $this->getSign($params);
        $result = $this->request($config['payment/wechat_pay/gateway'] . 'pay/orderquery', $params);
        if ($result === false) {
            return false;
        }
        $state = $result->getElementsByTagName('trade_state');
        return $state->length && $state->item(0)->nodeValue === 'SUCCESS';
    }

    public function syncNotice($data)
    {
        if ($data['sign'] === $this->getSign($data)) {
            $log = new Model;
            $log->setData([
                'order_id' => null,
                'trade_id' => $data['out_trade_no'],
                'params' => http_build_query($data),
                'is_request' => 0,
                'method' => __CLASS__
            ])->save();
            return $this->getBaseUrl($data['is_success'] === 'T' ? 'checkout/success/' : 'checkout/success/');
        }
        return false;
    }

    public function asyncNotice($data)
    {
        if ($data['sign'] === $this->getSign($data)) {
            $config = $this->getContainer()->get('config');
            $responseText = file_get_contents($config['payment/alipay_direct_pay/gateway'] .
                    '?service=notify_verify&partner=' .
                    $config['payment/alipay_direct_pay/partner'] .
                    '&notify_id=' . $data['notify_id']);
            if (!preg_match("/true$/i", $responseText)) {
                return false;
            }
            $log = new Model;
            $log->setData([
                'order_id' => null,
                'trade_id' => $data['out_trade_no'],
                'params' => http_build_query($data),
                'is_request' => 0,
                'method' => __CLASS__
            ])->save();
            $collection = new Collection;
            $collection->where(['trade_id' => $data['out_trade_no']])
            ->where->isNotNull('order_id');
            $status = new Status;
            $status->join('sales_order_phase', 'sales_order_phase.id=sales_order_status.phase_id')
                    ->where([
                        'is_default' => 1,
                        'salse_order_phase.code' => 'processing'
                    ])->limit(1);
            $currency = new Currency;
            $currency->load('CNY', 'code');
            $total = $currency->rconvert($data['total_fee']);
            $orders = [];
            foreach ($collection as $log) {
                $order = new Order;
                $order->load($log['order_id']);
                if ($order->getPhase()->getId() < 3) {
                    $order->setData([
                        'status' => $status[0]['id'],
                        'base_total_paid' => $order->offsetGet('base_total'),
                        'total_paid' => $order->offsetGet('total')
                    ]);
                    $orders[] = $order;
                    $total -= $order->offsetGet('base_total');
                }
            }
            if ($total == 0) {
                foreach ($orders as $order) {
                    $order->save();
                }
            }
            return 'success';
        }
        return false;
    }

    public function getSign(array $params)
    {
        unset($params['sign'], $params['sign_type']);
        ksort($params);
        $str = '';
        foreach ($params as $key => $param) {
            if ($param !== '') {
                $str .= $key . '=' . $param . '&';
            }
        }
        return strtoupper(md5(trim($str, '&') . '&key=' . $this->getContainer()->get('config')['payment/wechat_pay/app_secret']));
    }

    public function request($url, $params)
    {
        $xml = new DOMDocument;
        $root = $xml->createElement('xml');
        foreach ($params as $key => $value) {
            $node = $xml->createElement($key, $value);
            $root->appendChild($node);
        }
        $xml->appendChild($root);
        $client = curl_init($url);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($client, CURLOPT_POST, 1);
        curl_setopt($client, CURLOPT_HTTPHEADER, ['Content-Type: text/xml; charset=UTF-8']);
        curl_setopt($client, CURLOPT_POSTFIELDS, $xml->saveXML());
        $result = new DOMDocument;
        $result->loadXML(curl_exec($client));
        curl_close($client);
        return $result;
    }

}
