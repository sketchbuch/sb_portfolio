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
	 * Methods required for the Client Page.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Pages_Client extends Sbp_Pages_AbstractPostPage {

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}



		/**
		 * Renders the Metabox for the portfolio items.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function renderMetaboxPortfolioOptions($rec) {
				/* Add Switches */
				/* ------------ */
			$output = self::optionBoxStart(__('Display', 'sbp'));
			$output .= self::renderSwitch($rec, array(
				'name'		=> esc_attr(sanitize_key('sbp_client_featured')),
				'label'		=> __('Featured', 'sbp'),
				'labelOn'	=> __('Yes', 'sbp'),
				'labelOff'	=> __('No', 'sbp')
			));
			$output .= self::optionBoxEnd();
			echo $output;




				/* Add Additional Options */
				/* ---------------------- */

			self::renderRelated($rec, 'client', array(
				'testimonial' => array(
					subType				=> 'sbp_testimonial',
					name				=> 'sbp_client_testimonial',
					tabLabel			=> __('Testimonial:', 'sbp'),
					label				=> __('Search testimonials', 'sbp'),
					addNewLabel			=> __('Add new testimonial', 'sbp'),
					addNewToggle		=> __('+ Add new testimonial', 'sbp'),
					addNewPlaceholder	=> esc_attr__('New testimonial title', 'sbp'),
					removeLabel			=> __('None', 'sbp'),
					ajaxError			=> esc_attr__('An error occured whilst creating testimonial', 'sbp')
				)
			));
		}

		/**
		 * Renders the Metabox for the display items.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function renderMetaboxLinks($rec) {
			$custPostTypes	= get_post_types(array('_builtin' => FALSE, 'public' => TRUE, 'publicly_queryable' => TRUE));
			$tabData		= array(
				'url' => array(
					'label'			=> __('URL', 'sbp'),
					'icon'			=> 'admin-links',
					'selected'		=> TRUE,
					'value'			=> '',
					'type'			=> 'url',
					'placeholder'	=> __('Enter the full URL', 'sbp'),
					'classes'		=> 'urlTab',
					'media'			=> array(
						'linkText'			=> __('Set image', 'sbp'),
						'linkTextRemove'	=> __('Remove image', 'sbp'),
						'buttontxt'			=> __('Attach Image', 'sbp'),
						'titletxt'			=> __('Select Image', 'sbp')
					)
				),
				'post' => array(
					'label'			=> __('Post', 'sbp'),
					'icon'			=> 'admin-post',
					'selected'		=> FALSE,
					'value'			=> '',
					'type'			=> 'fixed',
					'searchLabel'	=> __('Search posts', 'sbp'),
					'addNew'		=> array(
						'addNewLabel'		=> __('Add new post', 'sbp'),
						'addNewToggle'		=> __('+ Add new post', 'sbp'),
						'addNewPlaceholder'	=> esc_attr__('New post title', 'sbp'),
						'ajaxError'			=> esc_attr__('An error occured whilst creating new post', 'sbp')
					)
				),
				'page' => array(
					'label'			=> __('Page', 'sbp'),
					'icon'			=> 'admin-page',
					'selected'		=> FALSE,
					'value'			=> '',
					'type'			=> 'fixed',
					'searchLabel'	=> __('Search pages', 'sbp'),
					'addNew'		=> array(
						'addNewLabel'		=> __('Add new page', 'sbp'),
						'addNewToggle'		=> __('+ Add new page', 'sbp'),
						'addNewPlaceholder'	=> esc_attr__('New page title', 'sbp'),
						'ajaxError'			=> esc_attr__('An error occured whilst creating new page', 'sbp')
					)
				),
				'portfolio' => array(
					'label'			=> __('Portfolio', 'sbp'),
					'icon'			=> 'portfolio',
					'selected'		=> FALSE,
					'value'			=> '',
					'type'			=> 'select',
					'options'		=> array_filter($custPostTypes, array(Sbp_PluginBase, 'arrayFilterOutNonSbp')),
					'selOption'		=> ''
				),
				'post_types' => array(
					'label'			=> __('Post Types', 'sbp'),
					'icon'			=> 'images-alt',
					'selected'		=> FALSE,
					'value'			=> '',
					'type'			=> 'select',
					'options'		=> array_filter($custPostTypes, array(Sbp_PluginBase, 'arrayFilterOutSbp')),
					'selOption'		=> ''
				)
			);

			self::renderLinks($rec, array(
				'recType'	=> 'client',
				'tabData'	=> $tabData,
				'noneMsg'	=> __('No links exists for this client, why not create one?', 'sbp')
			));
		}
		
		/**
		 * Renders the Metabox for the palette.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function renderMetaboxPortfolioPalette($rec) {
			self::renderPalette($rec, array(
				'emptyLabel'	=> __('No swatches exists for this client, why not create one?', 'sbp'),
				'addLabel'		=> __('Add swatch', 'sbp')
			));
		}

		/**
		 * Renders the Metabox for the display items.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function renderMetaboxItems($rec) {
			global $wpdb;

				// Count items:
			$itemCount = $wpdb->get_var("SELECT count(DISTINCT postmeta.post_id)
				FROM $wpdb->postmeta postmeta
				JOIN $wpdb->posts posts ON (posts.ID = postmeta.post_id)
				WHERE postmeta.meta_key	= 'sbp_item_client'
				AND postmeta.meta_value	= $rec->ID
				AND posts.post_type		= 'sbp_item'
				AND (posts.post_status = 'publish' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'trash')
			");

			$output = 	'<div class="sbpClientPortfolio sbpClientItems sbpRecordList">' . PHP_EOL;
			
			if ($itemCount > 0) {
				$output	.=	'<div class="clientItemControls">' . PHP_EOL;
				$output	.=		'<p class="sbpInfoMsg sbpRecordListMsgPreload">' . sprintf(esc_html__('%d Items are associated with this client.', 'my-text-domain' ), $itemCount) . '</p>' . PHP_EOL;
				$output	.= 		'<a class="clientItemsButton button" data-action="loadItems" href="#">' . __('Load items', 'sbp') . '</a> <span class="spinner sbpSpinner"></span>' . PHP_EOL;
				$output	.=	'</div>' . PHP_EOL;
				$output	.=	'<p class="clientItemsAjaxError sbpAjaxError hidden"></p>' . PHP_EOL; // AJAX error for search.

			} else {
				$output	.= '<p class="sbpClientItemsEmpty sbpEmptyMsg">' . __('No items are associated with this client.', 'sbp') . '</p>' . PHP_EOL;
			}

			$output	.= 	'</div>' . PHP_EOL;

				// Add widget config:
			$output	.=	'<script id="sbpSettings-clientitems" type="text/javascript">' . PHP_EOL;
			$output	.=		'if (window.sbpSettings !== undefined && window.sbpSettings.guiConfig !== undefined && window.sbpSettings.guiConfig.default !== undefined) {' . PHP_EOL;
			$output	.=			'window.sbpSettings.guiConfig.default.clientItems = {' . PHP_EOL;
			$output	.=				'pageBrowser: {' . PHP_EOL;
			$output	.=					'labels: {' . PHP_EOL;
			$output	.=						'first:			"' . __('First', 'sbp') . '",' . PHP_EOL;
			$output	.=						'last:			"' . __('Last', 'sbp') . '",' . PHP_EOL;
			$output	.=						'next:			"' . __('Next', 'sbp') . '",' . PHP_EOL;
			$output	.=						'prev:			"' . __('Prev', 'sbp') . '",' . PHP_EOL;
			$output	.=						'displayLabel:	"' . __('Displaying', 'sbp') . '",' . PHP_EOL;
			$output	.=						'loadingLabel:	"' . __('Loading', 'sbp') . '"' . PHP_EOL;
			$output	.=					'},' . PHP_EOL;
			$output	.=					'total: ' . $itemCount . PHP_EOL;
			$output	.=				'},' . PHP_EOL;
			$output	.=				'labels: {' . PHP_EOL;
			$output	.=					'"yes":								"' . __('Yes', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"no":								"' . __('No', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-disassociate-true":		"' . __('Disassociate from this client', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-disassociate-false":		"' . __('Re-associate with this client', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-delete":					"' . __('Delete', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-edit":						"' . __('Edit', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-view":						"' . __('View', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-featured-true":			"' . __('Featured: YES', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-featured-false":			"' . __('Featured: NO', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-inprogress-true":			"' . __('In progress: YES', 'sbp') . '",' . PHP_EOL;
			$output	.=					'"action-inprogress-false":			"' . __('In progress: NO', 'sbp') . '"' . PHP_EOL;
			$output	.=				'}' . PHP_EOL;
			$output	.=			'}' . PHP_EOL;
			$output	.=		'}' . PHP_EOL;
			$output	.=	'</script>' . PHP_EOL;

			echo $output;
		}

		/**
		 * Called when an item is saved allowing saving of this plugins custom meta data.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param integer $recId The $rec ID.
		 * @param object $rec The item post.
		 * @return void
		 */
		public static function saveClient($recId, $rec) {
			if ($rec->post_type == 'sbp_client') { // Are we even processing the right post type???
				$pluginVars = Sbp_PluginBase::getPluginVars();

				if (!empty($pluginVars)) { // Plugin vars exists.
					foreach ($pluginVars as $varKey => $varVal) {
						$cleanedVal = '';

							// Check for expected properties.
						switch($varKey) {
								// Boolean like Properties.
							case 'sbp_client_featured':
								$cleanedVal = intval($varVal); // Sanitise.
								if ($cleanedVal != 1) $cleanedVal = 0;
							break;

								// Integer Properties.
							case 'sbp_client_testimonial':
								$cleanedVal = intval($varVal);
							break;

								// JSON Properties, decode to check json is valid and not NULL.
							case 'sbp_client_colours':
							case 'sbp_client_links':
								$decodedVal = json_decode(stripslashes($varVal));

									// If json is valid.
								if (!empty($decodedVal)) $cleanedVal = $varVal; // NULL is empty.

							break;

								// Else, do nothing. We don't need it.
							default:
							break;
						}

							// Update meta.
						if (!empty($cleanedVal)){
							update_post_meta($recId, $varKey, $cleanedVal);

						} else {
							delete_post_meta($recId, $varKey);
						}
					}
				}
			}
		}

		/**
		 * Adds additional headers for custom rec table columns.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @param array $columns The column titles.
		 * @return array $columns The column titles.
		 */
		public static function addColumnHeaders($columns) {
			$columns['sbp_featured'] = self::getColHeaderIcon('dashicons-star-filled', __('Featured', 'sbp'));
			return $columns;
		}

		/**
		 * Adds content to custom rec table columns.
		 *
		 * @since 0.1.0
		 * @access
		 * @param string $columnName The column name.
		 * @param integer $postId The post's ID.
		 */
		public static function addColumnContent($columnName, $postId) {
			if ($columnName == 'sbp_featured') {
				if (get_post_meta($postId, 'sbp_client_featured', TRUE) == 1) {
					echo self::getColValTick(__('Featured', 'sbp'));

				} else {
					echo self::getColValEmpty(__('Not featured', 'sbp'));
				}
			}
		}
	}
?>