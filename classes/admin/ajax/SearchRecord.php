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
	 * Finds records in the database and returns the results.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_SearchRecord extends AbstractSearch implements SearchInterface {
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
		 * @return array The results.
		 */
		public function getRecords() {
			global $wpdb;
			
			$searchingId	= FALSE;
			$where			= $wpdb->posts . '. post_title' . ' LIKE %s';
			
				// Change stuff if the search term is an integer number:
			if (is_numeric($this->vars['term'])) {
				$intTerm = intval($this->vars['term']);
				
				if ($intTerm > 0) {
					$searchingId	= TRUE;
					$where			= $wpdb->posts . '.ID' . ' = ' . $intTerm;
				}
			}
			
			$query = implode(' ', array(
				'SELECT ID, post_title, post_type, post_status',
				'FROM ' . $wpdb->posts,
				'WHERE ' . $where,
				'AND ' . $wpdb->posts . '.post_type ' . ' = %s',
				'AND (' . $wpdb->posts . ".post_status = 'publish'",
				'OR ' . $wpdb->posts . ".post_status = 'draft'",
				'OR ' . $wpdb->posts . ".post_status = 'pending'",
				'OR ' . $wpdb->posts . ".post_status = 'trash')",
				'ORDER BY post_date DESC'
			));
			
			if ($searchingId) {
				return $wpdb->get_results($wpdb->prepare($query, array($this->vars['subType'])));
				
			} else {
				return $wpdb->get_results($wpdb->prepare($query, array('%' . $this->vars['term'] . '%', $this->vars['subType'])));
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
					$result = $this->getRecords();
					
					if (is_array($result)) {
						$jsonObj['error']			= FALSE;
						$jsonObj['errorMessage']	= '';
						
						foreach ($result as $record) {
							$tnId = get_post_thumbnail_id($record->ID);
							
							if (empty($tnId)) continue;
							$tnData = wp_get_attachment_image_src($tnId);
							$record->tn = array(
								'ID'		=> $tnId,
								'url'		=> $tnData[0],
								'width'		=> $tnData[1],
								'height'	=> $tnData[2],
								'alt'		=> get_post_meta($tnId, '_wp_attachment_image_alt', TRUE)
							);
						}
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
			$this->vars['term']		= trim(sanitize_text_field(Sbp_PluginBase::varSubmitted('term')));
			$this->vars['subType']	= trim(sanitize_key(Sbp_PluginBase::varSubmitted('subType')));
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
			if (isset($this->vars['term']) && !empty($this->vars['term'])) {
				if (isset($this->vars['subType']) && !empty($this->vars['subType'])) {
					if ($this->vars['postId'] > 0) return TRUE;
				}
			}
			
			return FALSE;
		}
	}
?>