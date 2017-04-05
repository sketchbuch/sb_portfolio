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
		if (is_admin()) {
			/**
			 * This adds a film source to the postmedia hosted video select tag.
			 * 
			 * @author Stephen Bungert <hello@stephenbungert.com>
			 * @since 0.1.0
			 * @access public
			 * @param array $hvConf The hosted video config data.
			 * @return array $hvConf The hosted video config data, modified as needed.
			 */
			function Sbp_PostmediaFilmTypes(array $hvConf) {
				if (isset($hvConf['selector'])) {
					if (!isset($hvConf['selector']['daily_motion'])) {
						$hvConf['selector']['daily_motion'] = array(
							'label'			=> __('Daily Motion', 'sbp_filmtypes'),
							'regex'			=> '^.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?',
							'matchindex'	=> 2,
							'matchkey'		=> -1,
							'url'			=> 'https://api.dailymotion.com/video/###SEARCH_VAL###?fields=id,title,description,user,embed_url,url,media_type,thumbnail_url,thumbnail_120_url,thumbnail_480_url,thumbnail_720_url'
						);
						
							// Now add template
						if (isset($hvConf['subTemplate']) && !isset($hvConf['subTemplate']['hosted_video_daily_motion'])) {
							$hvConf['subTemplate']['hosted_video_daily_motion'] = $hvConf['subTemplate']['hosted_video'] . '<span class="sbpThumbnailSub sbpImgSrc sbpDailyMotionImg"><svg class="svgDashicon" viewBox="0 0 20 20"><use xlink:href="' . plugin_dir_url(__FILE__) . 'di_daily_motion_color.svg#dashIcon"></use></svg></span>';
						}
					}
				}
				
				return $hvConf;
			}
			
				// Setup hooks:
			add_filter('sbp_postmedia_hostedvideo', 'Sbp_PostmediaFilmTypes', 10, 1); // Film hosts for the hosted_video select tag.
			
				// Add JS:
			wp_enqueue_script('sbp_postmedia_hostedvideo_filmtypes_js', plugin_dir_url(__FILE__) . 'filmtypes.js', array(), false, true);	// JS callbacks and methods for getting images from Flickr.
			
		} else { // FE.
			/* Template Methods */
			/* ---------------- */
			
			// The following methods are used by sbp_the_media() in classes/frontend/template.php to output content for flickr media.
			
			
			
			/**
			 * Returns the string content for a dm film. 
			 *
			 * @since 0.1.0
			 * @access public
			 * @param object $mediaFile The media data object.
			 * @return string $item The item str content.
			 */
			function sbp_media_hosted_video_daily_motion($mediaFile) {
				return '<iframe type="text/html" width="400" height="300" src="//www.dailymotion.com/embed/video/' . $mediaFile->mediaId . '"></iframe>' . PHP_EOL;
			}
		}
	}
?>