<?php
                if (!isset($defaultContent) || $defaultContent === false) {
                    echo FatApp::getConfig('CONF_EMAIL_TEMPLATE_FOOTER_HTML' . $langId, FatUtility::VAR_STRING, '');
                } else {
                    /* Html content used to reset footer content */
                    ?>

                    <table align="center" cellpadding="0" cellspacing="0" style="width:100%; margin:auto;">
                        <tr>
                            <td style="background:#fff;vertical-align:top;text-align: center;">
                                <table cellpadding="0" cellspacing="0" style="width: 100%;">
                                    <tr>
                                        <td style="color:#999;padding:30px 30px;">
                                            Get in touch if you have any questions regarding our Services.<br /> Feel free to contact us 24/7. We are here to help.<br />
                                            <br /> All the best,<br /> The {website_name} Team<br />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 30px 30px;background:rgba(0,0,0,0.04); text-align: center;">
                                <h4 style="font-size:20px; color:#000;margin: 0;">Need more help?</h4>
                                <a href="{contact_us_url}" style="color:#ff3a59;">We are here, ready to talk</a>
                                <br> <br>
                                {social_media_icons}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0; text-align: center; font-size:13px; color:#999;vertical-align:top; line-height:20px;padding: 10px;">
                                {website_name} Inc.
                            </td>
                        </tr>
                    </table>
                <?php } ?>
                </td>
            </tr>
        </table>
    </div>
</div>
