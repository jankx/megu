<?php
namespace Jankx\Megu;

use Jankx\Megu\Extensions\Vertical\VerticalMenu;
use Jankx\Megu\Constracts\Extension;

class Megu
{
    protected static $instance;
    protected static $extensions = array();

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->bootstrap();
        $this->loadExtensions();
        $this->initHooks();
    }

    protected function bootstrap()
    {
        if (!defined('JANKX_MEGA_MENU_ROOT')) {
            define('JANKX_MEGA_MENU_ROOT', dirname(__DIR__));
        }
        require_once sprintf('%s/megamenu.php', JANKX_MEGA_MENU_ROOT);
    }

    public function loadExtensions()
    {
        $extensions = apply_filters('jankx_megu_extensions', array(
            VerticalMenu::class,
        ));

        foreach ($extensions as $extension) {
            $extensionObj = new $extension();
            if (!is_a($extensionObj, Extension::class)) {
                continue;
            }
            static::$extensions[$extensionObj->getName()] = $extensionObj;

            // Execute the extension
            $extensionObj->execute();
        }
    }

    public function initHooks()
    {
    }

    public static function getExtension($name)
    {
        if (isset(static::$extensions[$name])) {
            return static::$extensions[$name];
        }
    }
}
