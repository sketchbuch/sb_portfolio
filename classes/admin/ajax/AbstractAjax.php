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
	
	#define( 'SHORTINIT', true ); // Speed up initialising WP, we don't need everything started!
	
		// Includes
	#require_once('../Utility/PluginBase.php');
	#require_once('../Db/Connector.php');
	#require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	
	
	
	/**
	 * An asbtract class for AJAX responses to build upon.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	abstract class AbstractAjax implements AjaxInterface {
		
		/**
		 * Stores incoming vars for the request.
		 *
		 * @since 0.1.0
		 * @access public
		 * @var array
		 */
		public $vars = array();
		
		/**
		 * Stores anything for outputting. Only use in testing.
		 *
		 * @since 0.1.0
		 * @access public
		 * @var array
		 */
		public $debug = array();
		
		/**
		 * Has the AJAX request passed the security check?
		 *
		 * @since 0.1.0
		 * @access public
		 * @var boolean
		 */
		public $securityCheck = FALSE;
		
		
		
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->getVars();
			
			$securityTest = check_ajax_referer('update-post_' . $this->vars['postId'], 'security', FALSE);
			
			if ($securityTest == 1 || $securityTest == 2) {
				$this->securityCheck = TRUE;
				
			} else {
				$this->securityCheck = FALSE;
			}
		}
		
		/**
		 * Get vars that all AJAX requests should contain.
		 * 
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public function getVars() {
			$this->vars['security']	= trim(sanitize_text_field(Sbp_PluginBase::varSubmitted('security')));
			$this->vars['action']	= trim(sanitize_key(Sbp_PluginBase::varSubmitted('action')));
			
			$this->getSpecificVars();
		}
		
		/**
		 * Get vars specific to this AJAX request. OVERWRITE in extending class, if needed.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @return void
		 */
		protected function getSpecificVars() {
		}
		
		/**
		 * Returns an array to be used as the default response.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return array.
		 */
		public function getDefaultResponse() {
			return array(
				'error'			=> TRUE,
				'errorMessage'	=> __('An unknown error occured', 'sbp'),
				'vars'			=> $this->vars
			);
		}
	}
?>