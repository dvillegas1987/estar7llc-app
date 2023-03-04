<?php

class MailchimpHelper
{
    public static function saveSubscriber($email)
    {
        include_once CONF_INSTALLATION_PATH . 'library/Mailchimp.php';
        $apiKey = FatApp::getConfig("CONF_MAILCHIMP_KEY");
        $listId = FatApp::getConfig("CONF_MAILCHIMP_LIST_ID");
        if ($apiKey == '' || $listId == '') {
            return false;
        }

        $MailchimpObj = new Mailchimp($apiKey);
        $Mailchimp_ListsObj = new Mailchimp_Lists($MailchimpObj);
        try {
            $subscriber = $Mailchimp_ListsObj->subscribe($listId, array( 'email' => htmlentities($email)));
        } catch (Mailchimp_Error $e) {
        }
        return true;
    }
}
