<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>

<?php
$showDefalultLi = true;
if ($languages && count($languages) > 1) {
    $showDefalultLi = false;
?>
    <li>
        <div class="dropdown dropdown--lang">
            <a class="dropdown-toggle no-after" data-toggle="dropdown" data-display="static" href="javascript:void(0)">
                <?php if ($languages[$siteLangId]['language_country_code']) { ?> <img class="icon--img" alt="<?php echo Labels::getLabel('LBL_Language_Flag', $siteLangId); ?>" src="<?php echo CONF_WEBROOT_URL; ?>images/flags/<?php echo FatApp::getConfig('CONF_COUNTRY_FLAG_TYPE', FatUtility::VAR_STRING, 'round'); ?>/<?php echo $languages[$siteLangId]['language_country_code'] . '.svg'; ?>"> <?php } ?>
                <span><?php echo $languages[$siteLangId]['language_name']; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-fit dropdown-menu-anim">
                <div class="scroll-y" data-simplebar>
                    <ul class="nav nav-block">
                        <li class="nav__item">
                            <h6 class="dropdown-header expand-heading"><?php echo Labels::getLabel('LBL_Select_Language', $siteLangId); ?></h6>
                        </li>
                        <?php foreach ($languages as $langId => $language) { ?>
                            <li class="<?php echo ($siteLangId == $langId) ? 'nav__item is-active' : 'nav__item'; ?>"><a class="dropdown-item nav__link" href="javascript:void(0);" onClick="setSiteDefaultLang(<?php echo $langId; ?>)"><?php if ($language['language_country_code']) { ?> <img class="icon--img" alt="<?php echo Labels::getLabel('LBL_Language_Flag', $siteLangId); ?>" src="<?php echo CONF_WEBROOT_URL; ?>images/flags/<?php echo FatApp::getConfig('CONF_COUNTRY_FLAG_TYPE', FatUtility::VAR_STRING, 'round'); ?>/<?php echo $language['language_country_code'] . '.svg'; ?>"> <?php } ?> <?php echo ' ' . $language['language_name']; ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>

    </li>
<?php }
if ($currencies && count($currencies) > 1) {
    $showDefalultLi = false;
?>
    <li>
        <div class="dropdown dropdown--currency">
            <a class="dropdown-toggle no-after" data-toggle="dropdown" href="javascript:void(0)">
                <?php echo (CommonHelper::getCurrencySymbolRight()) ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft(); ?> <span> <?php echo $currencies[$siteCurrencyId]; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-fit dropdown-menu-anim">
                <div class="scroll-y" data-simplebar>
                    <ul class="nav nav-block">
                        <li class="nav__item">
                            <h6 class="dropdown-header expand-heading"><?php echo Labels::getLabel('LBL_Select_Currency', $siteLangId); ?></h6>
                        </li>
                        <?php foreach ($currencies as $currencyId => $currency) { ?>
                            <li class="<?php echo ($siteCurrencyId == $currencyId) ? 'nav__item is-active' : 'nav__item'; ?>">
                                <a class="dropdown-item nav__link" href="javascript:void(0);" onClick="setSiteDefaultCurrency(<?php echo $currencyId; ?>)"> <?php echo $currency; ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </li>
<?php }

/*if ($showDefalultLi) {            ?>
    <li class="dropdown dropdown--arrow">
        <a href="javascript:void(0)" class="dropdown__trigger dropdown__trigger-js"><i class="icn-language"><img class="icon--img"> </i><span></span> </a></li>
<?php } */
?>