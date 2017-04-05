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
 * Javascript required to add flickr support to postmedia.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */

(function($, undefined) {
	'use_strict';
	
	if (window.sbpSettings !== undefined && window.sbpSettings.hooks !== undefined) {
		if (window.sbpSettings.hooks.postmedia === undefined)			window.sbpSettings.hooks.postmedia			= {};
		if (window.sbpSettings.hooks.postmedia.buttons === undefined)	window.sbpSettings.hooks.postmedia.buttons	= {};
		
		if (window.sbpSettings.hooks.postmedia.buttons.flickr === undefined) {
			window.sbpSettings.hooks.postmedia.buttons.flickr = {
				/* Required Functions */
				/* ------------------ */
				
				/**
				* Sets up flickr content.
				*/
				setup: function() {
					var widget			= window.sbpSettings.hooks.postmedia.widget;
					var addMediaContent = widget.addMediaPanes;
					if (addMediaContent === undefined)	return;
					if (addMediaContent.length < 1)		return;
					
					var flickrContent = addMediaContent.filter('.addMediaContent-flickr').not('.setup');
					if (flickrContent.length < 1) return;
					
					flickrContent.addClass('setup');
					
					var searchBox	= flickrContent.find('input.sbpRecordSearchTerm');
					var addBut		= searchBox.siblings('.button').not('.bound');
					var postType	= searchBox.siblings('.sbpLinkPostType');
					
						// Bind search.
					this.sbpFlickr_bindSearchField(searchBox);
					
						// Bind add button.
					addBut.addClass('bound').on('click', function(event){
						event.preventDefault();
						event.stopPropagation();
						
						if ($(this).hasClass('working')) return;
						
						var flickrStr	= $.trim(searchBox.val());
						var flickrId	= '';
						var flickrType	= postType.val();
						var isUrl		= true;
						
						if (flickrStr.indexOf('/') > -1) { // If there is a slash, assume a URL.
							if (flickrType == 'flickr_file') {
								var fRegex	= /(photos\/[^/]+\/)(\w+)/g;
								var matches	= fRegex.exec(flickrStr);
								
								if (matches !== null && matches.length > 2) flickrId = $.trim(matches[2]);
								
							} else {
								var fRegex	= /((albums|sets)\/)(\w+)/g;
								var matches	= fRegex.exec(flickrStr);
								
								if (matches !== null && matches.length > 2) flickrId = $.trim(matches[3]);
							}
							
						} else { // Probably ID.
							flickrId	= flickrStr;
							isUrl		= false;
						}
						
						if (flickrId != '') { // Get photo information.
							$(this).addClass('working');
							$(this).blur();
							
							var requestUrl	= 'https://api.flickr.com/services/rest/?method=flickr.photosets.getInfo&nojsoncallback=1&format=json';
							var requestData	= {photoset_id: flickrId};
							var errType		= 'album';
							
							if (flickrType == 'flickr_file') {
								requestUrl	= 'https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&nojsoncallback=1&format=json';
								requestData = {photo_id: flickrId};
								errType		= 'photo';
							}
							
							window.sbpSettings.hooks.postmedia.buttons.flickr.sbpFlickr_request({
								'url':	requestUrl,
								'data': requestData
							}, {
								'success':
									function(ajaxData, options){
										if (flickrType == 'flickr_file' && ajaxData.photo !== undefined) {
													widget._mediaBrowserAdd([{
														special:		options.specialType,
														farm:			ajaxData.photo.farm,
														server:			ajaxData.photo.server,
														mediaId:		ajaxData.photo.id,
														secret:			ajaxData.photo.secret,
														media:			ajaxData.photo.media,
														ownerId:		ajaxData.photo.owner.nsid,
														title:			widget.fixStr(ajaxData.photo.title._content),
														description:	widget.fixStr(ajaxData.photo.description._content),
														url:			ajaxData.photo.urls.url[0]._content,
														thumbnail:		'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_s.jpg',
														medium:			'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_c.jpg',
														large:			'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_h.jpg',
														original:		'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_o.jpg',
														srcString:		options.flickrStr,
														searchString:	flickrId
													}]);
													widget._mediaBrowserUpdateValue();
													options.flickrField.trigger('reset');
													
										} else if (flickrType == 'flickr_set' && ajaxData.photoset !== undefined) {
											window.sbpSettings.hooks.postmedia.buttons.flickr.sbpFlickr_setInfoSuccess(ajaxData, options);
											
										} else {
											options.error('Flickr successfully contacted, but no ' + errType + ' data was returned');
										}
									},
								'error':			function(errMsg){searchBox.trigger('error', errMsg);},
								'complete':			function(){addBut.removeClass('working');},
								'flickrStr':		flickrStr,
								'flickrField':		searchBox,
								'flickrAddBut':		addBut,
								'specialType':		flickrType,
								'flickrId':			flickrId,
								'flickrStrWasUrl':	isUrl
							});
						}
					});
				},
				
				
				
				/* Flickr Specific Functions */
				/* ------------------------- */
				
				/**
				* Done if the photoset primary photo is returned OK.
				* 
				* @param object ajaxData Data returned by the AJAX request.
				* @param object options Options for the AJAX request.
				*/
				sbpFlickr_setPrimaryPhotoSuccess: function(ajaxData, options) {
					var mediaData = {
						special:		options.specialType,
						mediaId:		options.photoset.id,
						ownerId:		options.photoset.owner,
						photo:			{
							id:				ajaxData.photo.id,
							farm:			ajaxData.photo.farm,
							secret:			ajaxData.photo.secret,
							server:			ajaxData.photo.server,
							title:			window.sbpSettings.hooks.postmedia.widget.fixStr(ajaxData.photo.title._content),
							url:			ajaxData.photo.urls.url[0]._content,
							description:	window.sbpSettings.hooks.postmedia.widget.fixStr(ajaxData.photo.description._content)
						},
						title:			window.sbpSettings.hooks.postmedia.widget.fixStr(options.photoset.title._content),
						description:	window.sbpSettings.hooks.postmedia.widget.fixStr(options.photoset.description._content),
						url:			'https://www.flickr.com/photos/' + options.photoset.username + '/albums/' + options.photoset.id,
						thumbnail:		'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_s.jpg',
						medium:			'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_c.jpg',
						large:			'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_h.jpg',
						original:		'https://farm' + ajaxData.photo.farm + '.staticflickr.com/' + ajaxData.photo.server + '/' + ajaxData.photo.id + '_' + ajaxData.photo.secret + '_o.jpg',
						srcString:		options.flickrStr,
						searchString:	options. flickrId
					};
					
					
					if (options.flickrStrWasUrl === true) mediaData.url = options.flickrStr;
					
					window.sbpSettings.hooks.postmedia.widget._mediaBrowserAdd([mediaData]);
					window.sbpSettings.hooks.postmedia.widget._mediaBrowserUpdateValue();
					options.flickrField.trigger('reset');
				},
				
				/**
				* Done if the photoset info is returned OK.
				* 
				* @param object ajaxData Data returned by the AJAX request.
				* @param object options Options for the AJAX request.
				*/
				sbpFlickr_setInfoSuccess: function(ajaxDataInfo, options) {
						// Not get primary photo data...
					this.sbpFlickr_request({
						'url':	"https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&nojsoncallback=1&format=json",
						'data':	{photo_id: ajaxDataInfo.photoset.primary}
					}, {
						'success':
							function(ajaxData, options){
								if (ajaxData.photo !== undefined) {
									window.sbpSettings.hooks.postmedia.buttons.flickr.sbpFlickr_setPrimaryPhotoSuccess(ajaxData, options);
									
								} else {
									options.error("Flickr successfully contacted, but no photo object was returned for album's primary image");
								}
							},
						'error':			function(errMsg){options.flickrField.trigger('error', errMsg);},
						'complete':			function(){options.flickrAddBut.removeClass('working');},
						'flickrStr':		options.flickrStr,
						'flickrField':		options.flickrField,
						'specialType':		'flickr_set',
						'flickrId':			options.flickrId,
						'photoset':			ajaxDataInfo.photoset,
						'flickrStrWasUrl':	options.flickrStrWasUrl
					});
				},
				
				/**
				* Binds a flickr search field.
				* 
				* @param jQuery flickrField The flickr field.
				*/
				sbpFlickr_bindSearchField: function(flickrSearchField) {
					if (flickrSearchField.hasClass('bound')) return;
					
					var ajaxError	= flickrSearchField.siblings('.sbpAjaxError');
					var errTimeout	= window.sbpSettings.hooks.postmedia.widget.options.errTimeout;
					
					flickrSearchField.addClass('bound').on('error', function(event, errorMessage){
						$(this).trigger('reset');
						ajaxError.html(errorMessage).removeClass('hidden');
						
						setTimeout(function() {
							ajaxError.addClass('hidden').empty();
						}, errTimeout);
						
					}).on('reset', function(event, errorMessage){
						$(this).val('');
					});
				},
				
				/**
				* Makes a request to flickr.
				* 
				* @param object conf The AJAX config.
				* @param object options Additonal options.
				*/
				sbpFlickr_request: function(conf, options) {
					var successCallback		= (options.success !== undefined && $.isFunction(options.success))		? options.success	: $.noop;
					var errorCallback		= (options.error !== undefined && $.isFunction(options.error))			? options.error		: $.noop;
					var completeCallback	= (options.complete !== undefined && $.isFunction(options.complete))	? options.complete	: $.noop;
					
					$.ajax($.extend(true, {}, {
						type:		'GET',
						cache:		false,
						dataType:	'json',
						data:		{
							api_key: sbpSettings["flickrApiKey"]
						},
						success:
							function(ajaxData, textStatus, jqXHR) {
								if (ajaxData.stat == 'ok') {
									successCallback(ajaxData, options);
									
								} else {
									errorCallback(ajaxData.message);
								}
							},
						complete:
							function(jqXHR, textStatus) {
								completeCallback();
							},
						error:
							function(jqXHR, textStatus, errorThrown) {
								errorCallback(textStatus + ' - ' + errorThrown);
							}
					}, conf));
				}
			}
		}
	}
} (jQuery));