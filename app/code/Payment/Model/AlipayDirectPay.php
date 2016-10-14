<?php

namespace Seahinet\Payment\Model;

class AlipayDirectPay extends AbstractMethod
{

    const METHOD_CODE = 'alipay_direct_pay';

    public function preparePayment($orders)
    {
        $config = $this->getContainer()->get('config');
        $params = [
            'service' => 'create_direct_pay_by_user',
            'partner' => '',
            '_input_charset' => 'UTF-8',
            'sign_type' => 'MD5',
            'sign' => '',
            'out_trade_no' => '',
            'subject' => '',
            'payment_type' => '1',
            'total_fee' => '',
            'anti_phishing_key'=>'',
            'exter_invoke_ip'=>''
        ];
        return $this->getBaseUrl('checkout/success/');
    }

}
