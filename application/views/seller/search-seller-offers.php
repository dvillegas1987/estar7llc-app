<?php
if (!empty($offers)) {
    foreach ($offers as $row) {
        $discountValue = ($row['coupon_discount_in_percent'] == ApplicationConstants::PERCENTAGE) ? $row['coupon_discount_value'] . ' %' : CommonHelper::displayMoneyFormat($row['coupon_discount_value']); ?>
        <div class="col-lg-6 mb-4">
            <div class="box--offer">
                <div class="row">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <div class="offer">
                            <div class="offer__logo"><img src="<?php echo UrlHelper::generateFullUrl('Image', 'coupon', array($row['coupon_id'], $siteLangId, 'NORMAL')) ?>" alt="<?php echo ($row['coupon_title'] == '') ? $row['coupon_identifier'] : $row['coupon_title']; ?>"></div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4><?php echo $discountValue; ?> <?php echo Labels::getLabel('LBL_OFF', $siteLangId); ?></h4>
                        <h6><?php echo ($row['coupon_title'] == '') ? $row['coupon_identifier'] : $row['coupon_title']; ?></h6>
                        <p><span class="lessText"><?php echo CommonHelper::truncateCharacters($row['coupon_description'], 85, '', '', true); ?></span>
                            <?php if (strlen($row['coupon_description']) > 85) { ?>
                                <span class="moreText hidden"><?php echo nl2br($row['coupon_description']); ?></span> <a class="readMore link" href="javascript:void(0);"> <?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?> </a></p>
                    <?php } ?>
                    <div class="offer__footer">
                        <div class="offer__grid">
                            <p>
                            <?php
                                if (isset($row['coupon_end_date']) && !empty(FatDate::format($row['coupon_end_date']))) {
                                    echo Labels::getLabel('LBL_Expires_On', $siteLangId); ?>: <strong><?php echo FatDate::format($row['coupon_end_date']); ?></strong>
                                <br>
                            <?php } ?>
                            <?php
                                if (isset($row['coupon_min_order_value']) && !empty((float)$row['coupon_min_order_value'])) {
                                    echo Labels::getLabel('LBL_Min_Order', $siteLangId); ?>:
                                <strong><?php echo CommonHelper::displayMoneyFormat($row['coupon_min_order_value']); ?></strong>
                            <?php } ?>
                            </p>
                        </div>
                        <span class="coupon-code"><?php echo $row['coupon_code']; ?></span>
                    </div>
                    </div>
                </div>
            </div>
        </div>
<?php }
} elseif (isset($noRecordsHtml)) {
    echo FatUtility::decodeHtmlEntities($noRecordsHtml);
}
