<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($optionCombinations)) {  ?>
    <table width="100%" class="table table-scroll">
        <thead>
            <tr>
                <th width="70%"><?php echo Labels::getLabel('LBL_Variants', $adminLangId); ?></th>
                <th width="30%"><?php echo Labels::getLabel('LBL_EAN/UPC_code', $adminLangId); ?></th>
            </tr>
        </thead>
        <tbody class="scroll-y">
            <?php
            foreach ($optionCombinations as $optionValueId => $optionValue) {
                $arr = explode('|', $optionValue);
                $key = str_replace('|', ',', $optionValueId);
                $variant = $optionValue;
                /*$variant = '';
                foreach($arr as $key2=>$val){	
                    if($key2 == 0){
                        $variant = $val;
                    }else{
                        $variant = $variant." / ".$val;
                    }						
                }  */
            ?>
                <tr>
                    <td><?php echo $variant; ?></td>
                    <td><input type="text" id="code<?php echo $optionValueId; ?>" name="code<?php echo $optionValueId ?>" value="<?php echo (isset($upcCodeData[$key]['upc_code'])) ? $upcCodeData[$key]['upc_code'] : ''; ?>" onBlur="updateUpc('<?php echo $productId; ?>','<?php echo $optionValueId; ?>')"></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_EAN/UPC_code', $adminLangId); ?></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input type="text" id="code0" name="code0" value="<?php echo (isset($upcCodeData[0]['upc_code'])) ? $upcCodeData[0]['upc_code'] : ''; ?>" onBlur="updateUpc('<?php echo $productId; ?>','0')">
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>