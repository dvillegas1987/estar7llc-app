<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$keywordFld = $headerSrchFrm->getField('keyword');
$submitFld = $headerSrchFrm->getField('btnSiteSrchSubmit');
$submitFld->setFieldTagAttribute('class', 'search--btn submit--js');
$keywordFld->setFieldTagAttribute('class', 'search--keyword search--keyword--js no--focus');
$keywordFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_I_am_looking_for...', $siteLangId));
$keywordFld->setFieldTagAttribute('id', 'header_search_keyword');
$selectFld = $headerSrchFrm->getField('category');
$selectFld->setFieldTagAttribute('id', 'searched_category');
?>

<div class="main-search">
    <a href="javascript:void(0)" class="toggle--search" data-trigger="form--search-popup"> <span class="icn"><svg
                class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#magnifying"
                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#magnifying"></use>
            </svg></span></a>
    <div class="form--search form--search-popup" id="form--search-popup"
        data-close-on-click-outside="form--search-popup">
        <?php echo $headerSrchFrm->getFormTag(); ?>
        <div class="main-search__field"><?php echo $headerSrchFrm->getFieldHTML('keyword'); ?>
            <div id="search-suggestions-js">
               
            </div>
        </div>

        <?php echo $headerSrchFrm->getFieldHTML('category'); ?>
        <?php echo $headerSrchFrm->getFieldHTML('btnSiteSrchSubmit'); ?>
        </form>
        <?php echo $headerSrchFrm->getExternalJS(); ?>
    </div>
</div>