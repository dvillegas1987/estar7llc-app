<?php

require_once CONF_INSTALLATION_PATH . 'library/elasticsearch/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

class ElasticSearch extends FullTextSearchBase
{
    private $client;
    private $indexName;
    private $search = [];
    private $fields = [];
    private $sortField = [];
    private $groupByFields;

    public const KEY_NAME = __CLASS__;
    public const INDEX_PREFIX = "yk-products-";
    
    public $requiredKeys = [
        'host',
        'username',
        'password'
    ];

    /* Creating ElasticSearch Connection
    *
    *  @indexName - Pass Index Name you Want to Create in Elasticsearch
    */
    public function __construct($langId)
    {
        $this->indexName = self::INDEX_PREFIX . $langId;
        $this->langId = $langId;
        
        if (false == $this->validateSettings()) {
            return false;
        }
       
        $this->client = ClientBuilder::create()
             ->setElasticCloudId($this->settings['host'])
             ->setBasicAuthentication($this->settings['username'], $this->settings['password'])
             ->build();

        $this->pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
    }

    public function addKeywordCondition($keyword)
    {
        if (empty($keyword)) {
            return false;
        }
        
        $textSearch = 	[ 'bool' =>
                            [ 'should'=>
                                [
                                    /* [ product general fields */
                                    ['match' => ['general.product_name' => $keyword ]  ],
                                    ['match' => ['general.product_model' => $keyword ] ],
                                    ['match' => ['general.product_description' => $keyword ] ],
                                    ['match' => ['general.product_tags_string' => $keyword ] ],
                                    /*  product general fields ] */

                                    /* [ inventory fields */
                                    ['match' => ['inventories.selprod_title' => $keyword ] ],
                                    ['match' => ['inventories.selprod_sku' => $keyword ] ],
                                    /*  inventory fields ] */

                                    /* [ brands fields */
                                    ['match' => ['brand.brand_name' => ['query' => $keyword , 'fuzziness'=> '1' ] ] ],
                                    ['match' => ['brand_short_description' => $keyword ] ],
                                    /*  brands fields ] */

                                    /* [ categories fields */
                                    ['match' => ['categories.prodcat_identifier' => ['query' => $keyword , 'fuzziness' => '1' ] ] ],
                                    ['match' => ['categories.prodcat_name' => $keyword ] ],
                                    /*  categories fields ] */

                                    /* [ options fields */
                                    ['match' => ['options.optionvalue_identifier' => ['query' => $keyword , 'fuzziness' => '1' ] ] ],
                                    ['match' => ['options.optionvalue_name' => ['query' => $keyword , 'fuzziness' => '1' ] ] ],
                                    /*  options fields ] */
                                ]
                            ]
                        ];

        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $textSearch);
        } else {
            $this->search["must"][0] = $textSearch;
        }
    }

    public function addBrandConditions($brand)
    {
        if (is_numeric($brand)) {
            $brands[] = $brand;
        } elseif (is_array($brand) && 0 < count($brand)) {
            $brands = array_filter(array_unique($brand));
        } else {
            if (!empty($brand)) {
                $brand = explode(",", $brand);
                $brands = array_filter(array_unique($brand));
            }
        }

        $brandsFilters['bool']['should'] = array();
        foreach ($brands as $key => $brand) {
            if (1 > $brand) {
                continue;
            }
            $brandsFilters['bool']['should'][$key] = ['match' => ['brand.brand_id' => $brand ]];
        }
        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $brandsFilters);
        } else {
            $this->search["must"][0] = $brandsFilters;
        }
        //array('brand.brand_id','brand.brand_name'), 'brand.brand_name', array('brand.brand_name.keyword' => 'asc')
    }

    public function addCategoryCondition($category)
    {
        if (is_numeric($category)) {
            $categories[] = $category;
        } elseif (is_array($category) && 0 < count($category)) {
            $categories = array_filter(array_unique($category));
        } else {
            if (!empty($category)) {
                $category = explode(",", $category);
                $categories = array_filter(array_unique($category));
            }
        }
        
        $categoryFilter['bool']['should'] = array();
        foreach ($categories as $key => $category) {
            if (1 > $category) {
                continue;
            }
            $categoryFilter['bool']['should'][$key] = [
                'wildcard' =>  [
                    'categories.prodcat_code' => [
                         "value" => '*' . str_pad($category, 6, 0, STR_PAD_LEFT) . '*'
                        ]
                    ]
                ];
        }

        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $categoryFilter);
        } else {
            $this->search["must"][0] = $categoryFilter;
        }
    }

    public function addShopIdCondition($shopId)
    {
        $shopId = FatUtility::int($shopId);
        if (1 > $shopId) {
            return ;
        }

        $shopFilter['bool']['must'] = [
            'match' =>  [
                'inventories.shop.shop_id' => $shopId
                ]
            ];

        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $shopFilter);
        } else {
            $this->search["must"][0] = $shopFilter;
        }
    }

    public function addOptionCondition($option)
    {
        if (is_numeric($option)) {
            $options[] = $option;
        } elseif (is_array($option) && 0 < count($option)) {
            $options = array_filter(array_unique($option));
        } else {
            if (!empty($option)) {
                $option = explode(",", $option);
                $options = array_filter(array_unique($option));
            }
        }

        $optionsFilters['bool']['should'] = array();
        foreach ($options as $key => $option) {
            if (1 > $option) {
                continue;
            }
            $optionsFilters['bool']['should'][$key] = ['match' => ['options.optionvalue_id' => $option ]];
        }
        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $optionsFilters);
        } else {
            $this->search["must"][0] = $optionsFilters;
        }
    }

    public function addConditionCondition($condition)
    {
        if (is_numeric($condition)) {
            $conditions[] = $condition;
        } elseif (is_array($condition) && 0 < count($condition)) {
            $conditions = array_filter(array_unique($condition));
        } else {
            if (!empty($condition)) {
                $condition = explode(",", $condition);
                $conditions = array_filter(array_unique($condition));
            }
        }

        $conditionsFilters['bool']['should'] = array();
        foreach ($conditions as $key => $condition) {
            if (1 > $condition) {
                continue;
            }
            $conditionsFilters['bool']['should'][$key] = ['match' => ['inventories.selprod_condition' => $condition ]];
        }
        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $conditionsFilters);
        } else {
            $this->search["must"][0] = $conditionsFilters;
        }
    }
    
    public function excludeOutOfStockProducts()
    {
        //@todo based on in_stock
    }

    public function addFeaturedProdCondition(){
        
        $filter['bool']['must'] = [
            'match' =>  [
                'general.product_featured' => 1
                ]
            ];

        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $filter);
        } else {
            $this->search["must"][0] = $filter;
        }
    }

    public function addPriceFilters($minPrice, $maxPrice)
    {
        if (empty($minPrice) && empty($maxPrice)) {
            return;
        }
        $priceFilters['range'] = [
                        'general.theprice'=> [ 'gte' => $minPrice, 'lte' => $maxPrice ]
                    ];

        if (array_key_exists('must', $this->search)) {
            array_push($this->search["must"], $priceFilters);
        } else {
            $this->search["must"][0] = $priceFilters;
        }
    }

    public function addCategoryFilter($categoryId)
    {
        $categoryId = FatUtility::int($categoryId);

        if ($categoryId) {
            $catCode = ProductCategory::getAttributesById($categoryId, 'prodcat_code');
            $categoryFilter['wildcard'] = ['categories.prodcat_code'=> [ "value" => $catCode.'*',"boost"=> "2.0", "rewrite"=>"constant_score" ] ];
            if (array_key_exists('must', $this->search)) {
                array_push($this->search["must"], $categoryFilter);
            } else {
                $this->search["must"][0] = $categoryFilter;
            }
        }
    }

    public function convertToSystemData($response, $filterKey = '')
    {
        $result = [];
        if (!is_array($response) || !isset($response['hits'])) {
            return $result;
        }
        
        foreach ($response['hits'] as $key => $value) {
            if (empty($filterKey)) {
                $result[$key] = $value['_source'];
            } else {
                $result[$key] = $value['_source'][$filterKey];
            }
        }
        return $result;
    }

    public function fetch($aggregationPrice = false)
    {
        if (empty($this->search)) {
            $this->search = ['match_all' => []];
        }

        return $this->results =  $this->search($this->search, $this->page, $this->pageSize, $aggregationPrice, $this->fields, $this->groupByFields, $this->sortField);
    }

    public function setPageNumber($page)
    {
        $this->page = FatUtility::int($page);
        /* if (0 <  $this->page) {
            $this->page = ($this->page - 1) * $this->pageSize;
        } */
    }

    public function setFields($arr = [])
    {
        $this->fields = $arr;
    }

    public function setSortFields($arr = [])
    {
        $this->sortField = $arr;
    }

    public function setGroupByField($field)
    {
        $this->groupByFields = $field;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = FatUtility::int($pageSize);
    }

    public function recordCount()
    {
        return isset($this->results['total']['value']) ? $this->results['total']['value'] : 0;
    }

    public function pages()
    {
        return $this->page;
    }
    
    
    /* Parameter Name
    *	@queryData => Pass the query data
    *	@source => Pass the Fields you want to select
    *	@groupByField => Pass the Groupby Field in string
    *	@sort => Sorting Field array
    *   @aggregation => performing text based aggregation get min and max price in query
    *	@from => same as offset field in mysql
    *	@size => same as limit field in mysql
    */

    private function search($queryData, $from, $size, $aggregation = false, $source = array(), $groupByField = null, $sort = array())
    {
        $result = array();
        $params = [
            'index' => $this->indexName,
            'body'  => [
                "_source" => $source,
                'query' => [
                    'bool' => $queryData,
                ],
                //'min_score'=>5,
                'sort' => $sort,
                'from' => $from,
                'size' => $size,
            ]
        ];
        if (isset($groupByField) && !empty($groupByField)) {
            $params['body']['collapse'] = [ "field" => $groupByField . '.keyword'];
        }
        if ($aggregation) {
            $params['body']['aggregations'] = [
                'min_price' => [ 'min' => ['field' => 'general.theprice' ] ],
                'max_price' => [ 'max' => ['field' => 'general.theprice' ] ]
            ];
        }
        try {
            $results = $this->client->search($params);
        } catch (error $e) {
            $this->setErrorMessage($e);
            return false;
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        
        if ($aggregation) {
            return $results;
        }

        return array_key_exists('hits', $results) ? $results['hits'] : $results;
    }
    

    /*	Creating Index into the ElasticSearch Host
    *
    */

    public function createIndex()
    {
        if ($this->isIndexExists()) {
            $this->error = Labels::getLabel('MSG_INDEX_ALREADY_EXISTS', $this->langId);
            return false;
        }
        $language = Language::getAttributesById($this->langId, "language_name");
        if (empty($language)) {
            $this->error = Labels::getLabel('MSG_NO_RECORD_FOUND', $this->langId);
            return false;
        }
        $params = [
            'index'  =>  $this->indexName,
            'body'   =>  [
                'settings' => [
                    'analysis' =>[
                        'filter' => [
                            mb_strtolower($language) . "_stop" => [ "type" => "stop","stopwords" => "_" . mb_strtolower($language) . "_"],
                            mb_strtolower($language) . "_stemmer"  => [ "type" => "stemmer", "language" => mb_strtolower($language) ]
                        ],
                        "analyzer" => [
                            "rebuilt_" . mb_strtolower($language) => [
                                "tokenizer" => "standard",
                                "filter"  => [ "lowercase", "decimal_digit", mb_strtolower($language) . "_stop", mb_strtolower($language) . "_stemmer", "snowball"]
                            ]
                        ]
                    ]
                ]
             ]
          ];

        // index name
        switch ($this->langId) {
            case '1':
                $arr = [
                        'type' => 'stemmer',
                        'language' => 'possessive_' . mb_strtolower($language)
                ];
            $params['body']['settings']['analysis']['filter'][ mb_strtolower($language) . '_possessive_stemmer'] = $arr;
            array_push($params['body']['settings']['analysis']['analyzer']["rebuilt_" . mb_strtolower($language) ]["filter"], mb_strtolower($language) . "_possessive_stemmer");
            break;
        }
                
        try {
            $response = $this->client->indices()->create($params);
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }

    /*	Deleting Index in ElasticSearch Host
    *
    */
    public function deleteIndex()
    {
        $params = ['index' => $this->indexName];
        try {
            $response = $this->client->indices()->delete($params);
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }
    
    /*	Adding Data to the ElasticSearch Index
    *
    *   @id  -  Pass Unique document Id
    *   @data - Pass Data array
    */

    public function createDocument($documentId, $data)
    {
        $params = [
            'index' => $this->indexName,
            'type' => 'data',
            'id' =>  $documentId,
            'body' => $data
        ];

        try {
            $response = $this->client->index($params);
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }

    /*	Deleting Data into the ElasticSearch Index
    *
    *	@documentId  - Pass Unique document Id
    *
    */

    public function deleteDocument($documentId)
    {
        if (!$this->client->isDocumentExists($documentId)) {
            return true;
        }

        $params = [
            'index' => $this->indexName,
            'id'    => $documentId
        ];
        try {
            $response = $this->client->delete($params);
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }
    
    /*	Updating Main Document Data To The ElasticSearch Index
    *
    *   @id  -  Pass Unique document Id
    *   @data - Pass Data array
    */
    public function updateDocument($documentId, $data)
    {
        $params = [
            'index' => $this->indexName,
            'id'    => $documentId,
            'body'  => [
                'doc' => $data
            ]
        ];
        try {
            $response = $this->client->update($params);
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }

    /*	Searching By Id into the ElasticSearch Index
    *
    *   @documentId  -  Pass Unique document Id
    *
    */

    public function isDocumentExists($documentId)
    {
        $params = [
            'index' => $this->indexName,
            'id' => $documentId
        ];
        try {
            $response = $this->client->get($params);
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }
    
    public function isIndexExists()
    {
        $params = [
            'index' => $this->indexName
        ];
        try {
            $response = $this->client->indices()->exists($params);
            if (!$response) {
                return false;
            }
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }
    
    /*	Updating Nested Data Into The ElasticSearch Index
    *
    *   @documentId     - Pass Unique Id document id
    *   @dataIndexName  - Pass data index name where you want to push(like inventory)
    *   @dataIndexArray - Pass data in array with key name and value name like in case of inventory ('selprod_id' => value);
    *   @data           - Pass the Data Parameters
    */

    public function updateDocumentData($documentId, $dataIndexName, $dataIndexArray, $data)
    {
        $response = $this->deleteDocumentData($documentId, $dataIndexName, $dataIndexArray);
        if (!$response) {
            return false;
        }

        $params = [
            'index' => $this->indexName,
            'id'    => $documentId,
            'body'  => [
                'script' => array(
                    "source" => "ctx._source." . $dataIndexName . ".add(params." . $dataIndexName . ")",
                    "params" => $data
                )
            ]
        ];
        try {
            $response = $this->client->update($params);
        } catch (exception $e) {
            $this->setErrorMessage($e);
            return false;
        }
        return true;
    }

    /*	Delete Nested Data Into The ElasticSearch Index
    *
    *   @documentId  - Pass Unique document Id
    *   @data - Pass Data array with selprod_id
    *   @dataIndexArray - Pass the array with keyname and value
    */

    public function deleteDocumentData($documentId, $dataIndexName, $dataIndexArray)
    {
        if (!is_array($dataIndexArray)) {
            return false;
        }
        $dataIndexColumnName = array_key_first($dataIndexArray);

        $params = [
            'index' => $this->indexName,
            'id'    => $documentId,
            'body'  => [
                'script' => array(
                    "source" => "ctx._source.".$dataIndexName.".removeIf(data -> data.".$dataIndexColumnName." == params.".$dataIndexColumnName.")",
                    "params" => $dataIndexArray
                )
            ]
        ];

        try {
            $response = $this->client->update($params);
        } catch (exception $e) {
            //$this->error = $e->getMessage();
            return true; // sending true because in case of document data not exists then we are pushing  if exits then delete the document data and then pushing
        }
        return true;
    }

    public function getError()
    {
        return $this->error;
    }
    
    private function setErrorMessage($e)
    {
        $error = json_decode($e->getMessage(), true);
        $this->error = isset($error['error']['reason']) ? $error['error']['reason'] : "error";
    }
}
