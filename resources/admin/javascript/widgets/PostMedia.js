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
 * A widget for SB Portfolio for WordPress for the media metabox.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.postMedia', {
		options: {
			templates: {
				acItem: 			'<li></li>',
				acLink:				'<a><span class="label labelTitle">###ITEM_NAME###</span></a>',
				mediaItemError:		'<span class="dashicons dashicons-warning"></span>',
				mediaItemLoader:	'<span class="sbpThumbnailLoader"><img alt="" src=""></span></span>',
				mediaItem:			'<div class="postMediaItem sbpThumbnail loading"><span class="pmThumbnail" style="display: none;"></span></div>'
			},
			sortable:	true,
			errTimeout:	2000,
			hosts:		{}
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			this.mediaBrowser		= null;
			this.empty				= this.element.children('.sbpEmptyMsg');
			this.pmControls			= this.element.children('.postMediaControls');
			this.pmTemplates		= this.element.children('.postMediaTemplates');
			this.pmList				= this.element.children('.postMediaList');
			this.pmInput			= this.element.children('.postMediaInput');
			this.addMediaPanes		= this.element.children('.addMediaContent');
			this.addContent			= addContent = {};
			
				// Get additional content:
			if (this.pmTemplates.length > 0) {
				this.pmTemplates.children().each(function(){
					addContent[$(this).attr('data-templatename')] = $(this).children();
				});
			}
			
			if (this.options.sortable === true) {
				$(this.pmList).sortable({
					placeholder:	'ui-state-highlight',
					cancel:			'.deleted',
					start:
						function(event, ui){
							ui.helper.addClass('dragged');
						}
				}).disableSelection();
			}
			
			this._mediaBrowserBindExistingItems();
			this._controlsBindButtons();
			this._addNewBindToggle(this.addMediaPanes);
			this._searchfieldsBind(this.addMediaPanes);
			
				// Setup hooks.
			if (window.sbpSettings !== undefined) {
				if (window.sbpSettings.hooks !== undefined) {
					if (window.sbpSettings.hooks.postmedia === undefined)			window.sbpSettings.hooks.postmedia			= {}; // In case no extensions using hook.
					if (window.sbpSettings.hooks.postmedia.widget === undefined)	window.sbpSettings.hooks.postmedia.widget	= this;
					if (window.sbpSettings.hooks.postmedia.buttons === undefined)	window.sbpSettings.hooks.postmedia.buttons	= {};
					
					for (var hookMtype in window.sbpSettings.hooks.postmedia.buttons) {
						if (window.sbpSettings.hooks.postmedia.buttons[hookMtype].setup !== undefined) {
							window.sbpSettings.hooks.postmedia.buttons[hookMtype].setup();
							
						} else {
							console.info('PostMedia._create() - postmedia.' + hookMtype + ' hook setup function not defined');
						}
					}
					
				} else {
					console.info('PostMedia._create() - sbpSettings.hooks is not defined');
				}
				
			} else {
				console.info('PostMedia._create() - sbpSettings is not defined');
			}
			
				// Activate buttons.
			this.pmControls.children('a').filter('.bound').removeClass('disabled');
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
			this.element.find('.bound').off();
		},
		
		
		
		/* Media Methods */
		/* ------------- */
		
		/**
		* Creates a media browser.
		* 
		* @param jQuery opener The button that wants to open the media player.
		*/
		_mediaBrowserCreate: function(opener) {
			var widget = this;
			
				// Create the media frame.
			this.mediaBrowser = wp.media.frames.file_frame = wp.media({
				title: opener.attr('data-titletxt'),
				button: {
					text: opener.attr('data-buttontxt'),
				},
				multiple: true
			});
				
				// Bind the select method.
			this.mediaBrowser.on('select', function() {
				attachment = widget.mediaBrowser.state().get('selection').toJSON(); // Get the attachments as a JSON object.
				
					// Now process the data.
				widget._mediaBrowserAdd(attachment);
			});
			
				// Finally, open the modal.
			this.mediaBrowser.open();
		},
		
		/**
		* Adds attachment thumbnails to the post media.
		* 
		* @param object attachments The attachments object returned by the media browser.
		*/
		_mediaBrowserAdd: function(attachments) {
			for (var media in attachments) {
				var thisMedia	= attachments[media];
				var thisId		= -1;
				var postType	= 'attachment';
				var postTitle	= '';
				var imgAlt		= '';
				var imgSrc		= '';
				var imgWidth	= 75;
				var imgHeight	= 'auto';
				
				if (thisMedia.special !== undefined) { // Something special like flickr images.
					thisId		= thisMedia.mediaId;
					postTitle	= thisMedia.title;
					postType	= thisMedia.special;
					imgAlt		= thisMedia.title
					
					if (thisMedia.thumbnail !== undefined) imgSrc = thisMedia.thumbnail;
					
				} else if (thisMedia.id !== undefined)  { // Attachment via media selector.
					thisId		= thisMedia.id;
					postTitle	= thisMedia.title;
					imgSrc		= (thisMedia.sizes !== undefined) ? thisMedia.sizes.thumbnail.url : '';
					
				} else if (thisMedia.ID !== undefined)  { // Other record via add button.
					thisId		= thisMedia.ID;
					postType	= thisMedia.post_type;
					postTitle	= thisMedia.post_title;
					imgAlt		= thisMedia.alt;
					
					if (thisMedia.tn !== undefined) {
						imgAlt		= thisMedia.tn.alt;
						imgSrc		= thisMedia.tn.url;
					}
					
				} else {
					continue; // Shouldn't ever happen, but can't do anything without id!
				}
				
				var existingMedia = this.pmList.children('[data-itemid="' + thisId + '"]').filter('[data-itemtype="' + postType + '"]');
				
				if (existingMedia.length > 0) {
					existingMedia = existingMedia.detach();
					this.pmList.append(existingMedia);
					
				} else {
					var newThumbnail	= $(this.options.templates.mediaItem);
					var pmThumbnail		= newThumbnail.children('.pmThumbnail');
					var loader			= $(this.options.templates.mediaItemLoader);
						loader.children('img').attr('src', sbpSettings['loadingSpinner']);
					var newImage		= $('<img />').attr('data-imgsrc', '');
					
					if (imgSrc != '') {
						newImage.attr({
							'alt':			imgAlt,
							'data-imgsrc':	imgSrc,
							'width':		imgWidth,
							'height':		imgHeight
						});
					}
					
					pmThumbnail.html(newImage);
					
						// Add additional content by post type.
					if (thisMedia.media !== undefined && this.addContent[postType + '_' + thisMedia.media] !== undefined) {
						newThumbnail.append(this.addContent[postType + '_' + thisMedia.media].clone(false));
						
					} else if (this.addContent[postType] !== undefined) {
						newThumbnail.append(this.addContent[postType].clone(false));
						
					}
					
					newThumbnail.attr({
						'title':			postTitle,
						'data-itemid':		thisId,
						'data-itemtype':	postType,
						'data-subtype':		''
					});
					
					if (thisMedia.special !== undefined) newThumbnail.attr('data-subtype', thisMedia.media).addClass('dataAdded hasMediaData').data('mediaData', thisMedia);
					
						// Bind and add.
					newThumbnail.append(loader);
					this._mediaBrowserBindItem(newThumbnail);
					this.pmList.append(newThumbnail);
					this._mediaBrowserBindRealImg(pmThumbnail);
				}
			}
			
			this.empty.addClass('hidden');
			this._mediaBrowserUpdateValue();
		},
		
		/**
		* Binds and loads real image.
		* 
		* @param jQuery theImage The .pmThumbnail tag.
		*/
		_mediaBrowserBindRealImg: function(pmThumbnail) {
			var mediaItemError	= this.options.templates.mediaItemError;
			var theImage		= pmThumbnail.children('img');
			
			if (theImage.length > 0 && theImage.attr('data-imgsrc') != '') {
				theImage.addClass('bound').on('load', function(){
					$(this).closest('.sbpThumbnail').removeClass('loading').addClass('loaded');
					$(this).parent().show();
					
				}).on('error', function(){
					$(this).closest('.sbpThumbnail').removeClass('loading').addClass('errored');
					$(this).parent().siblings('.sbpThumbnailLoader').empty().html(mediaItemError);
				}).attr('src', theImage.attr('data-imgsrc'));
			
			} else {
				pmThumbnail.closest('.sbpThumbnail').removeClass('loading').addClass('loaded');
				pmThumbnail.parent().show();
			}
		},
		
		/**
		* Binds any existing items.
		*/
		_mediaBrowserBindExistingItems: function() {
			this.pmInput.removeAttr('disabled');
			var postMediaItems = this.pmList.children().not('.bound');
			
			if (postMediaItems.length < 1) return;
			
			var widget = this;
			
			postMediaItems.each(function(){
				var thisItem = $(this);
				
				widget._mediaBrowserBindItem(thisItem);
				
					// Add additional content by post type.
				var postType	= thisItem.attr('data-itemtype');
				var subtype		= thisItem.attr('data-subtype');
				
				if (widget.addContent[postType + '_' + subtype] !== undefined) {;
					thisItem.append(widget.addContent[postType + '_' + subtype].clone(false));
					
				} else if (widget.addContent[postType] !== undefined) {
					thisItem.append(widget.addContent[postType].clone(false));
				}
				
				var mediaData = $(this).children('.mediaData');
				
				if (mediaData.length > 0) {
					$(this).addClass('dataAdded hasMediaData').data('mediaData', jQuery.parseJSON(mediaData.text()));
					mediaData.remove();
				}
				
				widget._mediaBrowserBindRealImg(thisItem.children('.pmThumbnail'));
			});
			
			this._mediaBrowserUpdateValue();
		},
		
		/**
		* Binds a new media list item.
		* 
		* @param jQuery theItem The item to bind.
		*/
		_mediaBrowserBindItem: function(theItem) {
			if (theItem.hasClass('bound')) return;
			
			var widget = this;
			
			theItem.addClass('bound').on('click', function(event){
				var thisItem = $(this);
				
				if (thisItem.hasClass('dragged')) {
					thisItem.removeClass('dragged');
					
				} else {
					thisItem.toggleClass('deleted');
				}
				
				widget._mediaBrowserUpdateValue();
			});
		},
		
		/**
		* Updates the media value.
		*/
		_mediaBrowserUpdateValue: function() {
			var items = this.pmList.children().not('.deleted');
			
			if (items.length < 1) {
				this.pmInput.val('');
				
			} else {
				var newVal = {items: []};
				
				items.each(function(){
					var itemId		= $.trim($(this).attr('data-itemid'));
					var itemType	= $.trim($(this).attr('data-itemtype'));
					var newItem		= {
						ID:			itemId,
						post_type:	itemType
					};
					
					if ($(this).hasClass('hasMediaData')) newItem['mediaData'] = $(this).data('mediaData');
					
					newVal['items'].push(newItem);
				});
				
				this.pmInput.val(JSON.stringify(newVal));
			}
		},
		
		
		
		/* Control Methods */
		/* --------------- */
		
		/**
		* Binds media control buttons.
		*/
		_controlsBindButtons: function() {
			var pmButtons = this.pmControls.children('a').not('bound');
			
			if (pmButtons.length < 1) return;
			
			var widget		= this;
			var amContents	= this.pmControls.siblings('.addMediaContent');
			
			pmButtons.addClass('bound').each(function(event){
				var thisbutton	= $(this);
				var type		= thisbutton.attr('href').substr(1);
				var thisType	= amContents.filter('.addMediaContent-' + type);
				var thisSearch	= thisType.find('.sbpAmcReset');
				
				thisbutton.on('click', function(event){
					event.preventDefault();
					
					if (thisbutton.hasClass('disabled')) return;
						
					if (thisbutton.hasClass('addMediaContentToggle')) {
						amContents.not('.addMediaContent-' + type).hide().addClass('hidden');
						
						if (thisType.hasClass('hidden')) {
							thisSearch.trigger('reset');
							thisType.removeClass('hidden').show();
							thisSearch.trigger('focus');
							
							
						} else {
							thisType.hide().addClass('hidden');
						}
						
					} else if (type == 'attachment') {
						amContents.hide().addClass('hidden');
						
							// Open the browser if already opened.
						if (widget.mediaBrowser !== null) {
							widget.mediaBrowser.open();
							
						} else {
							widget._mediaBrowserCreate(thisbutton);
						}
						
					} else {
						console.info('PostMedia._controlsBindButtons() - unknown type: "' + type + '"');
					}
				});
			});
		},
		
		
		
		/* Search Field Methods */
		/* -------------------- */
		
		/**
		* Sets up the search fields with autocomplete.
		* 
		* @param jQuery context The context of where to look for search fields.
		*/
		_searchfieldsBind: function(context) {
			var searchFields = $('input.sbpRecordSearchTerm').not('bound');
			
			if (searchFields.length < 1) return;
			
			var acItem		= this.options.templates.acItem;
			var acLink		= this.options.templates.acLink;
			var subType		= this.options.subType;
			var errTimeout	= this.options.errTimeout;
			var pmList		= this.pmList;
			var widget		= this;
			
			searchFields.addClass('bound').each(function(){
				var searchField		= $(this);
				var subTypeField	= searchField.siblings('.sbpLinkPostType');
				var ajaxError		= searchField.siblings('.sbpAjaxError');
				var addNewToggle	= searchField.siblings('.sbpAddNew').children('.sbpAddNewToggle');
				var thisAmc			= searchField.closest('.addMediaContent');
				var thisAmcType		= thisAmc.attr('data-mediatype');
				
					// Add default binds:
				searchField.on('reset', function(event, errorMessage){
					$(this).val('');
					
				}).on('error', function(event, errorMessage){
					$(this).trigger('reset');
					ajaxError.html(errorMessage).removeClass('hidden');
					
					setTimeout(function() {
						ajaxError.addClass('hidden').empty();
					}, errTimeout);
				});
				
					// Do search bind:
				if (thisAmcType !== undefined && $.isFunction(window['sbp_postmediaTypes_' + thisAmcType])) { // Function added by widget extending post media.
					window['sbp_postmediaTypes_' + thisAmcType](thisAmcType, searchField, subTypeField, ajaxError, addNewToggle);
					
				} else if (thisAmcType !== undefined && $.isFunction(widget['_searchfieldsBind_' + thisAmcType])) { // Function within widget.
					widget['_searchfieldsBind_' + thisAmcType](thisAmcType, searchField, subTypeField, ajaxError, addNewToggle);
					
				} else { // Bind searchfield.
					searchField.autocomplete({
						source:
							function (request, response) {
								$.ajax({
									url:		ajaxurl,
									type:		'POST',
									cache:		false,
									dataType:	'json',
									data: {
										action:		'searchrecord',
										security:	$('#_wpnonce').val(),
									    term:		request.term,
									    subType:	subTypeField.val(),
									    postId:		$('#post_ID').val()
									},
									success:
										function (ajaxData, textStatus, jqXHR) {
											if (ajaxData.error === true) { // Server error
										 		response({});
										 		searchField.trigger('error', ajaxData.errorMessage);
										   	 
											} else { // Everything went ok with the server
												if (ajaxData.result === undefined || ajaxData.result.length < 1) { // No results
														response({});
												        
												} else { // There are results
									           			// Check if terms are already selected.
									           		var finalResult = [];
									           		
									           			// Filter out any existing terms.
									           		for (var term in ajaxData.result) {
									           				// Term not already there.
														if (pmList.children().not('.deleted').filter('[data-itemid="' + ajaxData.result[term].ID + '"]').length < 1) {
															finalResult.push(ajaxData.result[term]); // Add it.
														}
									           		}
									           		
									           		ajaxData.result = finalResult;
													
													response(ajaxData.result);
												}
											}
										}
								});
							},
						minLength: 1,
						create:
							function() {
									// Item creation callback
								$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
									return $(acItem)
										.attr('data-value', item.ID).html(acLink.replace(/###ITEM_NAME###/g, item.post_title))
										.appendTo(ul);
								};
			
									// Menu creation callback
								$(this).data('ui-autocomplete')._renderMenu = function(ul, items) {
									var that = this;
									ul.addClass('sbpAutoComplete');
			
									$.each(items, function(index, item) {
										that._renderItemData(ul, item); // Add this item to the autocomplete ul
									});
								};
							},
						select:
							function(event, ui) {
								widget._mediaBrowserAdd([ui.item]);
								widget._mediaBrowserUpdateValue();
								searchField.trigger('reset');
								addNewToggle.trigger('reset');
								return false; // Don't change the search field value.*/
							}
					});
				}
			});
		},
		
		/**
		* Binding function for hosted_video.
		* 
		* @param string mediaType The media type.
		* @param jQuery searchField The search field.
		* @param jQuery subTypeField The field with the subtype.
		* @param jQuery ajaxError The AJAX error div.
		* @param jQuery addNewToggle The add new toggle.
		*/
		_searchfieldsBind_hosted_video: function(mediaType, searchField, subTypeField, ajaxError, addNewToggle) {
			var hfTypeField		= searchField.siblings('select');				// The type of hosted film.
			var hfAddButton		= searchField.siblings('a').filter('.button');	// The search field add button.
			var acItem			= this.options.templates.acItem;
			var acLink			= this.options.templates.acLink;
			var subType			= this.options.subType;
			var errTimeout		= this.options.errTimeout;
			var pmList			= this.pmList;
			var widget			= this;
			
				// Select field:
			hfTypeField.on('change', function(){
				searchField.focus();
			});
			
				// Add button:
			hfAddButton.on('click', function(){
				var searchVal = $.trim(searchField.val());
				if (searchVal == '') return;
				
				var sourceVal = searchVal;
				var isUrl		= false;
				var hfType		= hfTypeField.val();
				var matchOption	= widget.options.hosts[hfType];
				
				if (matchOption !== undefined) {
					var errMsg		= 'Service contacted successfully but no video data returned.';
					var matchIndex	= matchOption.matchindex;
					var matchKey	= matchOption.matchkey;
					var matchUrl	= matchOption.url;
					var noMatchKey	= false;
					
					if (searchVal.indexOf('/') > -1) { // If there is a slash, assume a URL.
						if (matchOption.regex !== undefined) { // Get regex:
							isUrl		= true;
							var matches	= matchOption.regex.exec(searchVal);;
							
							if (matches !== null && matches.length >= matchIndex) searchVal = $.trim(matches[matchIndex]);
							
						} else {
							console.error('No regex exists for: "' + hfType + '"');
						}
					}
					
					if (matchKey == -1) noMatchKey = true;
					
					var ajaxConfig = {
						canAjax:	true,
						type:		'GET',
						cache:		false,
						dataType:	'json',
						success:
							function(ajaxData, textStatus, jqXHR){
								if (textStatus == 'abort') return;
								
								if (noMatchKey === false && ajaxData[matchKey] === undefined) {
									searchField.trigger('error', errMsg);
									
								} else if (noMatchKey === true && jqXHR.status != 200) {
									searchField.trigger('error', errMsg);
									
								} else {
									var item = (noMatchKey === false) ? ajaxData[matchKey] : ajaxData;
									
									switch(hfType) {
										case 'vimeo':
											widget._mediaBrowserAdd([{
												special:			mediaType,
												media:				hfType,
												mediaId:			item.id,
												ownerId:			item.user_id,
												title:				widget.fixStr(item.title),
												description:		widget.fixStr(item.description),
												url:				item.url,
												thumbnail:			item.thumbnail_small,
												medium:				item.thumbnail_medium,
												large:				item.thumbnail_large, // Max size... but we need an original size on the data
												original:			item.thumbnail_large, // So don't leave empty
												srcString:			sourceVal,
												searchString:		searchVal
											}]);
											
										break;
										
										case 'youtube':
											if (item.length > 0) {
												item = item[0];
												
												if (item.snippet.thumbnails.high === undefined)		item.snippet.thumbnails.high = item.snippet.thumbnails.default;
												if (item.snippet.thumbnails.standard === undefined)	item.snippet.thumbnails.standard = item.snippet.thumbnails.high;
												if (item.snippet.thumbnails.maxres === undefined)	item.snippet.thumbnails.maxres = item.snippet.thumbnails.standard;
												
												widget._mediaBrowserAdd([{
													special:			mediaType,
													media:				hfType,
													mediaId:			item.id,
													ownerId:			item.snippet.channelId,
													title:				widget.fixStr(item.snippet.title),
													description:		widget.fixStr(item.snippet.description),
													url:				'https://www.youtube.com/watch?v=' + item.id,
													thumbnail:			item.snippet.thumbnails.default.url,
													medium:				item.snippet.thumbnails.high.url,
													large:				item.snippet.thumbnails.standard.url,
													original:			item.snippet.thumbnails.maxres.url,
													srcString:			sourceVal,
													searchString:		searchVal
												}]);
												
											} else {
												searchField.trigger('error', errMsg);
											}
											
										break;
										
										default:
											if ($.isFunction(window['sbpPostmediaHostedvideo_' + hfType])) {
												var dataToAdd = window['sbpPostmediaHostedvideo_' + hfType]({
													special:		mediaType,
													media:			hfType,
													mediaId:		item.id,
													srcString:		sourceVal,
													searchString:	searchVal
												}, item);
												
												widget._mediaBrowserAdd([dataToAdd]);
												
											} else {
												searchField.trigger('error', 'No success processor for videos of type: "' + hfType + '"');
											}
										break;
									}
									
									widget._mediaBrowserUpdateValue();
								}
								
								searchField.trigger('reset');
								addNewToggle.trigger('reset');
							},
						error:
							function(jqXHR, textStatus, errorThrown){
								if (textStatus == 'abort') return;
								
								if (errorThrown == '') {
									searchField.trigger('error', textStatus);
									
								} else {
									searchField.trigger('error', errorThrown);
								}
							}
					};
					
					if (matchUrl !== undefined) {
						matchUrl = matchUrl.replace('###SEARCH_VAL###', searchVal);
						
						if (sbpSettings[hfType + 'ApiKey'] !== undefined && sbpSettings[hfType + 'ApiKey'] != '') {
							matchUrl = matchUrl.replace('###API_KEY###', sbpSettings[hfType + 'ApiKey']);
						}
						
						ajaxConfig.url = matchUrl;
					}
					
					if (ajaxConfig.url !== undefined) {
						$.ajax(ajaxConfig);
	
					} else {
						console.error('PostMedia._searchfieldsBind_hosted_video() - AJAX config has no URL, hfType: "' + hfType + '"');
					}
					
				} else {
					console.error('PostMedia._searchfieldsBind_hosted_video() - No matchOption for: "' + hfType + '"');
				}
			});
		},
		
		
		
		/* Processing Methods */
		/* ------------------ */
		
		/**
		* Fixes a string that may contain special chars obtained via an API method.
		* 
		* @param string stringToFix The string to fix.
		* @return string fixedStr The fixed string.
		*/
		fixStr: function(stringToFix) {
			var fixedStr = $('<p>' + stringToFix + '</P>').text();	// Strip html.
				fixedStr.replace(/['"]+/g, '\"');					// Fix quotes.
				fixedStr.replace(/[\r\n]+/g, '');					// Remove new lines.
				
			return fixedStr;
		},
		
		
		
		/* Add New Methods */
		/* --------------- */
		
		/**
		* Binds the add new toggle.
		* 
		* @param jQuery addNewTarget Where to look for add new toggles.
		*/
		_addNewBindToggle: function(addNewTarget) {
			if (addNewTarget.length < 1) return;
			
			var toggleLink = addNewTarget.find('a.sbpAddNewToggle').not('.bound');
			
			if (toggleLink.length < 1) return;
			
			var widget = this;
			
			toggleLink.each(function(){
				var thisToggle		= $(this);
				var toggleParent	= thisToggle.parent();
				var addNewTitle		= thisToggle.siblings('.sbpAddNewFields').children('.addNewTitle');
				
				thisToggle.addClass('bound').on('click', function(event){
					event.preventDefault();
					event.stopPropagation();
					
					toggleParent.toggleClass('wp-hidden-children');
					if (!toggleParent.hasClass('wp-hidden-children')) addNewTitle.focus();
				});
				
				widget._addNewBindForm(thisToggle);
			});
		},
		
		/**
		* Binds the add new field and button.
		* 
		* @param jQuery toggleLink An a.sbpAddNewToggle tag.
		*/
		_addNewBindForm: function(toggleLink) {
			if (toggleLink.length < 1) return;
			
			var widget = this;
			var action = 'addrecord';
			
			toggleLink.each(function(){
				var thisAddNewTarget	= $(this);
				var addNewFields 		= thisAddNewTarget.siblings('.sbpAddNewFields');
				var addNewTitle			= addNewFields.children('.addNewTitle').not('.bound');
				var addNewSubmit		= addNewFields.children('.addNewSubmit').not('.bound');
				var subTypeField		= addNewFields.children('.sbpLinkPostType');
				var tabListContentPane	= thisAddNewTarget.closest('.tabListContentPane');
				var sbpAjaxError		= thisAddNewTarget.parent().siblings('.sbpAjaxError');
				var sbpLinkCurrent		= thisAddNewTarget.parent().siblings('.sbpLinkCurrent');
				var searchField			= tabListContentPane.find('.sbpRecordSearchTerm');
				
				if (addNewTitle.length < 1 || addNewSubmit.length < 1) return;
				
				addNewSubmit.addClass('bound').on('click', function(event, searchCreate){
					event.preventDefault();
					event.stopPropagation();
					
					var conf	= {
						url:		ajaxurl,
						type:		'POST',
						cache:		false,
						dataType:	'json',
						data:		{
							action:		action,
							security:	$('#_wpnonce').val(),
							subType:	subTypeField.val(),
							newTitle:	$.trim(addNewTitle.val()),
							postId:		$('#post_ID').val()
						},
						success:
							function(ajaxData, textStatus, jqXHR) {
								if (ajaxData.error === undefined || ajaxData.error === false) {
										// Add new item.
									widget._mediaBrowserAdd([ajaxData.result]);
									widget._mediaBrowserUpdateValue();
									
										// Hide and reset add new form.
									thisAddNewTarget.trigger('reset');
									searchField.trigger('reset');
									
								} else {
									widget._addNewError(sbpAjaxError, jqXHR, textStatus, 'successError');
									searchField.trigger('reset');
								}
							},
						error:
							function(jqXHR, textStatus, errorThrown) {
								widget._addNewError(sbpAjaxError, jqXHR, textStatus, errorThrown);
								searchField.trigger('reset');
							}
					};
					
					$.ajax(conf);
				});
				
					// Setup reset.
				thisAddNewTarget.on('reset', function(){
					if (!$(this).parent().hasClass('wp-hidden-children')) {
						$(this).trigger('click');
						addNewTitle.trigger('blur');
						addNewTitle.val('');
					}
				});
			});
		},
		
		/**
		* Called if error.
		* 
		* @param jQuery sbpAjaxError The error tag.
		* @param jqXHR The jQuery jqXHR object.
		* @param string textStatus The request's text status.
		* @param string errorThrown The error thrown.
		*/
		_addNewError: function(sbpAjaxError, jqXHR, textStatus, errorThrown) {
			var ajaxResponseStr = sbpAjaxError.attr('data-defaultstr');
			
				// Get error thrown by WP.
			if (jqXHR.responseJSON !== undefined) {
				if (jqXHR.responseJSON.result !== undefined) {
					if (jqXHR.responseJSON.result.errors !== undefined && jqXHR.responseJSON.result.error_data !== undefined) {
						ajaxResponseStr = '';
						
						for (var err in jqXHR.responseJSON.result.errors) {
							for (var errTxt in jqXHR.responseJSON.result.errors[err]) {
								if (ajaxResponseStr != '') ajaxResponseStr += '<br /><br />';
								
								ajaxResponseStr += '<strong>' + jqXHR.responseJSON.result.errors[err][errTxt] + '</strong>';
							}
						}
					}
					
				} else if (jqXHR.responseJSON.errorMessage !== undefined) {
					ajaxResponseStr = jqXHR.responseJSON.errorMessage;
				}
			}
			
			var ajaxResponse = sbpAjaxError;
				ajaxResponse.html(ajaxResponseStr).removeClass('hidden');
			
				// Hide message.
			setTimeout(function(){ajaxResponse.addClass('hidden');}, this.options.errTimeout);
		}
	});
} (jQuery));