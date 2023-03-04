<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Pickup_Addresses', $adminLangId); ?></h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <h1> </h1>
                <section class="section">
                    <div class="sectionhead">
                        <h4 class="label-js"><?php echo Labels::getLabel('LBL_PICKUP_ADDRESSES_LIST', $adminLangId); ?></h4>
                        <?php
                        if ($canEdit) {
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'addAddressForm(0,' . $adminLangId . ')',
                                            'class' => 'js-add-pickup-addr',
                                            'title' => Labels::getLabel('LBL_Add_Pickup_Address', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-plus"></i>'
                                    ],
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'searchAddresses()',
                                            'class' => 'js-pickup-addr d-none',
                                            'title' => Labels::getLabel('LBL_Back_To_pickup_Addresses', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-arrow-left"></i>'
                                    ],
                                ]
                            ];
        
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        }
                        ?>
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
