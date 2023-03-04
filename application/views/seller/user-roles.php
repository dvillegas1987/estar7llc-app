<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<form class="form">
    <div class="row">
        <?php foreach ($arrListing as $key => $arrList) { ?>
            <div class="col-xl-6 col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo $modulesArr[$key]; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="js-scrollable table-wrap">
                            <?php
                            $arr_flds = array(
                                'listserial' => Labels::getLabel('LBL_#', $siteLangId),
                                'module' => Labels::getLabel('LBL_Module', $siteLangId),
                                'permission' => Labels::getLabel('LBL_Permissions', $siteLangId),
                            );
                            $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
                            $th = $tbl->appendElement('thead')->appendElement('tr');
                            foreach ($arr_flds as $val) {
                                $e = $th->appendElement('th', array(), $val);
                            }

                            $sr_no = 0;
                            foreach ($arrList as $sn => $row) {
                                $sr_no++;
                                $tr = $tbl->appendElement('tr');

                                foreach ($arr_flds as $key => $val) {
                                    $td = $tr->appendElement('td');
                                    switch ($key) {
                                        case 'listserial':
                                            $td->appendElement('plaintext', array(), $sr_no);
                                            break;
                                        case 'module':
                                            $td->appendElement('plaintext', array(), $row, true);
                                            break;
                                        case 'permission':
                                            $listing = UserPrivilege::getPermissionArr($siteLangId);
                                            $options = '';
                                            foreach ($listing as $key => $list) {
                                                if (
                                                    in_array($sn, UserPrivilege::getWriteOnlyPermissionModulesArr())
                                                    && $key == UserPrivilege::PRIVILEGE_READ
                                                ) {
                                                    continue;
                                                }

                                                $selected = '';
                                                if (isset($userData[$sn]) && !empty($userData[$sn]) && $userData[$sn]['userperm_value'] == $key) {
                                                    $selected = 'selected';
                                                }
                                                $options .= "<option value=" . $key . " " . $selected . ">" . $list . "</option>";
                                            }
                                            $td->appendElement('plaintext', array(), "<select name='permission' onChange='updatePermission(" . $sn . ",this.value)'>" . $options . "</select>", true);
                                            break;
                                    }
                                }
                            }
                            if (count($arrList) == 0) {
                                $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $siteLangId));
                            }
                            echo $tbl->getHtml();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</form>