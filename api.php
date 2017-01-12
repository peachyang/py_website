<pre>
    <?php
    try {
        $client = new \SoapClient('http://ecomv2.idris.com/api/soap/?wsdl', ['cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1]);
        $key = <<<key
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAt3xYemmDeZMOmOFnC6xi
B78y/BySJrGXMrCU3CT+VbkIVX6Up2AufydHL0HgIlliZ1ijTGxUX91ybuldXLRG
t/dwJEN2bKjRwF/AzwlG/i2DkBDJOs7zcXSg8NAHu3u30c+a1HUkOi60rXlUKubd
wuGzXlTBZUUuouj6JbXEWicb9tsjPE40AU4Lp4L8xRs7e1H/AmwgV7WbfMf4O+Ha
1BVH9EVDGqv1TkD6BU4PLgXLSm9bZndLMb8d3rkIRGv96quUXj2GhsqCN+juM/L/
5ttP8h2Y2+9y2yxE5McYmF9QRRcBcWjhdX+NcMsyQ7h8qAvm7vtDB5Nr4BoVgqLE
UwIDAQAB
-----END PUBLIC KEY-----
key;
        openssl_public_encrypt('testtest', $password, $key, OPENSSL_PKCS1_OAEP_PADDING);
        $password = base64_encode($password);
        $session = $client->login('test', $password);
        var_dump($client->customerInfo($session, $client->customerValid($session, 'test', $password)));
        $client->endSession($session);
    } catch (\Exception $e) {
        var_dump($client->__getLastResponse());
    }
    ?>
</pre>