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
	 * Methods required for the Item Page.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Pages_Item extends Sbp_Pages_AbstractPostPage {
		
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
				'name'		=> esc_attr(sanitize_key('sbp_item_featured')),
				'label'		=> __('Featured', 'sbp'),
				'labelOn'	=> __('Yes', 'sbp'),
				'labelOff'	=> __('No', 'sbp')
			));
			$output .= self::renderSwitch($rec, array(
				'name'		=> esc_attr(sanitize_key('sbp_item_inprogress')),
				'label'		=> __('In-progress', 'sbp'),
				'labelOn'	=> __('Yes', 'sbp'),
				'labelOff'	=> __('No', 'sbp')
			));
			$output .= self::optionBoxEnd();
			echo $output;
			
			
			
			
				/* Add Additional Options */
				/* ---------------------- */
				
			self::renderRelated($rec, 'item', array(
				'client' => array(
					subType				=> 'sbp_client',
					name				=> 'sbp_item_client',
					tabLabel			=> __('Client:', 'sbp'),
					label				=> __('Search clients', 'sbp'),
					addNewLabel			=> __('Add new client', 'sbp'),
					addNewToggle		=> __('+ Add new client', 'sbp'),
					addNewPlaceholder	=> esc_attr__('New client title', 'sbp'),
					ajaxError			=> esc_attr__('An error occured whilst creating client', 'sbp'),
					removeLabel			=> __('None', 'sbp'),
					selected			=> TRUE
				),
				'testimonial' => array(
					subType				=> 'sbp_testimonial',
					name				=> 'sbp_item_testimonial',
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
		 * Renders the Metabox for the portfolio 3rd party API keys.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function renderMetaboxPortfolioApis($rec) {
			$apiData = array(
				'flickr' => array(
					metaName	=> 'sbp_item_flickr_api',
					label		=> __('Flickr:', 'sbp') 
				),
				'youtube' => array(
					metaName	=> 'sbp_item_youtube_api',
					label		=> __('YouTube:', 'sbp')
				)
			);
			
			$scriptArray	= array();
			$edit			= __('Edit', 'sbp');
			$def			= __('Default', 'sbp');
			$defKey			= __('Default Key', 'sbp');
			$placeholder	= esc_attr__('Enter new key', 'sbp');
			$output			= self::optionBoxStart(__('API Keys', 'sbp'), TRUE);
			
			foreach($apiData as $apiLabel => $api) {
				$postApiKey = get_post_meta($rec->ID, $api['metaName'], TRUE);
				$curVal		= (empty($postApiKey)) ? $defKey : $postApiKey;
				
				$output .=	'<div class="sbpTextList sbpTextList-' . esc_attr($apiLabel) . '">' . PHP_EOL;
				$output	.=		'<div class="sbpTextListRow">' . PHP_EOL;
				$output	.=			'<span class="sbpTextListLabel">' . $collapsed . $api['label'] . '</span> <span class="sbpTextListValue"><strong data-removetext="' . esc_attr($defKey) . '" data-resettext="' . esc_attr($defKey) . '">' . $curVal . '</strong> <span class="sbpTextListOptions"><a class="sbpTextListControl" href="#open">' . $edit . '</a></span></span>' . PHP_EOL;
				$output	.=			'<div class="sbpTextListContent">' . PHP_EOL;
				$output .= 				'<p class="sbpObDescription">' . ((!empty($api['description'])) ? $api['description'] : '') . '</p>' . PHP_EOL;
				$output .= 				'<input data-resetvalue="" placeholder="' . $placeholder . '" value="' . esc_attr($postApiKey) . '" class="sbpTextListRowField formInput" data-updatesetting="' . $apiLabel . 'ApiKey" type="text" autocomplete="off" size="16" name="sbp[' . $api['metaName'] . ']" id="' . $api['metaName'] . '">' . PHP_EOL;
				$output .= self::optionBoxButtons(array('remove' => $def));
				$output	.=			'</div>' . PHP_EOL;
				$output	.=		'</div>' . PHP_EOL;
				$output .= 	'</div>' . PHP_EOL;
				
					// Add to script output if not using default.
				if (!empty($postApiKey)) {
					$scriptArray[] = array(
						scriptKey => $apiLabel . 'ApiKey',
						scriptVal => $postApiKey
					);
				}
			}
			
			$output .= self::optionBoxEnd();
			
			if (!empty($scriptArray)) { // Add scrip content.
				$output	.= 	'<script id="sbpSettings-sbp_item_flickr_api" type="text/javascript">' . PHP_EOL;
				
				foreach($scriptArray as $script) {
					$output	.= 		'sbpSettings["' . $script['scriptKey'] . '"] = "' . $script['scriptVal'] . '";' . PHP_EOL;
				}
				
				$output	.= 	'</script>' . PHP_EOL;
			}
			
			echo $output;
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
				'recType'	=> 'item',
				'tabData'	=> $tabData,
				'noneMsg'	=> __('No links exists for this item, why not create one?', 'sbp')
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
				'emptyLabel'	=> __('No swatches exists for this item, why not create one?', 'sbp'),
				'addLabel'		=> __('Add swatch', 'sbp')
			));
		}
		
		/**
		 * Renders the Metabox for the client.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function renderMetaboxMedia($rec) {
			$templateVideo		= '<span class="sbpThumbnailSub sbpVideoLeft"><span></span><span></span><span></span><span></span><span></span><span></span></span><span class="sbpThumbnailSub sbpVideoRight"><span></span><span></span><span></span><span></span><span></span><span></span></span><span class="sbpThumbnailSub dashicons dashicons-video-alt3"></span>';
			$templateVimeo		= '<span class="sbpThumbnailSub sbpImgSrc sbpVimeoImg"><svg class="svgDashicon" viewBox="0 0 20 20"><use xlink:href="' . SBP_FE_FOLDER_ICONS . 'di_vimeo_color.svg#dashIcon"></use></svg></span>';
			$templateYoutube	= '<span class="sbpThumbnailSub sbpImgSrc sbpYoutubeImg"><svg class="svgDashicon" viewBox="0 0 20 20"><use xlink:href="' . SBP_FE_FOLDER_ICONS . 'di_youtube_color.svg#dashIcon"></use></svg></span>';
			
			$postMedia	= get_post_meta($rec->ID, 'sbp_item_postmedia', TRUE);
			$hostData	= array();
			$mediaTypes	= array(
				'attachment' => array(
					'type'					=> 'media',
					'cType'					=> 'media',
					'icon'					=> 'dashicons-admin-media',
					'buttonLabel'			=> __('Media', 'sbp'),
					'attrs'					=> array(
						'data-buttontxt'	=> __('Attach Media', 'sbp'),
						'data-titletxt'		=> __('Select Media', 'sbp')
					)
				),
				'hosted_video' => array(
					'type'			=> 'contentToggle',
					'cType'			=> 'hosted_video',
					'icon'			=> 'dashicons-video-alt3',
					'buttonLabel'	=> __('Film', 'sbp'),
					'contentLabel'	=> __('Add Hosted Film', 'sbp'),
					'rsPlaceholder'	=> __('Enter URL or ID', 'sbp'),
					'subTemplate'	=> array(
						'hosted_video'			=> $templateVideo,
						'hosted_video_vimeo'	=> $templateVideo . $templateVimeo,
						'hosted_video_youtube'	=> $templateVideo . $templateYoutube
					),
					'selector' => array(
						'youtube'	=> array(
							'label'			=> __('YouTube', 'sbp'),
							'regex'			=> '^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*', // http://stackoverflow.com/questions/3452546/javascript-regex-how-to-get-youtube-video-id-from-url
							'matchindex'	=> 7,
							'matchkey'		=> 'items',
							'url'			=> 'https://www.googleapis.com/youtube/v3/videos?id=###SEARCH_VAL###&key=###API_KEY###&fields=items(id,snippet(channelId,title,description,thumbnails(default, high, standard, maxres)))&part=snippet'
						),
						'vimeo'		=> array(
							'label'			=> __('Vimeo', 'sbp'),
							'regex'			=> '^.+vimeo.com\/(.*\/)?([^#\?]*)',
							'matchindex'	=> 2,
							'matchkey'		=> 0,
							'url'			=> 'http://vimeo.com/api/v2/video/###SEARCH_VAL###.json'
						)
					)
				)
			);
			
				// Add any additional types added by other plugins extending SB Portfolio.
			$mediaTypes = apply_filters('sbp_postmedia_types', $mediaTypes, array('templateVideo' => $templateVideo));
			
				// Add additonal hosted video types added by plugins.
			$mediaTypes['hosted_video'] = apply_filters('sbp_postmedia_hostedvideo', $mediaTypes['hosted_video']);
			
				// Templates:
			$output		 = 	'<div class="sbpPostMedia">' . PHP_EOL;
			$output		.=		'<p class="sbpMediaEmpty sbpEmptyMsg' . ((!empty($postMedia)) ? ' hidden' : '') . '">' . __('No media exists for this item, why not create one?', 'sbp') . '</p>' . PHP_EOL;
			$output		.= 		'<div class="postMediaTemplates" style="display: none;">' . PHP_EOL;
			
			foreach ($mediaTypes as $mKey => $mType) {
				if (!isset($mType['subTemplate'])) continue;
				
				foreach ($mType['subTemplate'] as $tempKey => $tempHtml) {
					$output .= '<div style="display: none;" data-templatename="' . $tempKey . '">' . $tempHtml . '</div>';
				}
			}

			$output		.=		'</div>' . PHP_EOL;
			
				// Controls:
			$output		.= 		'<div class="postMediaControls">' . PHP_EOL;
			
			foreach ($mediaTypes as $mKey => $mType) {
				$classes	= ($mType['type'] == 'media') ? '' : ' addMediaContentToggle';
				$attrs		= '';
				$butIcon	= '';
				
				if (isset($mType['attrs'])) {
					foreach ($mType['attrs'] as $aKey => $aVal) {
						$attrs .= esc_attr(trim($aKey)) . '="' . esc_attr(trim($aVal)) . '" ';
					}
				}
				
				if (isset($mType['icon'])) {
					$butIcon = '<span class="medialistButtonIcon dashicons ' . $mType['icon'] . '"></span>';
					
				} else if (isset($mType['svgIcon'])) {
					$butIcon = '<svg class="svgDashicon" viewBox="0 0 20 20"><use xlink:href="' . $mType['svgIcon'] . '#dashIcon" /></svg>';
					$classes .= ' svgIcon';
				}
				
				$output .= '<a ' . $attrs . 'href="#' . $mKey . '" class="medialistButton disabled button' . $classes . '">' . $butIcon . $mType['buttonLabel'] . '</a>' . '&nbsp;' . PHP_EOL;
			}
			
			$output		.=		'</div>' . PHP_EOL;
			
				// Add modal content:
			$spinnerImage = get_admin_url() . 'images/loading.gif';
			
			foreach ($mediaTypes as $mKey => $mType) {
				if ($mType['type'] != 'contentToggle') continue;
				
				$output .=	'<div class="addMediaContent addMediaContent-' . $mKey . ' hidden" data-mediatype="' . $mKey . '" style="display: none;">' . PHP_EOL;
				$output	.=		'<label class="addRecordTitle" for="' . $mKey . 'Field">' . ((isset($mType['contentLabel'])) ? $mType['contentLabel'] : $mType['buttonLabel	']) . '</label>' . PHP_EOL;
				
				if (has_filter('sbp_postmedia_types_' . $mType['cType'])) { // Add content from hook to output.
					$output = apply_filters('sbp_postmedia_types_' . $mType['cType'], $output, array('mKey' => $mKey, 'mType' => $mType, 'spinnerImage' => $spinnerImage));
					
				} else { // Use default handling for either hosted video or post_type.
					switch ($mType['cType']) {					
						case 'hosted_video':
							$output	.= 	'<div class="amcRow">' . PHP_EOL;
							$output	.= 		'<input class="sbpLinkPostType" type="hidden" value="' . $mKey . '" style="display: none;" />' . PHP_EOL;
							
							if (!empty($mType['selector'])) {
								$output	.= '<select autocomplete="off" class="sbpHostedVideoHost" id="' . $mKey . 'Field" name="amhvh_' . $mKey . '">' . PHP_EOL;
								
								foreach ($mType['selector'] as $optKey => $opt) {
									$output	.= '<option value="' . esc_attr__($optKey) . '">' . $opt['label'] . '</option>' . PHP_EOL;
										
										// Add host data:
									$hostData[$optKey] = $opt;
								}
								
								$output	.= '</select>' . PHP_EOL;
							}
							
							$output	.=		'<input placeholder="' . esc_attr($mType['rsPlaceholder']) . '" id="' . $mKey . 'Field" name="ams_' . $mKey . '" class="form-input-tip sbpAmcReset sbpRecordSearchTerm" size="16" autocomplete="off" type="text">' . PHP_EOL;
							$output	.=		'<a class="button" href="#flickr_file-get">' . __('Add', 'sbp') . '</a>' . PHP_EOL;
							$output	.=		'<img class="loader" alt="" src="' . $spinnerImage . '">' . PHP_EOL;
							$output	.=		'<p class="rsAjaxError sbpAjaxError hidden"></p>' . PHP_EOL;
							$output	.=	'</div>' . PHP_EOL;
						break;
						
						case 'post_type':
						default:
							$output	.= 	'<div class="amcRow">' . PHP_EOL;
							$output	.= 		'<input class="sbpLinkPostType" type="hidden" value="' . $mKey . '" style="display: none;" />' . PHP_EOL;
							$output	.=		'<input placeholder="' . esc_attr($mType['rsPlaceholder']) . '" id="' . $mKey . 'Field" name="ams_' . $mKey . '" class="form-input-tip sbpAmcReset sbpRecordSearchTerm" size="16" autocomplete="off" type="text">' . PHP_EOL;
							$output	.=		'<p class="rsAjaxError sbpAjaxError hidden"></p>' . PHP_EOL;
							$output	.=	'</div>' . PHP_EOL;
							
							$opts = array('subType' => $mKey);
							if (isset($mType['rsOptions']) && !empty($mType['rsOptions'])) $opts = array_merge($opts, $mType['rsOptions']);
							
							$output .= self::renderNewRecordSelector('sbpVideo', $opts);
						break;
					}
				}
				
				$output .=	'</div>' . PHP_EOL;
			}
			
				// Add current items:
			$output		.= 		'<div class="postMediaList">' . PHP_EOL;
			
			if (!empty($postMedia)) {
				$postMedia = json_decode($postMedia);
				
				if ($postMedia !== NULL && isset($postMedia->items)) {
					foreach ($postMedia->items as $media) {
						$tnUrl		= '';
						$subType	= (isset($media->mediaData->media)) ? $media->mediaData->media : '';
						
						if (isset($media->mediaData)) { // Only special types like flickr and hosted videos have media data.
							$tnUrl	 =  $media->mediaData->thumbnail;
							$output	.=	'<div class="postMediaItem sbpThumbnail loading" title="' . $media->mediaData->title . '" data-itemid="' . $media->ID . '" data-itemtype="' . $media->post_type . '" data-subtype="' . $subType . '">' . PHP_EOL;
							$output .=		'<span class="mediaData hidden" style="display: none;">' . json_encode($media->mediaData, JSON_HEX_QUOT | JSON_HEX_TAG) . '</span>' . PHP_EOL;
						
						} else {
							$relRec		= self::getRelRec($media->ID, $media->post_type, TRUE);
							$output	   .= '<div class="postMediaItem sbpThumbnail loading" title="' . $relRec->post_title . '" data-itemid="' . $media->ID . '" data-itemtype="' . $media->post_type . '" data-subtype="' . $subType . '">' . PHP_EOL;
						
							if ($media->post_type != 'attachment') {
								$tnUrl = wp_get_attachment_thumb_url(get_post_thumbnail_id($media->ID));
								
							} else { // Attachments
								$tnUrl = wp_get_attachment_thumb_url($media->ID);
							}
						}
						
						$output .=		'<span class="pmThumbnail" style="display: none;"><img width="75" height="auto" alt="" data-imgsrc="' . $tnUrl . '" /></span>' . PHP_EOL;
						$output .=		'<span class="sbpThumbnailLoader"><img alt="" src="' . $spinnerImage . '"></span></span>' . PHP_EOL;
						$output .=	'</div>' . PHP_EOL;
					}
				}
			}
			
			$output		.= '</div>' . PHP_EOL;
				
				// Input value will be added by JavaScript therfore the input is disabled 
				// so that, if there is a JS error that stops us getting the value from existing items,
				// an empty input isn't submitted as this would cause media to be removed.
			$output		.=		'<input disabled="disabled" id="itemPostMedia" name="sbp[sbp_item_postmedia]" class="hidden postMediaInput" autocomplete="off" type="hidden">' . PHP_EOL;
			$output		.=	'</div>' . PHP_EOL;
			$output		.=	PHP_EOL;
			
				// Add widget config.
			if (!empty($hostData)) {
				$output	.=	'<script id="sbpSettings-postmediaConf" type="text/javascript">' . PHP_EOL;
				$output	.=		'if (window.sbpSettings !== undefined && window.sbpSettings.guiConfig !== undefined && window.sbpSettings.guiConfig.default !== undefined) {' . PHP_EOL;
				$output	.=			'window.sbpSettings.guiConfig.default.postMedia = {' . PHP_EOL;
				$output	.=				'hosts: {' . PHP_EOL;
				
				foreach ($hostData as $optKey => $opt) {
					$output .= $optKey . ': {' . PHP_EOL;
					
					foreach ($opt as $optKey => $optVal) {
						$output .= $optKey . ': ' . (($optKey == 'regex') ? '/' . $optVal . '/': '"' . $optVal . '"') . ',' . PHP_EOL;
					}
					
					$output .= '},' . PHP_EOL;
				}
				
				$output	.=				'}' . PHP_EOL;
				$output	.=			'}' . PHP_EOL;
				$output	.=		'}' . PHP_EOL;
				$output	.=	'</script>' . PHP_EOL;
			}
			
			echo $output;
		}
		
		/**
		 * Called when an item is saved allowing saving of this plugins custom meta data.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param integer $itemId The $rec ID.
		 * @param object $rec The post record.
		 * @return void
		 */
		public static function saveItem($itemId, $rec) {
			if ($rec->post_type == 'sbp_item') { // Are we even processing the right post type???
				$pluginVars = Sbp_PluginBase::getPluginVars();
						
				if (!empty($pluginVars)) { // Plugin vars exists.
					foreach ($pluginVars as $varKey => $varVal) {
						$cleanedVal = '';
						
							// Check for expected properties.
						switch($varKey) {
								// Boolean like Properties.
							case 'sbp_item_inprogress':
							case 'sbp_item_featured':
								$cleanedVal = intval($varVal); // Sanitise.
								if ($cleanedVal != 1) $cleanedVal = 0;
							break;
							
								// Integer Properties.
							case 'sbp_item_client':
							case 'sbp_item_testimonial':
								$cleanedVal = intval($varVal);
							break;
							
								// JSON Properties, decode to check json is valid and not NULL.
							case 'sbp_item_links':
							case 'sbp_item_postmedia':
							case 'sbp_item_colours':
								$decodedVal = json_decode(stripslashes($varVal));
								
									// If json is valid.
								if (!empty($decodedVal)) $cleanedVal = $varVal; // NULL is empty.
								
							break;
							
								// Text values
							case 'sbp_item_flickr_api':
							case 'sbp_item_youtube_api':
								$cleanedVal = sanitize_text_field(trim($varVal));
								
							break;
							
								// Else, do nothing. We don't need it.
							default:
							break;
						}
								
							// Update meta.
						if (!empty($cleanedVal)){
							update_post_meta($itemId, $varKey, $cleanedVal);
							
						} else {
							delete_post_meta($itemId, $varKey);
						}
					}
				}
			}
		}
		
		/**
		 * Adds additional headers for custom item table columns.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @param array $columns The column titles.
		 * @return array $columns The column titles.
		 */
		public static function addColumnHeaders($columns) {
			$columns['sbp_featured']	= self::getColHeaderIcon('dashicons-star-filled', __('Featured', 'sbp'));
			$columns['sbp_inprogress']	= self::getColHeaderIcon('dashicons-clock', __('In progress', 'sbp'));
			return $columns;
		}
		
		/**
		 * Adds content to custom item table columns.
		 *
		 * @since 0.1.0
		 * @access 
		 * @param string $columnName The column name.
		 * @param integer $postId The post's ID.
		 */
		public static function addColumnContent($columnName, $postId) {
			if ($columnName == 'sbp_featured') {
				if (get_post_meta($postId, 'sbp_item_featured', TRUE) == 1) {
					echo self::getColValTick(__('Featured', 'sbp'));
					
				} else {
					echo self::getColValEmpty(__('Not featured', 'sbp'));
				}
				
			} else if ($columnName == 'sbp_inprogress') {
				if (get_post_meta($postId, 'sbp_item_inprogress', TRUE) == 1) {
					echo self::getColValTick(__('In progress', 'sbp'));
					
				} else {
					echo self::getColValEmpty(__('Not in progress', 'sbp'));
				}
			}
		}
	}
?>