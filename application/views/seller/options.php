<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frmSearch->setFormTagAttribute('onsubmit', 'searchOptions(this); return(false);');

    $frmSearch->setFormTagAttribute('class', 'form');
    $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
    $frmSearch->developerTags['fld_default_col'] = 12;

    $keyFld = $frmSearch->getField('keyword');
    $keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
    $keyFld->setWrapperAttribute('class', 'col-lg-6');
    $keyFld->developerTags['col'] = 6;
    $keyFld->developerTags['noCaptionTag'] = true;

    $submitBtnFld = $frmSearch->getField('btn_submit');
    $submitBtnFld->setFieldTagAttribute('class', 'btn-block');
    $submitBtnFld->setWrapperAttribute('class', 'col-lg-3');
    $submitBtnFld->developerTags['col'] = 3;
    $submitBtnFld->developerTags['noCaptionTag'] = true;

    $cancelBtnFld = $frmSearch->getField('btn_clear');
    $cancelBtnFld->setFieldTagAttribute('class', 'btn-block');
    $cancelBtnFld->setWrapperAttribute('class', 'col-lg-3');
    $cancelBtnFld->developerTags['col'] = 3;
    $cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Seller_Options', $siteLangId); ?></h2>
            </div>
            <?php if ($canEdit) { ?>
                <div class="col-auto">
                    <div class="btn-group">
                        <a class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_Add_Option', $siteLangId); ?>" onclick="optionForm(0)" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Add_Option', $siteLangId); ?></a>
                        <!-- <a href="#modal-popup" class="btn-block modaal-inline-content link" onclick="optionForm(0)"><?php echo Labels::getLabel('LBL_Add_Option', $siteLangId);?></a> -->
                        <a class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?>" onclick="deleteOptions()" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?></a>
                    </div>
                </div>
            <?php }?>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <?php
                                        $submitFld = $frmSearch->getField('btn_submit');
                                        $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');

                                        $fldClear= $frmSearch->getField('btn_clear');
                                        $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
                                        echo $frmSearch->getFormHtml();
                                        ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-body">
                            <div id="optionListing"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
