<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_TEMPLATE_DETAIL', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <?php
            $tempFrm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
            $tempFrm->setFormTagAttribute('id', 'frmStplSetup');
            $tempFrm->setFormTagAttribute('class', 'web_form');
            $tempFrm->developerTags['colClassPrefix'] = 'col-md-';
            $tempFrm->developerTags['fld_default_col'] = 12;

            $fld = $tempFrm->getField("stpl_body");
            $fld->setfieldTagAttribute('maxlength', applicationConstants::SMS_CHARACTER_LENGTH);
            $fld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_MAXIMUM_OF_160_CHARACTERS_ALLOWED', $adminLangId) . ' </small> : ' . 'YOUR message length : ' . strlen($fld->value) ;

            $replacementVarObj = $tempFrm->getField('stpl_replacements');
            $replacementVariables = !empty($replacementVarObj->value) ? json_decode($replacementVarObj->value, true) : [];
            $htm = '<ul class="list-group">';
        foreach ($replacementVariables as $val) {
            $htm .= '<li class="list-group-item">
                        <span>' . $val['title'] . '</span>
                        <span class="badge badge--unified-brand badge--inline badge--pill" data-container="body" data-toggle="tooltip" data-placement="top" title="' . $val['title'] . '">
                        ' . $val['variable'] . '
                        </span>
                    </li>';
        }
            $htm .= '</ul>';

            $replacementVarObj->value = !empty($replacementVariables) ? $htm : '';

            $langFld = $tempFrm->getField('lang_id');
            $langFld->setfieldTagAttribute('onChange', "detailSection('" . $stplCode . "', this.value);");

            $btnDiscard = $tempFrm->getField('btn_discard');
            $btnDiscard->setfieldTagAttribute('href', "javascript:void(0)");
            $btnDiscard->setfieldTagAttribute('onClick', "recordInfoSection();");

            echo  $tempFrm->getFormHtml();
        ?>
    </div>
</section>