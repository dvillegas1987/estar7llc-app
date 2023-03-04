<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Import_Export', $adminLangId); ?>
                            </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div class="tabs_nav_container vertical">
                    <?php 
                        $variables = array('adminLangId'=>$adminLangId,'action'=>$action);
                        $this->includeTemplate('import-export/_partial/top-navigation.php',$variables,false); 
                    ?>
                    <div id="tabData" class="tabs_panel_wrap"> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>