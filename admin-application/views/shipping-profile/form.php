<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('onsubmit', 'setupProfile(this); return(false);');
?>

<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title mb-5">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Shipping_Profile', $adminLangId); ?>
                            </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet">
                                    <div class="portlet__head">
                                        <div class="portlet__head-label">
                                            <h3 class="portlet__head-title"><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>
                                            </h3>
                                        </div>
                                        <div class="portlet__head-toolbar">
                                            <div class="portlet__head-actions"></div>
                                        </div>
                                    </div>
                                    <div class="portlet__body">
                                        <?php echo $frm->getFormTag();
                                        $pNameFld = $frm->getField('shipprofile_name['.$siteDefaultLangId.']');
                                        $pNameFld->htmlAfterField = "<span class='form-text text-muted'>" . Labels::getLabel("LBL_Customers_will_not_see_this", $adminLangId) . "</span>";

                                        //$pNameFld->addFieldTagAttribute('placeholder', 'P');
                                        $pNameFld->addFieldTagAttribute('class', 'form-control');
                                        ?>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group mb-0">
                                                    <?php
                                                    /*
                                                    if (!empty($profileData) && $profileData['shipprofile_default'] == 1) {
                                                        $pNameFld->addFieldTagAttribute('readonly', 'true');
                                                        $pNameFld->addFieldTagAttribute('disabled', 'true');
                                                    }
                                                     * 
                                                     */
                                                    echo $pNameFld->getHtml(); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <?php
                                                    echo $frm->getFieldHtml('shipprofile_id');
                                                    echo $frm->getFieldHtml('shipprofile_user_id');
                                                    
                                                    echo $frm->getFieldHtml('btn_submit');
                                                    /*
                                                    if (empty($profileData) || ((isset($profileData['shipprofile_default']) && $profileData['shipprofile_default'] != 1))) {
                                                       echo $frm->getFieldHtml('btn_submit');
                                                    }
                                                     * 
                                                     */
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php 
                                            if (!empty($languages)) {
                                            ?>
                                            <div class="accordians_container accordians_container-categories my-3" data-isdefaulthidden="1" >
                                                <div class="accordian_panel">
                                                    <span class="accordian_title accordianhead" id="collapse1">
                                                        <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                                                    </span>                                                  
                                                        <div class="accordian_body accordiancontent">
                                                        <div class="p-4 mb-4 bg-gray rounded">
                                                            <div class="row">
                                                                <?php
                                                                foreach ($languages as $langId => $data) {
                                                                    if ($siteDefaultLangId == $langId) {
                                                                        continue;
                                                                    }
                                                                    $layout = Language::getLayoutDirection($langId);
                                                                    ?>
                                                                    <div class="col-md-6 layout--<?php echo $layout; ?>">
                                                                        <div class="field-set">
                                                                            <div class="caption-wraper">
                                                                                <label class="field_label">
                                                                                <?php $fld = $frm->getField('shipprofile_name[' . $langId . ']');
                                                                                echo $fld->getCaption();
                                                                                ?>                       
                                                                                </label>
                                                                            </div>
                                                                            <div class="field-wraper">
                                                                                <div class="field_cover">
                                                                                    <?php echo $fld->getHtml(); ?>                         
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>                      
                                                                <?php } ?>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        </form>
                                        <?php echo $frm->getExternalJs(); ?>
                                    </div>
                                </div>
                                <!---->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?php if (empty($profileData) || ((isset($profileData['shipprofile_default'])))) { ?>
                            <div class="portlet" id="product-section--js">
                                <div class="portlet__head">
                                    <div class="portlet__head-label">
                                        <h3 class="portlet__head-title"><?php echo Labels::getLabel('LBL_Total_Products', $adminLangId); ?>
                                            : <?php echo $productCount; ?>
                                        </h3>
                                    </div>
                                </div>
                                <div class="portlet__body">
                                    <p><span class='form-text text-muted'><?php echo Labels::getLabel('LBL_We_don\'t_show_product_list_in_default_profile._The_products_removed_from_other_profiles_will_automatically_add_in_default_profile', $adminLangId); ?></span>
                                    </p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <!--shipping from--->
                                <div class="portlet">
                                    <div class="portlet__head">
                                        <div class="portlet__head-label">
                                            <h3 class="portlet__head-title"><?php echo Labels::getLabel('LBL_Shipping_to', $adminLangId); ?>
                                            </h3>
                                        </div>
                                        <div class="portlet__head-toolbar">
                                            <div class="portlet__head-actions">
                                                <a href="javascript:void(0);" onClick="zoneForm(<?php echo $profile_id; ?>, 0)" class="link font-bolder"><i class="fa fa-plus icon"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="profile_id" value="<?php echo $profile_id; ?>">
                                    <div id="listing-zones" class="portlet__body"></div>
                                </div>
                            </div>
<!--                            <div class="col-md-6" id="shipping--js">
                            </div>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>