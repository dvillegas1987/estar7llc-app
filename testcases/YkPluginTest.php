<?php
class YkPluginTest extends YkAppTest
{
    protected $pluginTest = true;

    /**
     * setupBeforeClass - This will treat as constructor.
     *
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        $class = get_called_class();
        $keyName = ($class)::KEY_NAME;
        $pluginType = ($class)::PLUGIN_TYPE;
        $directory = Plugin::getDirectory($pluginType);
        
        require_once CONF_PLUGIN_DIR . $directory . '/' . strtolower($keyName) . '/' . $keyName . '.php';
        
        if (!defined('LANG_CODES_ARR')) {
            $langCodeArr = [
                '1' => 'EN',
                '2' => 'AR'
            ];
            define('LANG_CODES_ARR', $langCodeArr);
        }
    }
}
