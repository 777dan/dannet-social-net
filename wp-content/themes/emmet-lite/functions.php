<?php
/**
 * emmet functions and definitions
 *
 * @package WordPress
 * @subpackage emmet
 * @since emmet 1.0
 */
require get_template_directory() . '/inc/theme/init.php';

/**
 * Note: Do not add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * http://codex.wordpress.org/Child_Themes
 */

function my_forcelogin_bypass($bypass)
{
    if (is_page('register')) {
        $bypass = true;
    }

    // Get visited URL without last part
    $url_path = dirname($_SERVER['REQUEST_URI']);

    // Allow URL
    if ('/aktivacziya-uchetnoj-zapisi' === $url_path) {
        $bypass = true;
    }
    return $bypass;
}
add_filter('v_forcelogin_bypass', 'my_forcelogin_bypass');