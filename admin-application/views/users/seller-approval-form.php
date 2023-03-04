<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupFormFields(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
/*$sformfield_identifier = $frm->getField('sformfield_identifier');
$sformfield_identifier->setUnique('tbl_user_supplier_form_fields', 'sformfield_identifier', 'sformfield_id', 'sformfield_id', 'sformfield_id');*/
?> <section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Set_Up_Form_Fields', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="tabs_nav_container responsive flat">
            <ul class="tabs_nav">
                <li><a class="active" href="javascript:void(0)" onclick="formFileds(<?php echo $sformfield_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                <li class="<?php echo (0 == $sformfield_id) ? 'fat-inactive' : ''; ?>">
                    <a href="javascript:void(0);" <?php echo (0 < $sformfield_id) ? "onclick='addLangFormFields(" . $sformfield_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                        <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                    </a>
                </li>
            </ul>
            <div class="tabs_panel_wrap">
                <div class="tabs_panel"> <?php echo $frm->getFormHtml(); ?> </div>
            </div>
        </div>
    </div>
</section>
