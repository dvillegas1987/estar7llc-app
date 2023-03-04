<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_FAQs', $adminLangId); ?>
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
                    $srchFrm->setFormTagAttribute('onsubmit', 'searchFaqs(this); return(false);');
                    $srchFrm->setFormTagAttribute('class', 'web_form');
                    $srchFrm->developerTags['colClassPrefix'] = 'col-md-';
                    $srchFrm->developerTags['fld_default_col'] = 6;
                    echo  $srchFrm->getFormHtml();
                ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Faq_List', $adminLangId); ?>
                        </h4>

                        <?php
                            $url = UrlHelper::generateUrl('FaqCategories');
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => "redirectUrl('" . $url . "')",
                                            'title' => Labels::getLabel('LBL_BACK', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-arrow-left"></i>'
                                    ],
                                ]
                            ];

                            if ($canEdit) {
                                $data['otherButtons'][] = [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => "addFaqForm('" . $faqcat_id . "',0)",
                                        'title' => Labels::getLabel('LBL_Add_Faq', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-plus"></i>'
                                ];
                            }
        
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>