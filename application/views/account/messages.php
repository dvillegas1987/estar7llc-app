<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSrch->setFormTagAttribute('onSubmit', 'searchMessages(this); return false;');
$frmSrch->setFormTagAttribute('class', 'form');
$frmSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmSrch->developerTags['fld_default_col'] = 12;

$keyFld = $frmSrch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-sm-6');
$keyFld->developerTags['col'] = 6;
$keyFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn-block');
$submitBtnFld->setWrapperAttribute('class', 'col-sm-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn-block');
$cancelBtnFld->setWrapperAttribute('class', 'col-sm-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Messages', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="withdrawalReqForm"></div>
                            <div class="replaced">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php
                                        $submitFld = $frmSrch->getField('btn_submit');
                                        $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');

                                        $fldClear = $frmSrch->getField('btn_clear');
                                        $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
                                        echo $frmSrch->getFormHtml();
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
                            <div id="messageListing"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>