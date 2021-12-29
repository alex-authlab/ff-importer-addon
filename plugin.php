<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 *
 * @wordpress-plugin
 * Plugin Name:       FF Importer
 * Plugin URI:        #
 * Description:       One click Import Caldera, Ninja Forms & Gravity Forms into Fluentforms. Go to Fluentforms > Tools and click import
 * Version:           1.0
 * Author:            #
 * Author URI:       #
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ff-importer-addon
 */



define('FF_MIG_VER', 1.0);
define('FF_MIG_DIR_PATH', plugin_dir_path(__FILE__));
define('FF_MIG_DIR_URL', plugin_dir_url( __FILE__ ));

require_once FF_MIG_DIR_PATH.'inc/Bootstrap.php';
add_action('plugins_loaded',function(){
    (new Bootstrap())->init();
});
