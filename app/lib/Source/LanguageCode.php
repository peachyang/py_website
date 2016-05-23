<?php

namespace Seahinet\Lib\Source;

class LanguageCode implements SourceInterface
{

    protected $code = [
        'af-ZA' => 'Afrikaans (Suid-Afrika)',
        'az-AZ' => 'Azərbaycan (Azərbaycan)',
        'id-ID' => 'Bahasa Indonesia (Indonesia)',
        'ms-MY' => 'Bahasa Melayu (Malaysia)',
        'bs-BA' => 'Bosanski (Bosna i Hercegovina)',
        'ca-ES' => 'Català (Espanya)',
        'cy-GB' => 'Cymraeg (Y Deyrnas Unedig)',
        'da-DK' => 'Dansk (Danmark)',
        'de-DE' => 'Deutsch (Deutschland)',
        'de-CH' => 'Deutsch (Schweiz)',
        'de-AT' => 'Deutsch (Österreich)',
        'et-EE' => 'Eesti (Eesti)',
        'en-AU' => 'English (Australia)',
        'en-CA' => 'English (Canada)',
        'en-IE' => 'English (Ireland)',
        'en-NZ' => 'English (New Zealand)',
        'en-GB' => 'English (United Kingdom)',
        'en-US' => 'English (United States)',
        'es-AR' => 'Español (Argentina)',
        'es-CL' => 'Español (Chile)',
        'es-CO' => 'Español (Colombia)',
        'es-CR' => 'Español (Costa Rica)',
        'es-ES' => 'Español (España)',
        'es-MX' => 'Español (México)',
        'es-PA' => 'Español (Panamá)',
        'es-PE' => 'Español (Perú)',
        'es-VE' => 'Español (Venezuela)',
        'fil-PH' => 'Filipino (Pilipinas)',
        'fr-CA' => 'Français (Canada)',
        'fr-FR' => 'Français (France)',
        'gl-ES' => 'Galego (España)',
        'hr-HR' => 'Hrvatski (Hrvatska)',
        'it-IT' => 'Italiano (Italia)',
        'it-CH' => 'Italiano (Svizzera)',
        'sw-KE' => 'Kiswahili (Kenya)',
        'lv-LV' => 'Latviešu (Latvija)',
        'lt-LT' => 'Lietuvių (Lietuva)',
        'hu-HU' => 'Magyar (Magyarország)',
        'nl-NL' => 'Nederlands (Nederland)',
        'nb-NO' => 'Norsk Bokmål (Norge)',
        'nn-NO' => 'Nynorsk (Noreg)',
        'pl-PL' => 'Polski (Polska)',
        'pt-BR' => 'Português (Brasil)',
        'pt-PT' => 'Português (Portugal)',
        'ro-RO' => 'Română (România)',
        'sq-AL' => 'Shqip (Shqipëri)',
        'sk-SK' => 'Slovenčina (Slovensko)',
        'sl-SI' => 'Slovenščina (Slovenija)',
        'sr-RS' => 'Srpski (Srbija)',
        'fi-FI' => 'Suomi (Suomi)',
        'sv-SE' => 'Svenska (Sverige)',
        'vi-VN' => 'Tiếng Việt (Việt Nam)',
        'tr-TR' => 'Türkçe (Türkiye)',
        'is-IS' => 'íslenska (Ísland)',
        'cs-CZ' => 'čeština (Česká republika)',
        'el-GR' => 'Ελληνικά (Ελλάδα)',
        'be-BY' => 'беларуская (Беларусь)',
        'bg-BG' => 'български (България)',
        'mk-MK' => 'македонски (Македонија)',
        'mn-MN' => 'монгол (Монгол)',
        'ru-RU' => 'русский (Россия)',
        'uk-UA' => 'українська (Україна)',
        'he-IL' => 'עברית (ישראל)',
        'ar-DZ' => 'العربية (الجزائر)',
        'ar-KW' => 'العربية (الكويت)',
        'ar-MA' => 'العربية (المغرب)',
        'ar-SA' => 'العربية (المملكة العربية السعودية)',
        'ar-EG' => 'العربية (مصر)',
        'fa-IR' => 'فارسی (ایران)',
        'hi-IN' => 'हिंदी (भारत)',
        'bn-BD' => 'বাংলা (বাংলাদেশ)',
        'gu-IN' => 'ગુજરાતી (ભારત)',
        'th-TH' => 'ไทย (ไทย)',
        'lo-LA' => 'ລາວ (ລາວ)',
        'ka-GE' => 'ქართული (საქართველო)',
        'km-KH' => 'ខ្មែរ (កម្ពុជា)',
        'zh-CN' => '中文 (中国)',
        'zh-HK' => '中文 (中華人民共和國香港特別行政區)',
        'zh-TW' => '中文 (台灣)',
        'ja-JP' => '日本語 (日本)',
        'ko-KR' => '한국어 (대한민국)'
    ];

    public function getSourceArray($code = null)
    {
        return is_null($code) ? $this->code : (isset($this->code[$code]) ? $this->code[$code] : null);
    }

}
