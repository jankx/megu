<?php
namespace Jankx\Megu\Extensions\Vertical;

use Jankx\Megu\Abstracts\Extension;

class VerticalMenu extends Extension
{
    public function getName()
    {
        return 'vertical';
    }

    public function execute()
    {
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        add_action('megamenu_settings_table', array($this, 'addOrientationSetting'), 10, 2);

        add_filter('megamenu_nav_menu_args', array( $this, 'appendVerticalCss'), 10, 3);
        add_filter('megamenu_load_scss_file_contents', array($this, 'verticalScssContent'));

        add_filter('megamenu_wrap_attributes', array( $this, 'applyAccordionAttributes'), 9, 5);
        add_filter('megamenu_nav_menu_css_class', array( $this, 'accordionKeepParentsOpen' ), 10, 3);

        if (apply_filters('jankx_megu_enable_vertical_menu_item', false)) {
            add_filter('jankx/layout/site/menu/itemtypes', array($this, 'registerVerticalMenuItem'));
            if (!apply_filters('jankx_megu_enable_custom_vertical_menu', false)) {
                add_filter('nav_menu_item_title', array($this, 'renderVerticalMenuItem'), 10, 4);
            }
        }
    }

    public function addOrientationSetting($location, $settings)
    {
        ?>
        <tr class='megamenu_orientation'>
                <td><?php _e("Orientation", "megamenupro"); ?></td>
                <td>
                    <select class='megamenu_orientation_select' name='megamenu_meta[<?php echo $location ?>][orientation]'>
                        <option value='horizontal'>Horizontal</option>
                        <option value='vertical' <?php selected(isset($settings[$location]['orientation']) && $settings[$location]['orientation'] == 'vertical') ?>><?php _e("Vertical", "megamenupro"); ?></option>
                        <option value='accordion' <?php selected(isset($settings[$location]['orientation']) && $settings[$location]['orientation'] == 'accordion') ?>><?php _e("Accordion", "megamenupro"); ?></option>
                    </select>
                </td>
            </tr>

            <?php
            if (isset($settings[$location]['orientation']) && $settings[$location]['orientation'] == 'accordion') {
                $display = 'table-row';
            } else {
                $display = 'none';
            }
            ?>

            <tr class='megamenu_accordion_behaviour' style='display: <?php echo $display; ?>;'>
                <td><?php _e("Accordion Behaviour", "megamenupro"); ?></td>
                <td>
                    <select name='megamenu_meta[<?php echo $location ?>][accordion_behaviour]'>
                        <option value='open_parents' <?php selected(isset($settings[$location]['accordion_behaviour']) && $settings[$location]['accordion_behaviour'] == 'open_parents') ?>><?php _e("Expand active sub menus", "megamenupro"); ?></option>
                        <option value='open_all' <?php selected(isset($settings[$location]['accordion_behaviour']) && $settings[$location]['accordion_behaviour'] == 'open_all') ?>><?php _e("Expand all sub menus", "megamenupro"); ?></option>
                        <option value='collapse_parents' <?php selected(isset($settings[$location]['accordion_behaviour']) && $settings[$location]['accordion_behaviour'] == 'collapse_parents') ?>><?php _e("Always collapse submenus", "megamenupro"); ?></option>
                    </select>
                </td>
            </tr>
        <?php
    }

    public function appendVerticalCss($args, $menu_id, $location)
    {
        $settings = get_option('megamenu_settings');

        if (isset($settings[$location]['orientation'])) {
            $args['menu_class'] = str_replace('horizontal', $settings[$location]['orientation'], $args['menu_class']);
        }

        return $args;
    }

    public function verticalScssContent($scss)
    {
        $contents = file_get_contents(
            sprintf('%s/assets/scss/vertical.scss', JANKX_MEGU_ROOT)
        );

        return $scss . $contents;
    }

    public function applyAccordionAttributes($attributes, $menu_id, $menu_settings, $settings, $current_theme_location)
    {
        if (isset($menu_settings['orientation']) && $menu_settings['orientation'] == 'accordion') {
            $attributes['data-document-click'] = 'disabled';
        }

        return $attributes;
    }

    public function accordionKeepParentsOpen($classes, $item, $args)
    {
        if (is_object($args) && strpos($args->menu_class, 'mega-menu-accordion') !== false) {
            $settings = get_option('megamenu_settings');

            $location = $args->theme_location;

            if (isset($settings[$location]['accordion_behaviour']) && $settings[$location]['accordion_behaviour'] == 'open_parents' || ! isset($settings[$location]['accordion_behaviour'])) {
                if (in_array('mega-menu-item-has-children', $classes)) {
                    $needles = apply_filters('megamenu_accordion_parent_classes', array(
                        'mega-current_page_ancestor',
                        'mega-current_page_item',
                        'mega-current-menu-ancestor',
                        'mega-current-menu-item',
                        'mega-current-menu-parent'
                    ));

                    $parent_is_current = array_intersect($needles, $classes);

                    if (! empty($parent_is_current)) {
                        $classes[] = 'mega-toggle-on';
                    }
                }
            }


            if (isset($settings[$location]['accordion_behaviour']) && $settings[$location]['accordion_behaviour'] == 'open_all') {
                if (in_array('mega-menu-item-has-children', $classes)) {
                    $classes[] = 'mega-toggle-on';
                }
            }
        }

        return $classes;
    }

    public function registerVerticalMenuItem($items)
    {
        $items['vertical_menu_item'] = __('Vertical Menu', 'jankx_megu');

        return $items;
    }

    public function renderVerticalMenuItem($item_title, $item, $args, $depth)
    {
        if ($item->type !== 'vertical_menu_item') {
            return $item_title;
        }

        $menu_location = apply_filters('jankx_megu_vertical_menu_item', null, $item, $args);
        if (is_null($menu_location)) {
            return $item_title;
        }

        $args->link_after = sprintf('%s%s', jankx_component('nav', array(
            'theme_location' => $menu_location,
        ), false), $args->link_after);

        return $item_title;
    }
}
