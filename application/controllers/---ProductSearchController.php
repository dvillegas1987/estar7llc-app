<?php

class ProductSearchController extends MyAppController
{
	private $fullTextSearch;
	
	public function __construct($action)
    {
        parent::__construct($action);
		
    }

	public function search()
    {
        $this->productsData(__FUNCTION__);
    }

	public function filters()
    {
		$categoriesArr = array();
		$db = FatApp::getDb();
        $headerFormParamsAssocArr = FilterHelper::getParamsAssocArr();
		
		$categoryId = 0;
		if (array_key_exists('category', $headerFormParamsAssocArr)) {
            $categoryId = FatUtility::int($headerFormParamsAssocArr['category']);
        }
		
		$keyword = '';
        $langIdForKeywordSeach = 0;
        if (array_key_exists('keyword', $headerFormParamsAssocArr) && !empty($headerFormParamsAssocArr['keyword'])) {
            $keyword = $headerFormParamsAssocArr['keyword'];
            $langIdForKeywordSeach = $this->siteLangId;
			//$this->fullTextSearch->addKeywordCondition($keyword);
        }
		
		$fullTextSearch = $this->getFilterSearchObj($headerFormParamsAssocArr);
		
		/* Brand Filters Data[ */
		$brandsCheckedArr = FilterHelper::selectedBrands($headerFormParamsAssocArr);
		$brandsArr = $this->getBrands($fullTextSearch);
		/* ] */
		
		/* Category Filters Data[ */
			$categoriesArr = $this->getCategories($fullTextSearch);
		/* ] */
		
		/* Price Filters [ */
		
			unset($headerFormParamsAssocArr['doNotJoinSpecialPrice']);
			$priceArr = array();
			$priceArr = $this->getPrice($fullTextSearch);
			$priceInFilter = false;
			
			$filterDefaultMinValue = $priceArr['minPrice'];
			$filterDefaultMaxValue = $priceArr['maxPrice'];
			
			if ($this->siteCurrencyId != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1) || (array_key_exists('currency_id', $headerFormParamsAssocArr) && $headerFormParamsAssocArr['currency_id'] != $this->siteCurrencyId)) {
				$filterDefaultMinValue = CommonHelper::displayMoneyFormat($brandWithAggregations['aggregations']['min_price']['value'], false, false, false);
				$filterDefaultMaxValue = CommonHelper::displayMoneyFormat($brandWithAggregations['aggregations']['min_price']['value'], false, false, false);
				$priceArr['minPrice'] = $filterDefaultMinValue;
				$priceArr['maxPrice'] = $filterDefaultMaxValue;
			}
			
			if (array_key_exists('price-min-range', $headerFormParamsAssocArr) && array_key_exists('price-max-range', $headerFormParamsAssocArr)) {
				$priceArr['minPrice'] = $headerFormParamsAssocArr['price-min-range'];
				$priceArr['maxPrice'] = $headerFormParamsAssocArr['price-max-range'];
				$priceInFilter = true;
			}
			if (array_key_exists('currency_id', $headerFormParamsAssocArr) && $headerFormParamsAssocArr['currency_id'] != $this->siteCurrencyId) {
				$priceArr['minPrice'] = CommonHelper::convertExistingToOtherCurrency($headerFormParamsAssocArr['currency_id'], $headerFormParamsAssocArr['price-min-range'], $this->siteCurrencyId, false);
				$priceArr['maxPrice'] = CommonHelper::convertExistingToOtherCurrency($headerFormParamsAssocArr['currency_id'], $headerFormParamsAssocArr['price-max-range'], $this->siteCurrencyId, false);
			}
			
		/* Price Filters ] */
		

		$productFiltersArr = array();
		$shopCatFilters    = array();
		$prodcatArr        = array();
		$optionValueCheckedArr = array();
		$conditionsArr  = array();
		$conditionsCheckedArr  = array();
		$options = array();
		
		$availabilityArr = array();
		$availability = array();

        $this->set('productFiltersArr', $productFiltersArr);
        $this->set('headerFormParamsAssocArr', $headerFormParamsAssocArr);
        $this->set('categoriesArr', $categoriesArr);
        $this->set('shopCatFilters', $shopCatFilters);
        $this->set('prodcatArr', $prodcatArr);
        // $this->set('productCategories',$productCategories);
        $this->set('brandsArr', $brandsArr);
        $this->set('brandsCheckedArr', $brandsCheckedArr);
        $this->set('optionValueCheckedArr', $optionValueCheckedArr);
        $this->set('conditionsArr', $conditionsArr);
        $this->set('conditionsCheckedArr', $conditionsCheckedArr);
        $this->set('options', $options);
        $this->set('priceArr', $priceArr);
        $this->set('priceInFilter', $priceInFilter);
        $this->set('filterDefaultMinValue', $filterDefaultMinValue);
        $this->set('filterDefaultMaxValue', $filterDefaultMaxValue);
        $this->set('availability', $availability);
        $availabilityArr = (true ===  MOBILE_APP_API_CALL) ? array_values($availabilityArr) : $availabilityArr;
        $this->set('availabilityArr', $availabilityArr);

        if (true ===  MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        echo $this->_template->render(false, false, 'productSearch/filters.php', true);
        exit;
	}
	
	private function getCategories($fullTextSearchObj)
	{
		$fullTextSearchObj->setPageNumber(0);
		$fullTextSearchObj->setPageSize(1000);
		$fullTextSearchObj->setFields(array('categories.prodcat_id','categories.prodcat_code','categories.prodcat_name','categories.prodcat_ordercode'));
		$categories = $fullTextSearchObj->fetch();
		$categories = $this->removeElasticSourceIndex($categories,'categories');
		
		$filterCategory = array();
		if(isset($categories) && !empty($categories))
		{
			foreach($categories as $key =>$category)
			{
				foreach($category as $catCode)
				{
					$filterCategory[ $catCode['prodcat_code'] ] = $catCode['prodcat_name'];
					/*explode('-',$catCode['prodcat_code']);
					$filterCategory[ $catCode['prodcat_id'] ][$key]['prodcat_code'] = $catCode['prodcat_code'];
					$filterCategory[ $catCode['prodcat_id'] ][$key]['prodcat_name'] = $catCode['prodcat_name'];
					$filterCategory[ $catCode['prodcat_id'] ][$key]['prodcat_ordercode'] = $catCode['prodcat_ordercode'];*/
					
				}
			}
		}
		//asort($filterCategory);
		
		return $categories;
	}
	
	private function getBrands($fullTextSearchObj)
	{
		$brandSorting = array('brand.brand_name.keyword' => array('order'=>'asc'));
		
		$fullTextSearchObj->setFields(array('brand.brand_id','brand.brand_name'));
		$fullTextSearchObj->setSortFields( $brandSorting );
		$fullTextSearchObj->setGroupByField('brand.brand_name');
		$fullTextSearchObj->setPageNumber(0);
		$fullTextSearchObj->setPageSize(1000);
		$brands = $fullTextSearchObj->fetch();
		$brands = $this->removeElasticSourceIndex($brands,'brand');
		return $brands;
	}
	
	private function getPrice($fullTextSearch)
	{
		$priceArr = array();
		$fullTextSearch->setFields(array('general.product_id'));
		$fullTextSearch->setPageNumber(0);
		$fullTextSearch->setPageSize(1000);
		$price = $this->fullTextSearch->fetch(true);
		
		if(array_key_exists('aggregations',$price))
		{
			$priceArr['minPrice'] = $price['aggregations']['min_price']['value'];
			$priceArr['maxPrice'] = $price['aggregations']['max_price']['value'];
		}
		
		return $priceArr;
	}

	private function productsData($method)
    {
        $db = FatApp::getDb();
        $get = Product::convertArrToSrchFiltersAssocArr(FatApp::getParameters());
		
        $includeKeywordRelevancy = false;
        $keyword = '';
        if (array_key_exists('keyword', $get)) {
            $includeKeywordRelevancy = true;
            $keyword = $get['keyword'];
        }

        $frm = $this->getProductSearchForm($includeKeywordRelevancy);

        $arr = array();

        switch ($method) {
            case 'index':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_All_PRODUCTS', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'index'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_PRODUCT_INDEX,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'allProducts'),
                );
                break;
            case 'search':
                $arr = array(
                    'pageTitle'=> Labels::getLabel('LBL_Search_results_for', $this->siteLangId),
                    'canonicalUrl'=>UrlHelper::generateFullUrl('Products', 'search'),
                    'productSearchPageType'=>SavedSearchProduct::PAGE_PRODUCT,
                    'bannerListigUrl'=>UrlHelper::generateFullUrl('Banner', 'searchListing'),
                    'keyword' => $keyword,
                );
                break;
            case 'featured':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_FEATURED_PRODUCTS', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'featured'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_FEATURED_PRODUCT,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'searchListing'),
                );
                $get['featured'] = 1;
                break;
        }

        $frm->fill($get);
		$get['join_price'] = 1;

        $data = $this->getListingData($get);

        $common = array(
            'frmProductSearch' => $frm,
            'recordId' => 0,
            'showBreadcrumb' => false
        );

        $data = array_merge($data, $common, $arr);
        if (FatUtility::isAjaxCall()) {
            $this->set('products', $data['products']);
            $this->set('page', $data['page']);
            $this->set('pageCount', $data['pageCount']);
            $this->set('postedData', $get);
            $this->set('recordCount', $data['recordCount']);
            $this->set('siteLangId', $this->siteLangId);
            echo $this->_template->render(false, false, 'productSearch/products-list.php', true);
            exit;
        }

        $this->set('data', $data);

        $this->includeProductPageJsCss();
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true, 'productSearch/index.php');
    }

	

	private function getListingData($get)
    {
		$categoryId = null;
		$category = array();
		$this->fullTextSearch = new FullTextSearch($this->siteLangId);
		
		if (array_key_exists('keyword', $get)) {
			$this->fullTextSearch->addKeywordCondition($get['keyword']);
        }
		
		if (array_key_exists('brand', $get)) {
			$this->fullTextSearch->addBrandConditions($get['brand']);
		}
		
		if( array_key_exists('price-min-range',$get) && array_key_exists('price-max-range',$get) )
		{
			$this->fullTextSearch->addPriceFilters($get['price-min-range'], $get['price-max-range']);
		}
		
		if( array_key_exists('category',$get) )
		{
			$this->fullTextSearch->addCategoryFilter($get['category']);
		}
		
		$pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
		if (array_key_exists('pageSize', $get))
		{
			$pageSize = FatUtility::int($get['pageSize']);
        }
		
		$page = 1;
        if (array_key_exists('page', $get)) {
            $page = FatUtility::int($get['page']);
			/*if ($page < 2) {
                $page = 1;
            }*/
        }
		$from =  ($page - 1) * $pageSize;
		$this->fullTextSearch->setPageNumber($from);
		$this->fullTextSearch->setPageSize($pageSize);
		$this->fullTextSearch->setSortFields(array('general.product_id'=>array('order' => 'desc')));
		$response = $this->fullTextSearch->fetch();
		$products = $response['hits'];
		$total = FatUtility::int($response['total']['value']);
		$pageCount = $this->totalPageCount($total, $pageSize);
		
		$data = array(
            'products' => $products,
            'category' => $category,
            'categoryId' => $categoryId,
            'postedData' => $get,
            'page' => $page,
            'pageCount' => $pageCount,
            'pageSize' =>  $pageSize,
            'recordCount'=> $total,
            'siteLangId'=> $this->siteLangId
        );
		return $data;
	}
	
	
	public function getFilterSearchObj($criteria)
	{
		$this->fullTextSearch = new FullTextSearch($this->siteLangId);
		
		if (array_key_exists('keyword', $criteria)) {
			$this->fullTextSearch->addKeywordCondition($criteria['keyword']);
        }
		
		if (array_key_exists('brand', $criteria)) {
			$this->fullTextSearch->addBrandConditions($criteria['brand']);
		}
		return $this->fullTextSearch;
	}
	

	private function totalPageCount($total, $pageSize)
	{
		return ceil($total/$pageSize);
	}
	
	private function removeElasticSourceIndex($dataValues, $filterKey = null)
	{
		$returnData = array();
		foreach($dataValues['hits'] as $key => $value)
		{
			if(empty($filterKey))
			{
				$returnData[$key] = $value['_source'];
			}
			else
			{
				$returnData[$key] = $value['_source'][$filterKey];
			}

		}
		return $returnData;
	}

}
