<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
/* $frmSearch->setFormTagAttribute ( 'class', 'web_form last_td_nowrap' );
$frmSearch->setFormTagAttribute ( 'onsubmit', 'searchSlides(this); return(false);' );
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 4; */

/* $btn_clear = $frmSearch->getField('btn_clear');
$btn_clear->addFieldTagAttribute('onClick', 'clearSearch()'); */
?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Home_Page_Slides', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <!-- <section class="section searchform_filter">
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php /* echo $frmSearch->getFormHtml(); */ ?>
                    </div>
                </section> -->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Slides_List', $adminLangId); ?> </h4>
                        <?php
                        if ($canEdit) {
                            $otherButtons = [
                                [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'addSlideForm(0)',
                                        'title' => Labels::getLabel('LBL_Add_New_Slide', $adminLangId),
                                    ],
                                    'label' => '<i class="fas fa-plus"></i>'
                                ],
                            ];
                            $this->includeTemplate('_partial/action-buttons.php', ['otherButtons' => $otherButtons, 'adminLangId' => $adminLangId], false);
                        } ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
