<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="tabs_panel">
    <div class="row">
        <div class="col-sm-12">
            <h4><?php echo Labels::getLabel('LBL_Manage_Meta_Tags', $adminLangId); ?>
            </h4>
            <?php if (!empty($frmSearch)) { ?>
            <?php if ($toShowForm) { ?>
            <section class="section searchform_filter">
                <div class="sectionhead">
                    <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?>
                    </h4>
                </div>
                <div class="sectionbody space togglewrap" style="display:none;">
                    <?php
                    $frmSearch->addFormTagAttribute('class', 'web_form');
                    $frmSearch->addFormTagAttribute('onsubmit', 'searchMetaTag(this);return false;');
                    $frmSearch->setFormTagAttribute('id', 'frmSearch');
                    $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                    $frmSearch->developerTags['fld_default_col'] = 6;

                    ($frmSearch->getField('keyword')) ? $frmSearch->getField('keyword')->addFieldtagAttribute('class', 'search-input') : null;
                    ($frmSearch->getField('hasTagsAssociated')) ? $frmSearch->getField('hasTagsAssociated')->addFieldtagAttribute('class', 'search-input') : null;
                    
                    ($frmSearch->getField('btn_clear')) ? $frmSearch->getField('btn_clear')->addFieldtagAttribute('onclick', 'clearSearch();') :  null;
                    
                    echo  $frmSearch->getFormHtml();
                ?>
                </div>
            </section>
            <?php } else {
            echo $frmSearch->getFormHtml();
        }
        ?>
            <?php } ?>
        </div>
        <div class="col-sm-12">
            <section class="section">
                <div class="sectionhead">
                    <h4><?php echo Labels::getLabel('LBL_Meta_Tags_Listing', $adminLangId); ?>
                    </h4>
                    <?php
                    if (isset($canAddNew) && $canAddNew ==true) {
                        $data = [
                            'adminLangId' => $adminLangId,
                            'statusButtons' => false,
                            'deleteButton' => false,
                            'otherButtons' => [
                                [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => "addMetaTagForm(0,'" . $metaType . "',0)",
                                        'title' => Labels::getLabel('LBL_Add_Meta_Tag', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-plus"></i>'
                                ],
                            ]
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
    </div>
</div>