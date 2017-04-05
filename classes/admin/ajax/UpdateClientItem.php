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
	class Sbp_UpdateClientItem extends AbstractAjax {
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		
		
		/**
		 * Updates the record's meta data.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return array The results.
		 */
		public function updateRecord() {
			$metaKey = 'sbp_item_' . $this->vars['toggleType'];
			$metaVal = 1;
			
			if ($this->vars['toggleType'] == 'disassociate') {
				$metaKey = 'sbp_item_client';
				$metaVal = $this->vars['postId'];
			}
			
			if ($this->vars['toggleState'] == 'true') {
				update_post_meta($this->vars['togglePostId'], $metaKey, $metaVal);
				
			} else {
				delete_post_meta($this->vars['togglePostId'], $metaKey);
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
					$result						= $this->updateRecord();
					$jsonObj['error']			= FALSE;
					$jsonObj['errorMessage']	= '';
					$jsonObj['result']			= $result;

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
			$this->vars['postId']		= intval(Sbp_PluginBase::varSubmitted('postId'));
			$this->vars['togglePostId']	= intval(Sbp_PluginBase::varSubmitted('togglePostId'));
			$this->vars['toggleType']	= trim(sanitize_key(Sbp_PluginBase::varSubmitted('toggleType')));
			$this->vars['toggleState']	= trim(sanitize_key(Sbp_PluginBase::varSubmitted('toggleState')));
			
			if ($this->vars['toggleState'] == 'true') {
				$this->vars['toggleState'] = TRUE;
				
			} else {
				$this->vars['toggleState'] = FALSE;
			}
		}

		/**
		 * Returns true if vars are ok for this request.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return boolean TRUE if all vars are ok.
		 */
		protected function varsOk() {
			if ($this->vars['postId'] > 0 && $this->vars['togglePostId'] > 0 && !empty($this->vars['toggleType'])) return TRUE;

			return FALSE;
		}
	}
?>