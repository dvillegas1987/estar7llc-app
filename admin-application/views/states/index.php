<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_States', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <!--<div class="row">
<!--<div class="row">
    <div class="col-sm-12"> -->
                <h1><?php //echo Labels::getLabel('LBL_Manage_States',$adminLangId);?> </h1>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                    <?php
                        $search->setFormTagAttribute('onsubmit', 'searchState(this); return(false);');
                        $search->setFormTagAttribute('class', 'web_form');
                        $search->setFormTagAttribute('id', 'frmSearch');
                        $search->developerTags['colClassPrefix'] = 'col-md-';
                        $search->developerTags['fld_default_col'] = 6;

                        $search->getField('keyword')->addFieldtagAttribute('class', 'search-input');
                        $search->getField('country')->addFieldtagAttribute('class', 'search-input');
                        $search->getField('btn_clear')->addFieldtagAttribute('onclick', 'clearSearch();');

                        echo  $search->getFormHtml();
                    ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_State_Listing', $adminLangId); ?></h4>
                        <?php
                        if ($canEdit) {
                            $data = [
                                'adminLangId' => $adminLangId,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'addStateForm(0)',
                                            'title' => Labels::getLabel('LBL_Add_State', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-plus"></i>'
                                    ],
                                ]
                            ];
        
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
