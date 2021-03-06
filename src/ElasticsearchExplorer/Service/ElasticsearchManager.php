<?php

namespace ElasticsearchExplorer\Service;

class ElasticsearchManager
{
    protected $client = false;
    protected $isConnected = false;
    protected $config = array();

    public function __construct(array $config)
    {
        $this->config = $config;
        if (!isset($config['hosts'])) {
            // Add default hosts if none is passed
            $this->config['hosts'] = 'localhost:9200';
        }

        try {
            $configuration = $this->getConfiguration();

            $clientBuilder = \Elasticsearch\ClientBuilder::create();
            $clientBuilder->setHosts($configuration['hosts']);
            $this->client = $clientBuilder->build();

            try {
                if ($this->client->ping()) {
                    $this->isConnected = true;
                }
            } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            }
        } catch (\Elasticsearch\Common\Exceptions\NoNodesAvailableException $e) {
        }
    }

    /**
     * Get the configuration as an array.
     *
     * @return array arrConfiguration
     */
    public function getConfiguration()
    {
        $arrDefaultConfiguration = array(
            'hosts' => array($this->config['hosts']),
        );

        return $arrDefaultConfiguration;
    }

    /**
     * Get the current indexes on the host with some statistics.
     *
     * @return array arrIndexes
     */
    public function getIndexStats()
    {
        $arrIndexes = array();
        
        if ($this->isConnected) {
            $objIndexes = $this->client->indices();
            $arrStats = $objIndexes->stats();
            $arrIndexesStats = $arrStats['indices'];

            foreach ($arrIndexesStats as $indexKey => $indexValues) {
                $arrIndexes[] = array(
                    'name' => $indexKey,
                    'total_docs' => $indexValues['total']['docs']['count'],
                    'total_size' => $indexValues['total']['store']['size_in_bytes'],
                );
            }
        }
        
        return $arrIndexes;
    }

    /**
     * Get the types in the specified index.
     *
     * @param string $index
     *
     * @return array $arrMappingTypes
     */
    public function getIndexMappingTypes($index)
    {
        $arrMappingTypes = array();
        
        if ($this->isConnected) {
            $objIndexes = $this->client->indices();
            $arrMappings = $objIndexes->getMapping(array(
                'index' => $index,
            ));

            if (isset($arrMappings[$index]['mappings']) && !empty($arrMappings[$index]['mappings'])) {
                foreach ($arrMappings[$index]['mappings'] as $typeKey => $typeValue) {
                    $arrMappingTypes[] = array(
                        'name' => $typeKey,
                    );
                }
            }
        }
        
        return $arrMappingTypes;
    }

    /**
     * Get the fields in the specified type.
     *
     * @param string $index
     * @param string $type
     *
     * @return array $arrFields
     */
    public function getFieldsInIndexType($index, $type)
    {
        $arrFields = array();
        
        if ($this->isConnected) {
            $objIndexes = $this->client->indices();
            $arrMappings = $objIndexes->getMapping(array('index' => $index));

            if (isset($arrMappings[$index]['mappings'][$type]['properties']) && !empty($arrMappings[$index]['mappings'][$type]['properties'])) {
                foreach ($arrMappings[$index]['mappings'][$type]['properties'] as $typeKey => $typeValue) {
                    $arrFields[] = array(
                        'name' => $typeKey,
                        'type' => $typeValue['type'],
                        'index' => isset($typeValue['index']) ? $typeValue['index'] : '',
                    );
                }
            }
        }
        
        return $arrFields;
    }

    /**
     * Execute a search for a term in the specified fields, index, and type.
     *
     * @param string $index
     * @param string $type
     * @param string $fields
     * @param string $term
     *
     * @return array $results
     */
    public function search($index, $type, $fields, $term)
    {
        if ($this->isConnected) {
            try {
                $arrFields = $this->convertSearchfieldsToArray($fields);

                $params = array(
                    'index' => $index,
                    'type' => $type,
                    'body' => array(
                        'query' => array(
                            'bool' => array(
                                'should' => array(
                                    'multi_match' => array(
                                        'query' => $term,
                                        'operator' => 'or',
                                        'fields' => $arrFields,
                                    ),
                                ),
                            ),
                        ),
                    ),
                );

                $results = $this->client->search($params);
                if (isset($results['hits']) && isset($results['hits']['hits']) && !empty($results['hits']['hits'])) {
                    return $results['hits']['hits'];
                }
            } catch (\Elasticsearch\Common\Exceptions\BadRequest400Exception $e) {
                return array();
            }
        }
        
        return array();
    }

    /**
     * Get plugins installed on specified elasticsearch node.
     *
     * @return array $arrPlugins
     */
    public function getPlugins()
    {
        $arrPlugins = array();
        
        if ($this->isConnected) {
            $arrStatsCluster = $this->client->cluster()->stats();
            $arrPlugins = $arrStatsCluster['nodes']['plugins'];
        }
        
        return $arrPlugins;
    }

    /**
     * Convert a string of searchfields to an array as expected by the elasticsearch client.
     *
     * @param string $searchfields
     *
     * @return array $arrFields
     */
    public function convertSearchfieldsToArray($searchfields)
    {
        if (strpos($searchfields, ',') !== false) {
            $arrFields = explode(',', $searchfields);
        } else {
            $arrFields = array($searchfields);
        }

        return $arrFields;
    }
}
