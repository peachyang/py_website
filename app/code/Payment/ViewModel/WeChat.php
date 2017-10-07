<?php

namespace Seahinet\Payment\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use TCPDF2DBarcode;

class WeChat extends Template
{

    public function getQRCode($string, $width = 120, $height = 120)
    {
        $barcode = new TCPDF2DBarcode($string, 'QRCODE,M');
        $png = $barcode->getBarcodePngData(intval($width / 30), intval($height / 30));
        return base64_encode($png);
    }

}
