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
	 * Adds a new record to the database.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_AddRecord extends AbstractAjax {
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		
		
		/**
		 * Creates a new record and then returns information about it including the ID.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return array The results.
		 */
		public function addRecord() {
			global $wpdb;
			
			$newTitle	= wp_strip_all_tags($this->vars['newTitle']);
			$newPostId	= wp_insert_post(array(
				'post_title'	=> $newTitle,
				'post_content'	=> '',
				'post_status'	=> 'pending',
				'post_type'		=> $this->vars['subType']
			));
			
			if ($newPostId > 0) {
				return array(
					'ID'			=> $newPostId,
					'post_title'	=> $newTitle,
					'post_type'		=> $this->vars['subType']
				);
				
			} else {
				return array($newPostId);
			}
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
					$result = $this->addRecord();
					
					if (isset($result['ID'])) {
						$jsonObj['error']			= FALSE;
						$jsonObj['errorMessage']	= '';
						
					} else {
						$jsonObj['errorMessage'] = 'Unable to create a new record'; // Maybe look at returning the WP error message if there is one.
					}
					
					$jsonObj['result'] = $result;
					
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
			$this->vars['newTitle']	= sanitize_text_field(trim(Sbp_PluginBase::varSubmitted('newTitle')));
			$this->vars['subType']	= sanitize_key(trim(Sbp_PluginBase::varSubmitted('subType')));
			$this->vars['postId']	= intval(Sbp_PluginBase::varSubmitted('postId'));
		}
		
		/**
		 * Returns true if vars are ok for this request.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return boolean TRUE if all vars are ok.
		 */
		protected function varsOk() {
			if (isset($this->vars['newTitle']) && !empty($this->vars['newTitle'])) {
				if (isset($this->vars['subType']) && !empty($this->vars['subType'])) {
					if ($this->vars['postId'] > 0) return TRUE;
				}
			}
			
			return FALSE;
		}
	}
?>