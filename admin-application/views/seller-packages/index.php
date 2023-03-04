<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i>
                            </span>
                            <h5><?php echo Labels::getLabel('LBL_Seller_Packages', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section" id="packageDetail">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Seller_Packages_Listings', $adminLangId); ?> </h4>
                        <?php if ($canEdit) {
                            $otherButtons = [
                                [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'PackageForm(0)',
                                        'title' => Labels::getLabel('LBL_Add_New', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-plus"></i>'
                                ]
                            ];
                            $this->includeTemplate('_partial/action-buttons.php', ['deleteButton' => false, 'otherButtons' => $otherButtons, 'adminLangId' => $adminLangId], false);
                        } ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?> </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
