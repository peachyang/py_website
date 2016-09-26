<?php

namespace Seahinet\I18n\Traits;

use Exception;
use Seahinet\I18n\Model\Currency as Model;

trait Currency
{

    protected $sync_url = 'http://download.finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s={{from}}{{to}}=x';

    protected function sync($cur, $base)
    {
        $result = ['error' => 0, 'message' => []];
        try {
            $this->beginTransaction();
            foreach ((array) $cur as $item) {
                if ($item === $base) {
                    $rate = 1;
                } else {
                    $url = str_replace(['{{from}}', '{{to}}'], [$base, $item], $this->sync_url);
                    $fp = @fopen($url, 'r');
                    if (!$fp) {
                        throw new Exception('Connection timed out.');
                    }
                    $exec = fgetcsv($fp);
                    fclose($fp);
                    $rate = $exec[1];
                }
                $model = new Model;
                $model->load($item, 'code');
                $model->setData([
                    'code' => $item,
                    'rate' => $rate
                ])->save();
            }
            $this->commit();
            $result['message'][] = ['message' => $this->translate('Currency rates have been synchronized successfully.'), 'level' => 'success'];
        } catch (Exception $e) {
            $this->rollback();
            $this->getContainer()->get('log')->logException($e);
            $result['error'] = 1;
            $result['message'][] = ['message' => $this->translate('An error detected while synchronizing, please try again later.'), 'level' => 'danger'];
        }
        return $result;
    }

}
