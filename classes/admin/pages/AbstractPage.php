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
	 * A base class that all pages should extend.
	 * See: https://codex.wordpress.org/Creating_Options_Pages
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	abstract class Sbp_Pages_AbstractPage {

		/**
		 * This page's slug.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var string
		 */
		protected static $slug = 'overwrite-in-your-extending-class';



		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
		}



		/**
		 * Renders the page.
		 *
		 * @return void
		 */
		public static function render() {
			self::pageStart('Overwrite this method in your page class');
			self::pageEnd();
		}

		/**
		 * Renders the start of the page.
		 *
		 * @param string $pageTitle The page title.
		 * @return void
		 */
		public static function pageStart($pageTitle = '') {
			if (empty($pageTitle)) $pageTitle = __('Untitled page', 'sbp');

			$output	 =	'<div class="wrap">' . PHP_EOL;
			$output	.=		'<h1>' . $pageTitle . '</h1>' . PHP_EOL;
			echo $output;

			settings_errors();
		}

		/**
		 * Renders the end of the page.
		 *
		 * @return void
		 */
		public static function pageEnd() {
			echo '</div>' . PHP_EOL;
		}

		/**
		 * Renders the start of the form.
		 *
		 * @param string $formUrl The form url.
		 * @return void
		 */
		public static function formStart($formUrl = '') {
			if (empty($formUrl)) $formUrl = 'options.php';

			echo '<form action="' . $formUrl . '" method="post">' . PHP_EOL;
		}

		/**
		 * Renders the end of the form.
		 *
		 * @param string $formUrl The form url.
		 * @return void
		 */
		public static function formEnd($showSubmit = TRUE) {
			if ($showSubmit === TRUE) submit_button();

			echo '</form>' . PHP_EOL;
		}

		/**
		 * Renders the end of the page.
		 *
		 * @return void
		 */
		public static function formTableStart() {
			$output	 =	'<table class="form-table">' . PHP_EOL;
			$output	.=		'<tbody>' . PHP_EOL;

			echo $output;
		}

		/**
		 * Renders the end of the page.
		 *
		 * @return void
		 */
		public static function formTableEnd() {
			$output	 =		'</tbody>' . PHP_EOL;
			$output	.=	'</table>' . PHP_EOL;

			echo $output;
		}

		/**
		 * Renders the end of the page.
		 *
		 * @return void
		 */
		public static function formTableRowStart() {
			echo '<tr>' . PHP_EOL;
		}

		/**
		 * Renders the end of the page.
		 *
		 * @return void
		 */
		public static function formTableRowEnd() {
			echo '</tr>' . PHP_EOL;
		}

		/**
		 * Adds content to the admin page header if on an SBP record page or settings.
		 *
		 * @return void
		 */
		public static function addHeadContent() {
			global $post_type;

			$canOutput = FALSE;

			if ($post_type === NULL && get_current_screen()->id == 'portfolio_page_sb_portfolio_settings') { // Settings.
				$canOutput = TRUE;

			} else if (strpos($post_type, 'sbp_') !== FALSE) { // SB Portfolio post type edit/create screens.
				$canOutput = TRUE;
			}

			if ($canOutput === TRUE) {
				$flickrApiKey	= get_option('sbp_flickr_api');
				$youtubeApiKey	= get_option('sbp_youtube_api');

					// Output some settings
				$output	 = 	'<script id="sbpSettings" type="text/javascript">' . PHP_EOL;
				$output	.= 		'window.sbpSettings = [];' . PHP_EOL;
				$output	.= 		'window.sbpSettings["iconPath"]					= "' . SBP_FE_FOLDER_ICONS . '";' . PHP_EOL;
				$output	.= 		'window.sbpSettings["flickrApiKeyDefault"]		= "' . $flickrApiKey . '";' . PHP_EOL;
				$output	.= 		'window.sbpSettings["flickrApiKey"]				= "' . $flickrApiKey . '";' . PHP_EOL;
				$output	.= 		'window.sbpSettings["youtubeApiKeyDefault"]		= "' . $youtubeApiKey . '";' . PHP_EOL;
				$output	.= 		'window.sbpSettings["youtubeApiKey"]			= "' . $youtubeApiKey . '";' . PHP_EOL;
				$output	.= 		'window.sbpSettings["loadingSpinner"]			= "' . get_admin_url() . 'images/loading.gif' . '";' . PHP_EOL;
				$output	.= 		'window.sbpSettings["hooks"]					= {};' . PHP_EOL;
				$output	.= 		'window.sbpSettings["guiConfig"]				= {};' . PHP_EOL;
				$output	.= 		'window.sbpSettings["guiConfig"]["default"]		= {};' . PHP_EOL;
				$output	.= 		'window.sbpSettings["guiConfig"]["specific"]	= {};' . PHP_EOL;
				$output	.= 	'</script>' . PHP_EOL;

				echo $output;
			}
		}
	}
?>