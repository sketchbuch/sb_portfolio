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
 * A switch widget for SB Portfolio for WordPress.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.switches', {
		options: {
			/**
			* Called on change.
			* 
			* @param integer curVal The current value, either 1 or 0.
			*/
			changed: $.noop
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			var switchButtons	= this.element.find('.sbpSwitchButton');
			var selected		= switchButtons.filter('.selected');
			this.switchSlider 	= this.element.find('.sbpSwitchSlider');
			
			
			this._setSlider(selected);
			this._bindButtons(switchButtons);
		},
		
		/**
		* Initialisation, called after _create and and on later widget calls.
		*/
		_init: function() {
		},
		
		/**
		* Destructor, cleanup when no longer needed.
		*/
		_destroy: function() {
			this.element.find('.dataAdded').removeData();
			this.element.off().find('.bound').off();
		},
		
		
		
		/* Switch Methods */
		/* -------------- */
		
		/**
		* Binds the switch buttons.
		* 
		* @param jQuery switchButtons The switch buttons.
		*/
		_bindButtons: function(switchButtons) {
			var widget			= this;
			var switchSlider	= this.switchSlider;
			var switchInput		= this.element.find('input');
			
			if (this.element.hasClass('bound')) return;
			
			this.element.addClass('bound').on('click.switches', function(){
				var selected	= switchButtons.filter('.selected');
				var unselected	= selected.siblings('.sbpSwitchButton');
				
				selected.removeClass('selected');
				unselected.addClass('selected');
				switchInput.prop('checked', true);
				
				if (unselected.hasClass('sbpSwitchButton-on')) {
					switchInput.val(1);
					
				} else {
					switchInput.val(0);
				}
				
				widget._setSlider(selected, switchSlider);
				widget.options.changed(switchInput.val());
			});
		},
		
		/**
		* Gives the slider the correct class.
		* 
		* @param jQuery selected The selected slider button.
		* @param jQuery switchSlider The switch's slider.
		*/
		_setSlider: function(selected, switchSlider) {
			var initialSet = false;
			
			if (switchSlider === undefined) {
				switchSlider	= this.switchSlider;
				initialSet		= true;
			}
			
			if ((initialSet && selected.hasClass('sbpSwitchButton-on')) || (!initialSet && selected.hasClass('sbpSwitchButton-off'))) {
				switchSlider.addClass('on').removeClass('off');
				
			} else {
				switchSlider.addClass('off').removeClass('on');
			}
		}
	});
} (jQuery));