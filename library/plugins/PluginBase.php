<?php
require_once CONF_INSTALLATION_PATH . 'vendor/autoload.php';

class PluginBase
{
    protected $userId = 0;
    use PluginHelper;
}
