<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = '12';

$frm->setFormTagAttribute('onsubmit', 'setupAppThemeSettings(this); return(false);');
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i>
                            </span>
                            <h5><?php echo Labels::getLabel('LBL_APP_THEME_Settings', $adminLangId); ?>
                            </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_APP_UI_COLORS', $adminLangId);?></h4>
                    </div>
                    <div class="sectionbody">
                        <div class="space">
                            <?php echo $frm->getFormHtml();?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>