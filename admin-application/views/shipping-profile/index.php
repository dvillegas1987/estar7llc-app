<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $search->setFormTagAttribute('class', 'web_form last_td_nowrap');
    $search->setFormTagAttribute('onsubmit', 'searchProfile(this); return(false);');
    $search->developerTags['colClassPrefix'] = 'col-md-';
    $search->developerTags['fld_default_col'] = 4;
    $search->getField('btn_clear')->addFieldtagAttribute('onclick', 'clearSearch();');
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Shipping_Profile', $adminLangId); ?>
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
                        <?php echo $search->getFormHtml(); ?>
                    </div>
                </section>
                <!--<div class="col-sm-12">-->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Shipping_Profile', $adminLangId); ?>
                        </h4>
                        <?php
                        if ($canEdit) {
                            $otherButtons = [
                                [
                                    'attr' => [
                                        'href' => UrlHelper::generateUrl('shippingProfile', 'form', array(0)),
                                        'title' => Labels::getLabel('LBL_Create_New_Profile', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-plus"></i>'
                                ]
                            ];
                            $this->includeTemplate('_partial/action-buttons.php', ['otherButtons' => $otherButtons, 'adminLangId' => $adminLangId], false);
                        }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="profile-listing--js"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>