<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php 
$arr_flds = array(
	'listserial'	=>	'',
	'user_name'=>Labels::getLabel('LBL_User',$adminLangId),
	'selprod_title'=>Labels::getLabel('LBL_Seller_product',$adminLangId),
    'abandonedcart_qty'=>Labels::getLabel('LBL_Qty',$adminLangId),
    'abandonedcart_action'=>Labels::getLabel('LBL_Status',$adminLangId),
	'abandonedcart_added_on'=>Labels::getLabel('LBL_Date',$adminLangId),
	'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['action']);
}
        
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive'));

if( $totCartRecovered > 0 ){
    $th = $tbl->appendElement('tr');
    $th->appendElement('td', array('class' => 'text-right', 'colspan' => '7'), Labels::getLabel('LBL_Cart_Recovered_Amount',$adminLangId).": <strong>".CommonHelper::displayMoneyFormat($totCartRecovered)."</strong>", true);
}

$th = $tbl->appendElement('tr');
/* foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
} */
$sr_no = $page==1?0:$pageSize*($page-1);

foreach ($records as $sn=>$row){  
    if($sn == 0){
        if($row['abandonedcart_action'] == AbandonedCart::ACTION_PURCHASED){
            $arr_flds['action'] = Labels::getLabel('LBL_Amount',$adminLangId);
        }
        foreach ($arr_flds as $val) {  
            $e = $th->appendElement('th', array(), $val);
        }
    }
    
	$sr_no++;
	$tr = $tbl->appendElement('tr');

	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'listserial':
				$td->appendElement('plaintext', array(), $sr_no);
			break;
			case 'abandonedcart_action': 
                if($row[AbandonedCart::DB_TBL_PREFIX.'discount_notification'] == 1 && $row[$key] != AbandonedCart::ACTION_PURCHASED){
                    $td->appendElement('plaintext', array(), Labels::getLabel('LBL_Discount_Coupon_Sent',$adminLangId));
                }else{
                    $actionArr = AbandonedCart::getActionArr($adminLangId);
                    $td->appendElement('plaintext', array(), $actionArr[$row[$key]]);
                }
			break;
			case 'abandonedcart_added_on': 
                $td->appendElement('plaintext',array(),FatDate::format($row[$key],true,true,FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())));
			break;
			case 'action':
                if ($canEdit) {
                    if($row['abandonedcart_action'] < AbandonedCart::ACTION_PURCHASED && $row[AbandonedCart::DB_TBL_PREFIX.'discount_notification'] == 0){                        
                        $td->appendElement('a', array('href'=>'javascript:void(0);', 'onclick'=>'discountNotification('.$row['abandonedcart_id'].','.$row['abandonedcart_user_id'].','.$row['selprod_product_id'].')', 'class'=>'btn btn-clean btn-sm btn-icon','title'=>Labels::getLabel('LBL_Send_Discount_Notification',$adminLangId)),'<i class="fas fa-percent"></i>', true);
                    }
                }
                
                if($row['abandonedcart_action'] == AbandonedCart::ACTION_PURCHASED){
                    $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['abandonedcart_amount']));
                }
			break; 
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($records) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
}
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmAbandonedCartSearchPaging' 
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>

<script type="text/javascript">
var DISCOUNT_IN_PERCENTAGE = '<?php echo applicationConstants::PERCENTAGE; ?>';
var DISCOUNT_IN_FLAT = '<?php echo applicationConstants::FLAT; ?>';
</script>
