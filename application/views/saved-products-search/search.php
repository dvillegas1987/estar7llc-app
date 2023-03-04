<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if(!empty($arrListing)){?>
<div class="saved-search-list">
	<ul>
	  <?php foreach ($arrListing as $sn => $row){ ?>
		<li>
			<div class="detail-side">
				<h6><?php echo ucfirst($row['pssearch_name']); ?></h6>
				<div class="heading5">
					<?php
						$str = '';
						foreach($row['search_items'] as $record){
							if(is_array($record['value'])){
								$str.= ' <strong>'.$record['label'].'</strong>: ';
								$listValues = '';
								foreach($record['value'] as $list){
									$listValues.= $list.',';
								}
								$str.= rtrim($listValues,' , ').' |';
							}else{
								$str.= ' <strong>'.$record['label'].'</strong>: '.$record['value'].' |';
							}
					}
					echo rtrim($str,'|');
					?>
				</div>
				<p class="date"><?php echo FatDate::format($row['pssearch_added_on']); ?></p>
			</div>
			<div class="results-side">
				<div class="btn-group">
					<a href="<?php echo html_entity_decode($row['search_url']);?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_View_results', $siteLangId); ?></a>
					<a href="javascript:void(0)" onclick="deleteSavedSearch(<?php echo $row['pssearch_id'];?>)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?></a>
				</div>
			</div>
		</li>
	  <?php }?>
	</ul>
</div>
<?php
	$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'siteLangId'=>$siteLangId);
	$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
}else{
	$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId),false);
}?>
