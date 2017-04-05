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
	 * A class to store data so that it can be accessed again without querying the database or being processed.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Cache {
		/**
		 * @var Sbp_Cache A reference to this class (this is a singleton).
		 */
		private static $instance;
		
		/**
		 * @var array An array to store data. All data for a post is stored in it's ID within this property.
		 */
		private static $data = array();
		
		
		
		/**
		 * Constructor.
		 * 
		 * Protected constructor to prevent creating a new instance of the with "new".
		 *
		 * @return void
		 */
		protected function __construct() {
		}
		
		
		
			/* Singleton Methods */
			/* ----------------- */
			
		/**
		 * Private clone method to prevent cloning of the instance.
		 *
		 * @return void
		 */
		private function __clone() {
		}
		
		/**
		 * Private unserialize method to prevent unserializing of the instance.
		 *
		 * @return void
		 */
		private function __wakeup() {
		}
		
		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Sbp_Cache Ths instance.
		 */
		public static function get() {
		    if (static::$instance === NULL) static::$instance = new Sbp_Cache();
		    
		    return static::$instance;
		}
		
		
		
			/* Cache Methods */
			/* ------------- */
			
		/**
		 * Sets a key for postId in the data array.
		 *
		 * @param integer $postId The post ID this data is for.
		 * @param array $key A key for the value to be stored under.
		 * @param array $value The value to be stored.
		 * @return boolean Returns TRUE if data stored OK, else FALSE.
		 */
		public static function setData($postId, $key, $value) {
			$postId	= intval($postId);
			$key	= trim($key);
			
			if ($postId > 0 && !empty($key)) {
				if (!isset(static::$data['post_' . $postId])) static::$data['post_' . $postId] = array();
				
				static::$data['post_' . $postId][$key] = $value;
				return TRUE;
			}
			
			return FALSE;
		}
		
		/**
		 * Gets a key from data array for the postId.
		 *
		 * @param integer $postId The post ID for the data required.
		 * @param array $key A key within the data that should be returned.
		 * @return mixed Either the data, or NULL.
		 */
		public static function getData($postId, $key) {
			$postId	= intval($postId);
			$key	= trim($key);
			
			if ($postId > 0 && isset(static::$data['post_' . $postId])) {
				if (!empty($key) && isset(static::$data['post_' . $postId][$key])) return static::$data['post_' . $postId][$key];
			}
			
			return NULL;
		}
		
		/**
		 * Gets all data from the data array for the postId.
		 *
		 * @param integer $postId The post ID for the data required.
		 * @return mixed Either the data, or NULL.
		 */
		public static function getPost($postId) {
			$postId = intval($postId);
			
			if ($postId > 0 && isset(static::$data['post_' . $postId])) return static::$data['post_' . $postId];
			
			return NULL;
		}
		
		/**
		 * Gets all the data.
		 *
		 * @return array The data array.
		 */
		public static function getAll() {
			return static::$data;
		}
	}
?>