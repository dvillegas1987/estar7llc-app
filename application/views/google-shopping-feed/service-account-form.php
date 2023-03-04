<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('id', 'pluginForm');
$frm->setFormTagAttribute('onsubmit', 'setuppluginform(this); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl($keyName, 'setup'));
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12; 

$btnSubmit = $frm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");

$serviceAccount = $frm->getField('service_account');
?>

<div class="box__head">
    <h4><?php echo $serviceAccount->getCaption(); ?></h4>
</div>
<div class="box__body">
    <div class="form__subcontent">
        <?php echo $frm->getFormTag();?>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frm->getFieldHtml('service_account'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('btn_submit'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php echo $frm->getExternalJS();?>
    </div>
</div>
