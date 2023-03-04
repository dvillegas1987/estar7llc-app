<div style="width:100%;padding:24px 0 16px 0;background-color:#f5f5f5;text-align:center">
    <div style="display:inline-block;width:90%;max-width:680px;min-width:280px;text-align:left;font-family:Arial,Helvetica,sans-serif">
        <table style="width:600px;margin: 0 auto; font-family:Arial; color:#333; line-height:26px;">
            <tr>
                <td style="background:#<?php echo FatApp::getConfig('CONF_EMAIL_TEMPLATE_COLOR_CODE' . $langId, FatUtility::VAR_STRING, 'FF3A59'); ?>;width:100%;text-align: center;">
                    <table align="center" cellpadding="0" cellspacing="0" style="width:100%;text-align: center;">
                        <tr>
                            <td style="padding:15px;width:100%">
                                <div style="margin: 0 auto; max-width:<?php echo (FatApp::getConfig('CONF_EMAIL_TEMPLATE_LOGO_RATIO', FatUtility::VAR_INT, 1) == EmailTemplates::LOGO_RATIO_SQUARE) ? '60px' : '150px' ?>;">
                                    <a style="display: table;text-align:center;width: 100%; margin:0 auto;" href="{website_url}">
                                        {Company_Logo}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>                        