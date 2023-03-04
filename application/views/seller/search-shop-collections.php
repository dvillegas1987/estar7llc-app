<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5>
        <div class="btn-group">
            <?php if ($canEdit) { ?>
                <a href="javascript:void(0)" onClick="toggleBulkCollectionStatues(1)" class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css"><?php echo Labels::getLabel('LBL_Activate', $siteLangId);?></a>
                <a href="javascript:void(0)" onClick="toggleBulkCollectionStatues(0)" class="btn btn-outline-brand btn-sm  formActionBtn-js formActions-css"><?php echo Labels::getLabel('LBL_Deactivate', $siteLangId);?></a>
                <a href="javascript:void(0)" onClick="deleteSelectedCollection()" class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css"><?php echo Labels::getLabel('LBL_Delete', $siteLangId);?></a>
                <?php if (count($arr_listing) > 0) { ?>
                <a href="javascript:void(0)" onClick="getShopCollectionGeneralForm(0)" class="btn btn-outline-brand btn-sm  btn-sm"><?php echo Labels::getLabel('LBL_Add_Collection', $siteLangId);?></a>
                <?php }?>
            <?php }?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12 col-md-12 js-scrollable table-wrap">
                <?php
                $arr_flds = array(
                    'listserial'=>'#',
                    'scollection_identifier'=>Labels::getLabel('LBL_Collection_Name', $siteLangId),
                    'scollection_active'=>Labels::getLabel('LBL_Status', $siteLangId),
                    'action' => '',
                );
                if (count($arr_listing) > 0) {
                    $arr_flds = array_merge(
                        array('select_all'=>''),
                        $arr_flds
                    );
                }
				$tableClass = '';
				if (0 < count($arr_listing) && $canEdit) {
					$tableClass = "table-justified";
				}
                $tbl = new HtmlElement(
                    'table',
                    array('width'=>'100%', 'class'=>'table '.$tableClass, 'id'=>'options')
                );

                $th = $tbl->appendElement('thead')->appendElement('tr');
                foreach ($arr_flds as $key => $val) {
                    if ('select_all' == $key && $canEdit) {
                        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"><i class="input-helper"></i>'.$val.'</label>', true);
                    } else {
                        $th->appendElement('th', array(), $val);
                    }
                }
                $sr_no = 0;
                foreach ($arr_listing as $sn => $row) {
                    $sr_no ++;
                    $tr = $tbl->appendElement('tr');
                    $tr->setAttribute("id", $row['scollection_id']);

                    foreach ($arr_flds as $key => $val) {
                        $td = $tr->appendElement('td');
                        switch ($key) {
                            case 'select_all':
                                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="scollection_ids[]" value='.$row['scollection_id'].'><i class="input-helper"></i></label>', true);
                                break;
                            case 'listserial':
                                $td->appendElement('plaintext', array(), $sr_no);
                                break;
                            case 'scollection_identifier':
                                $td->appendElement('plaintext', array(), $row[$key], true);
                                break;

                            case 'scollection_active':
                                /* $td->appendElement( 'plaintext', array(), $activeInactiveArr[$row[$key]],true ); */
                                $active = "";
                                if (applicationConstants::ACTIVE == $row['scollection_active']) {
                                    $active = 'checked';
                                }
                                $checked = (!$canEdit) ? 'disabled' : $active;
                                $str = '<label class="toggle-switch" for="switch'.$row['scollection_id'].'"><input '.$checked.' type="checkbox" value="'.$row['scollection_id'].'" id="switch'.$row['scollection_id'].'" onclick="toggleShopCollectionStatus(event,this)"/><div class="slider round"></div></label>';

                                $td->appendElement('plaintext', array(), $str, true);
                                break;

                            case 'action':
                                $ul = $td->appendElement("ul", array("class"=>"actions"));
                                if ($canEdit) {
                                    $li = $ul->appendElement("li");
                                    $li->appendElement(
                                        'a',
                                        array(
                                        'href'=>'javascript:void(0)',
                                        'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId),
                                        "onclick"=>"getShopCollectionGeneralForm(".$row['scollection_id'].")"),
                                        '<i class="fa fa-edit"></i>',
                                        true
                                    );

                                    $li = $ul->appendElement("li");
                                    $li->appendElement(
                                        'a',
                                        array(
                                        'href'=>"javascript:void(0)", 'class'=>'button small green',
                                        'title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"deleteShopCollection(".$row['scollection_id'].")"),
                                        '<i class="fa fa-trash"></i>',
                                        true
                                    );
                                }
                                break;
                            default:
                                $td->appendElement('plaintext', array(), $row[$key], true);
                                break;
                        }
                    }
                }

                $frm = new Form('frmCollectionsListing', array('id'=>'frmCollectionsListing'));
                $frm->setFormTagAttribute('class', 'form');
                $frm->setFormTagAttribute('onsubmit', 'formAction(this, searchShopCollections ); return(false);');
                $frm->setFormTagAttribute('action', UrlHelper::generateUrl('Seller', 'toggleBulkCollectionStatuses'));
                $frm->addHiddenField('', 'collection_status', '');

                echo $frm->getFormTag();
                echo $frm->getFieldHtml('collection_status');
                echo $tbl->getHtml();
                if (count($arr_listing) == 0) {
                    $message = Labels::getLabel('LBL_No_Collection_found', $siteLangId);
                    $linkArr = array(
                        0=>array(
                        'href'=>'javascript:void(0);',
                        'label'=>Labels::getLabel('LBL_Add_Collection', $siteLangId),
                        'onClick'=>"getShopCollectionGeneralForm(0)",
                        )
                    );
                    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'linkArr'=>$linkArr,'message'=>$message));
                } ?>
                </form>
            </div>
        </div>
    </div>
</div>
