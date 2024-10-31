<?php

/**
 * @link              https://sevengits.com/plugin/order-approval-for-wcfm-pro/
 * @since             1.0.0
 * @package           owfm
 *
 * @wordpress-plugin
 * Plugin Name:       Order approval for WCFM
 * Plugin URI:        https://sevengits.com/plugin/order-approval-for-wcfm/
 * Description:       Order Approval for WCFM plugin allows wcfm market place vendors to approve or reject the orders placed by customers before payment is processed.
 * Version:           1.0.3
 * Author:            Sevengits
 * Author URI:        https://sevengits.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       order-approval-for-wcfm
 * Domain Path:       /languages
 * WC requires at least: 3.7
 * WC tested up to:      8.1
 * WCFM tested up to:   6.7
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!function_exists('get_plugin_data')) {
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if (!defined('owfm_version')) {
	define('owfm_version', get_plugin_data(__FILE__)['Version']);
}
if (!defined('owfm_plugin_path')) {
	define('owfm_plugin_path', plugin_dir_path(__FILE__));
}

if (!defined('owfm_plugin_basename')) {
	define('owfm_plugin_basename', plugin_basename(__FILE__));
}
if (!class_exists('\OWFM\Reviews\Notice')) {
	require_once plugin_dir_path(__FILE__) . 'includes/packages/plugin-review/notice.php';
}
if (!function_exists('owfm_is_depencies_deactivated')) {
	function owfm_is_depencies_deactivated()
	{
		/**
		 * disable if depencies not activate
		 * 
		 */
		$depended_plugins = array(
			array(
				'plugins' => array(
					'Sg Order Approval for Woocommerce' => 'order-approval-woocommerce/order-approval-woocommerce.php',
					'Sg Order Approval for Woocommerce Pro' => 'order-approval-woocommerce-pro/order-approval-woocommerce-pro.php'
				),
				'links' => array(
					'free' => 'https://wordpress.org/plugins/order-approval-woocommerce/',
					'pro' => 'https://sevengits.com/plugin/order-approval-woocommerce-pro'
				)
			),
			array(
				'plugins' => array(
					'WCFM - WooCommerce Multivendor Marketplace' => 'wc-multivendor-marketplace/wc-multivendor-marketplace.php'
				), 'links' => array(
					'free' => 'https://wordpress.org/plugins/wc-multivendor-marketplace/'
				)
			),
			array('plugins' => array(
				'WooCommerce' => 'woocommerce/woocommerce.php'
			), 'links' => array(
				'free' => 'https://wordpress.org/plugins/woocommerce/'
			))

		);
		$message = __('The following plugins are required for <b>' . get_plugin_data(__FILE__)['Name'] . '</b> plugin to work. Please ensure that they are activated: ', 'order-approval-for-wcfm');
		$is_disabled = false;
		foreach ($depended_plugins as $key => $dependency) {
			$dep_plugin_name = array_keys($dependency['plugins']);
			$dep_plugin = array_values($dependency['plugins']);
			if (count($dep_plugin) > 1) {
				if (!in_array($dep_plugin[0], apply_filters('active_plugins', get_option('active_plugins'))) && !in_array($dep_plugin[1], apply_filters('active_plugins', get_option('active_plugins')))) {
					$class = 'notice notice-error is-dismissible';
					$is_disabled = true;
					if (isset($dependency['links'])) {
						$message .= '<br/> <a href="' . $dependency['links']['free'] . '" target="_blank" ><b>' . $dep_plugin_name[0] . '</b></a> Or <a href="' . $dependency['links']['pro'] . '" target="_blank" ><b>' . $dep_plugin_name[1] . '</b></a>';
					} else {
						$message .= "<br/> <b> $dep_plugin_name[0] </b> Or <b> $dep_plugin_name[1] . </b>";
					}
				}
			} else {
				if (!in_array($dep_plugin[0], apply_filters('active_plugins', get_option('active_plugins')))) {
					$class = 'notice notice-error is-dismissible';
					$is_disabled = true;
					if (isset($dependency['links'])) {
						$message .= '<br/> <a href="' . $dependency['links']['free'] . '" target="_blank" ><b>' . $dep_plugin_name[0] . '</b></a>';
					} else {
						$message .= "<br/><b>$dep_plugin_name[0]</b>";
					}
				}
			}
		}
		if ($is_disabled) {
			if (!defined('OWFM_DISABLED')) {
				define('OWFM_DISABLED', true);
			}
			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
		}
		/**
		 * review notice for collect user experience
		 */
		if (class_exists('\OWFM\Reviews\Notice')) {
			// delete_site_option('owfm_reviews_time'); // FOR testing purpose only. this helps to show message always
			$message = sprintf(__("Hello! Seems like you have been using %s for a while – that’s awesome! Could you please do us a BIG favor and give it a 5-star rating on WordPress? This would boost our motivation and help us spread the word.", 'order-approval-for-wcfm'), "<b>" . get_plugin_data(__FILE__)['Name'] . "</b>");
			$actions = array(
				'review'  => __('Ok, you deserve it', 'order-approval-for-wcfm'),
				'later'   => __('Nope, maybe later I', 'order-approval-for-wcfm'),
				'dismiss' => __('already did', 'order-approval-for-wcfm'),
			);
			$notice = \OWFM\Reviews\Notice::get(
				'order-approval-for-wcfm',
				get_plugin_data(__FILE__)['Name'],
				array(
					'days'          => 7,
					'message'       => $message,
					'action_labels' => $actions,
					'prefix' => "owfm"
				)
			);

			// Render notice.
			$notice->render();
		}
	}
}
add_action('admin_notices', 'owfm_is_depencies_deactivated');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-order-approval-for-wcfm-activator.php
 */
if (!function_exists('owfm_activate')) {
	function owfm_activate()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-order-approval-for-wcfm-activator.php';
		owfm_activator::activate();
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-order-approval-for-wcfm-deactivator.php
 */
if (!function_exists('owfm_deactivate')) {
	function owfm_deactivate()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-order-approval-for-wcfm-deactivator.php';
		owfm_Deactivator::deactivate();
	}
}

register_activation_hook(__FILE__, 'owfm_activate');
register_deactivation_hook(__FILE__, 'owfm_deactivate');

/**
 * feedback survey form
 */
require plugin_dir_path(__FILE__) . 'plugin-deactivation-survey/deactivate-feedback-form.php';

add_filter('sgits_deactivate_feedback_form_plugins', 'owfm_deactivate_feedback_form_plugins');
function owfm_deactivate_feedback_form_plugins($plugins)
{
	$plugins[] = (object)array(
		'slug'		=> 'order-approval-for-wcfm',
		'version'	=> 'owfm_version'
	);
	return $plugins;
};
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-order-approval-for-wcfm.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if (!function_exists('owfm_run')) {
	function owfm_run()
	{

		$plugin = new owfm();
		$plugin->run();
	}
}


owfm_run();
