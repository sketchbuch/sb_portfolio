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
	 * Called by the action "admin_menu". Creates the BE Menus for sb_portfolio.
	 * 
	 * The add_action() calls are here because add_submenu_page()/add_menu_page() return the page name
	 * which is necessary for the add_action function.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Actions_AdminMenu {
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
		}
		
		
		
		/**
		 * Adds the admin menus for sb_portfolio.
		 * which is needed for the add_action() arguments.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public function addAdminMenus() {
			self::createMainMenu();
		}
		
		
		
		/**
		 * Adds a main page menu.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		protected function createMainMenu() {
			add_menu_page(
				__('Portfolio', 'sbp'),		// Page title
				__('Portfolio', 'sbp'),		// Menu title
				'edit_posts',				// Capability
				'sb_portfolio_main',		// Page name (wp menu slug)
				'',
				'dashicons-portfolio'
			);
			
			add_submenu_page(
				'sb_portfolio_main',				// Parent slug
				__('Portfolio Settings', 'sbp'),	// Page title
				__('Settings', 'sbp'),				// Menu title
				'edit_posts',						// Capability
				'sb_portfolio_settings',			// Page name (wp menu slug)
				array(Sbp_Pages_Settings, 'render')
			);
		}
	}
?>