<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php');
?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row ">
            <div class="col">
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Requests', $siteLangId); ?>
                </h2>
            </div>
            <?php if ($canEdit && !$noRecordFound) { ?>
            <div class="col-auto">
                <div class="dropdown dashboard-user">
                    <button class="btn btn-outline-brand dropdown-toggle" type="button" id="dashboardDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo Labels::getLabel('LBL_New_Request', $siteLangId); ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim" aria-labelledby="dashboardDropdown">
                        <ul class="nav nav-block">
                            <?php if (FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) { ?>
                                <li class="nav__item">
                                    <a class="dropdown-item nav__link"
                                        href="<?php echo UrlHelper::generateUrl('Seller', 'customCatalogProductForm'); ?>"><?php echo Labels::getLabel('LBL_Marketplace_Product', $siteLangId);?></a>
                                </li>
                            <?php } ?>
                            <?php if (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) { ?>
                            <li class="nav__item">
                                <a class="dropdown-item nav__link" href="javascript:void(0);"
                                    onClick="addBrandReqForm(0)"><?php echo Labels::getLabel('LBL_Brand', $siteLangId);?></a>
                            </li>
                            <?php } ?>
                            <?php if (FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) { ?>
                            <li class="nav__item">
                                <a class="dropdown-item nav__link" href="javascript:void(0);"
                                    onClick="addCategoryReqForm(0)"><?php echo Labels::getLabel('LBL_Category', $siteLangId);?></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <?php if ($noRecordFound) { ?>
								<div class="row justify-content-center my-5">
									<div class="col-md-6">
										<div class="info">
											<span> <svg class="svg">
												<use xlink:href="/yokartv8/images/retina/sprite.svg#info" href="/yokartv8/images/retina/sprite.svg#info">
												</use>
											</svg><?php echo Labels::getLabel('LBL_Generate_requests_using_buttons_below', $siteLangId); ?></span>
										</div>							
									</div>
								</div>
                                <div class="row justify-content-center">
                                    <?php if (FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) { ?>
                                        <div class="col-md-4">
                                            <div class="no-data-found">
                                                <div class="img">
                                                    <img src="images/retina/no-product-requests.svg" width="70px" height="70px">
                                                </div>
                                                <div class="data">
                                                    <div class="action">
                                                        <a class="btn btn-outline-brand btn-sm" href="<?php echo UrlHelper::generateUrl('Seller', 'customCatalogProductForm'); ?>"><?php echo Labels::getLabel('LBL_New_Product_Request', $siteLangId);?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
									<?php if (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) { ?>
                                    <div class="col-md-4">
                                        <div class="no-data-found">
                                            <div class="img">
                                                <img src="images/retina/no-brand-requests.svg" width="70px" height="70px">
                                            </div>
                                            <div class="data">
                                                <div class="action">
                                                    <a class="btn btn-outline-brand btn-sm" href="javascript:void(0);" onClick="addBrandReqForm(0)"><?php echo Labels::getLabel('LBL_New_Brand_Request', $siteLangId);?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<?php } ?>
									<?php if (FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) { ?>
                                    <div class="col-md-4">
                                        <div class="no-data-found">
                                            <div class="img">
                                                <img src="images/retina/no-category-requests.svg" width="70px" height="70px">
                                            </div>
                                            <div class="data">
                                                <div class="action">
                                                    <a class="btn btn-outline-brand btn-sm" href="javascript:void(0);" onClick="addCategoryReqForm(0)"><?php echo Labels::getLabel('LBL_New_Category_Request', $siteLangId);?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<?php } ?>
                                </div>
                            <?php } else { ?>
                                <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $siteLangId); ?></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
var ratioTypeSquare = <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?> ;
var ratioTypeRectangular = <?php echo AttachedFile::RATIO_TYPE_RECTANGULAR; ?> ;
var canRequestCustomProduct = <?php echo FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0); ?>;
$(document).ready(function() {
    if (canRequestCustomProduct) {
        searchCustomCatalogProducts(document.frmSearchCustomCatalogProducts);
    } else {
        searchBrandRequests(document.frmSearchBrandRequest);
    }
});
</script>