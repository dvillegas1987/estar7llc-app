<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$selprodDownloadFrm->setFormTagAttribute('id', 'frmDownload');
$selprodDownloadFrm->setFormTagAttribute('class', 'form form--horizontal');
$selprodDownloadFrm->developerTags['colClassPrefix'] = 'col-md-';
$selprodDownloadFrm->developerTags['fld_default_col'] = 4; ?>
<div class="row">
    <div class="col-md-12">
        <div class="form__subcontent">
            <?php echo $selprodDownloadFrm->getFormTag(); ?>
            <?php foreach ($savedOptions as $key => $val) {
                $downloadableFileFld = $selprodDownloadFrm->getField('downloadable_file' . $key);
                $downloadableFileFld->setFieldTagAttribute('onchange', 'setUpSellerProductDownloads(' . applicationConstants::DIGITAL_DOWNLOAD_FILE . ', ' . $product_id . ',' . $key . '); return false;');
                $dwnldLink = $selprodDownloadFrm->getField('selprod_downloadable_link' . $key);
                $dwnldLink->setFieldTagAttribute('class', 'h-auto');
                $submitButton = $selprodDownloadFrm->getField('btn_submit' . $key);
                $submitButton->setFieldTagAttribute('class', 'btn btn-brand btn-wide');
                $submitButton->setFieldTagAttribute('onClick', 'setUpSellerProductDownloads(' . applicationConstants::DIGITAL_DOWNLOAD_LINK . ', ' . $product_id . ',' . $key . '); return false;'); ?>
                <div class="p-4 mb-4 border rounded">
                    <h6><?php echo ($val !== '') ? Labels::getLabel('LBL_Add_downloads_for', $siteLangId) . ' ' . str_replace("_", " | ", $val) : '' ?></h6>
                    <div class="row">
                        <?php /* <div class="col-md-4">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $selprodDownloadFrm->getField('download_type'.$key)->getCaption(); ?><span
                                            class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $selprodDownloadFrm->getFieldHtml('download_type'.$key); ?></div>
                                </div>
                            </div>
                        </div> */ ?>
                        <div class="col-md-9 downloadable_link_fld<?php echo $key; ?>">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $selprodDownloadFrm->getField('selprod_downloadable_link' . $key)->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $selprodDownloadFrm->getFieldHtml('selprod_downloadable_link' . $key); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 submit_button<?php echo $key; ?>">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $selprodDownloadFrm->getFieldHtml('btn_submit' . $key); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 lang_fld<?php echo $key; ?>">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $selprodDownloadFrm->getField('lang_id' . $key)->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $selprodDownloadFrm->getFieldHtml('lang_id' . $key); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 downloadable_file_fld<?php echo $key; ?>">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $selprodDownloadFrm->getField('downloadable_file' . $key)->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $selprodDownloadFrm->getFieldHtml('downloadable_file' . $key); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 filesList<?php echo $key; ?> js-scrollable table-wrap">
                            <?php
                            $arr_flds = array(
                                'listserial' => Labels::getLabel('LBL_#', $siteLangId),
                                'afile_name' => Labels::getLabel('LBL_File', $siteLangId),
                                'afile_lang_id' => Labels::getLabel('LBL_Language', $siteLangId),
                                'action' => Labels::getLabel('LBL_Action', $siteLangId),
                            );
							$tableClass = '';
							$attachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD, $key, 0, -1);
							if (0 < count($attachments)) {
								$tableClass = "table-justified";
							}
                            $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table '.$tableClass));
                            $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
                            foreach ($arr_flds as $val) {
                                $e = $th->appendElement('th', array(), $val);
                            }

                            $sr_no = 0;
                            foreach ($attachments as $sn => $row) {
                                $sr_no++;
                                $tr = $tbl->appendElement('tr');
                                foreach ($arr_flds as $key => $val) {
                                    $td = $tr->appendElement('td');
                                    switch ($key) {
                                        case 'listserial':
                                            $td->appendElement('plaintext', array(), $sr_no, true);
                                            break;
                                        case 'afile_lang_id':
                                            $lang_name = Labels::getLabel('LBL_All', $siteLangId);
                                            if ($row['afile_lang_id'] > 0) {
                                                $lang_name = $languages[$row['afile_lang_id']];
                                            }
                                            $td->appendElement('plaintext', array(), $lang_name, true);
                                            break;
                                        case 'afile_name':
                                            $fileName = '<a target="_blank" href="' . UrlHelper::generateUrl('seller', 'downloadDigitalFile', array($row['afile_id'], $row['afile_record_id'])) . '">' . $row[$key] . '</a>';
                                            $td->appendElement('plaintext', array(), $fileName, true);
                                            break;
                                        case 'action':
                                            $ul = $td->appendElement("ul", array("class" => "actions"), '', true);

                                            $li = $ul->appendElement("li");
                                            $li->appendElement(
                                                "a",
                                                array('title' => Labels::getLabel('LBL_Product_Images', $siteLangId), 'onclick' => 'deleteDigitalFile(' . $row['afile_record_id'] . ',' . $row['afile_id'] . ')', 'href' => 'javascript:void(0)'),
                                                '<i class="fa fa-trash"></i>',
                                                true
                                            );

                                            break;
                                        default:
                                            $td->appendElement('plaintext', array(), $row[$key], true);
                                            break;
                                    }
                                }
                            }
                            if (!empty($attachments)) {
                                echo $tbl->getHtml();
                            } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </form>
            <?php echo $selprodDownloadFrm->getExternalJS(); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    var DIGITAL_DOWNLOAD_FILE = <?php echo applicationConstants::DIGITAL_DOWNLOAD_FILE; ?>;
    var DIGITAL_DOWNLOAD_LINK = <?php echo applicationConstants::DIGITAL_DOWNLOAD_LINK; ?>;

    /* $(".downloadType-js").each(function() {
        var id = $(this).attr('id');
        $("select[name='download_type"+id+"']").trigger("change");
        $(document).on("change", "select[name='download_type"+id+"']", function(){
            if ($(this).val() == DIGITAL_DOWNLOAD_FILE) {
                $(".lang_fld"+id).show();
                $(".downloadable_file_fld"+id).show();
                $(".filesList"+id).show();
                $(".downloadable_link_fld"+id).hide();
                $(".submit_button"+id).hide();
            } else {
                $(".lang_fld"+id).hide();
                $(".downloadable_file_fld"+id).hide();
                $(".filesList"+id).hide();
                $(".downloadable_link_fld"+id).show();
                $(".submit_button"+id).show();
            }
        });

    }); */
</script>