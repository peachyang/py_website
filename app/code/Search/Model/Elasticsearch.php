<?php

namespace Seahinet\Search\Model;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions;
use Exception;

class Elasticsearch implements EngineInterface
{

    use \Seahinet\Lib\Traits\Container;

    private $client;
    private $index;

    public function __construct()
    {
        if (!class_exists('\\Elasticsearch\\ClientBuilder')) {
            throw new Exception('Not Available');
        }
        $config = $this->getContainer()->get('config')['adapter']['search_engine'] ?? [];
        $this->index = $config['index'] ?? ['prefix' => 'seahinet'];
        unset($config['adapter'], $config['index']);
        $this->client = ClientBuilder::fromConfig($config);
    }

    public function createIndex($prefix)
    {
        $config = $this->index;
        unset($config['index']['prefix']);
        $indicesNS = $this->client->indices();
        try {
            $indicesNS->delete(['index' => $this->index['prefix'] . '_' . $prefix]);
        } catch (Exceptions\Missing404Exception $e) {
            
        }
        $indicesNS->create([
            'index' => $this->index['prefix'] . '_' . $prefix,
            'body' => [
                'settings' => $config['index'] ?? [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0
                        ]
            ]
        ]);
    }

    public function select($prefix, $data, $languageId)
    {
        $constraint = [['match' => ['data' => $data['q']]]];
        if (!empty($data['store_id'])) {
            $constraint[] = ['match' => ['store_id' => $data['store_id']]];
        }
        $config = $this->getContainer()->get('config');
        $limit = (int) ($data['limit'] ?? empty($data['mode']) ?
                $config['catalog/frontend/default_per_page_grid'] :
                $config['catalog/frontend/default_per_page_' . $data['mode']]);
        $resultSet = $this->client->search([
            'index' => $this->index['prefix'] . '_' . $prefix,
            'type' => $languageId,
            '_source' => false,
            'size' => $limit,
            'from' => isset($data['page']) ? (int) ($data['page'] - 1) * $limit : 0,
            'body' => ['query' => ['bool' => ['must' => $constraint]]]
        ]);
        $result = [];
        foreach ($resultSet['hits']['hits'] ?? [] as $item) {
            $result[] = ['id' => $item['_id'], 'weight' => $item['_score']];
        }
        return $result;
    }

    public function delete($prefix, $id, $languageId)
    {
        $this->client->delete([
            'index' => $this->index['prefix'] . '_' . $prefix,
            'type' => $languageId,
            'id' => $id
        ]);
    }

    public function update($prefix, $data)
    {
        foreach ($data as $languageId => $values) {
            foreach ($values as $item) {
                $this->client->index([
                    'index' => $this->index['prefix'] . '_' . $prefix,
                    'type' => $languageId,
                    'id' => $item['id'],
                    'body' => $item
                ]);
            }
        }
    }

}
