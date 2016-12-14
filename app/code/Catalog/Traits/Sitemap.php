<?php

namespace Seahinet\Catalog\Traits;

use DateTime;
use DateTimeZone;
use Exception;
use Seahinet\Cms\Model\Page;
use Seahinet\Lib\Model\Collection\Language;

trait Sitemap
{

    use \Seahinet\Lib\Traits\DB;

    public function generate($response = true)
    {
        $collection = new Language;
        $collection->where(['status' => 1]);
        $indexer = $this->getContainer()->get('indexer');
        $config = $this->getContainer()->get('config');
        $filename = BP . trim($config['catalog/sitemap/path'], '/') . DS . $config['catalog/sitemap/filename'];
        $baseurl = $this->getBaseUrl();
        $timezone = new DateTimeZone($config['global/locale/timezone']);
        $count = $collection->count();
        try {
            foreach ($collection as $language) {
                $result = $indexer->select('catalog_url', $language->getId());
                $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                foreach ($result as $url) {
                    if ($url['product_id']) {
                        $model = $this->getTableGateway('product_entity')->select(['id' => $url['product_id']])->toArray()[0];
                    } else {
                        $model = $this->getTableGateway('category_entity')->select(['id' => $url['category_id']])->toArray()[0];
                    }
                    $xml .= '<url><loc>' . $baseurl . $url['path'] . '.html</loc><changefreq>daily</changefreq><priority>' . ($url['product_id'] ? '1.0' : '0.5') . '</priority><lastmod>' . (new DateTime($model['updated_at'] ?: $model['created_at'], $timezone))->format(DateTime::W3C) . '</lastmod></url>';
                }
                $result = $indexer->select('cms_url', $language->getId());
                foreach ($result as $url) {
                    if ($url['page_id']) {
                        $model = new Page;
                        $model->load($url['page_id']);
                    }
                    $xml .= '<url><loc>' . $baseurl . $url['path'] . '.html</loc><changefreq>daily</changefreq><priority>0.2</priority><lastmod>' . (new DateTime($model['updated_at'] ?: $model['created_at'], $timezone))->format(DateTime::W3C) . '</lastmod></url>';
                }
                $fp = fopen($filename . ($count === 1 ? '' : ('-' . $language->getId())) . '.xml', 'w');
                fwrite($fp, $xml . '</urlset>');
                fclose($fp);
            }
            if ($response) {
                return ['error' => 0, 'message' => [[
                    'message' => $this->translate('The sitemap has been generated.'),
                    'level' => 'success'
                ]]];
            } else {
                return 'The sitemap has been generated.';
            }
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            return $response ? ['error' => 1, 'message' => [[
                'message' => $this->translate('An error detected. Please try again later.'),
                'level' => 'danger'
                    ]]] : $e->getMessage();
        }
    }

}
