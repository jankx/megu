<?php
namespace Jankx\Megu\Extensions\IconFonts;

use Jankx\Megu\Abstracts\Extension;
use Jankx\Megu\Abstracts\FontIconGenerator;
use Jankx\Megu\Extensions\IconFonts\Generator\Fontastic;

class ThemeIconFonts extends Extension
{
    const EXTENSION_NAME = 'theme_icon_fonts';

    protected $generators = array();

    public function getName()
    {
        return 'theme_icon_fonts';
    }

    public function execute()
    {
        if (is_admin()) {
            if (wp_is_request('ajax') && isset($_REQUEST['action']) && $_REQUEST['action'] === 'mm_get_lightbox_html') {
                $generator_classes = apply_filters('jankx_megu_icon_font_generators', array(
                    Fontastic::class,
                ));
                foreach ($generator_classes as $generator_class) {
                    $generator = new $generator_class();
                    if (!is_a($generator, FontIconGenerator::class)) {
                        continue;
                    }
                    array_push($this->generators, $generator);
                }
            }
            add_action('jankx/icon/fonts/new', array($this, 'integrateCoreIconFont'), 10, 4);
        }
    }

    protected function detectGenerator($font_name, $path, $font_family)
    {
        foreach ($this->generators as $generator) {
            $generator->setFontPath($path);
            $generator->setFontName($font_name);
            $generator->setFontFamily($font_family);

            if ($generator->isMatched()) {
                return $generator;
            }
        }
    }

    protected function createFontNameHumanReadble($font_name)
    {
        return preg_replace_callback(
            array(
                '/([-_])(\w)/',
                '/^(\w)/'
            ),
            function ($matches) {
                return strtoupper(end($matches));
            },
            $font_name
        );
    }

    public function integrateCoreIconFont($font_name, $path, $display_name, $font_family)
    {
        $generator = $this->detectGenerator($font_name, $path, $font_family);
        if ($generator) {
            add_filter('megamenu_icon_tabs', function ($tabs) use ($generator, $display_name) {
                $font_family = $generator->getFontFamily();
                $tabs[] = array(
                    'title'   => $display_name ? $display_name : $this->createFontNameHumanReadble($font_family),
                    'active'  => isset($menu_item_meta['icon']) && substr($menu_item_meta['icon'], 0, strlen($font_family)) === $font_family,
                    'content' => $generator->iconSelector(),
                );

                return $tabs;
            }, 10, 5);
        }

        add_action('admin_head', function () use ($font_family) {
            echo "<style>
                .nav-menus-php #cboxContent .menu_icon .icon_selector .{$font_family} label:before {
                    font: 400 24px/1 \"{$font_family}\";
                }
            </style>";
        });
    }
}
