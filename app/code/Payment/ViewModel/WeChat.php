<?php

namespace Seahinet\Payment\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use TCPDF2DBarcode;

class WeChat extends Template
{

    public function getQRCode($string, $width = 100, $height = 100)
    {
        $barcode = new TCPDF2DBarcode($string, 'QRCODE,M');
        ob_start();
        $barcode->getBarcodePNG($width, $height);
        $png = ob_get_clean();
        return base64_encode($png);
    }

}
