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
 * The main SbpAdmin class.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 * @param jQuery $ The jQuery object, so that $ works within the class.
 * @param object settings Settings for SbpAdmin to over-ride default settings, if needed.
 */
function SbpAdmin($, settings) {
	'use_strict';
	
	
	
	/* Settings and store */
	/* ------------------ */
	
	var self		= this;
	var settings	= $.extend(true, {
		metaboxes: {
			prefix:'sbpMetaBox-'
		}
	}, settings);
	
	
	
	/* Initialisation Functions */
	/* ------------------------ */
	
	/**
	* Creates widgets.
	*/
	function widgetSetup() {
		widgetCreate('recordSelector', $('input.sbpRecordSelector'));
		widgetCreate('postMedia', $('div.sbpPostMedia'));
		widgetCreate('sbpLinks', $('div.sbpLinks'));
		widgetCreate('switches', $('span.sbpSwitch'));
		widgetCreate('textList', $('div.sbpTextList'));
		widgetCreate('palettes', $('div.sbpPalette'));
		widgetCreate('paletteKeys', $('div.sbpPaletteKeys'));
		widgetCreate('clientItems', $('div.sbpClientItems'));
	}
	
	/**
	* Creates a widget.
	*/
	function widgetCreate(widgetName, elements) {
		if (elements.length < 1 || widgetName === undefined) return;
		
		if ($.isFunction($.fn[widgetName])) {
			elements.each(function(){
				var configName		= $(this).attr('data-config');
				var specificConfig	= (configName !== undefined && window.sbpSettings.guiConfig.specific[configName] !== undefined) ? window.sbpSettings.guiConfig.specific[configName] : {};
				var widgetConfig	= (window.sbpSettings.guiConfig.default[widgetName] !== undefined) ? window.sbpSettings.guiConfig.default[widgetName] : {};
				var config			= {};
				
				config = $.extend(true, config, widgetConfig, specificConfig);
				$(this)[widgetName](config);
			});
		}
	}
	
	/**
	* Initialise
	*/
	function init() {
		widgetSetup();
	}
	
	init();
}