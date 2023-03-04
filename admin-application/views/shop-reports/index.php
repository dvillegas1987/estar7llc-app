<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<script>
    var shopId = '<?php echo $shopId; ?>';
</script>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Shop_Reports', $adminLangId); ?>
                            </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Shop_Reports_Listing', $adminLangId); ?>
                            </h4>
                            <?php
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => "window.location.replace('" . UrlHelper::generateUrl('Shops') . "')",
                                            'title' => Labels::getLabel('LBL_Back_to_Shops', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-arrow-left"></i>'
                                    ],
                                ]
                            ];

                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                            ?>
                        </div>
                        <div class="sectionbody">
                            <div class="tablewrap">
                                <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>