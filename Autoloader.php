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
	 * Loads all classes for sb_portfolio, this currently loads them alphabetically, so make sure your base classes
	 * are named in away so that any extending them are included after them! Or create subfolders for extending classes.
	 *
	 * Interfaces and abstract classes are now loaded first, this done by looking for "Abstract" at the beginning of the class, 	 
	 * or  "Interface" at the end of the class.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	class Sbp_Autoloader {
		
		/**
		 * Where includable files are stored.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var array
		 */
		const BASE_FOLDERS = array('classes/shared', 'classes', 'addons');
		
		/**
		 * The maximum number of folders deep to look in (to stop infinite searching)
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var integer
		 */
		const MAX_DEPTH = 10;
		
		/**
		 * The type of autoload.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var string
		 */
		protected $type = '';
		
		/**
		 * Interfaces that need loading, found by looking for "Interface" at the end of the class name.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var array
		 */
		protected $interfaceClasses = array();
		
		/**
		 * Abstract classes that need loading, found by looking for "Abstract" at the beginning of the class name.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var array
		 */
		protected $abstractClasses = array();
		
		/**
		 * Normal classes that need loading, anything not added to $interfaceClasses and $abstractClasses.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @var array
		 */
		protected $normalClasses = array();
		
		
		
		/**
		 * Constructor.
		 *
		 * @param string $type The type of autolading.
		 * @return void
		 */
		public function __construct($type = '') {
			$this->type = $type;
			
			if (!empty($this->type)) {
				foreach (self::BASE_FOLDERS as $folder) {
					$this->findClasses(SBP_FOLDER . self::BASE_FOLDERS[$folder]);
				}
				
				$this->includeClasses();
			}
		}
		
		/**
		 * Includes found classes.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @param string $filePath The folder to be looked in.
		 * @return void.
		 */
		protected function includeClasses() {
			if (!empty($this->interfaceClasses)) {
				foreach ($this->interfaceClasses as $file) {
					require_once($file);
				}
			}
			
			if (!empty($this->abstractClasses)) {
				foreach ($this->abstractClasses as $file) {
					require_once($file);
				}
			}
			
			if (!empty($this->normalClasses)) {
				foreach ($this->normalClasses as $file) {
					require_once($file);
				}
			}
		}
		
		/**
		 * A recursive function that looks for php files and sorts them into their correct storage array.
		 *
		 * @since 0.1.0
		 * @access protected
		 * @param string $filePath The folder to be looked in.
		 * @return void.
		 */
		protected function findClasses($filePath, $currentDepth = 0) {
			if ($filePath == 'Classes') $filePath .= '/' . $this->type;
			
			$files = scandir($filePath);
			$files = array_values(array_diff($files, array('.', '..'))); // Remove the . and .. folders and re-index
			
			foreach ($files as $file) {
				$fullFilePath = $filePath . DIRECTORY_SEPARATOR . $file;
				
				if (is_dir($fullFilePath)) { // If it is a folder, scan it
					if ($currentDepth < self::MAX_DEPTH) {
						$newDepth = $currentDepth + 1;
						$this->findClasses($fullFilePath, $newDepth);
					}
					   
				} else { // Else see if it is a php file
					$pathInfo = pathinfo($fullFilePath);
					
					if ($pathInfo['extension'] == 'php') {
						if (substr($pathInfo['filename'], -9) === 'Interface') {
							$this->interfaceClasses[] = $fullFilePath;
							
						} else if (substr($pathInfo['filename'], 0, 8) === 'Abstract') {
							$this->abstractClasses[] = $fullFilePath;
							
						} else {
							$this->normalClasses[] = $fullFilePath;
						}
					}
				}
			}
		}
	}
?>