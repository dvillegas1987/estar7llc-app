<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
if (count($productSpecifications) > 0) {
    $specificationData = array();
    foreach ($productSpecifications as $data) {
        $count = 0;
        foreach ($data as $value) {
            $specificationData[$count] = array(
                'prod_spec_name' => $productSpecifications['prod_spec_name'][$count], 
                'prod_spec_value' => $productSpecifications['prod_spec_value'][$count], 
                'prod_spec_group' => isset($productSpecifications['prod_spec_group'][$count]) ? $productSpecifications['prod_spec_group'][$count] : ''
            );
            $count++;
        }
    }
    ?>
    <div class="row" dir="<?php echo $layout; ?>">
        <div class="col-md-12">
            <div class="tablewrap js-scrollable table-wrap">
                <?php
                $arr_flds = array(
                    'prod_spec_name' => Labels::getLabel('LBL_Specification_Name', $siteLangId),
                    'prod_spec_value' => Labels::getLabel('LBL_Specification_Value', $siteLangId),
                    'prod_spec_group' => Labels::getLabel('LBL_Specification_Group', $siteLangId),
                    'action' => ''
                );

                $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
                $th = $tbl->appendElement('thead')->appendElement('tr');
                foreach ($arr_flds as $key => $val) {
                    if ($key == 'prodspec_name' || $key == 'prod_spec_value' || $key == 'prod_spec_group') {
                        $e = $th->appendElement('th', array('width' => '27%'), $val);
                    } else {
                        $e = $th->appendElement('th', array(), $val);
                    }
                }

                foreach ($specificationData as $keyData=>$specification) {
                    $tr = $tbl->appendElement('tr');
                    foreach ($arr_flds as $key => $val) {
                        $td = $tr->appendElement('td');
                        switch ($key) {
                            case 'action':
                                $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), 'onClick' => 'prodSpecificationSection(' . $langId . ',' . $keyData . ')'), '<i class="fa fa-edit"></i>', true);
                                $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Delete', $siteLangId), 'onClick' => 'deleteProdSpec(' . $keyData . ',' . $langId . ')'), '<i class="fa fa-trash"></i>', true);
                                break;
                            default:
                                $td->appendElement('plaintext', array(), $specification[$key], true);
                                break;
                        }
                    }
                }
                echo $tbl->getHtml();
                ?>
            </div>
        </div>
    </div>
<?php } ?>

