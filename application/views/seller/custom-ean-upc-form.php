<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if(!empty($optionCombinations)){  ?>
   <div class="variants-wrap js-scrollable table-wrap">
       <table width="100%" class="table-fixed-header">                             
        <thead>
            <tr>
                <th width="70%"><?php echo Labels::getLabel('LBL_Variants',$siteLangId);?></th>	
                 <th width="30%"><?php echo Labels::getLabel('LBL_EAN/UPC_code',$siteLangId);?></th>
            </tr>
        </thead>
        <tbody>
            <?php     
            foreach($optionCombinations as $optionValueId=>$optionValue){
                $arr = explode('|',$optionValue);
                $key = str_replace('|',',',$optionValueId); 
                $variant = $optionValue;
                /* $variant = '';
                foreach($arr as $key2=>$val){	
                    if($key2 == 0){
                        $variant = $val;
                    }else{
                        $variant = $variant." / ".$val;
                    }						
                } */ 
            ?>
            <tr>
                <td width="70%"><?php echo $variant; ?></td>	
                <td width="30%">
                    <input type="text" id="code<?php echo $optionValueId; ?>" name="code<?php echo $optionValueId?>" value="<?php echo (isset   ($upcCodeData[$optionValueId]))?$upcCodeData[$optionValueId]:'';?>" onBlur="updateUpc('<?php echo $preqId; ?>','<?php echo $optionValueId;?>')">
                </td>
            </tr>
            <?php } ?>	
        </tbody>
    </table></div>
<?php } ?>
