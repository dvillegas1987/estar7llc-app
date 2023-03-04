<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
if (count($productSpecifications) > 0){ ?>
<div class="row">
    <div class="col-md-12">
        <div class="tablewrap">
        <?php 
            $arr_flds = array(
                'prodspec_name' => Labels::getLabel('LBL_Specification_Name', $adminLangId),
                'prodspec_value' => Labels::getLabel('LBL_Specification_Value', $adminLangId),
                'prodspec_group' => Labels::getLabel('LBL_Specification_Group', $adminLangId),
                'action' => Labels::getLabel('', $adminLangId)
            );
           
            $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-bordered'));
            $th = $tbl->appendElement('thead')->appendElement('tr');
            foreach ($arr_flds as $key=>$val) {
                if($key == 'prodspec_name' || $key == 'prodspec_value' || $key == 'prodspec_group'){
                    $e = $th->appendElement('th', array('width'=>'30%'), $val);
                }else{
                    $e = $th->appendElement('th', array(), $val);
                }                
            }
            
            foreach ($productSpecifications as $specification){
                $tr = $tbl->appendElement('tr');
                    foreach ($arr_flds as $key=>$val){
                        $td = $tr->appendElement('td');
                        switch ($key){
                            case 'action':       
                                 $prodSpecId = $specification['prodspec_id'];
                                 $td->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'btn btn-sm btn-clean btn-icon btn-icon-md','title'=>Labels::getLabel('LBL_Edit',$adminLangId), 'onClick' => 'prodSpecificationSection('.$langId.','.$prodSpecId.')'), '<i class="fa fa-edit"></i>', true );
                                 $td->appendElement('a', array('href'=>'javascript:void(0)', 'class'=>'btn btn-sm btn-clean btn-icon btn-icon-md','title'=>Labels::getLabel('LBL_Delete',$adminLangId) , 'onClick' => 'deleteProdSpec('.$prodSpecId.','.$langId.')'), '<i class="fa fa-trash"></i>', true );
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
<?php }  ?>

