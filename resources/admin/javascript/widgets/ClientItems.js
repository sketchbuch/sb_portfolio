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
 * A widget to display items for client posts in the items metabox.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.clientItems', {
		options: {
			templates: {
				sbpList:		'<div class="sbpCiList hidden"></div>',
				sbpListItem:   '<div class="sbpCiListItem"> \
									<span class="ciImage sbpThumbnail"><img class="ciImg" src="" alt=""></span> \
									<span class="ciTitle"></span> \
									<span class="ciItemTags"></span> \
								</div>',
				sbpLiControls: '<div class="ciControls sbpLiControls"> \
									<a href="#" class="sbpLiControl toggle button" data-propstatus="false" data-action="featured"><span class="dashicons dashicons-star-filled"></span></a> \
									<a href="#" class="sbpLiControl toggle button spacer" data-propstatus="false" data-action="inprogress"><span class="dashicons dashicons-clock"></span></a> \
									<a href="#" class="sbpLiControl toggle button spacer" data-propstatus="true" data-action="disassociate"><span class="dashicons dashicons-editor-unlink"></span></a> \
									<a href="#" class="sbpLiControl button" data-action="edit"><span class="dashicons dashicons-edit"></span></a> \
									<a href="#" class="sbpLiControl button" data-action="view"><span class="dashicons dashicons-visibility"></span></a> \
								</div>'
			},
			labels: {},
			errTimeout:	2000,
			offset:		0,
			pageBrowser: {
				enabled:	true,
				total:		0,
				pages:		5,
				perPage:	5
			}
		},



		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			this.pb				= null;
			this.itemControls	= null;
			this.controls		= null;
			this.ciList			= null;
			this.ajaxCache		= {};
			this.ajaxError		= this.element.children('.sbpAjaxError');
			this._controlsSetup();
			this._itemsSetup();
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


			/* Control Methods */
			/* --------------- */

		/**
		* Binds control buttons.
		*/
		_controlsSetup: function() {
			this.controls	= this.element.children('.clientItemControls');
			var buttons		= this.controls.children('.button').not('bound');

			if (buttons.length < 1) return;

			var widget = this;

			buttons.each(function(){
				var thisBut		= $(this);
				var butAction	= thisBut.attr('data-action');

				thisBut.addClass('bound').on('click', function(event){
					event.preventDefault();

					switch(butAction) {
						case 'loadItems':
							thisBut.attr('disabled', 'disabled').siblings('.spinner').addClass('is-active');
							widget._itemsLoad(thisBut);

						break;

						default:
							console.info('ClientItems._controlsBind() - unknown action: "' + butAction + '"');

						break;
					}
				});
			});
		},


			/* Page Browser Methods */
			/* -------------------- */

		/**
		* Binds control buttons.
		* 
		* @param object confObj An object with additional options/data.
		*/
		_pbAdd: function(confObj) {
			if (this.options.pageBrowser.total < 1 || this.options.pageBrowser.enabled !== true)	return;
			if (this.options.pageBrowser.perPage >= this.options.pageBrowser.total)					return;
			
			if (confObj === undefined) confObj = {};
			
			if (this.pb	!== null) { // Update
				this.pb.pageBrowser('update', confObj);

			} else { // Create
				var thisWidget	= this;
				var pbConfig	= $.extend({}, this.options.pageBrowser);
				pbConfig.change = function(pbWidget, pBut, pPage, pNum) {
					thisWidget._itemsLoad(null, {
						page: pNum
					});
				}
				
				pbConfig.initItemCount = confObj.itemCount;

					// Create page browser:
				this.pb = $('<div class="pageBrowser"></div>');
				this.controls.after(this.pb);
				this.pb.pageBrowser(pbConfig);
			}
		},


			/* AJAX Cache Methods */
			/* ------------------ */

		/**
		* Sets a cache entry for 
		*/
		_ajaxCacheSet: function(cacheKey, cacheValue) {
			if (cacheKey == '' || cacheKey === undefined)	return;
			if (cacheValue.length < 1)						return;
			
			this.ajaxCache[cacheKey] = cacheValue;
		},

		/**
		* Sets a cache entry for 
		*/
		_ajaxCacheGet: function(cacheKey) {
			if (cacheKey == '' || cacheKey === undefined) return false;
			
			if (this.ajaxCache[cacheKey] !== undefined) {
				return this.ajaxCache[cacheKey];
			}
			
			return false;
		},


			/* Item Methods */
			/* ------------ */

		/**
		* Binds control buttons.
		*/
		_itemsSetup: function() {
			this.ciList = $(this.options.templates.sbpList);
			this.controls.before(this.ciList);

		},

		/**
		* Loads the items.
		*
		* @param mixed theBut The button clicked if not null. Only needed the first time.
		* @param object conf An AJX config object to override the default config.
		*/
		_itemsLoad: function(theBut, conf) {
			if (conf === undefined)			conf		= {};
			if (conf.page === undefined)	conf.page	= 1;
			
			var widget		= this;
			var ajaxError	= this.ajaxError;
			var cacheKey	= 'sbpgetclientitems_' + conf.page + '_' + this.options.pageBrowser.perPage;
			var cacheEntry	= this._ajaxCacheGet(cacheKey);
			
			if (cacheEntry !== false) {
				this._itemsAdd(cacheEntry);
				
			} else {
				this.ajaxError.addClass('hidden').empty();
				
				$.ajax({
					url:		ajaxurl,
					type:		'GET',
					cache:		false,
					dataType:	'json',
					data: {
						action:		'sbpgetclientitems',
						security:	$('#_wpnonce').val(),
					    postId:		$('#post_ID').val(),
					    page:		conf.page,
					    perPage:	this.options.pageBrowser.perPage
					},
					success:
						function (ajaxData, textStatus, jqXHR) {
							if (textStatus == 'abort') return;
	
							if (ajaxData.error === true) { // Server error
								widget._itemsError(ajaxData.errorMessage);
	
							} else { // Everything went ok with the server
								if (ajaxData.result === undefined || ajaxData.result.length < 1) { // No results
									widget._itemsError(ajaxData.errorMessage);
	
								} else { // There are results
									if (theBut !== null) theBut.parent().addClass('hidden'); // Hide controls, not needed anymore.
									widget._ajaxCacheSet(cacheKey, ajaxData.result);
									widget._itemsAdd(ajaxData.result);
								}
							}
						},
					error:
						function (jqXHR, textStatus, errorThrown) {
							if (textStatus == 'abort') return;
	
							if (errorThrown == '') {
								widget._itemsError(textStatus);
	
							} else {
								widget._itemsError(errorThrown);
							}
						},
					complete:
						function (jqXHR, textStatus) {
							if (textStatus == 'abort') return;
	
							if (theBut !== null) theBut.removeAttr('disabled', 'disabled').siblings('.spinner').removeClass('is-active');
						}
				});
			}
		},

		/**
		* Done when there is an error.
		*
		* @param string msg The error msg.
		*/
		_itemsError: function(msg) {
			var pb			= this.pb;
			var ajaxError	= this.ajaxError;
			ajaxError.html(msg).removeClass('hidden');

			setTimeout(function() {
				ajaxError.addClass('hidden').empty();
				if (pb !== null) pb.pageBrowser('error');
			}, this.options.errTimeout);
		},

		/**
		* Adds an item.
		*
		* @param array items An array of items to add.
		*/
		_itemsAdd: function(items) {
			if (items.length < 1) {
				return;

			} else {
				this.ciList.find('.bound').off();
				this.ciList.find('.dataAdded').removeData();
				this.ciList.empty().removeClass('hidden');
				
				var labels = this.options.labels;

				if (this.itemControls === null) { // Create controls:
					this.itemControls	= $(this.options.templates.sbpLiControls);

					this.itemControls.children('.button').each(function(){
						var thisAction = $(this).attr('data-action');
						
						if (labels['action-' + thisAction] !== undefined) {
							$(this).attr('title', labels['action-' + thisAction]);
							
						} else {
							$(this).attr('title', thisAction);
						}
					});
				}

					// Add items:
				var itemCount	= 0;
				var widget		= this;
				
				for (var item in items) {
					var iData	= items[item];
					var newItem	= $(this.options.templates.sbpListItem);

						// Add thumbnail:
					if (iData.thumbnail !== false) {
						newItem.children('.ciImage').children('img').attr('src', iData.thumbnail);

					} else {
						var ciImage = newItem.children('.ciImage');
							ciImage.addClass('empty').append('?');
							ciImage.children('img').remove();
					}

						// Set title:
					newItem.children('.ciTitle').html(iData.post_title);
					
						// Add tags:
					var ciItemTags = newItem.children('.ciItemTags');
					if (iData.post_statusLabel !== undefined) ciItemTags.html(iData.post_statusLabel);
					
						// Set controls:
					var newControls	= this.itemControls.clone(false);
					var cButtons	= newControls.children();
					
					cButtons.attr('data-postid', iData.ID);
					cButtons.filter('[data-action="edit"]').attr('href', iData.editLink);
					cButtons.filter('[data-action="view"]').attr('href', iData.viewLink);
					
						// Now change toggles:
					cButtons.filter('.toggle').each(function(){
						var thisToggle	= $(this);
						var toggle		= $(this).attr('data-action');
						var propstatus	= $(this).attr('data-propstatus');
						
						if (iData[toggle] !== undefined) {
							thisToggle.attr('data-propstatus', iData[toggle]);
							
							if (labels['action-' + toggle + '-' + iData[toggle]] !== undefined) thisToggle.attr('title', labels['action-' + toggle + '-' + iData[toggle]]);
							
						} else if (labels['action-' + toggle + '-' + propstatus] !== undefined) {
							thisToggle.attr('title', labels['action-' + toggle + '-' + propstatus]);
						}
						
							// Bind toggle:
						thisToggle.on('click', function(event) {
							event.preventDefault();
							
							if (thisToggle.attr('data-propstatus') == 'true') {
								thisToggle.attr('data-propstatus', 'false');
								widget._toggleChange(thisToggle, toggle, false);
								
							} else {
								thisToggle.attr('data-propstatus', 'true');
								widget._toggleChange(thisToggle, toggle, true);
							}
						});
					});
					
					newItem.append(newControls);
					
					// Set attrs:
					newItem.attr({
						'data-type':	iData.post_status,
						'title':		'[' + iData.post_statusLabel + '] ' + iData.post_title
					});
					
					this.ciList.append(newItem);
					itemCount ++;
				}

				this._pbAdd({'itemCount': itemCount});
			}
		},

		/**
		* Update item based on a toggle change.
		*
		* @param jQuery toggleButton The toggle button pressed.
		* @param string toggleType The type of toggle.
		* @param boolean toggleState The toggle's current state.
		*/
		_toggleChange: function(toggleButton, toggleType, toggleState) {
			var widget = this;
			this.ajaxError.addClass('hidden').empty();
			
			$.ajax({
				url:		ajaxurl,
				type:		'GET',
				cache:		false,
				dataType:	'json',
				data: {
					action:			'sbpupdateclientitem',
					security:		$('#_wpnonce').val(),
				    postId:			$('#post_ID').val(),
				    toggleType:		toggleType,
				    toggleState:	toggleState,
				    togglePostId:	toggleButton.attr('data-postid')
				},
				success:
					function (ajaxData, textStatus, jqXHR) {
						if (textStatus == 'abort') return;

						if (ajaxData.error === true) { // Server error
							widget._toggleError(ajaxData.errorMessage);

						} else { // Everything went ok with the server
							if (ajaxData.result === undefined) { // No results
								widget._toggleError(ajaxData.errorMessage);
							} // There are results
						}
					},
				error:
					function (jqXHR, textStatus, errorThrown) {
						if (textStatus == 'abort') return;
						
						if (errorThrown == '') {
							widget._toggleError(textStatus);

						} else {
							widget._toggleError(errorThrown);
						}
					},
				complete:
					function (jqXHR, textStatus) {
						toggleButton.blur();
					}
			});
		},

		/**
		* Done when there is an error.
		*
		* @param string msg The error msg.
		*/
		_toggleError: function(msg) {
			var ajaxError	= this.ajaxError;
			ajaxError.html(msg).removeClass('hidden');

			setTimeout(function() {
				ajaxError.addClass('hidden').empty();
			}, this.options.errTimeout);
		}
	});
} (jQuery));