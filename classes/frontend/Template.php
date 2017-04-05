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
	 * Either returns a client record attached to an item or echos the title (linked and wrapped), or calls a custom render function that you can define.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @since 0.1.0
	 * @access public
	 * @param array $opts Optional. See sbp_getConf().
	 * @param mixed $subPost Optional. A post record or NULL. Will be used instead of get_post().
	 * @param string $renderFunction Optional. An optional name for a function that should handle the echoing of the data.
	 * @return mixed Depends on $conf['output']:
	 *		'return'	The client's post object or NULL if no client.
	 *		'echo'		An empty string if no client. Else $renderFunction() will be called if defined, or the client's title (linked if required) and wrapped will be echoed.
	 */
	function sbp_the_client(array $opts = array(), $subPost = NULL, $renderFunction = 'sbp_the_client_render') {
		$client	= NULL;
		$post	= ($subPost !== NULL) ? $subPost : get_post();
		$conf	= sbp_getConf($opts);

		if (Sbp_PluginBase::isSbpPost($post, 'item')) {
			$client = sbp_getRecFromMeta($post->ID, $post->post_type . '_client', $conf);

			if (is_object($client)) {
				if ($conf['expandRec'] === TRUE) {
					$client->featured		= sbp_isFeatured($client->ID, $client->post_type);
					$client->links			= sbp_getLinks($client->ID, $client->post_type);
					$client->testimonial	= sbp_the_testimonial(array('output' => 'return', 'expandRec' => TRUE), $client);
					sbp_addTnDataToObj($client->ID, $client);
				}

			} else {
				$client = NULL;
			}
		}

			// Output or return:
		if ($conf['output'] == 'echo') {
			if ($client === NULL) {
				echo '';

			} else { // Either echo the title (linked if required) or call a method defined by the theme.
				if (function_exists($renderFunction)) {
					call_user_func_array($renderFunction, array($client, $conf));

				} else {
					sbp_echoTitle($client, $conf);
				}
			}

		} else {
			return $client;
		}
	}

	/**
	 * Either returns a testimonial record attached to an item/client or echos the title (linked and wrapped), or calls a custom render function that you can define.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @since 0.1.0
	 * @access public
	 * @param array $opts Optional. See sbp_getConf().
	 * @param mixed $subPost Optional. A post record or NULL. Will be used instead of get_post().
	 * @param string $renderFunction Optional. An optional name for a function that should handle the echoing of the data.
	 * @return mixed Depends on $conf['output']:
	 *		'return'	The testimonial's post object or NULL if no client.
	 *		'echo'		An empty string if no testimonial. Else $renderFunction() will be called if defined, or the testimonial's title (linked if required) and wrapped will be echoed.
	 */
	function sbp_the_testimonial(array $opts = array(), $subPost = NULL, $renderFunction = 'sbp_the_testimonial_render') {
		$testimonial	= NULL;
		$post			= ($subPost !== NULL) ? $subPost : get_post();
		$conf			= sbp_getConf($opts);

		if (Sbp_PluginBase::isSbpPost($post, 'item|client')) {
			$testimonial = sbp_getRecFromMeta($post->ID, $post->post_type . '_testimonial', $conf);

			if (is_object($testimonial)) {
				if ($conf['expandRec'] === TRUE) {
					$testimonial->featured 	= sbp_isFeatured($testimonial->ID, $testimonial->post_type);
					$testimonial->name 		= get_post_meta($testimonial->ID, 'sbp_testimonial_name', TRUE);
					$testimonial->email 	= get_post_meta($testimonial->ID, 'sbp_testimonial_email', TRUE);
					$testimonial->position	= get_post_meta($testimonial->ID, 'sbp_testimonial_position', TRUE);
					$testimonial			= sbp_addTnDataToObj($testimonial->ID, $testimonial);
				}

			} else {
				$testimonial = NULL;
			}
		}

			// Output or return:
		if ($conf['output'] == 'echo') {
			if ($testimonial === NULL) {
				echo '';

			} else { // Either echo the title (linked if required) or call a method defined by the theme.
				if (function_exists($renderFunction)) {
					call_user_func_array($renderFunction, array($testimonial, $conf));

				} else {
					sbp_echoTitle($testimonial, $conf);
				}
			}

		} else {
			return $testimonial;
		}
	}

	/**
	 * Returns the boolean state of featured for the post with postId.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param integer $postId The post ID.
	 * @param string $postType The post type.
	 * @return boolean TRUE if featured.
	 */
	function sbp_isFeatured($postID, $postType) {
		return sbp_getBooleanFromMeta($postID, $postType, 'featured');
	}

	/**
	 * Returns the boolean state of inprogress for the post with postId.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param integer $postId The post ID.
	 * @param string $postType The post type.
	 * @return boolean TRUE if featured.
	 */
	function sbp_isInprogress($postID, $postType) {
		return sbp_getBooleanFromMeta($postID, $postType, 'inprogress');
	}

	/**
	 * Echos testimonial position.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param object $post A post object to use.
	 * @param array $opts Optional. See $baseConf in sbp_getTestimonialMeta().
	 * @return void
	 */
	function sbp_the_testimonial_name($post = NULL, array $opts = array()) {
		if (empty($post)) $post = get_post();

		sbp_getTestimonialMeta('name', $post, $opts);
	}

	/**
	 * Echos testimonial position.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param object $post A post object to use.
	 * @param array $opts Optional. See $baseConf in sbp_getTestimonialMeta().
	 * @return void
	 */
	function sbp_the_testimonial_email($post = NULL, array $opts = array()) {
		if (empty($post)) $post = get_post();

		sbp_getTestimonialMeta('email', $post, $opts);
	}

	/**
	 * Echos testimonial position.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param object $post A post object to use.
	 * @param array $opts Optional. See $baseConf in sbp_getTestimonialMeta().
	 * @return void
	 */
	function sbp_the_testimonial_position($post = NULL, array $opts = array()) {
		if (empty($post)) $post = get_post();

		sbp_getTestimonialMeta('position', $post, $opts);
	}

	/**
	 * Echos or returns a testimonial property.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param string $type The type of testimonial meta value.
	 * @param object $post A post object to use.
	 * @param array $opts Optional. See $baseConf below.
	 * @return void
	 */
	function sbp_getTestimonialMeta($type, $post, array $opts) {
		if (!Sbp_PluginBase::isSbpPost($post->post_type)) return NULL;

		$metaVal = sbp_getMeta($post->ID, $post->post_type, $type);

		if (!empty($metaVal)) {
			$baseConf = array(
				'output'	=> 'echo',														// string Either echo or return.
				'before'	=> '<p class="testimonialProp testimonialProp-' . $type . '">',	// string HTML before output. Only applies if output = 'echo'.
				'after'		=> '</p>'														// string HTML after output. Only applies if output = 'echo'.
			);

			$conf = (!empty($opts)) ? array_merge($baseConf, $opts) : $baseConf;

			if ($conf['output'] == 'echo') {
				echo $conf['before'] . $metaVal . $conf['after'];

			} else {
				return $metaVal; // Either the object or the str.
			}
		}
	}

	/**
	 * Echos links, if any exist.
	 * You can change the output of the links by creating $renderFunction() in your theme, otherwise a UL list will be created.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param string $linkType Optional. The type of links to get.
	 * @param string $renderFunction Optional. An optional name for a function that should handle the echoing of the data.
	 * @return void
	 */
	function sbp_the_links($linkType = '', $renderFunction = 'sbp_the_links_render') {
		$links		= sbp_getLinks($linkType);
		$linkCount	= count($links);

		if ($linkCount > 0) {
			if (function_exists($renderFunction)) {
				call_user_func_array($renderFunction, array($links, $linkCount, $linkType));

			} else {
				$output			= '<ul class="sbpLinks">' . PHP_EOL;
				$linkCounter	= 1;

				foreach($links as $link) {
						// Create li attributes:
					$liAttrs = sbp_addListAttrs(array(
						'class'			=> 'sbpLink',
						'title'			=> esc_attr($link->text),
						'data-linknum'	=> $linkCounter,
						'data-linkkind'	=> esc_attr($link->kind),
						'data-linktype'	=> esc_attr($link->linkType)
					), $linkCounter, $linkCount);

					$liAttrsStr = sbp_getAttrStr($liAttrs);



						// Create link attributes:
					$attrs = array('href' => '#');

					if ($link->nofollow === TRUE)	$attrs['relation']	= 'nofollow';
					if (!empty($link->target))		$attrs['target']	= esc_attr($link->target);

					if ($link->kind == 'url') {
						$attrs['href'] = $link->url;

					} else { // Posts.
						if (!empty($link->url)) $attrs['href'] = $link->url->url;
					}

					$linkAttrsStr = sbp_getAttrStr($attrs);



						// Add link to output:
					$output .=	'<li' . $liAttrsStr . '><a' . $linkAttrsStr . '>' . $link->text . '</a></li>' . PHP_EOL;
					$linkCounter ++;
				}

				$output .= '</ul>' . PHP_EOL;

				echo $output;
			}
		}
	}

	/**
	 * Echos media, if any exist.
	 * You can change the output of the media by creating $renderFunction() in your theme, otherwise inbuilt rendering methods will be used.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param string $mediaType See: sbp_getMedia.
	 * @param string $mediaSubtype See: sbp_getMedia.
	 * @param string $renderFunction Optional. An optional name for a function that should handle the echoing of the data.
	 * @return void
	 */
	function sbp_the_media($mediaType = '', $mediaSubtype = '', $renderFunction = 'sbp_the_media_render') {
		$media		= sbp_getMedia($mediaType, $mediaSubtype);
		$mediaCount	= count($media);

		if ($mediaCount > 0) {
			if (function_exists($renderFunction)) {
				call_user_func_array($renderFunction, array($media, $mediaCount, $mediaType, $mediaSubtype));

			} else {
				$output			= '<div class="sbpMedia">';
				$mediaCounter	= 1;

				foreach($media as $mediaFile) {
						// Create attributes:
					$mediaAttrs = sbp_addListAttrs(array(
						'class'				=> 'sbpMediaItem',
						'data-medianum'		=> $mediaCounter,
						'data-mediatype'	=> '',
						'data-mediasubtype'	=> '',
					), $mediaCounter, $mediaCount);

						// Add media based attributes:
					if (isset($mediaFile->special)) {
						$mediaAttrs['class']		   .= ' special';
						$mediaAttrs['data-mediatype']	= esc_attr($mediaFile->special);

						if (isset($mediaFile->media)) $mediaAttrs['data-mediasubtype'] = esc_attr($mediaFile->media);

					} else {
						$mediaAttrs['data-mediatype']		= $mediaFile->post_type;
						$mediaAttrs['data-mediasubtype']	= $mediaFile->post_mime_type;
					}

					$mediaAttrsStr = sbp_getAttrStr($mediaAttrs);



						// Create item:
					$item = '';

						// Order:
						// ------
						// 1 - sbp_media_[type]_[subtype]()
						// 2 - sbp_media_[type]()
						// 3 - Error message in HTML
					if (function_exists('sbp_media_' . $mediaAttrs['data-mediatype'] . '_' . $mediaAttrs['data-mediasubtype'])) {
						$item = call_user_func_array('sbp_media_' . $mediaAttrs['data-mediatype'] . '_' . $mediaAttrs['data-mediasubtype'], array($mediaFile));

					} else if (function_exists('sbp_media_' . $mediaAttrs['data-mediatype'])) {
						$item = call_user_func_array('sbp_media_' . $mediaAttrs['data-mediatype'], array($mediaFile));

					} else {
						$item .= PHP_EOL . '<!-- sbp_the_media() no media method exists for: ' . $mediaAttrs['data-mediatype'] . ((!empty($mediaAttrs['data-mediasubtype']) ? ' or ' . $mediaAttrs['data-mediatype'] . '_' . $mediaAttrs['data-mediasubtype'] : '')) . ' -->' . PHP_EOL;
					}



						// Add media to output:
					$output .=	'<div' . $mediaAttrsStr . '>' . $item . '</div>' . PHP_EOL;
					$mediaCounter ++;
				}

				$output .= '</div>';

				echo $output;
			}
		}
	}

	/**
	 * Returns the string content for a hosted film.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param object $mediaFile The media data object.
	 * @return string $item The item str content.
	 */
	function sbp_media_hosted_video($mediaFile) {
		$item = '';

		if ($mediaFile->media == 'youtube') {
			$item .= '<iframe type="text/html" width="400" height="300" src="//www.youtube.com/embed/' . $mediaFile->mediaId . '?autoplay=0" frameborder="0"/></iframe>' . PHP_EOL;

		} else if ($mediaFile->media == 'vimeo') {
			$item .= '<iframe src="//player.vimeo.com/video/' . $mediaFile->mediaId . '" width="400" height="300" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' . PHP_EOL;
		}

		return $item;
	}

	/**
	 * Returns the string content for an attachment.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param object $mediaFile The media data object.
	 * @return string $item The item str content.
	 */
	function sbp_media_attachment($mediaFile) {
		return sbp_media_linkedImage($mediaFile);
	}

	/**
	 * Returns the string content for an attachment.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param object $mediaFile The media data object.
	 * @return string $item The item str content.
	 */
	function sbp_media_linkedImage($mediaFile) {
		$linkAttrs		= array(
			'target'	=> '_self',
			'class'		=> 'sbpMediaImageLink',
			'href'		=> $mediaFile->url
		);
		$imgAttrs		= array(
			'src'	=> $mediaFile->large,
			'class'	=> 'sbpMediaImage',
			'alt'	=> __('Image', 'sbp')
		);

		if (isset($mediaFile->special)) {
			$linkAttrs['title']		= esc_attr($mediaFile->title);
			$linkAttrs['target']	= '_blank';
			$linkAttrs['class']	   .= ' external';

			$imgAttrs['title']				= esc_attr($mediaFile->title);
			$imgAttrs['data-mediaid']		= esc_attr($mediaFile->mediaId);
			$imgAttrs['data-mediatype']		= esc_attr($mediaFile->special);

			if (isset($mediaFile->media)) $imgAttrs['data-mediasubtype'] = esc_attr($mediaFile->media);

		} else {
			$linkAttrs['title'] = esc_attr($mediaFile->post_title);

			$imgAttrs['title']				= esc_attr($mediaFile->post_title);
			$imgAttrs['data-mediaid']		= $mediaFile->ID;
			$imgAttrs['data-mediatype']		= esc_attr($mediaFile->post_type);
			$imgAttrs['data-mediasubtype']	= esc_attr($mediaFile->post_mime_type);
		}

		$imgAttrsStr	= sbp_getAttrStr($imgAttrs);
		$linkAttrsStr	= sbp_getAttrStr($linkAttrs);

		$item  =	'<a' . $linkAttrsStr . '>' . PHP_EOL;
		$item .=		'<img' . $imgAttrsStr . '>' . PHP_EOL;
		$item .=	'</a>' . PHP_EOL;

		return $item;
	}

	/**
	 * Returns an array of media objects for an sbp post. Optionally you can filter the media to get a certain type and or subtype (for special media types).
	 *
	 * @since 0.1.0
	 * @access public
	 * @param string $mediaType The type of media to get: 'attachment', 'hosted_video', 'flickr_img', 'flickr_set' (this can be expanded upon - see the flickr example extension).
	 * @param string $mediaSubtype Certain special types (hosted_video, and flickr_img for example) have sub types ('vimeo', 'youtobe', 'photo', 'video'). You can select these with this property.
	 * @param object $post A post object to use.
	 * @return array $postmedia Either an array of media objects (stdClass and posts) or an empty array.
	 */
	function sbp_getMedia($mediaType = '', $mediaSubtype = '', $post = NULL) {
		if (empty($post)) $post = get_post();
		if (!Sbp_PluginBase::isSbpPost($post, 'item')) return NULL;

		$postmedia		= array();
		$postmediaMeta	= sbp_getMeta($post->ID, $post->post_type, 'postmedia', TRUE);

		if (!empty($postmediaMeta)) {
			$newMedia	= array();
			$postmedia	= json_decode($postmediaMeta)->items;

			foreach($postmedia as $media) {
				$newMediaItem = NULL;

				if (isset($media->mediaData)) { // Special types.
						// Skip this media if we are looking for a specific type/subtype and this isn't it
					if (!empty($mediaType) && $media->mediaData->special != $mediaType)											continue;
					if (!empty($mediaSubtype) && isset($media->mediaData->media) && $media->mediaData->media != $mediaSubtype)	continue;

					$newMediaItem = $media->mediaData;

				} else { // Either attachment or post/custom_posts... treat the same.
					if (!empty($mediaType) && $media->post_type != $mediaType) continue; // Skip this media if we are looking for a specific type and this isn't it

					$newMediaItem				= get_post($media->ID);
					$newMediaItem->url			= get_permalink($newMediaItem->ID);
					$newMediaItem				= sbp_addTnDataToObj($newMediaItem->ID, $newMediaItem, TRUE);
				}

				if ($newMediaItem !== NULL) $newMedia[] = $newMediaItem;
			}

			$postmedia = $newMedia;
		}

		return $postmedia;
	}

	/**
	 * Returns an array of link objects for an sbp post. Optionally you can filter the links to get a certain type.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param string $linkType The type of links to get.
	 * @return array $links Either an array of links objects or an empty array.
	 */
	function sbp_getLinks($linkType = '', $post = NULL) {
		if (empty($post)) $post = get_post();
		if (!Sbp_PluginBase::isSbpPost($post, 'item|client')) return NULL;

		$links		= array();
		$linkMeta	= sbp_getMeta($post->ID, $post->post_type, 'links', TRUE);

		if (!empty($linkMeta)) {
			$newLinks	= array();
			$links		= json_decode($linkMeta)->items;

			foreach($links as $link) {
				if (!empty($linkType) && $link->linkType != $linkType) continue; // Skip this link if we are looking for a specific type and this isn't it.

				$linkParts		= Sbp_PluginBase::explodeAndTrim($link->url, '|');
				$link->kind		= $linkParts[0];
				$link->url		= $linkParts[1];
				$link->nofollow = ($link->nofollow == 1) ? TRUE : FALSE;

					// Get URL image:
				if ($link->kind == 'url') {
					$urlImage		= $link->urlImage;
					$link->urlImage	= FALSE;

					if (!empty($urlImage)) {
						$imgParts = Sbp_PluginBase::explodeAndTrim($urlImage, '|');

						if (intval($imgParts[0]) > 0) {
							$link->urlImage				= get_post($imgParts[0]);
							$link->urlImage->url		= get_permalink($link->urlImage->ID);
							$link->urlImage				= sbp_addTnDataToObj($link->urlImage->ID, $link->urlImage);
						}
					}

				} else if (intval($link->url) > 0) { // Posts... get and add permalink.
					if (intval($link->url) > 0) $link->url = get_post($link->url);

					if (!empty($link->url)) {
						$link->url->url			= get_permalink($link->url->ID);
						$link->url				= sbp_addTnDataToObj($link->url->ID, $link->url);
						$link->text				= str_replace('%t%', $link->url->post_title, $link->text);			// Replace %t% marker.
						$link->description		= str_replace('%t%', $link->url->post_title, $link->description);	// Replace %t% marker.
					}
				}

				$newLinks[] = $link;
			}

			$links = $newLinks;
		}

		return $links;
	}

	/**
	 * Returns the colours swatches for a record.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param object $post An sbp_item object.
	 * @return array $colours Either an array of colours or an empty array.
	 */
	function sbp_getColours($post = NULL) {
		if (empty($post)) $post = get_post();

		$colours = sbp_getMeta($post->ID, $post->post_type, 'colours');

		if (!empty($colours)) {
			$colours	= json_decode($colours)->swatches;
			$newColours	= array();

			foreach($colours as $colour) {
				$newColours[$colour->key] = $colour->colour;
			}

			$colours = $newColours;

		} else {
			$colours = array();
		}

		return $colours;
	}

	/**
	 * Returns a meta value, either from the DB or, if already collected, from the SBP Cache.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param integer $postId The post ID.
	 * @param string $postType The post type.
	 * @param string $metaKeySuffix The meta value's key suffix.
	 * @param boolean $skipCheck Should the check for a SBP post be skipped - means already checked elsewhere.
	 * @return mixed NULL or the value.
	 */
	function sbp_getMeta($postID, $postType, $metaKeySuffix = '', $skipCheck = FALSE) {
		if ($skipCheck === TRUE || Sbp_PluginBase::isSbpPost($postType)) {
			$sbpCache	= Sbp_Cache::get();
			$metaKey	= $postType . ((!empty($metaKeySuffix)) ? '_' . $metaKeySuffix : '');
			$metaVal	= Sbp_Cache::getData($postID, $metaKey);

				// Get from DB.
			if ($metaVal === NULL) {
				$metaVal = get_post_meta($postID, $metaKey, TRUE);
				Sbp_Cache::setData($postID, $metaKey, $metaVal);
			}

			return $metaVal;
		}

		return NULL;
	}

	/**
	 * Returns the boolean state of a meta property.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param integer $postId The post ID.
	 * @param string $postType The post type.
	 * @param string $metaKeySuffix The meta value's key suffix.
	 * @return boolean $status TRUE if the boolean property is 1, else FALSE.
	 */
	function sbp_getBooleanFromMeta($postID, $postType, $metaKeySuffix) {
		$bool	= sbp_getMeta($postID, $postType, $metaKeySuffix);
		$status = FALSE;

		if ($bool == 1) $status = TRUE;

		return $status;
	}

	/**
	 * Adds thumbnail data to an object/array.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param integer $objId The main post's ID.
	 * @param mixed $obj The object array to add thumbnail data to or an actual object.
	 * @param boolean $addAllsizes Should all sizes be added?
	 * @return mixed $obj The object array to add thumbnail data to or an actual object.
	 */
	function sbp_addTnDataToObj($objId, $obj, $addAllsizes = FALSE) {
		$tnId		= ($addAllsizes === TRUE) ? $objId : get_post_thumbnail_id($objId);
		$imgData	= wp_get_attachment_image_src($tnId, 'thumbnail');

		if ($imgData !== FALSE) {
			$imgDataMedium		= ($addAllsizes === TRUE) ? wp_get_attachment_image_src($tnId, 'medium') : NULL;
			$imgDataLarge		= ($addAllsizes === TRUE) ? wp_get_attachment_image_src($tnId, 'large') : NULL;
			$imgDataOriginal	= ($addAllsizes === TRUE) ? wp_get_attachment_image_src($tnId, 'full') : NULL;

			if (is_array($obj)) {
				$obj['thumbnail'] 		= $imgData[0];
				$obj['thumbnailWidth'] 	= $imgData[1];
				$obj['thumbnailHeight'] = $imgData[2];

				if ($imgData[1] == $imgData[2]) {
					$obj['thumbnailShape'] = 'square';

				} else if ($imgData[1] > $imgData[2]) {
					$obj['thumbnailShape'] = 'landscape';

				} else {
					$obj['thumbnailShape'] = 'portrait';
				}

				if ($addAllsizes === TRUE) {
					$obj['medium']			= $imgDataMedium[0];
					$obj['mediumWidth'] 	= $imgDataMedium[1];
					$obj['mediumHeight'] 	= $imgDataMedium[2];

					if ($imgDataMedium[1] == $imgDataMedium[2]) {
						$obj['mediumShape'] = 'square';

					} else if ($imgDataMedium[1] > $imgDataMedium[2]) {
						$obj['mediumShape'] = 'landscape';

					} else {
						$obj['mediumShape'] = 'portrait';
					}

					$obj['large'] 		= $imgDataLarge[0];
					$obj['largeWidth'] 	= $imgDataLarge[1];
					$obj['largeHeight'] = $imgDataLarge[2];

					if ($imgDataLarge[1] == $imgDataLarge[2]) {
						$obj['largeShape'] = 'square';

					} else if ($imgDataLarge[1] > $imgDataLarge[2]) {
						$obj['largeShape'] = 'landscape';

					} else {
						$obj['largeShape'] = 'portrait';
					}

					$obj['original'] 		= $imgDataOriginal[0];
					$obj['originalWidth']	= $imgDataOriginal[1];
					$obj['originalHeight']	= $imgDataOriginal[2];

					if ($imgDataOriginal[1] == $imgDataOriginal[2]) {
						$obj['originalShape'] = 'square';

					} else if ($imgDataOriginal[1] > $imgDataOriginal[2]) {
						$obj['originalShape'] = 'landscape';

					} else {
						$obj['originalShape'] = 'portrait';
					}
				}

			} else {
				$obj->thumbnail			= $imgData[0];
				$obj->thumbnailWidth 	= $imgData[1];
				$obj->thumbnailHeight 	= $imgData[2];

				if ($imgData[1] == $imgData[2]) {
					$obj->thumbnailShape = 'square';

				} else if ($imgData[1] > $imgData[2]) {
					$obj->thumbnailShape = 'landscape';

				} else {
					$obj->thumbnailShape = 'portrait';
				}

				if ($addAllsizes === TRUE) {
					$obj->medium 		= $imgDataMedium[0];
					$obj->mediumWidth 	= $imgDataMedium[1];
					$obj->mediumHeight 	= $imgDataMedium[2];

					if ($imgDataMedium[1] == $imgDataMedium[2]) {
						$obj->mediumShape = 'square';

					} else if ($imgDataMedium[1] > $imgDataMedium[2]) {
						$obj->mediumShape = 'landscape';

					} else {
						$obj->mediumShape = 'portrait';
					}

					$obj->large 		= $imgDataLarge[0];
					$obj->largeWidth 	= $imgDataLarge[1];
					$obj->largeHeight 	= $imgDataLarge[2];

					if ($imgDataLarge[1] == $imgDataLarge[2]) {
						$obj->largeShape = 'square';

					} else if ($imgDataLarge[1] > $imgDataLarge[2]) {
						$obj->largeShape = 'landscape';

					} else {
						$obj->largeShape = 'portrait';
					}

					$obj->original 			= $imgDataOriginal[0];
					$obj->originalWidth 	= $imgDataOriginal[1];
					$obj->originalHeight 	= $imgDataOriginal[2];

					if ($imgDataOriginal[1] == $imgDataOriginal[2]) {
						$obj->originalShape = 'square';

					} else if ($imgDataOriginal[1] > $imgDataOriginal[2]) {
						$obj->originalShape = 'landscape';

					} else {
						$obj->originalShape = 'portrait';
					}
				}
			}
		}

		return $obj;
	}

	/**
	 * Returns a record object or a record's title (optionally wrapped and linked) based on a meta value for the current post.
	 * Used to get testimonial and client records that are related to items/clients.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param integer $postId The main post's ID.
	 * @param string $metaKey The meta key to look up.
	 * @param array $opts See sbp_getConf().
	 * @return mixed $rec Either a string or an object.
	 */
	function sbp_getRecFromMeta($postId, $metaKey, array $conf) {
		$sbpCache	= Sbp_Cache::get();
		$rec		= '';
		$recId		= sbp_getMeta($postId, $metaKey, '', TRUE);
		$recRec		= (!empty($recId)) ? get_post($recId) : NULL;

		if (!empty($recRec)) { // If there is a record:
			$rec		= $recRec;
			$rec->url	= get_permalink($recRec->ID);
		}

		return $rec;
	}

	/**
	 * Echos or returns the testimonial for an item/client, optionally linked to or the testimonial object.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param WP_Post $rec A post record.
	 * @param array $conf A config array, see sbp_getConf().
	 * @return void.
	 */
	function sbp_echoTitle(WP_Post $rec, array $conf) {
		echo $conf['before'] . '<a ' . $conf['linkAttrs'] . 'href="' . $rec->url . '">' . $rec->post_title . '</a>' . $conf['after'];
	}

	/**
	 * Returns the final conf array to be used for various SBP functions, modfied by $opts.
	 *
	 * @since 0.1.0
	 * @access public
	 * @param array $opts Output options: (
	 *		'before'	=> string Optional. Content to add before the output.
	 *		'after'		=> string Optional. Content to add after the output.
	 *		'output'	=> string Optional. Can be either 'return', or 'echo'.
	 *		'link'		=> boolean Optional. Should the post title be linked to?
	 *		'linkAttrs'	=> string Optional. Additional attributes for the link tag.
	 *		'expandRec'	=> boolean Optional. Should the object be expanded (related objects and metadata be collected and processed)?
	 * )
	 * @return array $conf The config array.
	 */
	function sbp_getConf(array $opts) {
		$conf = array(
			'before'	=> '',
			'after'		=> '',
			'output'	=> 'echo',
			'link'		=> TRUE,
			'linkAttrs'	=> '',
			'expandRec'	=> FALSE
		);

		if (!empty($opts)) { // Merge and check options.
			$conf = array_merge($conf, $opts);

				// Check conf:
			if (!is_bool($conf['expandRec'])) $conf['expandRec'] = FALSE;
			if (!is_bool($conf['link'])) $conf['link'] = TRUE;
			$conf['before']		= trim(strval($conf['before']));
			$conf['after']		= trim(strval($conf['after']));
			$conf['output']		= strtolower(trim(strval($conf['output'])));
			if ($conf['output'] != 'echo' && $conf['output'] != 'return') $conf['output'] = 'echo';
			$conf['linkAttrs']	= trim(strval($conf['linkAttrs']));
			if (!empty($conf['linkAttrs'])) $conf['linkAttrs'] .= ' ';
		}

		return $conf;
	}

	/**
	 * Returns an array of media objects for an sbp post. Optionally you can filter the media to get a certain type and or subtype (for special media types).
	 *
	 * @since 0.1.0
	 * @access public
	 * @param array $attrs The attributes array.
	 * @param integer cur The current index of a loop.
	 * @param integer max The max (last) index of a loop.
	 * @return array $attrs The attributes array, modified if needed.
	 */
	function sbp_addListAttrs(array $attrs, $cur, $max) {
		if ($cur == 1)		$attrs['class'] .= ' first';
		if ($cur == $max)	$attrs['class'] .= ' last';
		if ($max == 1)		$attrs['class'] .= ' only';

		return $attrs;
	}

	/**
	 * Returns an array of media objects for an sbp post. Optionally you can filter the media to get a certain type and or subtype (for special media types).
	 *
	 * @since 0.1.0
	 * @access public
	 * @param array $attrs The attributes array.
	 * @return array $attrs The attributes array, modified if needed.
	 */
	function sbp_getAttrStr(array $attrs) {
		$attrsStr = '';

		if (!empty($attrs)) {
			foreach($attrs as $attrKey => $attrVal) {
				$attrsStr .= ' ' . $attrKey . '="' . $attrVal . '"';
			}
		}

		return $attrsStr;
	}

	/**
	 * Adds post content to the JS posts array.
	 *
	 * @return void
	 */
	function sbp_addPostToJs() {
		$post = get_post();

		if (Sbp_PluginBase::isSbpPost($post)) { // If is sbp post type, add the single post as a javascript object:
			$postArray = (array) $post;

				// Now add meta data:
			$postArray['featured'] = ((sbp_isFeatured($post->ID, $post->post_type)) ? TRUE : FALSE);

			if ($post->post_type == 'sbp_item') {
				$postArray['inprogress']	= ((sbp_isInprogress($post->ID, $post->post_type)) ? TRUE : FALSE);
				$postArray['client']		= sbp_addPostToJs_client($post);
				$postArray['media']			= sbp_getMedia('', '', $post);
				$postArray					= sbp_addPostToJs_itemStd($post, $postArray);

			} else if ($post->post_type == 'sbp_client') {
				$postArray = sbp_addPostToJs_clientStd($post, $postArray);

			} else if ($post->post_type == 'sbp_testimonial') {
				$postArray = sbp_addPostToJs_testimonialStd($post, $postArray);
			}

				// Output as JSON:
			$output	 = 	'<script id="sbpSettings_post' . $post->ID . '" type="text/javascript">' . PHP_EOL;
			$output	.=		'window.sbpSettings.posts["post_' . $post->ID . '"] = ' . "" . json_encode($postArray, JSON_HEX_QUOT | JSON_HEX_TAG) . ";" . PHP_EOL;

				// Give a quick ref to the single post:
			if(is_single()) $output	.= 'window.sbpSettings.mainpost = window.sbpSettings.posts["post_' . $post->ID . '"];' . PHP_EOL;
			$output	.= 	'</script>' . PHP_EOL;

			echo $output;
		}
	}

	/**
	 * Returns the testimonial object for $post.
	 *
	 * @param object An sbp_* object.
	 * @return mixed NULL or a testimonial object.
	 */
	function sbp_addPostToJs_testimonial($post) {
		if (Sbp_PluginBase::isSbpPost($post)) {
			$testimonialObj = sbp_getRecFromMeta($post->ID, $post->post_type . '_testimonial', sbp_getConf(array('output' => 'return', 'expandRec' => TRUE)));

			if (!empty($testimonialObj)) {
				$testimonialArray				= sbp_addPostToJs_testimonialStd($testimonialObj, (array) $testimonialObj);
				$testimonialArray['featured']	= ((sbp_isFeatured($testimonialObj->ID, $testimonialObj->post_type)) ? TRUE : FALSE);

				return $testimonialArray;
			}
		}

		return NULL;
	}

	/**
	 * Returns the client object for $post.
	 *
	 * @param object $post An sbp_item object.
	 * @return mixed NULL or a client array.
	 */
	function sbp_addPostToJs_client($post) {
		if (Sbp_PluginBase::isSbpPost($post, 'item')) {
			$clientObj = sbp_getRecFromMeta($post->ID, $post->post_type . '_client', sbp_getConf(array('output' => 'return', 'expandRec' => TRUE)));

			if (!empty($clientObj)) {
				$clientArray				= sbp_addPostToJs_clientStd($clientObj, (array) $clientObj);
				$clientArray['featured']	= ((sbp_isFeatured($clientObj->ID, $clientObj->post_type)) ? TRUE : FALSE);

				return $clientArray;
			}
		}

		return NULL;
	}

	/**
	 * Updates the client data array with std props.
	 *
	 * @param object $clientObj A client post object.
	 * @param array $clientArray The client data array for updating.
	 * @return array $clientArray.
	 */
	function sbp_addPostToJs_clientStd($clientObj, array $clientArray) {
		$clientArray['testimonial']	= sbp_addPostToJs_testimonial($clientObj);
		$clientArray['links']		= sbp_getLinks('', $clientObj);
		$clientArray['colours']		= sbp_getColours($clientObj);
		$clientArray				= sbp_addTnDataToObj($clientObj->ID, $clientArray);

		return $clientArray;
	}

	/**
	 * Updates the item data array with std props.
	 *
	 * @param object $itemObj An item post object.
	 * @param array $itemArray The item data array for updating.
	 * @return array $itemArray.
	 */
	function sbp_addPostToJs_itemStd($itemObj, array $itemArray) {
		$itemArray['testimonial']	= sbp_addPostToJs_testimonial($itemObj);
		$itemArray['links']			= sbp_getLinks('', $itemObj);
		$itemArray['colours']		= sbp_getColours($itemObj);
		$itemArray					= sbp_addTnDataToObj($itemObj->ID, $itemArray);

			// Add API keys.
		$itemArray['flickrApiKey']	= sbp_getMeta($itemObj->ID, $itemObj->post_type, 'flickr_api');
		$itemArray['youtubeApiKey']	= sbp_getMeta($itemObj->ID, $itemObj->post_type, 'youtube_api');

		if (empty($itemArray['flickrApiKey']))	$itemArray['flickrApiKey']	= NULL;
		if (empty($itemArray['youtubeApiKey']))	$itemArray['youtubeApiKey']	= NULL;

		return $itemArray;
	}

	/**
	 * Updates the testimonial data array with std props.
	 *
	 * @param object $testimonialObj A testimonial post object.
	 * @param array $testimonialArray The testimonial data array for updating.
	 * @return array $testimonialArray.
	 */
	function sbp_addPostToJs_testimonialStd($testimonialObj, array $testimonialArray) {
		$testimonialArray['name']		= sbp_getMeta($testimonialObj->ID, $testimonialObj->post_type, 'name');
		$testimonialArray['email']		= sbp_getMeta($testimonialObj->ID, $testimonialObj->post_type, 'email');
		$testimonialArray['position']	= sbp_getMeta($testimonialObj->ID, $testimonialObj->post_type, 'position');
		$testimonialArray				= sbp_addTnDataToObj($testimonialObj->ID, $testimonialArray);

		return $testimonialArray;
	}

	/**
	 * Adds the system wide colours set in settings to JavaScript.
	 *
	 * @return void
	 */
	function sbp_addColoursToJs() {
		$colours = get_option('sbp_colours');

		if (!empty($colours)) {
			$colours	= json_decode($colours)->swatches;
			$newColours	= array();

			foreach($colours as $colour) {
				$newColours[$colour->key] = $colour->colour;
			}

			$output	 = 	'<script id="sbpSettings_colours" type="text/javascript">' . PHP_EOL;
			$output	.=		'window.sbpSettings.colours = ' . json_encode($newColours) . ';' . PHP_EOL;
			$output	.= 	'</script>' . PHP_EOL;

			echo $output;
		}
	}
?>