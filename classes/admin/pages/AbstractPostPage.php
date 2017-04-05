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
	 * A base class for all custom post type pages to extend from.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	abstract class Sbp_Pages_AbstractPostPage extends Sbp_Pages_AbstractPage {
		
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		
		
		/* Misc Methods */
		/* ------------ */
		
		/**
		 * Gets a post's related record.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @param integer $postId The post records ID.
		 * @param string $postType The post records post_type.
		 * @param boolean $fullRecord Should all fields be collected?
		 * @return array The results.
		 */
		protected function getRelRec($postId, $postType, $fullRecord = FALSE) {
			global $wpdb;
			
			$select	= ($fullRecord === TRUE) ? '*' : 'ID, post_title';
			$query = implode(' ', array(
				'SELECT ' . $select,
				'FROM ' . $wpdb->posts,
				'WHERE ' . $wpdb->posts . '.' . ID . '= ' . intval($postId),
				'AND ' . $wpdb->posts . '.' . post_type . " = '" . $postType . "'"
			));
			
			$results = $wpdb->get_results($query);
			
			if ($results !== NULL) $results = $results[0];
			
			return $results;
		}
		
		
		
		/* Option Box Methods */
		/* ------------------ */
		
		/**
		 * Returns the start HTML for an option box.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $title The title for the option box.
		 * @param boolean $collapsed Should it be shown collapsed?
		 * @return string The start HTML.
		 */
		public static function optionBoxStart($title, $collapsed = FALSE) {
			$output  = 	'<div class="sbpOptionsSection">' . PHP_EOL;
			$output	.=		'<div class="sbpSep">' . PHP_EOL;
			$output	.= 			'<span>' . $title . '</span>' . PHP_EOL;
			$output	.= 		'</div>' . PHP_EOL;
			$output .= 		'<div class="sbpOptionsBox">' . PHP_EOL;
			
			return $output;
		}
		
		/**
		 * Returns the end HTML for an option box.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return string The end HTML.
		 */
		public static function optionBoxEnd() {
			$output = 		'</div>' . PHP_EOL;	// .sbpOptionsBox
			$output .= 	'</div>' . PHP_EOL;		// .sbpOptionsSection
			
			return $output;
		}
		
		/**
		 * Returns the HTML for an option box buttons.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param array $butOpts Additonal button options.
		 * @return string The end HTML.
		 */
		public static function optionBoxButtons(array $butOpts = array()) {
			$output  = 	'<p>' . PHP_EOL;
			$output .= 		'<a class="sbpTextListControl button" href="#close">' . __('OK', 'sbp') . '</a>' . PHP_EOL;
			$output .= 		'<a class="sbpTextListControl button-cancel" href="#reset">' . __('Cancel', 'sbp') . '</a>' . PHP_EOL;
			
			if (!empty($butOpts['remove']))		$output .= '<a class="sbpTextListControl button-remove" href="#remove">' . $butOpts['remove'] . '</a>' . PHP_EOL;
			if (!empty($butOpts['default']))	$output .= '<a class="sbpTextListControl button-remove" href="#remove">' . $butOpts['default'] . '</a>' . PHP_EOL;
			
			$output .= 	'</p>' . PHP_EOL;
			
			return $output;
		}
		
		
		
		/* Switch Methods */
		/* -------------- */
		
		/**
		 * Renders a switch field.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @param array $options Options for creating the switch.
		 * @return void
		 */
		public static function renderSwitch($rec, $options) {
			if (empty($options)) return;
			
			$switchClasses	= ' off';
			$switchVal		= get_post_meta($rec->ID, $options['name'], TRUE);
			$onClasses		= '';
			$offClasses		= '';
			
			if (!empty($switchVal) && intval($switchVal) == 1) {
				$switchVal		= 'value="1" ';
				$onClasses		= ' selected';
				$switchClasses	= ' on';
				
			} else {
				$switchVal	= 'value="0" ';
				$offClasses	= ' selected';
			}
			
			$output  = 	'<span class="sbpSwitch">';
			$output	.= 		'<label class="sbpSwitchCol sbpSwitchCol-label">' .  $options['label'] . '</label>' . PHP_EOL;
			$output	.= 		'<span class="sbpSwitchCol sbpSwitchCol-button">' . PHP_EOL;
			$output	.= 			'<span class="sbpSwitchSlider' . $switchClasses . '"><span class="sbpSwitchHandle"></span>&nbsp;</span>' . PHP_EOL;
			$output	.= 			'<span class="sbpSwitchButton sbpSwitchButton-on' . $onClasses . '">' . PHP_EOL;
			$output	.= 				'<span class="sbpSwitchLabel">' .  $options['labelOn'] . '</span>' . PHP_EOL;
			$output	.= 			'</span>' . PHP_EOL;
			$output	.= 			'<span class="sbpSwitchButton sbpSwitchButton-off' . $offClasses . '">' . PHP_EOL;
			$output	.= 				'<span class="sbpSwitchLabel">' . $options['labelOff'] . '</span>' . PHP_EOL;
			$output	.= 			'</span>' . PHP_EOL;
			$output	.= 			'<input ' . $switchVal . 'type="hidden" id="' .  $options['name'] . 'Input" name="sbp[' .  $options['name'] . ']">' . PHP_EOL;
			$output	.= 		'</span>' . PHP_EOL;
			$output	.= 	'</span>' . PHP_EOL;
			
			if (isset($options['echo'])) {
				echo $output;
				
			} else {
				return $output;
			}
		}
		
		
		
		/* Related Methods */
		/* --------------- */
		
		/**
		 * Renders the related records: client and testimonial.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @param string $recType The $rec type.
		 * @param array $relData The related records to output.
		 * @return void
		 */
		public static function renderRelated($rec, $recType, array $relData = array()) {
			if (empty($relData)) return;
			 
			$none		= __('None', 'sbp');
			$editText	= __('Edit', 'sbp');
			$output		= self::optionBoxStart(__('Related Records', 'sbp'));
			$output    .= '<div class="sbpTextList sbpTextList-related">' . PHP_EOL;
			
			echo $output;
			
			foreach($relData as $relKey => $rel) {
				$classes		= (isset($rel['classes']) && !empty($rel['classes']))	? ' ' . trim($rel['classes']) : '';
				$relRecId		= intval(get_post_meta($rec->ID, $rel['name'], TRUE));
				$relRec			= ($relRecId > 0) ? self::getRelRec($relRecId, $rel['subType']): NULL;
				$relRecLabel	= (!empty($relRec)) ? $relRec->post_title : $none;
				$recLabelvalue	= (!empty($relRec)) ? $relRec->post_title : '';
				
				if ($relRecId < 1) $relRecId = ''; // Reset for attribute.
				
				$output	=	'';
				$output	.=	'<div class="sbpTextListRow sbpTextListRow-search' . $classes . '">' . PHP_EOL;
				$output	.=		'<span class="sbpTextListLabel">' . $rel['tabLabel'] . '</span> <span class="sbpTextListValue"><strong data-removetext="' . esc_attr($none) . '" data-resettext="' . esc_attr($relRecLabel) . '">' . $relRecLabel . '</strong> <span class="sbpTextListOptions"><a class="sbpTextListControl" href="#open">' . $editText . '</a></span></span>' . PHP_EOL;
				$output	.=		'<div class="sbpTextListContent">' . PHP_EOL;
				$output .= 			'<p class="sbpObDescription"></p>' . PHP_EOL;
				
				if (isset($rel['subType'])) $output	.= '<input class="sbpPostTypeField" type="hidden" value="' . $rel['subType'] . '" style="display: none;" />' . PHP_EOL;
				
				$output .= 			'<input data-labelvalue="' . esc_attr($recLabelvalue) . '" data-resetvalue="' . esc_attr($relRecId) . '" value="' . esc_attr($relRecId) . '" class="sbpTextListRowField isNumeric formInput" type="hidden" name="sbp[' . esc_attr($rel['name']) . ']">' . PHP_EOL;
				$output .= 			'<input placeholder="' . esc_attr($rel['label']) . '" class="sbpRecordSearchField formInput" type="text" autocomplete="off" size="16" name="sbp_' . $relKey . '_search" id="sbp_' . $relKey . '_search">' . PHP_EOL;;
				$output	.=			'<div id="sbp-' . $relKey . '-adder"' . ' class="sbpAddNew wp-hidden-children">' . PHP_EOL;
				$output	.=				'<a class="hide-if-no-js sbpAddNewToggle taxonomy-add-new" href="#sbp-' . $relKey . '-add" id="sbp-' . $relKey . '-add-toggle">' . $rel['addNewToggle'] . '</a>' . PHP_EOL;
				$output	.=				'<div class="sbpAddNewFields category-add wp-hidden-child">' . PHP_EOL;
				$output	.=					'<label for="' . $relKey . 'New" class="screen-reader-text">' . $rel['addNewLabel'] . '</label>' . PHP_EOL;	
				$output	.= 					'<input class="sbpLinkPostType" type="hidden" value="' . $rel['subType'] . '" style="display: none;" />' . PHP_EOL;
				$output	.=					'<input type="text" placeholder="' . $rel['addNewPlaceholder'] . '" class="addNewTitle form-required" id="' . $relKey . 'New" name="' . $relKey . 'New">' . PHP_EOL;	
				$output	.=					'<input type="button" value="' . esc_attr($rel['addNewLabel']) . '" class="button autoWidth addNewSubmit ' . $relKey . '-add-submit" id="' . $relKey . '-add-submit">' . PHP_EOL;	
				$output	.=				'</div>' . PHP_EOL;
				$output	.=			'</div>' . PHP_EOL;
				$output	.=			'<p class="rsAjaxError sbpAjaxError hidden" data-defaultstr="' . $rel['ajaxError'] . '"></p>' . PHP_EOL;
				$output .= 			self::optionBoxButtons(array('remove' => $rel['removeLabel']));
				$output	.=		'</div>' . PHP_EOL;
				$output	.=	'</div>' . PHP_EOL;
				echo $output;
			}

			$output		= '</div>' . PHP_EOL; // End sbpTextList.
			$output	   .=  self::optionBoxEnd();
			echo $output;
		}
		
		
		
		/* Palette Methods */
		/* --------------- */
		
		/**
		 * Renders the Metabox for the palette.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post object.
		 * @param array $config A config array: {
		 * 		emptyLabel string Label for no swatches.
		 * 		addLabel string Label for add button.
		 * }
		 * @return void
		 */
		public static function renderPalette($rec, array $config = array()) {
			$metaKey	= $rec->post_type . '_colours';
			$colourKeys	= get_option('sbp_colour_keys');
			$colours	= get_post_meta($rec->ID, $metaKey, TRUE);
			
			if (!empty($colourKeys)) $colourKeys = json_decode($colourKeys);
			
			$output  =	'<div class="sbpPalette" data-config="sbpMainPalette_' . $rec->ID . '">';
			$output .=		'<input class="sbpColoursInput" type="hidden" value="' . esc_attr($colours) . '" id="' . $metaKey . '_input" name="sbp[' . $metaKey . ']">';
			$output .=		'<span class="dashicons sbpColoursLoading" style="background: transparent url(' . get_admin_url() . 'images/loading.gif' . ') scroll no-repeat 2px 2px;"></span>' . PHP_EOL;
			$output .=	'</div>';
			
			
			$output	.=	'<script id="sbpSettings-sbpMainPalette" type="text/javascript">' . PHP_EOL;
			$output	.=		'if (window.sbpSettings !== undefined && window.sbpSettings.guiConfig !== undefined && window.sbpSettings.guiConfig.specific !== undefined) {' . PHP_EOL;
			$output	.=			'window.sbpSettings.guiConfig.specific.sbpMainPalette_' . $rec->ID . ' = {' . PHP_EOL;
			$output	.=				'labels: {' . PHP_EOL;
			$output	.=					'empty:			"' . $config['emptyLabel'] . '",' . PHP_EOL;
			$output	.=					'add:			"' . $config['addLabel'] . '",' . PHP_EOL;
			$output	.=					'invalidKey:	"' . __('Undefined Link Type (%s)', 'sbp') . '"' . PHP_EOL;
			$output	.=				'},' . PHP_EOL;
			$output	.=				'keys: [' . PHP_EOL;
			
			if (!empty($colourKeys) && !empty($colourKeys->keys)) {
				foreach($colourKeys->keys as $keyVal) {
					$output	.= '{' . PHP_EOL;
					$output	.= '	key:	"' . $keyVal->key . '",' . PHP_EOL;
					$output	.= '	label:	"' . $keyVal->label . '"' . PHP_EOL;
					$output	.= '},' . PHP_EOL;
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
		
		
		
		/* Link Methods */
		/* ------------ */
		
		/**
		 * Renders the Metabox for the display items.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @param array $config A config array: {
		 * 		recType string The type of record.
		 * 		tabData array The data for the linktabs.
		 * 		noneMsg string A message in case of no links.
		 * }
		 * @return void
		 */
		public static function renderLinks($rec, array $config = array()) {
			$recType	= $config['recType'];
			$tabData	= $config['tabData'];
			$recLinks	= get_post_meta($rec->ID, 'sbp_' . $recType . '_links', TRUE);
			
				// Remove selects with no options.
			foreach ($tabData as $tabKey => $tab) {
				if ($tab['type'] != 'select')	continue;
				if (count($tab['options']) < 1)	unset($tabData[$tabKey]);
			}
			
			if (!empty($recLinks)) {
				$recLinks = json_decode($recLinks);
				
				if (isset($recLinks->items)) {
					$recLinks = $recLinks->items;
					
				} else {
					$recLinks = '';
				}
			}
			
				// Start output.
			$output  = 	'<div class="sbpLinks">' . PHP_EOL;
			$output	.=		'<p class="sbpLinksEmpty sbpEmptyMsg' . ((!empty($recLinks)) ? ' hidden' : '') . '">' . $config['noneMsg'] . '</p>' . PHP_EOL;
			$output	.= 		'<div class="linkControls">' . PHP_EOL;
			$output	.= 			'<a href="#addLink" class="medialistButton disabled button">' . __('Add Link', 'sbp') . '</a>' . '&nbsp;' . PHP_EOL;
			$output	.=		'</div>' . PHP_EOL;
			
				// Input value will be added by JavaScript therfore the input is disabled 
				// so that, if there is a JS error that stops us getting the value from existing items,
				// an empty input isn't submitted as this would cause links to be removed.
			$output	.=		'<input disabled="disabled" id="itemLinksReal" name="sbp[sbp_' . $recType . '_links]" class="hidden linkListInput" autocomplete="off" type="hidden">' . PHP_EOL;
			
				// Add dummy item:
			$output .= 		'<div class="linkListDummy" style="display: none;">' . PHP_EOL;
			echo $output;
			
			$dummyLink				= new StdClass;
			$dummyLink->url			= '';
			$dummyLink->text		= '';
			$dummyLink->target		= '';
			$dummyLink->nofollow	= '';
			$dummyLink->description	= '';
			self::renderLinkItem($tabData, $rec, $dummyLink, 'dummy');
			
			$output = 		'</div>' . PHP_EOL;
			$output .= 		'<div class="linkList">' . PHP_EOL;
			echo $output;
			
				// Add current items:
			if (!empty($recLinks)) {
				$linkCounter = 0;
				
					// Output links.
				foreach ($recLinks as $link) {
					self::renderLinkItem($tabData, $rec, $link, $linkCounter);
					$linkCounter ++;
				}
			}
			
			$output	= 		'</div>' . PHP_EOL;
			$output	.= 	'</div>' . PHP_EOL;
			echo $output;
		}
		
		/**
		 * Renders a single metabox link.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param array $tabData An array of tabs to create.
		 * @param object $rec The item post.
		 * @param object $link The link data.
		 * @param mixed $linkCounter The current link number or a string.
		 * @return void
		 */
		public static function renderLinkItem(array $tabData, $rec, $link, $linkCounter) {
			$untitledLink	= __('Untitled link', 'sbp');
			$titleLabel		= (empty($link->text)) ? $untitledLink : $link->text;
			
				// Set correct opt and val and mark selected.
			if (!empty($link->url) && $link->url != '|') {
				$urlData		= Sbp_PluginBase::explodeAndTrim($link->url, '|');
				$urlPostType	= $urlData[0];
				$utlPostVal		= $urlData[1];
				
					// Find correct type.
				$tabFound = FALSE;
				
				foreach ($tabData as $tabKey => $tab) {
					if ($tabFound) break;
					
					if ($tab['type'] == 'select') {
						foreach ($tab['options'] as $opt) {
							if ($opt == $urlPostType) {
								$tabData[$tabKey]['selOption']	= $opt;
								$tabData[$tabKey]['value']		= $utlPostVal;
								$tabData[$tabKey]['selected']	= TRUE;
								$tabData['url']['selected']		= FALSE;
								$tabFound						= TRUE;
								break;
							}
						}
						
					} else if ($tabKey == $urlPostType) {
						$tabData[$tabKey]['value']	= $utlPostVal;
						
						if ($tabKey != 'url') {
							$tabData[$tabKey]['selected']	= TRUE;
							$tabData['url']['selected']		= FALSE;
						}
					}
				}
			}
			
			$output  =	'<div class="linkListItem" data-url="' . esc_attr($link->url) . '">' . PHP_EOL;
			$output .=		'<div class="llItemHeader" data-notitle="' . esc_attr($untitledLink) . '" title="' . esc_attr($link->text) . '">' . PHP_EOL;
			$output .=			'<span class="dashicons" style="background: transparent url(' . get_admin_url() . 'images/loading.gif' . ') scroll no-repeat 2px 2px;"></span>' . PHP_EOL;
			$output .=			'<span class="llItemLabel">' . $titleLabel . '</span>' . PHP_EOL;
			$output .=			'<span class="llItemControls">' . PHP_EOL;
			$output .=				'<span class="llItemControl llItemControl-edit"></span>' . PHP_EOL;
			$output .=			'</span>' . PHP_EOL;
			$output	.=		'</div>' . PHP_EOL;
			$output .=		'<div class="llItemContent" style="display: none;">' . PHP_EOL;
				
				// Text label.
			$output	.=			'<div class="llItemField">' . PHP_EOL;
			$output	.=				'<label class="lliFieldSub lliFieldSub-label" for="sbp_links-' . $linkCounter . '-text">' . __('Title', 'sbp') . '</label>' . PHP_EOL;	
			$output	.=				'<input autocomplete="off" type="text" value="' . esc_attr($link->text) . '" class="lliFieldSub lliFieldSub-content linkTitle form-required" id="sbp_links-' . $linkCounter . '-text" name="sbp_links[' . $linkCounter . ']text">' . PHP_EOL;
			$output	.=			'</div>' . PHP_EOL;
				
				// Description / Tooltip.
			$curDescrip = (!empty($link->description)) ? $link->description : '';
			$output	.=			'<div class="llItemField">' . PHP_EOL;
			$output	.=				'<label class="lliFieldSub lliFieldSub-label" for="sbp_links-' . $linkCounter . '-description">' . __('Description', 'sbp') . '</label>' . PHP_EOL;	
			$output	.=				'<input autocomplete="off" type="text" value="' . esc_attr($curDescrip) . '" class="lliFieldSub lliFieldSub-content linkDescription form-required" id="sbp_links-' . $linkCounter . '-description" name="sbp_links[' . $linkCounter . ']description">' . PHP_EOL;
			$output	.=				'<p class="hideOnNonUrl lliFieldSub lliFieldSub-description">' . __('%t% = Record title', 'sbp') . '</p>' . PHP_EOL;
			$output	.=			'</div>' . PHP_EOL;
			
				// Target.
			$selfSelected	= ($link->target == '_self')	? 'selected="selected" ' : '';
			$blankSelected	= ($link->target == '_blank')	? 'selected="selected" ' : '';
			
			$output	.=			'<div class="llItemField">' . PHP_EOL;
			$output	.=				'<label class="lliFieldSub lliFieldSub-label" for="sbp_links-' . $linkCounter . '-target">' . __('Open in', 'sbp') . '</label>' . PHP_EOL;	
			$output	.=				'<select autocomplete="off" class="lliFieldSub lliFieldSub-content form-required linkTarget" id="sbp_links-' . $linkCounter . '-target" name="sbp_links[' . $linkCounter . ']target ">' . PHP_EOL;
			$output	.=					'<option ' . $selfSelected . 'value="_self">' . __('Current tab/window', 'sbp') . '</option>' . PHP_EOL;
			$output	.=					'<option ' . $blankSelected . ' value="_blank">' . __('New tab/window', 'sbp') . '</option>' . PHP_EOL;
			$output	.=				'</select>' . PHP_EOL;
			$output	.=			'</div>' . PHP_EOL;
			
				// Relation "nofollow".
			$noFollowChecked = (property_exists ($link , 'nofollow') && $link->nofollow == 1) ? 'checked="checked" ' : '';
			
			$output	.=			'<div class="llItemField">' . PHP_EOL;
			$output	.=				'<label class="lliFieldSub lliFieldSub-label" for="sbp_links-' . $linkCounter . '-nofollow"></label>' . PHP_EOL;	
			$output	.=				'<div class="lliFieldSub lliFieldSub-content lliFieldSub-pad">' . PHP_EOL;
			$output	.=					'<input ' . $noFollowChecked . 'autocomplete="off" type="checkbox" value="nofollow" class="form-required linkNoFollow" id="sbp_links-' . $linkCounter . '-nofollow" name="sbp_links[' . $linkCounter . ']nofollow">' . PHP_EOL;
			$output	.=					__('Search engines should not follow', 'sbp') . PHP_EOL;
			$output	.=				'</div>' . PHP_EOL;
			$output	.=			'</div>' . PHP_EOL;
			
				// Tabs.
			$output	.=			'<div class="llItemField llItemField-urlList">' . PHP_EOL;
			$output	.=				'<input id="sbp_links-' . $linkCounter . '-url" name="sbp_links[' . $linkCounter . ']url" class="linkUrl hidden" autocomplete="off" type="hidden">' . PHP_EOL;
			$output	.=				'<label class="lliFieldSub lliFieldSub-label">' . __('Link to', 'sbp') . '</label>' . PHP_EOL;	
			echo $output;
			
			self::renderLinkUrlTabs($tabData, $link, $linkCounter);
			
			$output	 =			'</div>' . PHP_EOL;
			
			
			
				// Link type:
			$linkTypes		= json_decode(get_option('sbp_linktype_keys'))->keys;
			
			$output	.=			'<div class="llItemField">' . PHP_EOL;
			$output	.=				'<label class="lliFieldSub lliFieldSub-label" for="sbp_links-' . $linkCounter . '-linkType">' . __('Link Type', 'sbp') . '</label>' . PHP_EOL;	
			$output	.=				'<select autocomplete="off" class="lliFieldSub lliFieldSub-content form-required linkType" id="sbp_links-' . $linkCounter . '-linkType" name="sbp_links[' . $linkCounter . ']linkType ">' . PHP_EOL;
			$output	.=					'<option value="-1">' . __('None', 'sbp') . '</option>' . PHP_EOL;
			
			if (!empty($linkTypes)) {
				$linkIsValid	= (!empty($link->linkType) && $link->linkType != '-1') ? TRUE : FALSE;
				$linkFound		= FALSE;
				
				foreach ($linkTypes as $typeKey => $typeVal) {
					$selected = '';
					
					if ($linkIsValid && $link->linkType == $typeVal->key) {
						$selected	= 'selected="selected" ';
						$linkFound	= TRUE;
					}
					
					$output  .= '<option ' . $selected . 'value="' . $typeVal->key . '">' . $typeVal->label . '</option>' . PHP_EOL;
				}
				
				if (!$linkFound && $linkCounter != 'dummy') $output  .= '<option selected="selected" value="' . $typeVal->key . '">' . sprintf(__('Undefined Link Type (%s)', 'sbp'), $typeVal->key) . '</option>' . PHP_EOL;
			}
			
			$output	.=				'</select>' . PHP_EOL;
			$output	.=			'</div>' . PHP_EOL;
			
			
			
				// Footer:
			$output	.=			'<p class="llItemDelete"><a class="linkAction" data-action="removeLink" href="#">' . __('Delete link', 'sbp') . '</a></p>' . PHP_EOL;
			$output	.=		'</div>' . PHP_EOL;
			
			$output	.=	'</div>' . PHP_EOL;
			echo $output;
		}

		/**
		 * Renders the current tag of a link search item (the already selected item).
		 *
		 * @since 0.1.0
		 * @access public
		 * @param array $tabData An array of tabs to create.
		 * @param object $link The link data.
		 * @param mixed $linkCounter The current link number or a string.
		 * @return void
		 */
		public static function renderLinkUrlTabs(array $tabData, $link, $linkCounter) {
				// Start tabs.
			$output	 = '<div class="lliFieldSub lliFieldSub-content sbpTabList sbpTabList-url">' . PHP_EOL;
			
				// Tab buttons:
			$output	.= '<ul class="tabListButtons">' . PHP_EOL;
			
			$tabCount = 0;
			foreach($tabData as $tab) {
				$selected		= ($tab['selected'] === TRUE)							? ' selected' : '';
				$tabButClasses	= (isset($tab['classes']) && !empty($tab['classes']))	? ' ' . trim($tab['classes']) : '';
				$iconStr		= esc_attr('dashicons-' . $tab['icon']);
				
				$output	.= '<li data-icon="' . $iconStr . '" class="tabListButton' . $tabButClasses . $selected . '" title="' . esc_attr($tab['label']) . '"><a href="' . esc_url('#sbp_link' . $linkCounter . '_tab' . $tabCount) . '" class="tabListButtonLink dashicons ' . $iconStr . '"></a></li>' . PHP_EOL;
				$tabCount ++;
			}
			
			$output	.= '</ul>' . PHP_EOL;
			
				// Add tab content:
			$tabCount = 0;
			foreach($tabData as $tabKey => $tab) {
				$selected		= ($tab['selected'] === TRUE)	? '' : 'style="display: none;" ';
				$checked		= ($tab['selected'] === TRUE)	? 'checked="checked" ' : '';
				$placeholder	= (isset($tab['placeholder']))	? 'placeholder="' . $tab['placeholder'] . '" ' : '';
				
				$output	.=	'<div ' . $selected . 'id="sbp_link' . $linkCounter . '_tab' . $tabCount . '" data-tabkey="' . $tabKey . '" class="tabListContentPane">' . PHP_EOL;
				$output	.=		'<label class="tabContentTitle addRecordTitle">' . PHP_EOL;
				$output	.=			'<input style="display: none;" autocomplete="off" type="radio" ' . $checked . 'value="' . $tabKey . '" class="form-required" id="sbp_links-' . $linkCounter . '-url' . $tabKey . '" name="sbp_links[' . $linkCounter . ']url">' . $tab['label'] . PHP_EOL;
				$output	.=		'</label>' . PHP_EOL;
				$output	.=		'<div>' . PHP_EOL;
				
				switch($tab['type']) {
					case 'select';
					case 'fixed';
						if ($tab['type'] == 'select') {
							$output	.= '<select class="sbpLinkPostType">' . PHP_EOL;
							
							foreach ($tab['options'] as $opt) {
								$selectedOpt	= ($opt == $tab['selOption']) ? 'selected="selected" ' : '';
								$attrText		= esc_attr($opt);
								
								$output	.= '<option ' . $selectedOpt . 'data-placeholder="' . esc_attr__('Search', 'sbp') . ' ' . $attrText . '" value="' . $attrText . '">' . $opt . '</option>' . PHP_EOL;
							}
							
							$output	.= '</select>' . PHP_EOL;
										
						} else {
							$output	.= '<input class="sbpLinkPostType" type="hidden" value="' . $tabKey . '" style="display: none;" />' . PHP_EOL;
						}
						
						$output	.= '<label for="sbp_links-' . $linkCounter . '-url-' . $tabKey . '-search" class="screen-reader-text">' . $tab['searchLabel'] . '</label>' . PHP_EOL;
						$output	.= '<input type="text" autocomplete="off" size="16" class="sbpRecordSearchTerm form-input-tip ui-autocomplete-input" name="sbp_links-' . $linkCounter . '-url-' . $tabKey . '-search" id="sbp_links-' . $linkCounter . '-url-' . $tabKey . '-search" placeholder="' . esc_attr($tab['searchLabel']) . '" />' . PHP_EOL;
						$output	.= self::renderLinkUrlTabsCurrent($tabKey, $tab);
						
						$addNewName = $linkCounter . '_' . $tabCount;
						
						if (isset($tab['addNew']) && !empty($tab['addNew'])) {
							$output	.= self::renderNewRecordSelector($addNewName, $tab['addNew']);
							
						} else {
							$output	.= self::renderNewRecordSelector($addNewName);
						}
						
					break;
					
					case 'url';
					default;
						$hasUrlImg = FALSE;
						
						$output	.=	'<input class="sbpLinkPostType" type="hidden" value="' . $tabKey . '" style="display: none;" />' . PHP_EOL;
						$output	.=	'<input value="' .  esc_attr($tab['value']) . '" autocomplete="off" type="text" ' . $placeholder . 'class="form-required linkUrl' . ucfirst($tabKey) . '" id="sbp_links-' . $linkCounter . '-url-' . $tabKey . '" name="sbp_links[' . $linkCounter . ']url" />';
						
						if (!empty($tab['media'])) {
							$output	.=	'<div class="sbpMediaBox">' . PHP_EOL;
							
							if (!empty($link->urlImage)) {
								$urlImageParts = Sbp_PluginBase::explodeAndTrim($link->urlImage, '|');
								
								if (intval($urlImageParts[0]) > 0) {
									$imgRec = self::getRelRec($urlImageParts[0], 'attachment');
									
									if (!empty($imgRec)) {
										$hasUrlImg = TRUE;
										$output .= '<span class="sbpThumbnail"><img data-postid="' . intval($urlImageParts[0]) . '" data-posttype="attachment" width="75" height="auto" alt="" src="' . wp_get_attachment_thumb_url($imgRec->ID) . '" /></span>' . PHP_EOL;
									}
								}
							}
							
							if ($hasUrlImg) {
								
							} else {
							}
							
							$output	.=		'<p><a class="' . (($hasUrlImg) ? '' : 'hidden ') . 'sbpMediaLink sbpMediaRemover" href="#">' . $tab['media']['linkTextRemove'] . '</a>' . PHP_EOL;
							$output	.=		'<a class="' . ((!$hasUrlImg) ? '' : 'hidden ') . 'sbpMediaLink sbpMediaOpener" data-titletxt="' .  esc_attr($tab['media']['titletxt']) . '" data-buttontxt="' .  esc_attr($tab['media']['buttontxt']) . '" href="#">' . $tab['media']['linkText'] . '</a></p>' . PHP_EOL;
							$output	.=	'</div>' . PHP_EOL;
						}
					break;
				}
				
				$output	.=		'</div>' . PHP_EOL;
				$output	.=	'</div>' . PHP_EOL;
				
				$tabCount ++;
			}
			
				// End Tabs.
			$output	.= '</div>' . PHP_EOL;
			echo $output;
		}

		/**
		 * Renders the current tag of a link search item (the already selected item).
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $type The expected type.
		 * @param array $tab The tab data.
		 * @return string $currentStr The content for the current box.
		 */
		public static function renderLinkUrlTabsCurrent($type, array $tab) {
			if (isset($tab['selOption']) && !empty($tab['selOption'])) $type = $tab['selOption'];
			
			$hidden		= ($tab['selected'] === TRUE) ? '' : ' hidden';
			$currentStr	= '<div class="sbpLinkCurrent' . $hidden . '">' . PHP_EOL;
			
			if ($tab['selected'] === TRUE && !empty($tab['value'])) {
				$relRec		= self::getRelRec($tab['value'], $type);
				
				if (!empty($relRec)) $currentStr .= '<span class="alreadyAdded" data-reclabel="' . $relRec->post_title .'" data-posttype="' . $type . '" data-recid="' . $relRec->ID .'"><a class="ntdelbutton" data-value="' . $relRec->ID .'">X</a>' . $relRec->post_title .'</span>';
			}
			
			$currentStr .= '</div>' . PHP_EOL;
			return $currentStr;
		}
		
		
		
		/* Listing Column Methods */
		/* ---------------------- */

		/**
		 * Returns a string to be used as an tick value in a column on post table listings.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $titleTxt The title text for the tick icon.
		 * @return string The contenst for the column value.
		 */
		public static function getColValTick($titleTxt = '') {
			return '<span ' . ((!empty($titleTxt)) ? 'title="' . $titleTxt . '" ' : '') . 'class="dashicons dashicons-yes"></span>';
		}

		/**
		 * Returns a string to be used as an empty value "-" in a column on post table listings.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $label The label for screen readers.
		 * @return string The contenst for the column value.
		 */
		public static function getColValEmpty($label = '') {
			return '<span aria-hidden="true">&mdash;</span>' . ((!empty($label)) ? '<span class="screen-reader-text">' . $label . '</span>' : '');
		}

		/**
		 * Returns a string to be used as an empty value "-" in a column on post table listings.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $dashIcon The dash icon for the header icon.
		 * @param string $titleTxt The title text for the dash icon.
		 * @return string The contenst for the column value.
		 */
		public static function getColHeaderIcon($dashIcon = '', $titleTxt = '') {
			return '<span ' . ((!empty($titleTxt)) ? 'title="' . $titleTxt . '" ' : '') . 'class="dashicons ' . $dashIcon . '"></span>';
		}
		
		
		
		/* Record Selector Methods */
		/* ----------------------- */

		/**
		 * Returns the new record link for adding a new type for record.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param string $addNewName The add new name.
		 * @param array $options Options for creating the metabox.
		 * @return string The output.
		 */
		public static function renderNewRecordSelector($addNewName, $options = NULL) {
			if (empty($addNewName)) return '';
			
			if ($options === NULL) {
				$options = array(
					'addNewLabel'		=> __('Add new record', 'sbp'),
					'addNewToggle'		=> __('+ Add new record', 'sbp'),
					'addNewPlaceholder'	=> esc_attr__('New record title', 'sbp'),
					'ajaxError'			=> esc_attr__('An error occured whilst creating new record', 'sbp')
				);
			}
			
			$widgetConfig = (isset($options['widgetConfig'])) ? ' data-config="' . $options['widgetConfig'] . '"' : '';
			
			$output		 =		'<p class="sbpAjaxError hidden" data-defaultstr="' . $options['ajaxError'] . '"></p>' . PHP_EOL; // AJAX error for search.
			$output		.=		'<div id="' . $addNewName . '-adder"' . $widgetConfig . ' class="sbpAddNew wp-hidden-children">' . PHP_EOL;
			$output		.=			'<a class="hide-if-no-js sbpAddNewToggle taxonomy-add-new" href="#' . $addNewName . '-add" id="' . $addNewName . '-add-toggle">' . $options['addNewToggle'] . '</a>' . PHP_EOL;
			$output		.=			'<div class="sbpAddNewFields category-add wp-hidden-child">' . PHP_EOL;
			$output		.=				'<label for="' . $addNewName . 'New" class="screen-reader-text">' . $options['addNewLabel'] . '</label>' . PHP_EOL;	
			
			if (isset($options['subType'])) {
				$output	.= 				'<input class="sbpLinkPostType" type="hidden" value="' . $options['subType'] . '" style="display: none;" />' . PHP_EOL;
			}
			
			$output		.=				'<input type="text" placeholder="' . $options['addNewPlaceholder'] . '" class="addNewTitle form-required" id="' . $addNewName . 'New" name="' . $addNewName . 'New">' . PHP_EOL;	
			$output		.=				'<input type="button" value="' . esc_attr($options['addNewLabel']) . '" class="button autoWidth addNewSubmit ' . $addNewName . '-add-submit" id="' . $addNewName . '-add-submit">' . PHP_EOL;	
			$output		.=			'</div>' . PHP_EOL;
			$output		.=		'</div>' . PHP_EOL;
			
			return $output;
		}
	}
?>