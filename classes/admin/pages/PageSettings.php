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
	 * Creates content for a the main page.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Pages_Settings extends Sbp_Pages_AbstractSettingsPage {
		/**
		 * This page's slug.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var string
		 */
		protected static $slug = 'sb_portfolio_settings';

		/**
		 * This page's options.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var string
		 */
		protected static $options = array();




		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}



		/**
		 * Renders the page.
		 *
		 * @return void
		 */
		public static function render() {
			$showSubmit	= TRUE;
			$tab		= Sbp_PluginBase::varGet('tab'); // The current active tab.
			$tabs		= array(
				'colours' => array(
					'active'	=> FALSE,
					'href'		=> '?page=' . self::$slug . '&tab=',
					'label'		=> __('Colour Palettes', 'sbp')
				),
				'links' => array(
					'active'	=> FALSE,
					'href'		=> '?page=' . self::$slug . '&tab=',
					'label'		=> __('Links', 'sbp')
				),
				'apis' => array(
					'active'	=> FALSE,
					'href'		=> '?page=' . self::$slug . '&tab=',
					'label'		=> __('3rd Party APIs', 'sbp')
				),
				'watermarks' => array(
					'active'	=> FALSE,
					'href'		=> '?page=' . self::$slug . '&tab=',
					'label'		=> __('Watermarks', 'sbp')
				)
			);

				// Mark one selected, either the active tab or the first.
			if (isset($tabs[$tab])) {
				$tabs[$tab]['active'] = TRUE;

			} else {
				$firstTab	= current(array_keys($tabs));
				$tab		= $firstTab;
				$tabs[$firstTab]['active'] = TRUE;
			}

			self::pageStart(__('Portfolio Settings', 'sbp'));
			self::tabButtons($tabs);
			self::formStart();

			settings_fields('sbp_settings_' . $tab);
			do_settings_sections('sbp_settings_' . $tab);

			self::formEnd($showSubmit);
			self::pageEnd();
		}

		/**
		 * Registers settings, options and sections for this plugin.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function registerSettings() {
				// Colour Section:
			add_option('sbp_colours', '');
			add_option('sbp_colour_keys', '');
			register_setting('sbp_settings_colours', 'sbp_colours');
			register_setting('sbp_settings_colours', 'sbp_colour_keys');

			add_settings_section(
			    'sbp_settings_colours',			// ID
			    __('Colour Palettes', 'sbp'),	// Title
			    NULL,							// Callback
			    'sbp_settings_colours'			// Page/Group
			);

			add_settings_field(
				'sbp_colour_keys',											// ID
				__('Swatch Keys', 'sbp'),									// Title
			    array(Sbp_Pages_Settings, 'renderField_sbp_colourKeys'),	// Callback
			    'sbp_settings_colours',										// Page/Group
			    'sbp_settings_colours'										// Section
			);

			add_settings_field(
				'sbp_colours',											// ID
				__('Site Swatches', 'sbp'),								// Title
			    array(Sbp_Pages_Settings, 'renderField_sbp_colours'),	// Callback
			    'sbp_settings_colours',									// Page/Group
			    'sbp_settings_colours'									// Section
			);



				// Links:
			add_option('sbp_linktype_keys', '');
			register_setting('sbp_settings_links', 'sbp_linktype_keys');

			add_settings_section(
			    'sbp_settings_links',	// ID
			    __('Links', 'sbp'),		// Title
			    NULL,					// Callback
			    'sbp_settings_links'	// Page/Group
			);

			add_settings_field(
				'sbp_linktype_keys',										// ID
				__('Link Type Keys', 'sbp'),								// Title
			    array(Sbp_Pages_Settings, 'renderField_sbp_linktypeKeys'),	// Callback
			    'sbp_settings_links',										// Page/Group
			    'sbp_settings_links'										// Section
			);



				// API Section:
			add_option('sbp_flickr_api', '');
			add_option('sbp_youtube_api', '');
			register_setting('sbp_settings_apis', 'sbp_flickr_api', array(Sbp_Pages_Settings, 'sbp_sanitizeStr'));
			register_setting('sbp_settings_apis', 'sbp_youtube_api', array(Sbp_Pages_Settings, 'sbp_sanitizeStr'));

			add_settings_section(
			    'sbp_settings_apis_sec',		// ID
			    __('3rd Party APIs', 'sbp'),	// Title
			    NULL,							// Callback
			    'sbp_settings_apis'				// Page/Group
			);

			add_settings_field(
			    'sbp_flickr_api',									// ID
			    __('Flickr API Key', 'sbp'),						// Title
			    array(Sbp_Pages_Settings, 'renderField_flickrApi'),	// Callback
			    'sbp_settings_apis',								// Page/Group
			    'sbp_settings_apis_sec'								// Section
			);

			add_settings_field(
				'sbp_youtube_api',										// ID
				__('YouTube API Key', 'sbp'),							// Title
			    array(Sbp_Pages_Settings, 'renderField_youtubeApi'),	// Callback
			    'sbp_settings_apis',									// Page/Group
			    'sbp_settings_apis_sec'									// Section
			);



				// Watermarks Section:
			add_option('sbp_watermarks_enabled', '');
			register_setting('sbp_settings_watermarks', 'sbp_watermarks_enabled', array(Sbp_Pages_Settings, 'sbp_sanitizeCheckbox'));
			register_setting('sbp_settings_watermarks', 'sbp_watermarks_media');

			add_settings_section(
			    'sbp_settings_watermark',													// ID
			    __('Watermarks', 'sbp'),													// Title
			    array(Sbp_Pages_Settings, 'renderSection_watermarks'),						// Callback
			    'sbp_settings_watermarks'													// Page/Group
			);

			add_settings_field(
				'sbp_watermarks_media',										// ID
				__('Watermark', 'sbp'),										// Title
			    array(Sbp_Pages_Settings, 'renderField_watermarksMedia'),	// Callback
			    'sbp_settings_watermarks',									// Page/Group
			    'sbp_settings_watermark'									// Section
			);
		}

		/**
		 * Render the field: sbp_colour_keys.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function renderField_sbp_linktypeKeys() {
			Sbp_Pages_Settings::addFieldKeys(array(
				'labels' => array(
					'empty'					=>	__('No link keys exists for this site, why not create one?', 'sbp'),
					'add'					=>	__('Add key', 'sbp'),
					'keyPlaceholder'		=>	__('Enter new key', 'sbp'),
					'keyTextPlaceholder'	=>	__('Enter key label', 'sbp'),
					'keyDelTitle'			=>	__('Delete/undelete key', 'sbp'),
					'keyMovTitle'			=>	__('Click and drag to reorder keys', 'sbp')
				),
				'fieldKey'	=> 'sbp_linktype_keys'
			));
		}

		/**
		 * Render the field: sbp_colour_keys.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function renderField_sbp_colourKeys() {
			Sbp_Pages_Settings::addFieldKeys(array(
				'labels' => array(
					'empty'					=>	__('No swatch keys exists for this site, why not create one?', 'sbp'),
					'add'					=>	__('Add key', 'sbp'),
					'keyPlaceholder'		=>	__('Enter new key', 'sbp'),
					'keyTextPlaceholder'	=>	__('Enter new label', 'sbp'),
					'keyDelTitle'			=>	__('Delete/undelete key', 'sbp'),
					'keyMovTitle'			=>	__('Click and drag to reorder keys', 'sbp')
				),
				'fieldKey'	=> 'sbp_colour_keys'
			));
		}

		/**
		 * Renders a key field widget.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function addFieldKeys(array $fieldOptions = array()) {
			if (empty($fieldOptions)) return;

			$output   =	'<div data-config="' . $fieldOptions['fieldKey'] . 'PaletteKeys" class="sbpPaletteKeys">';
			$output  .=		'<input class="sbpKeysInput" type="hidden" id="' . $fieldOptions['fieldKey'] . '" name="' . $fieldOptions['fieldKey'] . '">';
			$output	 .=		'<span class="dashicons sbpColoursLoading" style="background: transparent url(' . get_admin_url() . 'images/loading.gif' . ') scroll no-repeat 2px 2px;"></span>' . PHP_EOL;
			$output  .=	'</div>';

				// Widget config:
			$output	.=	'<script id="sbpSettings-' . $fieldOptions['fieldKey'] . 'PaletteKeys" type="text/javascript">' . PHP_EOL;
			$output	.=		'if (window.sbpSettings !== undefined && window.sbpSettings.guiConfig !== undefined && window.sbpSettings.guiConfig.specific !== undefined) {' . PHP_EOL;
			$output	.=			'window.sbpSettings.guiConfig.specific.' . $fieldOptions['fieldKey'] . 'PaletteKeys = {' . PHP_EOL;
			$output	.=				'labels: {' . PHP_EOL;

			if (!empty($fieldOptions['labels'])) {
				foreach($fieldOptions['labels'] as $labKey => $labVal) {
					$output	.= $labKey . ': "' . $labVal . '",' . PHP_EOL;
				}
			}

			$output	.=				'},' . PHP_EOL;
			$output	.=				'data: ' . json_encode(json_decode(get_option($fieldOptions['fieldKey']))->keys) . PHP_EOL;
			$output	.=			'}' . PHP_EOL;
			$output	.=		'}' . PHP_EOL;
			$output	.=	'</script>' . PHP_EOL;

			echo $output;
		}

		/**
		 * Render the field: sbp_colours.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function renderField_sbp_colours() {
			$colourKeys	= get_option('sbp_colour_keys');
			$colours	= get_option('sbp_colours');

			if (!empty($colourKeys)) $colourKeys = json_decode($colourKeys);

			$output  =	'<div class="sbpPalette" data-config="sbpMainPalette">' . PHP_EOL;
			$output .=		'<input class="sbpColoursInput" type="hidden" id="sbp_colours" name="sbp_colours">' . PHP_EOL;
			$output .=		'<span class="dashicons sbpColoursLoading" style="background: transparent url(' . get_admin_url() . 'images/loading.gif' . ') scroll no-repeat 2px 2px;"></span>' . PHP_EOL;
			$output .=	'</div>' . PHP_EOL;

				// Widget config:
			$output	.=	'<script id="sbpSettings-sbp_palettes" type="text/javascript">' . PHP_EOL;
			$output	.=		'if (window.sbpSettings !== undefined && window.sbpSettings.guiConfig !== undefined && window.sbpSettings.guiConfig.default !== undefined) {' . PHP_EOL;
			$output	.=			'window.sbpSettings.guiConfig.default.palettes = {' . PHP_EOL;
			$output	.=				'labels: {' . PHP_EOL;
			$output	.=					'empty:				"' . __('No swatches exists for this item, why not create one?', 'sbp') . '",' . PHP_EOL;
			$output	.=					'add:				"' . __('Add swatch', 'sbp') . '",' . PHP_EOL;
			$output	.=					'invalidKey:		"' . __('Undefined Link Type (%s)', 'sbp') . '"' . PHP_EOL;
			$output	.=				'},' . PHP_EOL;
			$output	.=				'keys: [' . PHP_EOL;

			if (!empty($colourKeys) && !empty($colourKeys->keys)) {
				foreach($colourKeys->keys as $keyVal) {
					$output	.= '{key: "' . $keyVal->key . '", label: "' . $keyVal->label . '"},' . PHP_EOL;
				}
			}

			if (!empty($colours)) {
				$output	.=				'],' . PHP_EOL;
				$output	.=				'data: ' . json_encode(json_decode($colours)->swatches) . PHP_EOL;

			} else {
				$output	.=				']' . PHP_EOL;
			}

			$output	.=			'}' . PHP_EOL;
			$output	.=		'}' . PHP_EOL;
			$output	.=	'</script>' . PHP_EOL;

			echo $output;
		}

		/**
		 * Render the field: sbp_youtube_api.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function renderField_youtubeApi() {
			echo '<input type="text" class="regular-text" value="' . esc_attr(get_option('sbp_youtube_api')) . '" id="sbp_youtube_api" name="sbp_youtube_api">';
		}

		/**
		 * Render the field: sbp_flickr_api.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function renderField_flickrApi() {
			echo '<input type="text" class="regular-text" value="' . esc_attr(get_option('sbp_flickr_api')) . '" id="sbp_flickr_api" name="sbp_flickr_api">';
		}

		/**
		 * Render the field: sbp_watermarks_media.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function renderField_watermarksMedia() {
			echo '<a class="button" href="#attachment"><span class="medialistButtonIcon dashicons dashicons-admin-media"></span>' . __('Select a media file', 'sbp') . '</a>';
		}

		/**
		 * Render the field: sbp_watermarks_enabled.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public static function renderSection_watermarks() {
			echo '<p style="color: red;"><strong>CURRENTLY NOT WORKING!</strong></p>';
			$enabled = get_option('sbp_watermarks_enabled');

			self::renderSwitch(array(
				'switchVal'	=> $enabled,
				'echo'		=> TRUE,
				'name'		=> esc_attr(sanitize_key('sbp_watermarks_enabled')),
				'label'		=> __('Show watermarks on images', 'sbp'),
				'labelOn'	=> __('Yes', 'sbp'),
				'labelOff'	=> __('No', 'sbp')
			));

			if ($enabled != 1) echo '<p class="sbpSubText">' . __('Images are currently shown in the frontend <strong><em>without</em></strong> a watermark', 'sbp') . '</p>';
		}

		/**
		 * Sanitizes a text field setting.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $data The data to clean.
		 * @return string The $data, cleaned if needed or an empty string.
		 */
		public static function sbp_sanitizeStr($data) {
			if (!empty($data)) return sanitize_text_field(trim($data));

			return '';
		}

		/**
		 * Sanitizes a checkbox setting.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $data The data to clean.
		 * @return integer Either a one or a zero.
		 */
		public static function sbp_sanitizeCheckbox($data) {
			if (!empty($data)) return 1;

			return 0;
		}
	}
?>