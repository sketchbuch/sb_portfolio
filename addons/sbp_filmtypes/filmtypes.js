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
 * Javascript required to add daily motion support to postmedia hosted videos.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	window.sbpPostmediaHostedvideo_daily_motion = function(baseItem, item){
		return $.extend({
			ownerId:			item.user,
			title:				window.sbpSettings.hooks.postmedia.widget.fixStr(item.title),
			description:		window.sbpSettings.hooks.postmedia.widget.fixStr(item.description),
			url:				item.url,
			embed_url:			item.embed_url,
			thumbnail:			item.thumbnail_url,
			medium:				item.thumbnail_120_url,
			large:				item.thumbnail_480_url,
			original:			item.thumbnail_720_url
		}, baseItem);
	}
} (jQuery));