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
                            <h5><?php echo Labels::getLabel('LBL_Manage_Abusive_Words', $adminLangId); ?> </h5>
                             <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $frmSearch->setFormTagAttribute('onsubmit', 'searchWords(this); return(false);');
                            $frmSearch->setFormTagAttribute('id', 'frmLabelsSearch');
                            $frmSearch->setFormTagAttribute('class', 'web_form');
                            $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                            $frmSearch->developerTags['fld_default_col'] = 6;

                            $btn = $frmSearch->getField('btn_clear');
                            $btn->setFieldTagAttribute('onClick', 'clearSearch()');
                            echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Abusive_Keyword_List', $adminLangId); ?> </h4>
                        <?php
                            if ($canEdit) {
                                $data = [
                                    'adminLangId' => $adminLangId,
                                    'statusButtons' => false,
                                    'otherButtons' => [
                                        [
                                            'attr' => [
                                                'href' => 'javascript:void(0)',
                                                'onclick' => 'abusiveKeywordForm(0)',
                                                'title' => Labels::getLabel('LBL_Add_Keyword', $adminLangId)
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
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?> </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var langLayOuts = {
        <?php foreach ($languages as $langId => $langName) {
            $layOutDir = Language::getLayoutDirection($langId);
            echo '"'.$langId.'":"'.$layOutDir.'",';
        } ?>
    };
    (function() {
        changeFormLayOut = function(el) {
            var langId = $(el).val();
            var className = 'layout--' + langLayOuts[langId];
            $("#frmAbusiveWord").removeClass(function(index, className) {
                return (className.match(/(^|\s)layout--\S+/g) || []).join(' ');
            });
            $("#frmAbusiveWord").addClass(className);
        };
    })();
</script>
