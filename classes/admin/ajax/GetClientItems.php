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
	 * Returns a list of items that belong to a client.
	 *
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_GetClientItems extends AbstractAjax {
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}



		/**
		 * Gets a list of matching records.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return array $result The results.
		 */
		public function getRecords() {
			global $wpdb;

			$limit = $this->vars['perPage'];
			if ($this->vars['page'] > 1) $limit = (($this->vars['page'] - 1) * $this->vars['perPage']) . ',' . $this->vars['perPage'];
			
			$result = $wpdb->get_results("SELECT posts.ID, posts.post_title, posts.post_status
				FROM $wpdb->postmeta postmeta
				JOIN $wpdb->posts posts ON (posts.ID = postmeta.post_id)
				WHERE postmeta.meta_key	= 'sbp_item_client'
				AND postmeta.meta_value	= " . $this->vars['postId'] . "
				AND posts.post_type		= 'sbp_item'
				AND (posts.post_status = 'publish' OR posts.post_status = 'draft' OR posts.post_status = 'pending' OR posts.post_status = 'trash')
				ORDER BY post_title ASC
				LIMIT " . $limit . " 
			");

			if (!empty($result)) { // Set some additional props:
				$statuses = get_post_statuses();
				
				foreach($result as $post) {
					$post->thumbnail	= wp_get_attachment_thumb_url(get_post_thumbnail_id($post->ID));
					$post->viewLink		= get_permalink($post->ID);
					$post->editLink		= get_edit_post_link($post->ID, 'JSON');
					$post->inprogress	= (get_post_meta($post->ID, 'sbp_item_inprogress', TRUE)) ? TRUE : FALSE;
					$post->featured		= (get_post_meta($post->ID, 'sbp_item_featured', TRUE)) ? TRUE : FALSE;
					
					if (isset($statuses[$post->post_status])) {
						$post->post_statusLabel = $statuses[$post->post_status];
						
					} else if ($post->post_status == 'trash') {
						$post->post_statusLabel = __('Trashed', 'sbp');
						
					} else {
						$post->post_statusLabel = $post->post_status;
					}
				}
			}

			return $result;
		}

		/**
		 * Returns a JSON object.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return json The response.
		 */
		public function execute() {
			$jsonObj = $this->getDefaultResponse();

			if ($this->securityCheck === TRUE) {
				if ($this->varsOk()) {
					$result = $this->getRecords();

					if (is_array($result)) {
						$jsonObj['error']			= FALSE;
						$jsonObj['errorMessage']	= '';
					}

					$jsonObj['result'] = $result;
					$jsonObj['temp'] = get_post_statuses();

				} else {
					$jsonObj['errorMessage'] = __('One or more arguments are MIA', 'sbp');
				}

			} else {
				$jsonObj['errorMessage'] = __('Security check failed', 'sbp');
			}

			if (!empty($this->debug)) $jsonObj['debug'] = $this->debug;

			return json_encode($jsonObj);
		}

		/**
		 * Get vars specific to this AJAX request. OVERWRITE in extending class, if needed.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		protected function getSpecificVars() {
			$this->vars['postId']	= intval(Sbp_PluginBase::varSubmitted('postId'));
			$this->vars['page']		= intval(Sbp_PluginBase::varSubmitted('page'));
			$this->vars['perPage']	= intval(Sbp_PluginBase::varSubmitted('perPage'));
		}

		/**
		 * Returns true if vars are ok for this request.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return boolean TRUE if all vars are ok.
		 */
		protected function varsOk() {
			if ($this->vars['postId'] > 0 && $this->vars['page'] > 0 && $this->vars['perPage'] > 0) return TRUE;

			return FALSE;
		}
	}
?>