<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
	<?php $arr_flds = array(
		'utxn_id'    =>    Labels::getLabel('LBL_Txn_ID', $siteLangId),
		'utxn_gateway_txn_id'    =>    Labels::getLabel('LBL_GATEWAY_TXN_ID', $siteLangId),
		'utxn_date'    =>    Labels::getLabel('LBL_Date', $siteLangId),
		'utxn_credit' =>    Labels::getLabel('LBL_Credit', $siteLangId),
		'utxn_debit'    =>    Labels::getLabel('LBL_Debit', $siteLangId),
		'balance'    =>    Labels::getLabel('LBL_Balance', $siteLangId),
		'utxn_comments'    =>    Labels::getLabel('LBL_Comments', $siteLangId),
		'utxn_status'    =>    Labels::getLabel('LBL_Status', $siteLangId),
	);

	$tbl = new HtmlElement('table', array('class' => 'table'));
	$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
	foreach ($arr_flds as $val) {
		$e = $th->appendElement('th', array(), $val);
	}

	$sr_no = 0;
	foreach ($arrListing as $sn => $row) {
		$sr_no++;
		$tr = $tbl->appendElement('tr', array('class' =>'' ));

		foreach ($arr_flds as $key => $val) {
			$td = $tr->appendElement('td');
			switch ($key) {
				case 'utxn_id':
					$td->appendElement('plaintext', array(), Transactions::formatTransactionNumber($row[$key]), true);
					break;
				case 'utxn_gateway_txn_id':
					$pgTxnId = !empty($row[$key]) ? $row[$key] : Labels::getLabel('LBL_N/A', $siteLangId);
					$td->appendElement('plaintext', array(), $pgTxnId, true);
					break;
				case 'utxn_date':
					$td->appendElement('plaintext', array(), FatDate::format($row[$key]), true);
					break;
				case 'utxn_status':
					$td->appendElement('span', array('class' => 'label label-inline '.$statusClassArr[$row[$key]]), $statusArr[$row[$key]], true);
					break;
				case 'utxn_credit':
					$txt = CommonHelper::displayMoneyFormat($row[$key]);
					$td->appendElement('plaintext', array(), $txt, true);
					break;
				case 'utxn_debit':
					$txt = CommonHelper::displayMoneyFormat($row[$key]);
					$td->appendElement('plaintext', array(), $txt, true);
					break;
				case 'balance':
					$txt = CommonHelper::displayMoneyFormat($row[$key]);
					$td->appendElement('plaintext', array(), $txt, true);
					break;
				case 'utxn_comments':
                                    $comments = Transactions::formatTransactionComments($row[$key]);
                                    $commentsTxt = "<span class='lessText'>" . CommonHelper::truncateCharacters($comments, 150, '', '', true) . "</span>";
                                    if (strlen($comments) > 150) {
                                        $commentsTxt .= "<span class='moreText hidden'>";
                                        $commentsTxt .= nl2br($comments) . "</span>";
                                        $commentsTxt .= "</br><a class='readMore link--arrow btn-link' href='javascript:void(0);'>" . Labels::getLabel('Lbl_SHOW_MORE', $siteLangId) . "</a>";
                                    }
                                    $td->appendElement('plaintext', array(), $commentsTxt, true);
                                    break;
				default:
					$td->appendElement('plaintext', array(), $row[$key], true);
					break;
			}
		}
	}

	echo $tbl->getHtml();
	if (count($arrListing) == 0) {
		$message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
		$this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
	} ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmCreditSrchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount, 'callBackJsFunc' => 'goToOrderSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_SHOW_LESS', $siteLangId); ?>';
</script>
