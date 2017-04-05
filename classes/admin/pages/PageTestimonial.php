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
	 * Methods required for the Testimonial Page.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Pages_Testimonial extends Sbp_Pages_AbstractPostPage {

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}



		/**
		 * Renders the Metabox for the portfolio testimonial data.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function renderMetaboxTestimonialAuthor($rec) {
			$tData = array(
				'name' => array(
					'label'			=> __('Name', 'sbp'),
					'placeholder'	=> __('Enter name', 'sbp')
				),
				'email' => array(
					'label'			=> __('Email Address', 'sbp'),
					'placeholder'	=> __('Enter email', 'sbp')
				),
				'position' => array(
					'label'			=> __('Position', 'sbp'),
					'placeholder'	=> __('Enter position', 'sbp')
				)
			);
			
			$output =	'<div class="sbpColumns">' . PHP_EOL;
			$output .=		'<div class="sbpColumn" data-colnum="1">' . PHP_EOL;
			$output .=			'<div class="sbpTableBoxes">' . PHP_EOL;
			
			foreach ($tData as $fieldType => $fieldData) {
				$fieldName		= 'sbp_testimonial_' . $fieldType;
				$fieldVal		= get_post_meta($rec->ID, $fieldName, TRUE);
				$placeholder	= (!empty($fieldData['placeholder'])) ? 'placeholder="' . $fieldData['placeholder'] . '" ' : '';
				
				$output .=	'<div class="sbpTableBox" data-type="' . esc_attr($fieldType) . '">' . PHP_EOL;
				$output .=		'<div class="sbpTbLabel">' . $fieldData['label'] . '</div>' . PHP_EOL;
				$output .=		'<div class="sbpTbValue">' . PHP_EOL;
				$output .=			'<input ' . $placeholder . 'type="text" value="' . esc_attr($fieldVal) . '" name="sbp[' . $fieldName . ']" id="' . $fieldName . '_field" class="form-required" autocomplete="off">' . PHP_EOL;
				$output .=		'</div>' . PHP_EOL;
				$output .=	'</div>' . PHP_EOL;
			}
			
			$output .=			'</div>' . PHP_EOL;	// .sbpTableBoxes
			$output .=		'</div>' . PHP_EOL;		// .sbpColumn
			$output .=		'<div style="display: none;" class="sbpColumn" data-colnum="2">' . PHP_EOL;
			$output .=		'</div>' . PHP_EOL;		// .sbpColumn
			$output .=	'</div>' . PHP_EOL;			// .sbpColumns
			
			echo $output;
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
				'name'		=> esc_attr(sanitize_key('sbp_testimonial_featured')),
				'label'		=> __('Featured', 'sbp'),
				'labelOn'	=> __('Yes', 'sbp'),
				'labelOff'	=> __('No', 'sbp')
			));
			$output .= self::optionBoxEnd();
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
		public static function saveTestimonial($recId, $rec) {
			if ($rec->post_type == 'sbp_testimonial') { // Are we even processing the right post type???
				$pluginVars = Sbp_PluginBase::getPluginVars();

				if (!empty($pluginVars)) { // Plugin vars exists.
					foreach ($pluginVars as $varKey => $varVal) {
						$cleanedVal = '';

							// Check for expected properties.
						switch($varKey) {
								// Boolean like Properties.
							case 'sbp_testimonial_featured':
								$cleanedVal = intval($varVal); // Sanitise.
								if ($cleanedVal != 1) $cleanedVal = 0;
							break;
							
								// Text values
							case 'sbp_testimonial_name':
							case 'sbp_testimonial_email':
							case 'sbp_testimonial_position':
								$cleanedVal = sanitize_text_field(trim($varVal));
								
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
				if (get_post_meta($postId, 'sbp_testimonial_featured', TRUE) == 1) {
					echo self::getColValTick(__('Featured', 'sbp'));

				} else {
					echo self::getColValEmpty(__('Not featured', 'sbp'));
				}
			}
		}
	}
?>