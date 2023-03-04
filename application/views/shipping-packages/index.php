<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); 
$frmSearch->setFormTagAttribute('onsubmit', 'searchShipPackages(this); return(false);');
$frmSearch->setFormTagAttribute('class', 'form ');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 6;

$keywordFld = $frmSearch->getField('keyword');
$keywordFld->developerTags['col'] = 8;
$keywordFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSearch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row ">
            <div class="col">
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Shipping_Packages', $siteLangId); ?>
                </h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php
                                $submitFld = $frmSearch->getField('btn_submit');

                                $fldClear= $frmSearch->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                echo $frmSearch->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $siteLangId); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
