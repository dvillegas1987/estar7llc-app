<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_PLUGINS', $adminLangId); ?>
                            </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div class="tabs_nav_container vertical">
                    <ul class="tabs_nav outerul">
                        <?php $count = 1;
                        foreach ($plugins as $formType => $tabName) {
                            $tabsId = 'tabs_' . $count; ?>
                            <li>
                                <a class="<?php echo ($activeTab == $formType) ? 'active' : '' ?>" 
                                rel=<?php echo $tabsId; ?> 
                                href="javascript:void(0)" 
                                onClick="searchPlugin(<?php echo $formType; ?>);" 
                                data-formtype="<?php echo $formType; ?>">
                                    <?php echo $tabName; ?>
                                </a>
                            </li>
                        <?php $count++;
                        } ?>
                    </ul>
                    <div id="pluginsListing" class="tabs_panel_wrap">
                        <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>