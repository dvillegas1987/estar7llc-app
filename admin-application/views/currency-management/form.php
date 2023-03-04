<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupCurrency(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

if ($defaultCurrency) {
    $fld = $frm->getField('currency_value');
    $fld->htmlAfterField = '<small>' . Labels::getLabel('LBL_This_is_your_default_currency', $adminLangId) . '</small>';
}
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Currency_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="tabs_nav_container responsive flat">
            <ul class="tabs_nav">
                <li><a class="active" href="javascript:void(0)"
                        onclick="currencyForm(<?php echo $currency_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                </li>
                <li
                    class="<?php echo ($currency_id == 0) ? 'fat-inactive' : ''; ?>">
                    <a href="javascript:void(0);" <?php echo ($currency_id) ? "onclick='editCurrencyLangForm(" . $currency_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                        <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                    </a>
                </li>
            </ul>
            <div class="tabs_panel_wrap">
                <div class="tabs_panel">
                    <?php echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</section>