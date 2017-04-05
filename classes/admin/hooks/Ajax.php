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
	 * Contains all AJAX related functions for SB Portfolio Admin.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Actions_Ajax {
		
		/**
		 * Searches for a record in WordPress.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public function searchRecord() {
			$newAjax = new Sbp_SearchRecord();
			
			echo $newAjax->execute();
			
			wp_die();
		}
		
		/**
		 * Searches for a record in WordPress.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public function addRecord() {
			$newAjax = new Sbp_AddRecord();
			
			echo $newAjax->execute();
			
			wp_die();
		}
		
		/**
		 * Returns all items that are for a client.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public function sbpGetClientItems() {
			$newAjax = new Sbp_GetClientItems();
			
			echo $newAjax->execute();
			
			wp_die();
		}
		
		/**
		 * Updates an item for toggle buton actions in the client items list.
		 *
		 * @since 0.1.0
		 * @access public
		 * @return void
		 */
		public function sbpUpdateClientItem() {
			$newAjax = new Sbp_UpdateClientItem();
			
			echo $newAjax->execute();
			
			wp_die();
		}
	}
?>