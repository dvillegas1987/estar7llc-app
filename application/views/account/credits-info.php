<!-- wallet balance[ -->
<?php
$showTotalBalanceAvailableDiv = false;
if ($userTotalWalletBalance != $userWalletBalance || ($promotionWalletToBeCharged) || ($withdrawlRequestAmount)) {
    $showTotalBalanceAvailableDiv = true;
} ?>
<?php if ($showTotalBalanceAvailableDiv) { ?>
<div class="row">
	<div class="col-lg-12 mb-3">
		<div class="balancebox border h-100 text-center rounded p-3">
			<div class="credits-number">
				<ul>
					<?php if ($userTotalWalletBalance != $userWalletBalance) { ?>
					<li>
						<span class="total"><?php echo Labels::getLabel('LBL_Wallet_Balance', $siteLangId); ?>: </span>
						<span class="total-numbers"><strong><?php echo CommonHelper::displayMoneyFormat($userTotalWalletBalance); ?></strong></span>
						<?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>

							<small>
								<?php echo Labels::getLabel('LBL_Approx.', $siteLangId); ?>
								<?php echo CommonHelper::displayMoneyFormat($userTotalWalletBalance, true, true); ?>
							</small>
						<?php } ?>
					</li>
					<?php } ?>
					<?php if ($promotionWalletToBeCharged) { ?>
					<li>
						<span class="total"><?php echo Labels::getLabel('LBL_Pending_Promotions_Charges', $siteLangId); ?>:</span>
						<span class="total-numbers"> <strong>
							<?php echo CommonHelper::displayMoneyFormat($promotionWalletToBeCharged); ?></strong></span>
							<?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
								<small>
									<?php echo Labels::getLabel('LBL_Approx.', $siteLangId);
									echo CommonHelper::displayMoneyFormat($promotionWalletToBeCharged, true, true); ?>
								</small>
							<?php } ?>
					</li>
					<?php } ?>
					<?php if ($withdrawlRequestAmount) { ?>
					<li>
						<span class="total"><?php echo Labels::getLabel('LBL_Pending_Withdrawl_Requests', $siteLangId); ?>:</span>
						<span class="total-numbers"> <strong>
						<?php echo CommonHelper::displayMoneyFormat($withdrawlRequestAmount); ?></strong></span>
						<?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
							<small><?php echo Labels::getLabel('LBL_Approx.', $siteLangId); ?> <?php echo CommonHelper::displayMoneyFormat($withdrawlRequestAmount, true, true); ?></small>
						<?php } ?>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php } ?>
