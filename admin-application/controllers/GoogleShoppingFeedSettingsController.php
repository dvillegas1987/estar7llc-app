<?php

class GoogleShoppingFeedSettingsController extends AdvertisementFeedSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmGoogleFeed');
        $frm->addRequiredField(Labels::getLabel('LBL_CLIENT_ID', $langId), 'client_id');
        $frm->addRequiredField(Labels::getLabel('LBL_CLIENT_SECRET', $langId), 'client_secret');
        $frm->addRequiredField(Labels::getLabel('LBL_DEVELOPER_KEY', $langId), 'developer_key');
        /* $channel = [
            'local' => Labels::getLabel('LBL_LOCAL', $langId),
            'online' => Labels::getLabel('LBL_ONLINE', $langId),
        ];
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_CHANNEL', $langId), 'channel', $channel, '', [], Labels::getLabel('LBL_Select', $langId));
        $fld->requirement->setRequired(true);*/
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
