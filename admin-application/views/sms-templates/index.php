<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="page__title">
                            <div class="row">
                                <div class="col--first col-lg-6">
                                    <span class="page__icon">
                                        <i class="ion-android-star"></i></span>
                                    <h5><?php echo Labels::getLabel('LBL_MANAGE_SMS_TEMPLATES', $adminLangId); ?>
                                    </h5>
                                    <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                                </div>
                            </div>
                        </div>
                        <section class="section searchform_filter">
                            <div class="sectionhead">
                                <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?>
                                </h4>
                            </div>
                            <div class="sectionbody space togglewrap" style="display:none;">
                                <?php
                                    $frmSearch->setFormTagAttribute('onsubmit', 'searchStpls(this); return(false);');
                                    $frmSearch->setFormTagAttribute('id', 'frmStplSearch');
                                    $frmSearch->setFormTagAttribute('class', 'web_form');
                                    $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                                    $frmSearch->developerTags['fld_default_col'] = 6;

                                    $btn = $frmSearch->getField('btn_clear');
                                    $btn->setFieldTagAttribute('onClick', 'clearSearch()');
                                    echo  $frmSearch->getFormHtml();
                                ?>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="row equal-height">
                    <div class="col-md-<?php echo ($canEdit) ? 6 : 12; ?>">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_SMS_TEMPLATE_LISTS', $adminLangId); ?></h4>
                                <?php
                                if ($canEdit) {
                                    $data = [
                                        'adminLangId' => $adminLangId,
                                        'deleteButton' => false
                                    ];
                
                                    $this->includeTemplate('_partial/action-buttons.php', $data, false);
                                } ?>
                            </div>
                            <div class="sectionbody">
                                <div class="tablewrap">
                                    <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <?php if ($canEdit) { ?>
                        <div class="col-md-6">
							<section class="section" id="templateDetail">
								<div class="sectionbody space"></div>
							</section>
						</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>