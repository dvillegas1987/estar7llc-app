<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$layout = Language::getLayoutDirection($langId);
if (count($productSpecifications) > 0){ ?>
<div class="row" dir="<?php echo $layout; ?>">
    <div class="col-md-12">
        <div class="js-scrollable table-wrap">
        <?php 
            $arr_flds = array(
                'prodspec_name' => Labels::getLabel('LBL_Specification_Name', $siteLangId),
                'prodspec_value' => Labels::getLabel('LBL_Specification_Value', $siteLangId),
                'prodspec_group' => Labels::getLabel('LBL_Specification_Group', $siteLangId),
                'action' => ''
            );
           
            $tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-justified'));
            $th = $tbl->appendElement('thead')->appendElement('tr');
            foreach ($arr_flds as $key=>$val) {
                if($key == 'prodspec_name' || $key == 'prodspec_value' || $key == 'prodspec_group'){
                    $e = $th->appendElement('th', array('width'=>'27%'), $val);
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
                                 $ul = $td->appendElement('ul', array('class' => 'actions'));
                                 $li = $ul->appendElement('li');
                                 $li->appendElement('a', array('href'=>'javascript:void(0)', 'title'=>Labels::getLabel('LBL_Edit',$siteLangId), 'onClick' => 'prodSpecificationSection('.$langId.','.$prodSpecId.')'), '<i class="fa fa-edit"></i>', true );
                                 
                                 $lia = $li->appendElement('li');
                                 $lia->appendElement('a', array('href'=>'javascript:void(0)', 'title'=>Labels::getLabel('LBL_Delete',$siteLangId) , 'onClick' => 'deleteProdSpec('.$prodSpecId.','.$langId.')'), '<i class="fa fa-trash"></i>', true );
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

