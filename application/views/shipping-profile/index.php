<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php');
$searchFrm->setFormTagAttribute('onSubmit', 'searchProfile(this); return false;');
$searchFrm->setFormTagAttribute('class', 'form ');
$searchFrm->developerTags['colClassPrefix'] = 'col-md-';
$searchFrm->developerTags['fld_default_col'] = 6;

$keywordFld = $searchFrm->getField('keyword');
$keywordFld->developerTags['col'] = 8;
$keywordFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $searchFrm->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $searchFrm->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row ">
            <div class="col">
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Shipping_Profiles', $siteLangId); ?>
                </h2>
            </div>
            <?php if ($canEdit) { ?>
                <div class="col-auto">
                    <div class="content-header-right">
                        <a href="<?php echo UrlHelper::generateUrl('shippingProfile', 'form', [0]); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Create_Profile', $siteLangId); ?></a>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php
                                $submitFld = $searchFrm->getField('btn_submit');

                                $fldClear = $searchFrm->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                echo $searchFrm->getFormHtml();
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
                            <div id="profilesListing">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>