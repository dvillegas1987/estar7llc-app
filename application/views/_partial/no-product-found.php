<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $pSrchFrm->setFormTagAttribute('class', 'form custom-form');
    $pSrchFrm->setFormTagAttribute('name', 'frmSiteSearchCustom');
    $pSrchFrm->setFormTagAttribute('id', 'frm_fat_id_frmSiteSearch_custom');
    $keywordFld = $pSrchFrm->getField('keyword');
    $submitFld = $pSrchFrm->getField('btnSiteSrchSubmit');
    $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-wide');
    $keywordFld->setFieldTagAttribute('class', 'search--keyword--js');
    $keywordFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_for_Product...', $siteLangId));
    /* $keywordFld->setFieldTagAttribute('autofocus','autofocus'); */
    $keywordFld->setFieldTagAttribute('id', 'header_search_keyword');
    $keywordFld->setFieldTagAttribute('onkeyup', 'animation(this)'); ?>
<div class="container align--center">
    <div class="no-product">
        <div class="block--empty m-auto text-center">
            <img class="block__img" src="<?php echo CONF_WEBROOT_URL; ?>images/retina/empty_cart.svg" alt="<?php echo Labels::getLabel('LBL_No_Product_found', $siteLangId);?>">
            <h3><?php echo Labels::getLabel('LBL_WE_COULD_NOT_FIND_ANY_MATCHES!', $siteLangId); ?></h3>
            <h6><?php echo Labels::getLabel('LBL_Please_check_if_you_misspelt_something_or_try_searching_again_with_fewer_keywords.', $siteLangId); ?>
            </h6><br>
            <div class="row justify-content-center">
                <div class="col-md-6 mb-4">
                    <div class="query-form">
                        <?php echo $pSrchFrm->getFormTag(); ?>
                        <?php echo $pSrchFrm->getFieldHTML('keyword'); ?>
                        <?php echo $pSrchFrm->getFieldHTML('btnSiteSrchSubmit'); ?>
                        </form>
                        <?php echo $pSrchFrm->getExternalJS(); ?>
                    </div>
                </div>
            </div>
            <?php
            $top_searched_keywords = SearchItem::getTopSearchedKeywords();
            if (count($top_searched_keywords) > 0) : ?>
            <h6><br>
                <strong><?php echo Labels::getLabel('LBL_OR', $siteLangId)?></strong><br>
                <br> <?php echo Labels::getLabel('L_Popular_Searches', $siteLangId)?></strong> <br>
                <br></h6>
            <ul class="links--inline">
                <?php $inc = 0; foreach ($top_searched_keywords as $record) {
                    $inc++;
                    if ($inc >1) {
                        echo "";
                    } ?>
                    <li><a href="<?php echo UrlHelper::generateUrl('products', 'search', array( 'keyword-'.$record['searchitem_keyword'])); ?>"><?php echo $record['searchitem_keyword']?> </a> </li>
                <?php } ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$postedData['page'] = 1;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging','id' => 'frmProductSearchPaging'));
