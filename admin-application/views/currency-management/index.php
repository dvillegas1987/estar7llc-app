<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Currencies', $adminLangId); ?>
                            </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Currency_Listing', $adminLangId); ?>
                        </h4>
                        <?php
                        if ($canEdit) {
                            $currencyPlugins = Plugin::getNamesByType(Plugin::TYPE_CURRENCY_CONVERTER, $adminLangId);
                            $obj = new Currency();
                            $currencyConverter = $obj->getCurrencyConverterApi();

                            $data = [
                                'adminLangId' => $adminLangId,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'editCurrencyForm(0)',
                                            'title' => Labels::getLabel('LBL_Add_Currency', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-plus"></i>'
                                    ]
                                ]
                            ];

                            if (!empty($currencyPlugins) && 0 < count($currencyPlugins) && false !== $currencyConverter) {
                                $data['otherButtons'][] = [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => "updateCurrencyRates('" . $currencyConverter . "')",
                                        'title' => Labels::getLabel('LBL_Update_Currency', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-file-download"></i>'
                                ];
                            }
        
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        }
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