<?php

class MetaTag extends MyAppModel
{
    public const DB_TBL = 'tbl_meta_tags';
    public const DB_TBL_PREFIX = 'meta_';

    public const DB_TBL_LANG = 'tbl_meta_tags_lang';
    public const DB_TBL_LANG_PREFIX = 'metalang_';

    public const META_GROUP_ALL_PRODUCTS = 'all_product';
    public const META_GROUP_PRODUCT_DETAIL = 'product_view';
    public const META_GROUP_ALL_SHOPS = 'all_shop';
    public const META_GROUP_SHOP_DETAIL = 'shop_view';
    public const META_GROUP_CMS_PAGE = 'cms_page_view';
    public const META_GROUP_DEFAULT = 'default';
    public const META_GROUP_ADVANCED = 'advanced_setting';
    public const META_GROUP_ALL_BRANDS = 'all_brand';
    public const META_GROUP_BRAND_DETAIL = 'brand_view';
    public const META_GROUP_CATEGORY_DETAIL = 'category_view';
    public const META_GROUP_BLOG_PAGE = 'BLOG_PAGE';
    public const META_GROUP_BLOG_CATEGORY = 'Blog_Category';
    public const META_GROUP_BLOG_POST = 'Blog_Post';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->objMainTableRecord->setSensitiveFields([static::DB_TBL_PREFIX . 'id']);
    }

    public static function getTabsArr($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if (!$langId) {
            $langId = CommonHelper::getLangId();
        }
        $metaGroups = array(
        static::META_GROUP_ALL_PRODUCTS => array(
        'serial' => 2,
        'name' => Labels::getLabel('LBL_All_Products', $langId),
        'controller' => 'Products',
        'action' => 'index',
        'isEntity' => false
        ),
        static::META_GROUP_PRODUCT_DETAIL => array(
        'serial' => 3,
        'name' => Labels::getLabel('LBL_Product_Detail', $langId),
        'controller' => 'Products',
        'action' => 'view',
        'Entity Caption' => Labels::getLabel('LBL_Product', $langId),
        'isEntity' => true
        ),
        static::META_GROUP_ALL_SHOPS => array(
        'serial' => 4,
        'name' => Labels::getLabel('LBL_All_Shops', $langId),
        'controller' => 'Shops',
        'action' => 'index',
        'isEntity' => false
        ),
        static::META_GROUP_SHOP_DETAIL => array(
        'serial' => 5,
        'name' => Labels::getLabel('LBL_Shop_Detail', $langId),
        'controller' => 'Shops',
        'action' => 'view',
        'Entity Caption' => Labels::getLabel('LBL_Shop', $langId),
        'isEntity' => true
        ),
        static::META_GROUP_CMS_PAGE => array(
        'serial' => 6,
        'name' => Labels::getLabel('LBL_CMS_Page', $langId),
        'controller' => 'Cms',
        'action' => 'view',
        'isEntity' => false
        ),
        static::META_GROUP_DEFAULT => array(
        'serial' => 0,
        'name' => Labels::getLabel('LBL_Default', $langId),
        'controller' => '',
        'action' => '',
        'isEntity' => false
        ),
        static::META_GROUP_ADVANCED => array(
        'serial' => 99,
        'name' => Labels::getLabel('LBL_Advanced_Setting', $langId),
        'controller' => '',
        'action' => '',
        'isEntity' => false
        ),
        static::META_GROUP_ALL_BRANDS => array(
        'serial' => 7,
        'name' => Labels::getLabel('LBL_All_Brands', $langId),
        'controller' => 'Brands',
        'action' => 'index',
        'isEntity' => false
        ),
        static::META_GROUP_BRAND_DETAIL => array(
        'serial' => 8,
        'name' => Labels::getLabel('LBL_Brand_Detail', $langId),
        'controller' => 'Brands',
        'action' => 'view',
        'Entity Caption' => Labels::getLabel('LBL_Brand_Detail', $langId),
        'isEntity' => true
        ),
        static::META_GROUP_CATEGORY_DETAIL => array(
        'serial' => 9,
        'name' => Labels::getLabel('LBL_Category_Detail', $langId),
        'controller' => 'Category',
        'action' => 'view',
        'Entity Caption' => Labels::getLabel('LBL_Category_Detail', $langId),
        'isEntity' => true
        ),
        static::META_GROUP_BLOG_PAGE => array(
        'serial' => 10,
        'name' => Labels::getLabel('LBL_Blog_Page', $langId),
        'controller' => 'Blog',
        'action' => 'index',
        'Entity Caption' => Labels::getLabel('LBL_Blog_Page', $langId),
        'isEntity' => true
        ),
        static::META_GROUP_BLOG_CATEGORY => array(
        'serial' => 11,
        'name' => Labels::getLabel('LBL_Blog_Category', $langId),
        'controller' => 'Blog',
        'action' => 'category',
        'Entity Caption' => Labels::getLabel('LBL_Blog_Category', $langId),
        'isEntity' => true
        ),
        static::META_GROUP_BLOG_POST => array(
        'serial' => 12,
        'name' => Labels::getLabel('LBL_Blog_Post', $langId),
        'controller' => 'Blog',
        'action' => 'postDetail',
        'Entity Caption' => Labels::getLabel('LBL_Blog_Post', $langId),
        'isEntity' => true
        )
        );

        uasort(
            $metaGroups,
            function ($group1, $group2) {
                if ($group1['serial'] == $group2['serial']) {
                    return 0;
                }
                return ($group1['serial'] < $group2['serial']) ? -1 : 1;
            }
        );

        return $metaGroups;
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'mt');

        return $srch;
    }
}
