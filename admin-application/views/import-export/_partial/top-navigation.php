<?php  defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<ul class="tabs_nav">
    <li><a href="javascript:void(0)" onClick="loadForm('general_instructions')" class="<?php echo !empty($action) && $action=='generalInstructions'?'active' : '';?>"><?php echo Labels::getLabel('LBL_Instructions',$adminLangId); ?></a></li>
    <li ><a href="javascript:void(0)" onClick="loadForm('export')" class="<?php echo !empty($action) && $action=='export'?'active' : '';?>"><?php echo Labels::getLabel('LBL_Export',$adminLangId); ?></a></li>
    <li ><a href="javascript:void(0)" onClick="loadForm('import')" class="<?php echo !empty($action) && $action=='import'?'active' : '';?>"><?php echo Labels::getLabel('LBL_Import',$adminLangId); ?></a></li>
    <li ><a href="javascript:void(0)" onClick="loadForm('bulk_media')" class="<?php echo !empty($action) && $action=='bulkMedia'?'active' : '';?>"><?php echo Labels::getLabel('LBL_Add_Media_To_Server',$adminLangId); ?></a></li>
    <li ><a href="javascript:void(0)" onClick="loadForm('settings')" class="<?php echo !empty($action) && $action=='settings'?'active' : '';?>"><?php echo Labels::getLabel('LBL_Settings',$adminLangId); ?></a></li>
</ul>



