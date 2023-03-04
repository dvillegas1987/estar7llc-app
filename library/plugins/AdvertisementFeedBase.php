<?php

class AdvertisementFeedBase extends PluginBase
{
    protected function getUserMeta($key = '')
    {
        $userId = 0 < $this->userId ? $this->userId : UserAuthentication::getLoggedUserId(true);
        return User::getUserMeta($userId, $key);
    }
}
