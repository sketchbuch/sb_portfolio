<?php
	/*
		Plugin Name:	SB Portfolio
		Plugin URI:
		Description:	A complete portfolio system including items, clients, categories, and testimonials.
		Version:		0.1.7
		Author:			Stephen Bungert
		Author URI:		http://www.stephenbungert.com/
		License:		GPLv2
	*/



	/*  
		Copyright 2017  Stephen Bungert  (email : hello@stephenbungert.com)

	    This program is free software; you can redistribute it and/or modify
	    it under the terms of the GNU General Public License, version 2, as
	    published by the Free Software Foundation.

	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.

	    You should have received a copy of the GNU General Public License
	    along with this program; if not, write to the Free Software
	    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/



	/*
	 * SB Portfolio - A complete portfolio system including items, clients, categories, and testimonials.
	 * Based on my TYPO3 extension sb_portfolio.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 */
	if(!defined('ABSPATH')) {
		exit();

	} else {
		require_once('Autoloader.php');



			// Constants: FE and BE.
		define('SBP_NAME', 'sb_portfolio');
		define('SBP_VARS', 'sbp');

		if (substr(ABSPATH, -1) == '/') {
			define('SBP_ABSPATH', substr(ABSPATH, 0, -1));

		} else {
			define('SBP_ABSPATH', ABSPATH);
		}

		define('SBP_WP_PLUGIN_FOLDER', SBP_ABSPATH . DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR);
		define('SBP_FOLDER', SBP_WP_PLUGIN_FOLDER . SBP_NAME . DIRECTORY_SEPARATOR);
		define('SBP_FE_FOLDER', plugin_dir_url( __FILE__ ));



			/* Hooks: FE & BE */
			/* -------------- */

		add_action('init', array('Sbp_Actions_Init', 'addPostTypes')); // Add custom post types.



		if (is_admin()) { // BE.
				// Constants:
			define('SBP_FE_FOLDER_JS', SBP_FE_FOLDER . 'resources/admin/javascript/');
			define('SBP_FE_FOLDER_CSS', SBP_FE_FOLDER . 'resources/admin/css/');
			define('SBP_FE_FOLDER_ICONS', SBP_FE_FOLDER . 'resources/admin/icons/');



				/* Hooks */
				/* ----- */

				// General:
			add_action('admin_init', array('Sbp_Actions_AdminInit', 'initialisePluginBe'));	// Initiaisation for the BE.
			add_action('admin_init', array('Sbp_Actions_AdminInit', 'createMetaboxes'));	// Create metaboxes.
			add_action('admin_menu', array('Sbp_Actions_AdminMenu', 'addAdminMenus'));		// Add menus hook.

				// AJAX:
			add_action('wp_ajax_addrecord', array('Sbp_Actions_Ajax', 'addRecord'));							// Add a record AJAX ŕequest.
			add_action('wp_ajax_searchrecord', array('Sbp_Actions_Ajax', 'searchRecord'));						// Search for a record AJAX ŕequest.
			add_action('wp_ajax_sbpgetclientitems', array('Sbp_Actions_Ajax', 'sbpGetClientItems'));			// Gets items for a client record
			add_action('wp_ajax_sbpupdateclientitem', array('Sbp_Actions_Ajax', 'sbpUpdateClientItem'));		// Update items in client items list

				// Header content.
			add_action('admin_head', array('Sbp_Pages_AbstractPage', 'addHeadContent')); // Adds javascript vars to the WP head tag.



				/* Items */
				/* ----- */
			add_filter('manage_sbp_item_posts_columns', array('Sbp_Pages_Item', 'addColumnHeaders'));				// Table Headers.
			add_action('manage_sbp_item_posts_custom_column', array('Sbp_Pages_Item', 'addColumnContent'), 10, 2);	// Table Columns.
			add_action('save_post_sbp_item', array('Sbp_Pages_Item', 'saveItem'), 10, 2);							// Save posts.



				/* Clients */
				/* ------- */

			add_filter('manage_sbp_client_posts_columns', array('Sbp_Pages_Client', 'addColumnHeaders'));				// Table Headers.
			add_action('manage_sbp_client_posts_custom_column', array('Sbp_Pages_Client', 'addColumnContent'), 10, 2);	// Table Columns.
			add_action('save_post_sbp_client', array('Sbp_Pages_Client', 'saveClient'), 10, 2);							// Save posts.



				/* Testimonials */
				/* ------------ */

			add_filter('manage_sbp_testimonial_posts_columns', array('Sbp_Pages_Testimonial', 'addColumnHeaders'));					// Table Headers.
			add_action('manage_sbp_testimonial_posts_custom_column', array('Sbp_Pages_Testimonial', 'addColumnContent'), 10, 2);	// Table Columns.
			add_action('save_post_sbp_testimonial', array('Sbp_Pages_Testimonial', 'saveTestimonial'), 10, 2);						// Save posts.



				/* Settings */
				/* -------- */
			add_action('admin_init', array('Sbp_Pages_Settings', 'registerSettings'));	// Register settings.



				// Go!
			$sbpAutoLoader = new Sbp_Autoloader('admin');

		} else { // FE.
				// Constants:
			define('SBP_FE_FOLDER_JS', SBP_FE_FOLDER . 'resources/frontend/javascript/');
			define('SBP_FE_FOLDER_CSS', SBP_FE_FOLDER . 'resources/frontend/css/');
			define('SBP_FE_FOLDER_ICONS', SBP_FE_FOLDER . 'resources/frontend/icons/');

				/* Hooks */
				/* ----- */

				// General:
			add_action('wp_enqueue_scripts', array('Sbp_Frontend', 'initialiseFe'));	// Initiaisation for the FE.
			add_filter('body_class', array('Sbp_Frontend', 'addBodyClasses'));			// Add classes for stylig etc.

				// Header content.
			add_action('wp_head', array('Sbp_Frontend', 'addHeadContent'));



				/* Shortcodes */
				/*----------- */
			add_shortcode('sbp_item', 'sbp_shortcodeItem');
			add_shortcode('sbp_client', 'sbp_shortcodeClient');
			add_shortcode('sbp_testimonial', 'sbp_shortcodeTestimonial');



				// Go!
			$sbpAutoLoader = new Sbp_Autoloader('frontend');
		}
	}
?>