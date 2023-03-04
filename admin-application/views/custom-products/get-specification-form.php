<div class="form__cover nopadding--bottom specification"
    id="specification<?php echo $divCount; ?>">
    <div class="divider"></div>
    <div class="gap"></div>
    <?php
    $defaultLang = true;
    foreach ($languages as $langId => $langName) {
        $class = 'langField_' . $langId;
        if (true === $defaultLang) {
            $class .= ' defaultLang';
            $defaultLang = false;
        }
        ?>
        <div class="row align-items-center mb-4">
            <div class="col-md-3">
                <div class="h5 mb-0"><strong><?php  echo $langName; ?></strong></div>
            </div>
            <div class="col-md-3">
                <input
                    class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                    title="Specification Name" type="text"
                    name="prod_spec_name[<?php echo $langId ?>][<?php echo $divCount ?>]"
                    placeholder="<?php echo Labels::getLabel('LBL_Specification_Name', $adminLangId)?>">
            </div>
            <div class="col-md-3">
                <input
                    class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                    title="Specification Value" type="text"
                    name="prod_spec_value[<?php echo $langId ?>][<?php echo $divCount ?>]"
                    placeholder="<?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId)?>">
            </div>
            <div class="col-md-3">
                <input
                    class="<?php echo 'layout--' . Language::getLayoutDirection($langId); ?> <?php echo $class; ?>"
                    title="Specification Group" type="text"
                    name="prod_spec_group[<?php echo $langId ?>][<?php echo $divCount ?>]"
                    placeholder="<?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId)?>">
            </div>
            <?php if ($langId == key(array_slice($languages, -1, 1, true))) { ?>
                <div class="col-lg-1 col-md-1 col-sm-4 col-xs-12">
                    <button 
                        type="button"
                        onclick="removeSpecDiv(<?php echo $divCount ?>);"
                        class="btn btn--secondary ripplelink"
                        title="<?php echo Labels::getLabel('LBL_Remove', $adminLangId)?>">
                        <i class="ion-minus-round"></i>
                    </button>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
