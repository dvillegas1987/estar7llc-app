<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupGateway(this); return(false);');
$ratioFld = $frm->getField('ratio_type');
$ratioFld->addFieldTagAttribute('class', 'prefRatio-js');
$ratioFld->addOptionListTagAttribute('class', 'list-inline');
$fld = $frm->getField('plugin_icon');
$fld->addFieldTagAttribute('onChange', 'popupImage(this)');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Payment_Method_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="tabs_nav_container responsive flat">
            <ul class="tabs_nav">
                <li><a class="active" href="javascript:void(0)" onclick="gatewayForm(<?php echo $plugin_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                <li class="<?php echo (0 == $plugin_id) ? 'fat-inactive' : ''; ?>">
                    <a href="javascript:void(0);" <?php echo (0 < $plugin_id) ? "onclick='editGatewayLangForm(" . $plugin_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
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
<script type="text/javascript">
$('input[name=min_width]').val(150);
$('input[name=min_height]').val(150);
var ratioTypeSquare = <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?>;
$(document).on('change','.prefRatio-js',function(){
    if($(this).val() == ratioTypeSquare)
    {
        $('input[name=min_width]').val(150);
        $('input[name=min_height]').val(150);
    } else {
        $('input[name=min_width]').val(150);
        $('input[name=min_height]').val(85);
    }
});
</script>
