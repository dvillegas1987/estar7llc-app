<?php
if (!empty($collections)) {
    switch ($collection['collection_type']) {
        case Collections::COLLECTION_TYPE_PRODUCT: ?>           
                <?php $this->includeTemplate('products/products-list.php', array('products' => $collections, 'pageCount' => $pageCount, 'recordCount' => $recordCount, 'siteLangId' => $siteLangId, 'colMdVal' => 5), false);    ?>
            
        <?php break;
        case Collections::COLLECTION_TYPE_CATEGORY:
            $this->includeTemplate('category/categories-list.php', array('categoriesArr' => $collections, 'siteLangId' => $siteLangId), false);
            break;

        case Collections::COLLECTION_TYPE_SHOP:
            $this->includeTemplate('shops/search.php', array('allShops' => $collections, 'siteLangId' => $siteLangId, 'totalProdCountToDisplay' => $totalProdCountToDisplay), false);
            break;

        case Collections::COLLECTION_TYPE_BRAND:
            $this->includeTemplate('brands/brands-list.php', array('brandsArr' => $collections, 'siteLangId' => $siteLangId), false);
            break;

        case Collections::COLLECTION_TYPE_BLOG:
            $this->includeTemplate('blog/collection-list.php', array('collections' => $collections[Collections::TYPE_BLOG_LAYOUT1], 'siteLangId' => $siteLangId), false);
            break;
    }
} else {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false);
}
