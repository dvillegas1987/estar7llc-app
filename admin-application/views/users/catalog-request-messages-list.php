<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'image'=>'Image',
	'detail'=>'Detail',
	'scatrequestmsg_msg'=>'Message',
	'scatrequestmsg_date'=>'Date',
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table--listing'));


if($page == 1 ){
	$th = $tbl->appendElement('thead')->appendElement('tr',array('class'=>'tr--first'));
	foreach ($arr_flds as $val) {
		$e = $th->appendElement('th', array(), $val);
	} 	
}

$sr_no = $page==1 ? 0 : $pageSize*($page-1);
foreach ($messagesList as $sn=>$row){

	$sr_no++;
	$tr = $tbl->appendElement('tr');
	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'image':
				$img = '<img title="'.$row['msg_user_name'].'" src = "'.UrlHelper::generateUrl('Image','User', array($row['scatrequestmsg_from_user_id'], 'THUMB', 1), CONF_WEBROOT_FRONT_URL).'" alt = "'.$row['msg_user_name'].'" >';
				$username = $row['msg_user_name'];
				
				if( $row['scatrequestmsg_from_admin_id'] ){
					$username = $row['admin_name'];
					$img = '<img title="'.$row['admin_name'].'" src = "'.UrlHelper::generateUrl('Image','siteAdminLogo', array( $adminLangId )).'" alt = "'.$row['admin_name'].'" >';
				}
				
				$td->appendElement('div', array('class'=>'avtar avtar--small'), $img, true );
				$td->appendElement('span', array('class'=>'avtar__name'),$username , true );
			break;
			case 'detail':
				
				if( $row['scatrequestmsg_from_admin_id'] ){
					$txt = '<br/>'.$row['admin_name'].' ('.$row['admin_username'].')';
					$txt .= '<br/>'.$row['admin_email'];
				} else {
					$txt = '<br/>'.$row['msg_user_name'].' ('.$row['msg_username'].')';
					$txt .= '<br/>'.$row['msg_user_email'];
				}
				
				$td->appendElement('plaintext', array(), $txt, true );
			break;
			case 'scatrequestmsg_msg':

                $message = '<a href="#"> <strong>'.$row['scatrequestmsg_msg'].'</strong></a>';
				$td->appendElement('div', array('class'=>'listing__desc'), $message, true );
				
			break;			
			default:
				$td->appendElement('span', array('class'=>'date'), date('d/m/Y',strtotime($row[$key])));
			break;
		}
	}
}

if (count($messagesList) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_messages_found',$adminLangId));
} else {
	echo $tbl->getHtml();
	$postedData['page'] = $page;
	echo FatUtility::createHiddenFormFromData ( $postedData, array ('name' => 'frmCatalogRequestMsgsSrchPaging') );
}
