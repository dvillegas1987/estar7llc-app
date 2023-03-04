<div class="cg-main">
    <?php if (!empty($brandsArr)) {
        foreach ($brandsArr[Collections::TYPE_BRAND_LAYOUT1] as $allBrands) {
            if (!empty($allBrands['brands'])) {
                $firstCharacter = '';
                foreach ($allBrands['brands'] as $brands) {
                    $brandName = !empty($brands['brand_name']) ? $brands['brand_name'] : $brands['brand_identifier'];
                    $str = substr(strtolower($brandName), 0, 1);
                    if (is_numeric($str)) {
                        $str = '0-9';
                    }

                    if ($str != $firstCharacter) {
                        if ($firstCharacter != '') {
                            echo "</ul></div>";
                        }
                        $firstCharacter = $str; ?>
                        <div class="item">
                            <h6 class="big-title"><?php echo $firstCharacter; ?></h6>
                            <ul>
                    <?php } ?>
                                <li>
                                    <a href="<?php echo UrlHelper::generateUrl('Brands', 'view', array($brands['brand_id'])); ?>">
                                        <?php echo $brandName; ?>
                                    </a>
                                </li>
                <?php } ?>
                            </ul>
                        </div>
            <?php }
        }
    } ?>
</div>