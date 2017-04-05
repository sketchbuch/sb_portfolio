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
	
	
	
	/* Shortcode Processors */
	/* -------------------- */
	
	/**
	 * Handles the sbp_item shortcode.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param mixed $atts Attributes in the shortcode, or an empty string if there are no attributes.
	 * @param string $content Any content contained within the shortcode
	 * @param string $tag The name of the current shortcode.
	 * @return string The content for this shortcode.
	 */
	function sbp_shortcodeItem($atts, $content, $tag) {
		return sbp_shortcode($atts, $content, $tag);
	}
	
	/**
	 * Handles the sbp_client shortcode.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param mixed $atts Attributes in the shortcode, or an empty string if there are no attributes.
	 * @param string $content Any content contained within the shortcode
	 * @param string $tag The name of the current shortcode.
	 * @return string The content for this shortcode.
	 */
	function sbp_shortcodeClient($atts, $content, $tag) {
		return sbp_shortcode($atts, $content, $tag);
	}
	
	/**
	 * Handles the sbp_testimonial shortcode.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param mixed $atts Attributes in the shortcode, or an empty string if there are no attributes.
	 * @param string $content Any content contained within the shortcode
	 * @param string $tag The name of the current shortcode.
	 * @return string The content for this shortcode.
	 */
	function sbp_shortcodeTestimonial($atts, $content, $tag) {
		return sbp_shortcode($atts, $content, $tag);
	}
	
	/**
	 * Handles the sbp_testimonial shortcode.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param mixed $atts Attributes in the shortcode, or an empty string if there are no attributes.
	 * @param string $content Any content contained within the shortcode
	 * @param string $tag The name of the current shortcode.
	 * @return string The content for this shortcode.
	 */
	function sbp_shortcode($atts, $content, $tag) {
		$output		= '';
		$latestPost	= NULL;
		$recId		= -1;
		
		if (is_array($atts) && !empty($atts['id'])) {
			$recId = intval($atts['id']);
			
		} else { // Get latest post ID.
			$latestPost = wp_get_recent_posts(array(
				'numberposts'		=> 1,
				'post_type'			=> $tag,
				'post_status'		=> 'publish',
				'suppress_filters'	=> false
			), FALSE);
			
			if (!empty($latestPost)) {
				$latestPost	= $latestPost[0];
				$recId		= intval($latestPost->ID);
			}
		}
		
		if ($recId > 0) {
			$post = (!empty($latestPost)) ? $latestPost : get_post($recId); // Use latest post or get record matching id.
			
			if (!empty($post) && $post->post_type == $tag) $output = sbp_renderShortcode($post, $tag);
		}
		
		return $output;
	}
	
	
	
	/* Shortcode Renderers */
	/* ------------------- */
	
	/**
	 * Renders the sbp_content for shortcodes.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param WP_Post $post The post object to output.
	 * @param string $tag The type of shortcode
	 * @return string The content to be displayed on the website.
	 */
	function sbp_renderShortcode(WP_Post $post, $tag) {
			// Get path to correct template (theme template will over-ride the plugin version).
		$name = get_template_directory() . DIRECTORY_SEPARATOR . 'templates/' . $tag . '_shortcode.html';
		if (!is_file($name)) $name = SBP_FOLDER . 'templates/' . $tag . '_shortcode.html';
		
		if (is_file($name)) {
			$template = file_get_contents($name);
			
			if (!empty($template)) {
					// Additional markers
				$extraMarkers = array(
					'SHORTCODE'			=> $tag,
					'PERMALINK'			=> get_permalink($post),
					'SNIPPET'			=> get_the_excerpt($post),
					'IMG_THUMBNAIL'		=> get_the_post_thumbnail($post, 'thumbnail'),
					'IMG_MEDIUM'		=> get_the_post_thumbnail($post, 'medium'),
					'IMG_MEDIUMLARGE'	=> get_the_post_thumbnail($post, 'medium_large'),
					'IMG_LARGE'			=> get_the_post_thumbnail($post, 'large'),
					'IMG_FULL'			=> get_the_post_thumbnail($post, 'full'),
					'DEBUG_POST'		=> Sbp_PluginBase::debug($post, 'Post', FALSE)
				);
				
					// Extend additional markers:
				if (function_exists('sbp_scMarkersCore_' . $tag))	$extraMarkers = call_user_func_array('sbp_scMarkersCore_' . $tag, array($post, $extraMarkers));
				if (function_exists('sbp_scMarkers' . $tag))		$extraMarkers = call_user_func_array('sbp_scMarkers' . $tag, array($post, $extraMarkers));
				
				$extraMarkers[DEBUG_MARKERS] = Sbp_PluginBase::debug($extraMarkers, 'Extra Markers', FALSE);
				
					// Replace additonal markers.
				foreach($extraMarkers as $markerKey => $markerVal) {
					$template = str_replace('{' . $markerKey . '}', $markerVal, $template);
				}
				
					// Replace basic post property markers.
				foreach($post as $propKey => $propVal) {
					$template = str_replace('{' . strtoupper($propKey) . '}', $propVal, $template);
				}
				
				return $template;
			}
		}
		
		return '';
	}
	
	/**
	 * Sets core additional markers for item records to be used in shortcode templates.
	 * 
	 * Define the method sbp_scMarkers_sbp_item() in your theme with the same signiture and return to be able to add your own markers.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param WP_Post $post The post object to output.
	 * @param array $extraMarkers An array of additional markers.
	 * @return array $extraMarkers The additional markers.
	 */
	function sbp_scMarkersCore_sbp_item(WP_Post $post, array $extraMarkers) {
		$extraMarkers['IS_FEATURED']		= 'false';
		$extraMarkers['IS_INPROGRESS']		= 'false';
		$extraMarkers['FEATURED_CLASS']		= '';
		$extraMarkers['INPROGRESS_CLASS']	= '';
		
		if (sbp_isFeatured($post->ID, $post->post_type)) {
			$extraMarkers['IS_FEATURED']		= 'true';
			$extraMarkers['FEATURED_CLASS']		= ' featured';
		}
		
		if (sbp_isInprogress($post->ID, $post->post_type)) {
			$extraMarkers['IS_INPROGRESS']		= 'true';
			$extraMarkers['INPROGRESS_CLASS']	= ' inprogress';
		}
		
		$extraMarkers['CLIENT'] = '-';
		$client = sbp_the_client(array('output' => 'return'), $post);
		
		if (!empty($client)) $extraMarkers['CLIENT'] = $client;
		
		return $extraMarkers;
	}
	
	/**
	 * Sets core additional markers for client records to be used in shortcode templates.
	 * 
	 * 
	 * Define the method sbp_scMarkers_sbp_client() in your theme with the same signiture and return to be able to add your own markers.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param WP_Post $post The post object to output.
	 * @param array $extraMarkers An array of additional markers.
	 * @return array $extraMarkers The additional markers.
	 */
	function sbp_scMarkersCore_sbp_client(WP_Post $post, array $extraMarkers) {
		$extraMarkers['IS_FEATURED']	= 'false';
		$extraMarkers['FEATURED_CLASS']	= '';
		
		if (sbp_isFeatured($post->ID, $post->post_type)) {
			$extraMarkers['IS_FEATURED']	= 'true';
			$extraMarkers['FEATURED_CLASS']	= ' featured';
		}
		
		return $extraMarkers;
	}
	
	/**
	 * Sets core additional markers for testimonial records to be used in shortcode templates.
	 * 
	 * Define the method sbp_scMarkers_sbp_testimonial() in your theme with the same signiture and return to be able to add your own markers.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param WP_Post $post The post object to output.
	 * @param array $extraMarkers An array of additional markers.
	 * @return array $extraMarkers The additional markers.
	 */
	function sbp_scMarkersCore_sbp_testimonial(WP_Post $post, array $extraMarkers) {
		$extraMarkers['IS_FEATURED']	= 'false';
		$extraMarkers['FEATURED_CLASS']	= '';
		
		if (sbp_isFeatured($post->ID, $post->post_type)) {
			$extraMarkers['IS_FEATURED']	= 'true';
			$extraMarkers['FEATURED_CLASS']	= ' featured';
		}
		
		$extraMarkers['TESTIMONIAL_NAME']		= get_post_meta($post->ID, 'sbp_testimonial_name', TRUE);
		$extraMarkers['TESTIMONIAL_EMAIL']		= get_post_meta($post->ID, 'sbp_testimonial_email', TRUE);
		$extraMarkers['TESTIMONIAL_POSITION']	= get_post_meta($post->ID, 'sbp_testimonial_position', TRUE);
		
		return $extraMarkers;
	}
?>