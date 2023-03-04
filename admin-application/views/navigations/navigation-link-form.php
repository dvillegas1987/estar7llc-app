<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupNavigationLink(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$nlink_typeFld = $frm->getField('nlink_type');
$nlink_typeFld->setFieldTagAttribute('onchange', 'callPageTypePopulate(this)');

$nlink_cpage_idFld = $frm->getField('nlink_cpage_id');
$nlink_cpage_idFld->setWrapperAttribute('id', 'nlink_cpage_id_div');
$nlink_category_idFld = $frm->getField('nlink_category_id');
$nlink_category_idFld->setWrapperAttribute('id', 'nlink_category_id_div');

$nlink_urlFld = $frm->getField('nlink_url');
$nlink_urlFld->setWrapperAttribute('id', 'nlink_url_div');

?>
<section class="section">
    <div class="sectionhead">

        <h4><?php echo Labels::getLabel('LBL_Navigation_Link_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Navigation_Link_Setup',$adminLangId);?>
                </h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a class="active" href="javascript:void(0)"
                                onclick="navigationLinkForm(<?php echo $nav_id . ',' . $nlink_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
                        <li
                            class="<?php echo (0 == $nlink_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $nlink_id) ? "onclick='navigationLinkLangForm(" . $nav_id . "," . $nlink_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
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
            <script type="text/javascript">
                $(document).ready(function() {
                    callPageTypePopulate($("select[name='nlink_type']"));
                });
            </script>