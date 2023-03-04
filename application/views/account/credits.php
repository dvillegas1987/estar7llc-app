<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSrch->setFormTagAttribute('onSubmit', 'searchCredits(this); return false;');
$frmSrch->setFormTagAttribute('class', 'form');
$frmSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmSrch->developerTags['fld_default_col'] = 12;

$keyFld = $frmSrch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-6');
$keyFld->developerTags['col'] = 6;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSrch->getField('debit_credit_type');
$keyFld->setWrapperAttribute('class', 'col-lg-6');
$keyFld->developerTags['col'] = 6;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSrch->getField('date_from');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_From_Date', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-4');
$keyFld->developerTags['col'] = 4;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSrch->getField('date_to');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_To_Date', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-4');
$keyFld->developerTags['col'] = 4;
$keyFld->developerTags['noCaptionTag'] = true;

/* $keyFld = $frmSrch->getField('date_order');
$keyFld->setWrapperAttribute('class','col-lg-6');
$keyFld->developerTags['col'] = 6; */

$submitBtnFld = $frmSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn-block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn-block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <?php if ($codMinWalletBalance > -1) { ?>
                            <div class="cards-header pb-0">
                                <p class="note"><?php echo Labels::getLabel('MSG_Minimum_balance_Required_For_COD', $siteLangId) . ' : ' . CommonHelper::displaymoneyformat($codMinWalletBalance); ?></p>
                            </div>
                        <?php } ?>
                        <div class="card-body">
                            <div id="credits-info"></div>
                            <div class="row">
                                <div class="col-lg-6 mb-3 mb-lg-0">
                                    <div class="balancebox border h-100 rounded text-center p-3">
                                        <p><?php echo Labels::getLabel('LBL_Available_Balance', $siteLangId); ?>: </p>
                                        <h3><?php echo CommonHelper::displayMoneyFormat($userWalletBalance); ?></h3>
                                        <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                                            <small class="d-block">
                                                <?php echo Labels::getLabel('LBL_Approx.', $siteLangId); ?> <?php echo CommonHelper::displayMoneyFormat($userWalletBalance, true, true); ?>
                                            </small>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-md-8 mb-3 mb-md-0">
                                                <select name='payout_type' class='custom-select payout_type'>
                                                    <?php
                                                    foreach ($payouts as $type => $name) { ?>
                                                        <option value='<?php echo $type; ?>'><?php echo $name; ?></option>
                                                    <?php }
                                                    ?>
                                                </select>

                                            </div>
                                            <div class="col-md-4">
                                                <a href="javascript:void(0)" onClick="withdrawalReqForm()" class="btn btn-brand btn-block">
                                                    <?php echo Labels::getLabel('LBL_Withdraw', $siteLangId); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $srchFormDivWidth = $canAddMoneyToWallet ? '8' : '12'; ?>
                                <?php if ($canAddMoneyToWallet) { ?>
                                    <div class="col-lg-6">
                                        <div class="replaced amount-added-box border h-100 rounded text-center p-3">
                                            <h6 class="card-title mb-4">
                                                <?php 
                                                $str = Labels::getLabel('LBL_Add_Wallet_Credits_[{CURRENCY-SYMBOL}]', $siteLangId);
                                                echo CommonHelper::replaceStringData($str, ['{CURRENCY-SYMBOL}' => CommonHelper::getDefaultCurrencySymbol()]); ?>
                                            </h6>
                                            <div id="rechargeWalletDiv" class="cellright nopadding--bottom">
                                                <?php
                                                $frmRechargeWallet->setFormTagAttribute('onSubmit', 'setUpWalletRecharge(this); return false;');
                                                $frmRechargeWallet->setFormTagAttribute('class', 'form');
                                                $frmRechargeWallet->developerTags['colClassPrefix'] = 'col-md-';
                                                $frmRechargeWallet->developerTags['fld_default_col'] = 12;
                                                $frmRechargeWallet->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_WITH_NONE);

                                                $amountFld = $frmRechargeWallet->getField('amount');
                                                $amountFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Enter_Amount', $siteLangId));
                                                $amountFld->developerTags['noCaptionTag'] = true;
                                                $amountFld->developerTags['col'] = 7;
                                                $buttonFld = $frmRechargeWallet->getField('btn_submit');
                                                $buttonFld->setFieldTagAttribute('class', 'btn-block block-on-mobile');
                                                $buttonFld->developerTags['noCaptionTag'] = true;
                                                $buttonFld->developerTags['col'] = 5;
                                                $buttonFld->setFieldTagAttribute('class', "btn btn-brand btn-block");
                                                echo $frmRechargeWallet->getFormHtml(); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row mb-4 d-none withdrawForm">
                <div class="col-lg-12">
                    <div class="card">
                        <div id="withdrawalReqForm"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Search_Transactions', $siteLangId); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="replaced">

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
            <div class="gap"></div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-body">
                            <div id="creditListing"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>