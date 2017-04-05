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
	 * Methods for the admin init action.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Actions_AdminInit {

		/**
		 * Required scripts for this plugin in the BE.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var array
		 */
		protected static $reqScripts = array(
			'jquery-ui-sortable',
			'jquery-ui-draggable',
		);

		/**
		 * JavaScript files for this plugin in the BE, each can have an array of dependecies.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var array
		 */
		protected static $jsFiles = array(
			array('widgets/PageBrowser', array('jquery', 'jquery-ui-widget')),
			array('widgets/ClientItems', array('jquery', 'jquery-ui-widget')),
			array('widgets/PaletteKeys', array('jquery', 'jquery-ui-widget')),
			array('widgets/Palettes', array('jquery', 'jquery-ui-widget')),
			array('widgets/TextList', array('jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete')),
			array('widgets/Links', array('jquery', 'jquery-ui-widget')),
			array('widgets/Switches', array('jquery', 'jquery-ui-widget')),
			array('widgets/PostMedia', array('jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete')),
			array('widgets/RecordSelector', array('jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete')),
			array('SbpAdmin', array('jquery')),
			array('Init', array('jquery'))
		);

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
		 * Do things that the plugin requries on BE load.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function initialisePluginBe() {
			self::loadRequiredJavaScript();
			self::loadJavaScript();
			self::loadCss();
		}

		/**
		 * Loads JavaScript in the BE.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		protected static function loadJavaScript() {
			if (!empty(self::$jsFiles)) {
				foreach (self::$jsFiles as $jsFile) {
					$dependencies	= array();
					$handle			= (isset($jsFile[2])) ? $jsFile[1] : $jsFile[0];
					$source			= SBP_FE_FOLDER_JS . $jsFile[0] . '.js';

					if (isset($jsFile[1]) && is_array($jsFile[1])) $dependencies = $jsFile[1];

					wp_enqueue_script($handle, $source, $dependencies);
				}
			}
		}

		/**
		 * Makes sure required JavaScript files in the BE are included before including plugin JS.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		protected static function loadRequiredJavaScript() {
			if (!empty(self::$reqScripts)) {
				foreach (self::$reqScripts as $scriptHandle) {
					if (is_array($scriptHandle)) {
						wp_enqueue_script($scriptHandle[2], SBP_FE_FOLDER_JS . $scriptHandle[0] . '.js', $scriptHandle[1]);

					} else {
						wp_enqueue_script($scriptHandle);
					}
				}
			}
		}

		/**
		 * Loads CSS in the BE.
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
		 * Creates the metaboxes.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */

		public static function createMetaboxes() {
				/* Item Metaboxes */
				/* -------------- */

			add_meta_box(
				'sbp_mb_item_media',
				__('Portfolio Media', 'sbp'),
				array('Sbp_Pages_Item', 'renderMetaboxMedia'),
				'sbp_item',
				'advanced'
			);

			add_meta_box(
				'sbp_mb_item_links',
				__('Portfolio Links', 'sbp'),
				array('Sbp_Pages_Item', 'renderMetaboxLinks'),
				'sbp_item',
				'advanced'
			);

			add_meta_box(
				'sbp_mb_item_options',
				__('Portfolio Options', 'sbp'),
				array('Sbp_Pages_Item', 'renderMetaboxPortfolioOptions'),
				'sbp_item',
				'side'
			);

			add_meta_box(
				'sbp_mb_item_apis',
				__('Portfolio API Keys', 'sbp'),
				array('Sbp_Pages_Item', 'renderMetaboxPortfolioApis'),
				'sbp_item',
				'side'
			);

			add_meta_box(
				'sbp_mb_palette',
				__('Portfolio Colour Palette', 'sbp'),
				array('Sbp_Pages_Item', 'renderMetaboxPortfolioPalette'),
				'sbp_item',
				'side'
			);



				/* Client Metaboxes */
				/* ---------------- */

			add_meta_box(
				'sbp_mb_client_links',
				__('Portfolio Links', 'sbp'),
				array('Sbp_Pages_Client', 'renderMetaboxLinks'),
				'sbp_client',
				'advanced'
			);

			add_meta_box(
				'sbp_mb_client_items',
				__('Portfolio Items', 'sbp'),
				array('Sbp_Pages_Client', 'renderMetaboxItems'),
				'sbp_client',
				'advanced'
			);

			add_meta_box(
				'sbp_mb_client_options',
				__('Portfolio Options', 'sbp'),
				array('Sbp_Pages_Client', 'renderMetaboxPortfolioOptions'),
				'sbp_client',
				'side'
			);



				/* Testimonial Metaboxes */
				/* --------------------- */

			add_meta_box(
				'sbp_mb_testimonial_info',
				__('Portfolio Testimonial', 'sbp'),
				array('Sbp_Pages_Testimonial', 'renderMetaboxTestimonialAuthor'),
				'sbp_testimonial',
				'advanced'
			);

			add_meta_box(
				'sbp_mb_testimonial_options',
				__('Portfolio Options', 'sbp'),
				array('Sbp_Pages_Testimonial', 'renderMetaboxPortfolioOptions'),
				'sbp_testimonial',
				'side'
			);

			add_meta_box(
				'sbp_mb_palette',
				__('Portfolio Colour Palette', 'sbp'),
				array('Sbp_Pages_Client', 'renderMetaboxPortfolioPalette'),
				'sbp_client',
				'side'
			);

		}
	}
?>