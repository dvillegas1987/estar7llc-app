<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card cards-js">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Social_Platforms', $siteLangId); ?></h5>
        <div class="">
            <?php if ($canEdit) { ?>
                <a href="javascript:void(0)" class="btn btn-outline-brand btn-sm" onclick="addForm(0)"><?php echo Labels::getLabel('LBL_Add_Social_Platform', $siteLangId);?></a>
            <?php }?>
        </div>
    </div>
    <div class="card-body">
        <div class="js-scrollable table-wrap">
            <?php $arr_flds = array(
                'listserial'=>'#',
                'splatform_identifier'=>Labels::getLabel('LBL_Title', $siteLangId),
                'splatform_url'    =>    Labels::getLabel('LBL_URL', $siteLangId),
                'splatform_active'    =>    Labels::getLabel('LBL_Status', $siteLangId)
            );
            if ($canEdit) {
                $arr_flds['action'] = '';
            }
			$tableClass = '';
			if (0 < count($arr_listing)) {
				$tableClass = "table-justified";
			}
            $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table '.$tableClass));
            $th = $tbl->appendElement('thead')->appendElement('tr');
            foreach ($arr_flds as $key => $val) {
                if ($key == 'listserial') {
                    $e = $th->appendElement('th', array('width' => '5%'), $val);
                } elseif ($key == 'splatform_identifier') {
                    $e = $th->appendElement('th', array('width' => '25%'), $val);
                } elseif ($key == 'splatform_url') {
                    $e = $th->appendElement('th', array('width' => '45%'), $val);
                } elseif ($key == 'splatform_active') {
                    $e = $th->appendElement('th', array('width' => '10%'), $val);
                }  elseif ($key == 'action') {
                    $e = $th->appendElement('th', array('width' => '15%'), $val);
                }
            }

            $sr_no = 0;
            foreach ($arr_listing as $sn=>$row) {
                $sr_no++;
                $tr = $tbl->appendElement('tr', array('class' => ($row['splatform_active'] != applicationConstants::ACTIVE) ? 'fat-inactive' : '' ));
                foreach ($arr_flds as $key=>$val) {
                    $td = $tr->appendElement('td');
                    switch ($key) {
                        case 'listserial':
                            $td->appendElement('plaintext', array(), $sr_no);
                        break;
                        case 'splatform_identifier':
                            if ($row['splatform_title']!='') {
                                $td->appendElement('plaintext', array(), $row['splatform_title'], true);
                                $td->appendElement('br', array());
                                $td->appendElement('plaintext', array(), '('.$row[$key].')', true);
                            } else {
                                $td->appendElement('plaintext', array(), $row[$key], true);
                            }
                        break;
                        case 'splatform_active':
                            /* $td->appendElement( 'plaintext', array(), $activeInactiveArr[$row[$key]],true ); */
                            $active = "";
                            if (applicationConstants::ACTIVE == $row['splatform_active']) {
                                $active = 'checked';
                            }
                            $checked = (!$canEdit) ? 'disabled' : $active;
                            $str = '<label class="toggle-switch" for="switch'.$row['splatform_id'].'"><input '.$checked.' type="checkbox" value="'.$row['splatform_id'].'" id="switch'.$row['splatform_id'].'" onclick="toggleSocialPlatformStatus(event,this)"/><div class="slider round"></div></label>';

                            $td->appendElement('plaintext', array(), $str, true);
                            break;
                        case 'action':
                            $ul = $td->appendElement("ul", array("class"=>"actions"));
                            $li = $ul->appendElement("li");
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Edit', $siteLangId),"onclick"=>"addForm(".$row['splatform_id'].")"), '<i class="fa fa-edit"></i>', true);
                            $li = $ul->appendElement("li");
                            $li->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'button small green', 'title'=>Labels::getLabel('LBL_Delete', $siteLangId),"onclick"=>"deleteRecord(".$row['splatform_id'].")"), '<i class="fa fa-trash"></i>', true);
                        break;
                        default:
                            $td->appendElement('plaintext', array(), $row[$key], true);
                        break;
                    }
                }
            }
            echo $tbl->getHtml();
            if (count($arr_listing) == 0) {
                $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
                $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
            }
            ?>
            </form>
        </div>
    </div>
</div>
