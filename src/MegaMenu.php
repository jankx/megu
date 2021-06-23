<?php
namespace Jankx\MegaMenu;

class MegaMenu
{
    protected static $instance;

    public static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->bootstrap();
    }

    protected function bootstrap() {
        if (!defined('JANKX_MEGA_MENU_ROOT')) {
            define('JANKX_MEGA_MENU_ROOT', dirname(__DIR__));
        }
        require_once sprintf('%s/megamenu.php', JANKX_MEGA_MENU_ROOT);
    }
}
