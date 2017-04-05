<?php
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



	/**
	 * Methods for the init action.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Actions_Init {

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
		}



		/**
		 * Register custom post types.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */

		public static function addPostTypes() {
			register_post_type('sbp_item', array(
				'labels' => array(
					'name'					=> __('Items', 'sbp'),
					'singular_name'			=> __('Item', 'sbp'),
				    'menu_name'				=> __('Items', 'sbp'),
				    'name_admin_bar'		=> __('Item', 'sbp'),
				    'add_new'				=> __('Add New', 'sbp'),
				    'add_new_item'			=> __('Add New Item', 'sbp'),
				    'new_item'				=> __('New Item', 'sbp'),
				    'edit_item'				=> __('Edit Item', 'sbp'),
				    'view_item'				=> __('View Item', 'sbp'),
				    'all_items'				=> __('Items', 'sbp'),
				    'search_items'			=> __('Search Items', 'sbp'),
				    'parent_item_colon'		=> __('Parent Item', 'sbp'),
				    'not_found'				=> __('No Items Found', 'sbp'),
				    'not_found_in_trash'	=> __('No Items Found in Trash', 'sbp')
				),
				'taxonomies'			=> array('category', 'post_tag'),
			    'public'				=> TRUE,
			    'exclude_from_search'	=> FALSE,
			    'publicly_queryable'	=> TRUE,
			    'show_ui'				=> TRUE,
			    'show_in_nav_menus'		=> TRUE,
   				'show_in_menu'			=> 'sb_portfolio_main',
			    'show_in_admin_bar'		=> TRUE,
			    'menu_position'			=> 5,
			    'menu_icon'				=> 'dashicons-admin-appearance',
			    'capability_type'		=> 'post',
			    'hierarchical'			=> FALSE,
			    'supports'				=> array('title', 'editor', 'excerpt', 'thumbnail', 'comments', 'post-formats'),
			    'has_archive'			=> TRUE,
   				'rewrite'				=> array('slug' => 'items'),
			    'query_var'				=> TRUE
			));

			register_post_type('sbp_client', array(
				'labels' => array(
					'name'					=> __('Clients', 'sbp'),
					'singular_name'			=> __('Client', 'sbp'),
				    'menu_name'				=> __('Clients', 'sbp'),
				    'name_admin_bar'		=> __('Client', 'sbp'),
				    'add_new'				=> __('Add New', 'sbp'),
				    'add_new_item'			=> __('Add New Client', 'sbp'),
				    'new_item'				=> __('New Client', 'sbp'),
				    'edit_item'				=> __('Edit Client', 'sbp'),
				    'view_item'				=> __('View Client', 'sbp'),
				    'all_items'				=> __('Clients', 'sbp'),
				    'search_items'			=> __('Search Clients', 'sbp'),
				    'parent_item_colon'		=> __('Parent Client', 'sbp'),
				    'not_found'				=> __('No Clients Found', 'sbp'),
				    'not_found_in_trash'	=> __('No Clients Found in Trash', 'sbp')
				),
				'taxonomies'			=> array('category', 'post_tag'),
			    'public'				=> TRUE,
			    'exclude_from_search'	=> FALSE,
			    'publicly_queryable'	=> TRUE,
			    'show_ui'				=> TRUE,
			    'show_in_nav_menus'		=> TRUE,
   				'show_in_menu'			=> 'sb_portfolio_main',
			    'show_in_admin_bar'		=> TRUE,
			    'menu_position'			=> 5,
			    'menu_icon'				=> 'dashicons-admin-appearance',
			    'capability_type'		=> 'post',
			    'hierarchical'			=> FALSE,
			    'supports'				=> array('title', 'editor', 'excerpt', 'thumbnail', 'comments'),
			    'has_archive'			=> TRUE,
   				'rewrite'				=> array('slug' => 'clients'),
			    'query_var'				=> TRUE
			));

			register_post_type('sbp_testimonial', array(
				'labels' => array(
					'name'					=> __('Testmonials', 'sbp'),
					'singular_name'			=> __('Testmonial', 'sbp'),
				    'menu_name'				=> __('Testmonials', 'sbp'),
				    'name_admin_bar'		=> __('Testmonial', 'sbp'),
				    'add_new'				=> __('Add New', 'sbp'),
				    'add_new_item'			=> __('Add New Testmonial', 'sbp'),
				    'new_item'				=> __('New Testmonial', 'sbp'),
				    'edit_item'				=> __('Edit Testmonial', 'sbp'),
				    'view_item'				=> __('View Testmonial', 'sbp'),
				    'all_items'				=> __('Testmonials', 'sbp'),
				    'search_items'			=> __('Search Testmonials', 'sbp'),
				    'parent_item_colon'		=> __('Parent Testmonial', 'sbp'),
				    'not_found'				=> __('No Testmonials Found', 'sbp'),
				    'not_found_in_trash'	=> __('No Testmonials Found in Trash', 'sbp')
				),
				'taxonomies'			=> array('category', 'post_tag'),
			    'public'				=> TRUE,
			    'exclude_from_search'	=> FALSE,
			    'publicly_queryable'	=> TRUE,
			    'show_ui'				=> TRUE,
			    'show_in_nav_menus'		=> TRUE,
   				'show_in_menu'			=> 'sb_portfolio_main',
			    'show_in_admin_bar'		=> TRUE,
			    'menu_position'			=> 5,
			    'menu_icon'				=> 'dashicons-admin-appearance',
			    'capability_type'		=> 'post',
			    'hierarchical'			=> FALSE,
			    'supports'				=> array('title', 'excerpt', 'thumbnail', 'comments'),
			    'has_archive'			=> TRUE,
   				'rewrite'				=> array('slug' => 'testmonials'),
			    'query_var'				=> TRUE
			));
		}
	}
?>