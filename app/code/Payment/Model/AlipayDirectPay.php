<?php

namespace Seahinet\Payment\Model;

use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Bootstrap;
use Seahinet\Log\Model\Collection\Payment as Collection;
use Seahinet\Log\Model\Payment as Model;

class AlipayDirectPay extends AbstractMethod
{

    const METHOD_CODE = 'alipay_direct_pay';

    public function preparePayment($orders)
    {
        $config = $this->getContainer()->get('config');
        $params = [
            'service' => 'create_direct_pay_by_user',
            'partner' => $config['payment/alipay_direct_pay/partner'],
            '_input_charset' => 'UTF-8',
            'sign_type' => 'MD5',
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
        foreach ($orders as $order) {
            $ids[] = $order['increment_id'];
            if ($order->offsetGet('currency') !== 'CNY') {
                $currency = new Currency;
                $currency->load('CNY', 'code');
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
            $params['anti_phishing_key'] = '';
            $params['exter_invoke_ip'] = $_SERVER['REMOTE_ADDR'];
        }
        ksort($params);
        $query = http_build_query($params);
        $params = $query . '&sign=' . md5($query);
        foreach ($logs as $log) {
            $log->setData([
                'trade_id' => $params['out_trade_no'],
                'params' => $params,
                'is_request' => 1,
                'method' => __CLASS__
            ])->save();
        }
        return $config['payment/alipay_direct_pay/gateway'] . '?' . $params;
    }

    public function syncResponse($data)
    {
        
    }

    public function asyncResponse($data)
    {
        
    }

}
