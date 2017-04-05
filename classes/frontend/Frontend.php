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
	 * Contains hooks for the frontend.
	 * 
	 * The add_action() calls are here because add_submenu_page()/add_menu_page() return the page name
	 * which is necessary for the add_action function.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Frontend {

		/**
		 * CSS files for this plugin in the BE.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var array
		 */
		protected static $cssFiles = array(
			'Core'
		);
		
		
		
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
		public static function initialiseFe() {
			self::loadCss();
		}

		/**
		 * Loads CSS in the FE.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		protected static function loadCss() {
			if (!empty(self::$cssFiles)) {
	            $cssFileType = '.css';
	            
				foreach (self::$cssFiles as $cssFile) {
					$cssFilePath = SBP_FE_FOLDER_CSS . $cssFile;
					wp_register_style($cssFile, $cssFilePath . $cssFileType);
					wp_enqueue_style($cssFile, $cssFilePath);
				}
			}
		}

		/**
		 * Adds body classes to the body tag.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @param array $classes An array of post classes.
		 * @return void
		 */
		public static function addBodyClasses(array $classes) {
			if(is_single()) {
				$post = get_post();
				
				if (Sbp_PluginBase::isSbpPost($post)) { // If is sbp post type...
					if (sbp_isFeatured($post->ID, $post->post_type))										$classes[] = 'featured';
					if ($post->post_type == 'sbp_item' && sbp_isInprogress($post->ID, $post->post_type))	$classes[] = 'inprogress';
				}
			}
			
			return $classes;
		}
		
		/**
		 * Adds content to the FE page header.
		 *
		 * @return void
		 */
		public static function addHeadContent() {
				// Output some settings
			$output	 = 	'<script id="sbpSettings" type="text/javascript">' . PHP_EOL;
			$output	.= 		'window.sbpSettings					= [];' . PHP_EOL;
			$output	.= 		'window.sbpSettings.posts			= [];' . PHP_EOL;
			$output	.= 		'window.sbpSettings.colours			= {};' . PHP_EOL;
			$output	.= 		'window.sbpSettings.flickrApiKey	= "' . get_option('sbp_flickr_api') . '";' . PHP_EOL;
			$output	.= 		'window.sbpSettings.youtubeApiKey	= "' . get_option('sbp_youtube_api') . '";' . PHP_EOL;
			$output	.= 	'</script>' . PHP_EOL;
			
			echo $output;
		}
	}
?>