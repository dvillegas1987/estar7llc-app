<?php

class UserFavorite extends SearchBase
{
    private $langId;
    private $productsJoined;
    private $sellerUserJoined;
    private $sellerProductsJoined;

    public const DB_TBL = 'tbl_user_favourite_products';
    public const DB_TBL_PREFIX = 'ufp_';

    public function __construct($langId = 0)
    {
        parent::__construct(Product::DB_TBL_PRODUCT_FAVORITE, 'ufp');
        $this->langId = FatUtility::int($langId);
        $this->productsJoined = false;
    }

    public static function getUserFavouriteItemCount($userId = 0)
    {
        $getFavouriteProducts = new SearchBase('(' . UserFavoriteProductSearch::joinFavouriteUserProductsCount($userId) . ') as productCount');
        $getFavouriteProducts->addfld('count(userFavProductcount_user_id) as totalFavouriteItems');
        $getFavouriteProducts->doNotCalculateRecords();
        $getFavouriteProducts->doNotLimitRecords();
        $countFavouriteItemsRs = $getFavouriteProducts->getResultSet();
        $totalFavouriteItems = FatApp::getDb()->fetch($countFavouriteItemsRs, 'totalFavouriteItems');
        return $totalFavouriteItems['totalFavouriteItems'];
    }
}
