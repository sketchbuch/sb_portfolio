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

	if(!defined('ABSPATH')) {
		exit();

	} else {
		if (is_admin()) { // BE.
			/**
			 * This adds flickr media type buttons to the post media metabox.
			 *
			 * @author Stephen Bungert <hello@stephenbungert.com>
			 * @since 0.1.0
			 * @access public
			 * @param array $mediaTypes The media types array.
			 * @param array $data Additional data: {
			 *		templateVideo: string The video template used by the post media widget.
			 * }
			 * @return array $mediaTypes The array, modified as needed.
			 */
			function Sbp_PostmediaTypesFlickr(array $mediaTypes, array $data) {
				$thisFolder		= plugin_dir_url(__FILE__);
				$templateFlickr	= '<span class="sbpThumbnailSub sbpImgSrc sbpFlickrImg"><svg class="svgDashicon" viewBox="0 0 20 20"><use xlink:href="' . $thisFolder . 'di_flickr_color.svg#dashIcon"></use></svg></span>';

				if (!isset($mediaTypes['flickr'])) {
					$mediaTypes['flickr'] = array(
						'type'			=> 'contentToggle',
						'cType'			=> 'flickr',
						'svgIcon'		=> $thisFolder . 'di_flickr.svg',
						'buttonLabel'	=> __('Flickr', 'sbp_flickr'),
						'contentLabel'	=> __('Add Flickr Content', 'sbp_flickr'),
						'rsPlaceholder'	=> __('Enter URL or ID', 'sbp_flickr'),
						'subTemplate'	=> array(
							'flickr_set'		=> $templateFlickr,
							'flickr_file_photo'	=> $templateFlickr,
							'flickr_file_video'	=> $data['templateVideo'] . $templateFlickr
						),
						'selector' => array(
							'flickr_file'	=> array(
								'label'		=> __('Image/Video', 'sbp_flickr')
							),
							'flickr_set'	=> array(
								'label'		=> __('Album/Set', 'sbp_flickr')
							)
						)
					);
				}

				return $mediaTypes;
			}

			/**
			 * Renders the toggle content for flickr media type button.
			 *
			 * @since 0.1.0
			 * @access public
			 * @param string $output The output for the post media metabox.
			 * @param array $data Additional data: {
			 *		mKey: string The media type key.
			 *		mType: array The media type config array.
			 *		spinnerImage: string The spinner image source.
			 * }
			 * @return string $output The output, modified as needed.
			 */
			function Sbp_PostmediaTypesFlickr_flickr($output, array $data) {
				$output	.= 	'<div class="amcRow">' . PHP_EOL;

				if (!empty($data['mType']['selector'])) {
					$output	.= '<select autocomplete="off" class="sbpLinkPostType" id="' . $data['mKey'] . 'Field">' . PHP_EOL;

					foreach ($data['mType']['selector'] as $optKey => $opt) {
						$output	.= '<option value="' . esc_attr__($optKey) . '">' . $opt['label'] . '</option>' . PHP_EOL;
					}

					$output	.= '</select>' . PHP_EOL;
				}

				$output	.=		'<input placeholder="' . esc_attr($data['mType']['rsPlaceholder']) . '" id="' . $mKey . 'Field" name="ams_' . $mKey . '" class="form-input-tip sbpAmcReset sbpRecordSearchTerm" size="16" autocomplete="off" type="text">' . PHP_EOL;
				$output	.=		'<a class="button" href="#' . $data['mKey'] . '-get">' . __('Add', 'sbp') . '</a>' . PHP_EOL;
				$output	.=		'<img class="loader" alt="" src="' . $data['spinnerImage'] . '">' . PHP_EOL;
				$output	.=		'<p class="rsAjaxError sbpAjaxError hidden"></p>' . PHP_EOL;
				$output	.=	'</div>' . PHP_EOL;

				return $output;
			}

				// Setup hooks:
			add_filter('sbp_postmedia_types', 'Sbp_PostmediaTypesFlickr', 10, 2);					// Buttons in the metabox.
			add_filter('sbp_postmedia_types_flickr', 'Sbp_PostmediaTypesFlickr_flickr', 10, 2);		// Content for: flickr_set.

				// Add CSS and JS:
			wp_enqueue_style('sbp_postmedia_types_flickr_css', plugin_dir_url(__FILE__) . 'flickr.css');						// CSS for Flickr media items.
			wp_enqueue_script('sbp_postmedia_types_flickr_js', plugin_dir_url(__FILE__) . 'flickr.js', array(), false, true);	// JS callbacks and methods for getting images from Flickr.

		} else { // FE.

			/* Template Methods */
			/* ---------------- */

			// The following methods are used by sbp_the_media() in classes/frontend/template.php to output content for flickr media.



			/**
			 * Returns the string content for a flickr set.
			 *
			 * If you just want a linked image you should use sbp_media_linkedImage().
			 *
			 * @since 0.1.0
			 * @access public
			 * @param object $mediaFile The media data object.
			 * @return string $item The item str content.
			 */
			function sbp_media_flickr_set($mediaFile) {
				return sbp_media_flickrEmbed($mediaFile);
			}

			/**
			 * Returns the string content for a flickr file.
			 *
			 * If you just want a linked image you should use sbp_media_linkedImage().
			 *
			 * @since 0.1.0
			 * @access public
			 * @param object $mediaFile The media data object.
			 * @return string $item The item str content.
			 */
			function sbp_media_flickr_img($mediaFile) {
				return sbp_media_flickrEmbed($mediaFile);
			}

			/**
			 * Returns the string content for embedding a flickr resource.
			 *
			 * @since 0.1.0
			 * @access public
			 * @param object $mediaFile The media data object.
			 * @return string $item The item str content.
			 */
			function sbp_media_flickrEmbed($mediaFile) {
				$alt	= '';
				$title	= esc_attr($mediaFile->title);

				if ($mediaFile->media == 'video') {
					$alt = esc_attr(__('Video', 'sbp_flickr'));

				} else if ($mediaFile->special == 'flickr_set') {
					$alt = esc_attr(__('Album', 'sbp_flickr'));

				} else {
					$alt = esc_attr(__('Image', 'sbp_flickr'));
				}

				$item  =	'<a data-flickr-embed="true" data-header="false" data-footer="false" href="' . $mediaFile->url . '" title="' . $title . '">' . PHP_EOL;
				$item .=		'<img src="' . $mediaFile->medium . '" width="800" height="400" title="' . $title . '" alt="' . $alt . '">' . PHP_EOL;
				$item .=	'</a><script async src="//embedr.flickr.com/assets/client-code.js" charset="utf-8"></script>' . PHP_EOL;

				return $item;
			}
		}
	}
?>