<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (empty($products)) {
    $pSrchFrm = Common::getSiteSearchForm();
    $pSrchFrm->fill(array('btnSiteSrchSubmit' => Labels::getLabel('LBL_Submit', $siteLangId)));
    $pSrchFrm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this); return(false);');

    $this->includeTemplate('_partial/no-product-found.php', array('pSrchFrm' => $pSrchFrm, 'siteLangId' => $siteLangId, 'postedData' => $postedData), true);
    return;
}

$frmProductSearch->setFormTagAttribute('onSubmit', 'searchProducts(this); return(false);');
$keywordFld = $frmProductSearch->getField('keyword');
// $keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search', $siteLangId));
// $keywordFld = $frmProductSearch->getField('keyword');
$keywordFld->overrideFldType("hidden");

$sortByFld = $frmProductSearch->getField('sortBy');
$sortByFld->addFieldTagAttribute('class', 'custom-select sorting-select');

$pageSizeFld = $frmProductSearch->getField('pageSize');
$pageSizeFld->addFieldTagAttribute('class', 'custom-select sorting-select');

$desktop_url = '';
$tablet_url = '';
$mobile_url = '';
$category['banner'] = isset($category['banner']) ? (array) $category['banner'] : array();
if (!empty($category['banner'])) {
    $desktop_url = UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'DESKTOP', applicationConstants::SCREEN_DESKTOP));
    $tablet_url = UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'TABLET', applicationConstants::SCREEN_MOBILE));
    $mobile_url = UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'MOBILE', applicationConstants::SCREEN_IPAD));
    $catBannerArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, $siteLangId);
    foreach ($catBannerArr as $slideScreen) {
        $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
        switch ($slideScreen['afile_screen']) {
            case applicationConstants::SCREEN_MOBILE:
                $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, 0, applicationConstants::SCREEN_MOBILE);
                $mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'MOBILE', applicationConstants::SCREEN_MOBILE)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                break;
            case applicationConstants::SCREEN_IPAD:
                $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, 0, applicationConstants::SCREEN_IPAD);
                $tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'TABLET', applicationConstants::SCREEN_IPAD)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                break;
            case applicationConstants::SCREEN_DESKTOP:
                $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id'], 0, 0, applicationConstants::SCREEN_DESKTOP);
                $desktop_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'Banner', array($category['prodcat_id'], $siteLangId, 'DESKTOP', applicationConstants::SCREEN_DESKTOP)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                break;
        } ?>
    <?php } ?>
    <section class="bg-shop">
        <div class="shop-banner">
            <picture>
                <source data-aspect-ratio="4:3" srcset="<?php echo rtrim($mobile_url, ','); ?>" media="(max-width: 767px)">
                <source data-aspect-ratio="4:3" srcset="<?php echo rtrim($tablet_url, ','); ?>" media="(max-width: 1024px)">
                <source data-aspect-ratio="4:1" srcset="<?php echo rtrim($desktop_url, ','); ?>">
                <img data-aspect-ratio="4:1" src="<?php echo $desktop_url; ?>" alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $pageTitle; ?>" title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $pageTitle; ?>">
            </picture>
        </div>
        <?php /* if (!empty($category['prodcat_description']) && array_key_exists('prodcat_description', $category)) { ?>
        <div class="page-category__content">
            <p><?php  echo FatUtility::decodeHtmlEntities($category['prodcat_description']); ?></p>
        </div>
        <?php } */ ?>
    </section>
    <?php }
if (array_key_exists('brand_id', $postedData) && $postedData['brand_id'] > 0) {
    $brandImgArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, $siteLangId);
    if (!empty($brandImgArr)) {
        foreach ($brandImgArr as $slideScreen) {
            $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
            switch ($slideScreen['afile_screen']) {
                case applicationConstants::SCREEN_MOBILE:
                    $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, 0, applicationConstants::SCREEN_MOBILE);
                    $mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'MOBILE', 0, applicationConstants::SCREEN_MOBILE)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                    break;
                case applicationConstants::SCREEN_IPAD:
                    $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, 0, applicationConstants::SCREEN_IPAD);
                    $tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'TABLET', 0, applicationConstants::SCREEN_IPAD)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                    break;
                case applicationConstants::SCREEN_DESKTOP:
                    $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_BRAND_IMAGE, $postedData['brand_id'], 0, 0, applicationConstants::SCREEN_DESKTOP);
                    $desktop_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'BrandImage', array($postedData['brand_id'], $siteLangId, 'DESKTOP', 0, applicationConstants::SCREEN_DESKTOP)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
                    break;
            }
        } ?>
        <section class="bg-shop">
            <div class="shop-banner">
                <picture>
                    <source data-aspect-ratio="4:3" srcset="<?php echo $mobile_url; ?>" media="(max-width: 767px)">
                    <source data-aspect-ratio="4:3" srcset="<?php echo $tablet_url; ?>" media="(max-width: 1024px)">
                    <source data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>">
                    <img data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>" alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $pageTitle; ?>" title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $pageTitle; ?>">
                </picture>

            </div>
        </section>
    <?php } ?>
<?php } ?>

<?php if (isset($pageTitle)) { ?>
    <section class="bg-second pt-3 pb-3">
        <div class="container">
            <div class="section-head section--white--head section--head--center mb-0">
                <div class="section__heading">
                    <h1 class="mb-0">
                        <?php $keywordStr = '';
                        if (isset($keyword) && !empty($keyword)) {
                            $short_keyword = (mb_strlen($keyword) > 20) ? mb_substr($keyword, 0, 20) . "..." : $keyword;
                            $keywordStr = '<span title="' . $keyword . '" class="search-results">"' . $short_keyword . '"</span>';
                        }
                        echo $pageTitle; ?> <?php echo $keywordStr; ?></h1>
                    <?php if (isset($showBreadcrumb) && $showBreadcrumb) { ?>
                        <div class="breadcrumbs breadcrumbs--white breadcrumbs--center">
                            <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php'); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
<?php } ?>
<?php $this->includeTemplate('_partial/productsSearchForm.php', array('frmProductSearch' => $frmProductSearch, 'siteLangId' => $siteLangId, 'recordCount' => $recordCount, 'pageTitle' => (isset($pageTitle)) ? $pageTitle : 'Products'), false);  ?>
<div class="section">
    <div class="container">
        <div class="collection-listing <?php echo FatApp::getConfig('CONF_FILTERS_LAYOUT', FatUtility::VAR_INT, 1) == FilterHelper::LAYOUT_TOP ? 'filter-top' : 'filter-left'; ?>">

            <?php
            /*
                if (FatApp::getConfig('CONF_FILTERS_LAYOUT', FatUtility::VAR_INT, 1) == FilterHelper::LAYOUT_TOP) {
                    require_once('filters-layout-top.php');
                } else {
                    require_once('filters-layout-left.php');
                } 
                */
            require_once('filters-layout.php');

            ?>

            <main class="collection-content">
                <button class="btn btn-float link__filter btn--filters-control" data-trigger="collection-sidebar"><i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter"></use>
                        </svg>
                    </i></button>
                <div class="row align-items-center justify-content-between flex-column flex-md-row page-sort-wrap">
                    <div class="col mb-3 mb-md-0">
                        <div class="total-products">
                            <h4>
                                <?php echo isset($scollection_name) && !empty($scollection_name) ? $scollection_name : ''; ?>
                                <span class="hide_on_no_product">
                                    <small class="text-muted">
                                        <span id="total_records"><?php echo $recordCount; ?></span> <?php echo Labels::getLabel('LBL_ITEM(S)', $siteLangId); ?>
                                    </small>
                                </span>
                            </h4>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div id="top-filters" class="page-sort hide_on_no_product">
                            <ul>
                                <li class="list__item">
                                    <?php if (!(UserAuthentication::isUserLogged()) || (UserAuthentication::isUserLogged() && (User::isBuyer()))) { ?>
                                        <a href="javascript:void(0)" onclick="saveProductSearch()" class="btn btn-brand btn--filters-control saveSearch-js">
                                            <i class="icn fas fa-file-download d-md-none"></i><span class="txt"><?php echo Labels::getLabel('LBL_Save_Search', $siteLangId); ?></span></a>
                                    <?php } ?>
                                </li>
                                <li>
                                    <?php echo $frmProductSearch->getFieldHtml('sortBy'); ?></li>
                                <li>
                                    <?php echo $frmProductSearch->getFieldHtml('pageSize'); ?></li>

                                <li class="d-none d-md-block">
                                    <div class="list-grid-toggle switch--link-js">
                                        <div class="icon">
                                            <div class="icon-bar"></div>
                                            <div class="icon-bar"></div>
                                            <div class="icon-bar"></div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="listing-products -listing-products ">
                    <?php
                    $productsData = array(
                        'products' => $products,
                        'page' => $page,
                        'pageCount' => $pageCount,
                        'postedData' => $postedData,
                        'recordCount' => $recordCount,
                        'siteLangId' => $siteLangId,
                        'colMdVal' => 4
                    );
                    $this->includeTemplate('products/products-list.php', $productsData, false); ?>
                </div>
            </main>

        </div>
    </div>
</div>
<section>
    <div class="container">
        <div class="row">
            <div class="col-md-3 col--left col--left-adds">
                <div class="wrapper--adds">
                    <div class="grids" id="searchPageBanners">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="gap"></div>
<script>
    $(document).ready(function() {
        $currentPageUrl = "<?php echo html_entity_decode($canonicalUrl, ENT_QUOTES, 'utf-8'); ?>";
        $productSearchPageType = '<?php echo $productSearchPageType; ?>';
        $recordId = <?php echo $recordId; ?>;
        /* bannerAdds('<?php echo $bannerListigUrl; ?>'); */
        loadProductListingfilters(document.frmProductSearch);
    });
</script>