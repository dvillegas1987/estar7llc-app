<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($optionCombinations)) {  ?>
    <div class="variants-wrap js-scrollable table-wrap">
        <table width="100%" class="table-fixed-header">
            <thead>
                <tr>
                    <th width="20%"><?php echo Labels::getLabel('LBL_#', $siteLangId); ?></th>
                    <th width="80%"><?php echo Labels::getLabel('LBL_Variants', $siteLangId); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0;
                foreach ($optionCombinations as $optionValueId => $optionValue) {
                    $arr = explode('|', $optionValue);
                    $key = str_replace('|', ',', $optionValueId);
                    $variant = $optionValue;
                    $count++;
                ?>
                    <tr>
                        <td width="20%"><?php echo $count; ?></td>
                        <td width="80%"><?php echo $variant; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>