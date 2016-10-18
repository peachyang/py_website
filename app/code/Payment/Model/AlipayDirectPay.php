<?php

namespace Seahinet\Payment\Model;

use DOMDocument;
use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Bootstrap;
use Seahinet\Log\Model\Collection\Payment as Collection;
use Seahinet\Log\Model\Payment as Model;
use Seahinet\Sales\Model\Order;
use Seahinet\Sales\Model\Collection\Order\Status;

class AlipayDirectPay extends AbstractMethod
{

    const METHOD_CODE = 'alipay_direct_pay';

    public function preparePayment($orders)
    {
        $config = $this->getContainer()->get('config');
        $params = [
            'service' => 'create_direct_pay_by_user',
            'partner' => $config['payment/alipay_direct_pay/partner'],
            'sign_type' => 'MD5',
            '_input_charset' => 'utf-8',
            'out_trade_no' => '',
            'subject' => Bootstrap::getMerchant()->offsetGet('name'),
            'notify_url' => $this->getBaseUrl('payment/notify/'),
            'return_url' => $this->getBaseUrl('payment/return/'),
            $config['payment/alipay_direct_pay/seller_type'] => $config['payment/alipay_direct_pay/seller_id'],
            'payment_type' => '1',
            'total_fee' => 0
        ];
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
        $params['out_trade_no'] = md5(implode($ids));
        if ($config['payment/alipay_direct_pay/anti_phishing'] && !empty($_SERVER['REMOTE_ADDR'])) {
            $params['anti_phishing_key'] = $this->queryTimestamp();
            $params['exter_invoke_ip'] = $_SERVER['REMOTE_ADDR'];
        }
        $query = http_build_query($params) . '&sign=' . $this->getSign($params);
        foreach ($logs as $log) {
            $log->setData([
                'trade_id' => $params['out_trade_no'],
                'params' => $query,
                'is_request' => 1,
                'method' => __CLASS__
            ])->save();
        }
        return $config['payment/alipay_direct_pay/gateway'] . '?' . $query;
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
        return md5(trim($str, '&') . $this->getContainer()->get('config')['payment/alipay_direct_pay/security_key']);
    }

    public function queryTimestamp()
    {
        $config = $this->getContainer()->get('config');
        $url = $config['payment/alipay_direct_pay/gateway'] .
                '?service=query_timestamp&partner=' .
                $config['payment/alipay_direct_pay/partner'] .
                '&_input_charset=UTF-8';
        $doc = new DOMDocument();
        $doc->load($url);
        $encryptKey = $doc->getElementsByTagName("encrypt_key");
        $result = $encryptKey->item(0)->nodeValue;
        return $result ?: '';
    }

}
