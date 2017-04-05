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
	 * A base class for all settings pages to extend from.
	 * 
	 * @author Stephen Bungert <hello@stephenbungert.com>
	 * @package WordPress
	 * @subpackage SB Portfolio
	 * @since 0.1.0
	 */
	abstract class Sbp_Pages_AbstractSettingsPage extends Sbp_Pages_AbstractPage {
		
		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		
		
		/**
		 * Renders setting tab buttons.
		 *
		 * @return void
		 */
		public static function tabButtons($tabs = array()) {
			if (empty($tabs)) return;
			
			$output = '<h2 class="nav-tab-wrapper">' . PHP_EOL;
			
			foreach ($tabs as $tabKey => $tab) {
				$selected	= ($tab['active'] === TRUE) ? ' nav-tab-active' : '';
				$output	   .= '<a href="' . $tab['href'] . $tabKey . '" class="nav-tab' . $selected . '">' . $tab['label'] . '</a>' . PHP_EOL;
			}
			
			$output .= '</h2>' . PHP_EOL;
			
			echo $output;
		}
		
		
		
		/* Switch Methods */
		/* -------------- */
		
		/**
		 * Renders a switch field.
		 *
		 * @since 0.1.0
		 * @access public
		 * @param array $options Options for creating the switch.
		 * @return void
		 */
		public static function renderSwitch(array $options) {
			if (empty($options)) return;
			
			$switchClasses	= ' off';
			$switchVal		= 'value="0" ';
			$onClasses		= '';
			$offClasses		= '';
			
			if (!empty($options['switchVal']) && intval($options['switchVal']) == 1) {
				$switchVal		= 'value="1" ';
				$onClasses		= ' selected';
				$switchClasses	= ' on';
				
			} else {
				$offClasses	= ' selected';
			}
			
			$output  = 	'<span class="sbpSwitch sbpSwitchSettings" data-config="switch' .  $options['name'] . '">';
			$output	.= 		'<label class="sbpSwitchCol sbpSwitchCol-label">' .  $options['label'] . '</label>' . PHP_EOL;
			$output	.= 		'<span class="sbpSwitchCol sbpSwitchCol-button">' . PHP_EOL;
			$output	.= 			'<span class="sbpSwitchSlider' . $switchClasses . '"><span class="sbpSwitchHandle"></span>&nbsp;</span>' . PHP_EOL;
			$output	.= 			'<span class="sbpSwitchButton sbpSwitchButton-on' . $onClasses . '">' . PHP_EOL;
			$output	.= 				'<span class="sbpSwitchLabel">' .  $options['labelOn'] . '</span>' . PHP_EOL;
			$output	.= 			'</span>' . PHP_EOL;
			$output	.= 			'<span class="sbpSwitchButton sbpSwitchButton-off' . $offClasses . '">' . PHP_EOL;
			$output	.= 				'<span class="sbpSwitchLabel">' . $options['labelOff'] . '</span>' . PHP_EOL;
			$output	.= 			'</span>' . PHP_EOL;
			$output	.= 			'<input ' . $switchVal . 'type="hidden" id="' .  $options['name'] . 'Input" name="' .  $options['name'] . '">' . PHP_EOL;
			$output	.= 		'</span>' . PHP_EOL;
			$output	.= 	'</span>' . PHP_EOL;
			
			if (isset($options['echo'])) {
				echo $output;
				
			} else {
				return $output;
			}
		}
	}
?>