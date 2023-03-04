<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Policy_Points', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Policy_Points_Listing', $adminLangId); ?></h4>
                        <?php
                        /* if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0) && $canView) {
                            $innerLiExport=$innerUl->appendElement('li');
                            $innerLiExport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Export', $adminLangId),"onclick"=>"addExportForm(".Importexport::TYPE_POLICY_POINTS.")"), Labels::getLabel('LBL_Export', $adminLangId), true);
                        }
                        if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0) && $canEdit) {
                            $innerLiImport=$innerUl->appendElement('li');
                            $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Import', $adminLangId),"onclick"=>"addImportForm(". Importexport::TYPE_POLICY_POINTS.")"), Labels::getLabel('LBL_Import', $adminLangId), true);
                        } */

                        if ($canEdit) {
                            $otherButtons = [
                                [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'addPolicyPointForm(0)',
                                        'title' => Labels::getLabel('LBL_Add_Policy_Point', $adminLangId),
                                    ],
                                    'label' => '<i class="fas fa-plus"></i>'
                                ],
                            ];
                            $this->includeTemplate('_partial/action-buttons.php', ['otherButtons' => $otherButtons, 'adminLangId' => $adminLangId], false);
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
