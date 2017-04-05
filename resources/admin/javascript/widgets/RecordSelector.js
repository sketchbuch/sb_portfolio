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
 * A widget for SB Portfolio for WordPress that lets you select records from a table..
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.recordSelector', {
		options: {
			templates: {
				acItem: 	'<li></li>',
				acLink:		'<a><span class="label labelTitle">###ITEM_NAME###</span></a>',
				rec:		'<span data-recid="###ITEM_ID###" data-reclabel="###ITEM_NAME###" class="searchAdded"></span>',
				recLink:	'<a data-value="###ITEM_ID###" class="ntdelbutton">X</a>',
				recInput:	'<input type="checkbox" class="alwaysHidden termAddInput" checked="checked" id="###ITEM_TYPE###-all-###ITEM_ID###" name="sb_portfolio[###ITEM_TYPE###][]" value="###ITEM_ID###">'
			},
			errTimeout: 2000
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			var pTag 				= this.element.parent();
			this.current			= pTag.siblings('.rsCurrent');
			this.addNew				= pTag.siblings('.sbpAddNew');
			this.addNewTitle		= this.addNew.find('.addNewTitle');
			this.postTypeField		= this.addNew.find('.sbpLinkPostType');
			this.mediaThumbnail 	= pTag.siblings('.mediaThumbnail');
			this.rsAjaxError 		= pTag.siblings('.sbpAjaxError');
			this.rsAjaxAddError 	= pTag.siblings('sbpAjaxError');
			this.mtImage 			= this.mediaThumbnail.children('img');
			this.real				= this.element.siblings('.sbpRecordSelectorReal');
			this.isMedia			= (this.options.subType == 'attachment') ? true : false;
			this.mediaBrowser		= null;
			var currentXlinks		= this.current.children('span');
			var numOfLinks			= currentXlinks.length;
			
				// Bind existing record.
			if (numOfLinks > 0) {
				for (var link = 0; link < numOfLinks; link ++) {
					var curRec	= currentXlinks.eq(link);
					var theItem	= {
						ID:	parseInt(curRec.attr('data-recid')),
						title:	curRec.attr('data-reclabel'),
						tnSrc:	curRec.attr('data-rectnsrc')
					};
					
					curRec.addClass('dataAdded').data('recData', theItem);
					
					this._recBindLink(curRec.children('a'));
				}	
			}
			
			this._searchFieldBind();
			this._mediaOpenerBind();
			this._addNewBindToggle();
			this._addNewBindForm();
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
			this.element.find('.ui-autocomplete-input').autocomplete('destroy').removeData();
			this.element.find('.dataAdded').removeData();
			this.element.find('.bound').off();
			this.element.autocomplete('destroy');
		},
		
		
		
		/* Media Methods */
		/* ------------- */
		
		/**
		* Binds the search field.
		* 
		* @param jQuery opener The button that wants to open the media player.
		*/
		_mediaOpenerCreate: function(opener) {
			var widget = this;
			
				// Create the media frame.
			this.isMedia = wp.media.frames.file_frame = wp.media({
				title: opener.attr('data-titletxt'),
				button: {
					text: opener.attr('data-buttontxt'),
				},
				multiple: false // Mutiple or single file.
			});
				
				// When an image is selected, run a callback.
			this.isMedia.on('select', function() {
					// We set multiple to false so only get one image from the uploader
				attachment = widget.isMedia.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
				widget.current.children('.alreadyAdded').not('.deleted').children('a').trigger('click');
				widget.current.append(widget._recNew({
					ID:	parseInt(attachment.id),
					title:	attachment.post_title,
					tnSrc:	attachment.sizes.thumbnail.url
				})).removeClass('hidden');
				widget._searchFieldReset();
				
				widget.real.val(parseInt(attachment.id));
			});
			
				// Finally, open the modal
			this.isMedia.open();
			
		},
		
		/**
		* Binds the search field.
		*/
		_mediaOpenerBind: function() {
			if (!this.isMedia) return;
			
			var mediaOpener = this.element.siblings('.mediaBrowserLinkWrap').children('a').not('.bound');
			
			if (mediaOpener.length < 1) return;
			
			var widget = this;
			
			mediaOpener.addClass('bound').on('click', function(event){
				event.preventDefault();
				event.stopPropagation();
				
					// Open the browser if already opened.
				if (widget.mediaBrowser) {
					widget.mediaBrowser.open();
					
				} else {
					widget._mediaOpenerCreate($(this));
				}
			});
		},
		
		
		
		/* Search Field Methods */
		/* -------------------- */
		
		/**
		* Binds the search field.
		*/
		_searchFieldBind: function() {
			var widget			= this;
			var current			= this.current;
			var currentVal		= current.children('.value');
			var real			= this.real;
			var mediaThumbnail	= this.mediaThumbnail;
			var mtImage			= this.mtImage;
			var acItem			= this.options.templates.acItem;
			var acLink			= this.options.templates.acLink;
			var postTypeField	= this.postTypeField;
			
			this.element.autocomplete({
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
							    subType:	postTypeField.val(),
							    postId:		$('#post_ID').val()
							},
							success:
								function (ajaxData) {
									if (ajaxData.error === true) { // Server error
								 		response({});
								 		widget._searchFieldError(ajaxData);
								   	 
									} else { // Everything went ok with the server
										if (ajaxData.result === undefined || ajaxData.result.length < 1) { // No results
												response({});
										        
										} else { // There are results
							           			// Check if terms are already selected.
							           		var finalResult = [];
							           		
							           			// Filter out any existing terms.
							           		for (var term in ajaxData.result) {
							           				// Term not already there.
												if (current.children().not('.deleted').filter('[data-recid="' + ajaxData.result[term].ID + '"]').length < 1) {
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
						widget._searchFieldErrorHide();
						
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
						current.children().filter('.searchAdded').off().remove();
						current.children().not('.deleted').addClass('deleted');
						current.append(widget._recNew(ui.item)).removeClass('hidden');
						real.val(ui.item.ID);
						
						widget._searchFieldReset();
						widget._addNewTitleFieldReset();
						
						return false; // Don't change the search field value.
					}
			});
		},
		
		/**
		* Called if there was an error getting data for the ac.
		* 
		* @param object ajaxData The AJAX data.
		*/
		_searchFieldError: function(ajaxData) {
			this.rsAjaxError.html(ajaxData.errorMessage).removeClass('hidden');
			
			var widget = this;
			
			setTimeout(function() {
				widget._searchFieldErrorHide();
			}, 2000);
		},
		
		/**
		* Hides the AJAX error tag.
		*/
		_searchFieldErrorHide: function() {
			this.rsAjaxError.addClass('hidden').empty();
		},
		
		/**
		* Clears the search field.
		*/
		_searchFieldReset: function() {
			this.element.trigger('blur');
			this.element.val('');
		},
		
		
		
		/* Add New Methods */
		/* --------------- */
		
		/**
		* Binds the add new toggle.
		*/
		_addNewBindToggle: function() {
			if (this.addNew.length < 1) return;
			
			var toggleLink = this.addNew.children('a').not('.bound');
			
			if (toggleLink.length < 1) return;
			
			var widget = this;
			
			toggleLink.addClass('bound').on('click', function(event){
				event.preventDefault();
				event.stopPropagation();
				
				widget.addNew.toggleClass('wp-hidden-children');
			});
		},
		
		/**
		* Binds the add new field and button.
		*/
		_addNewBindForm: function() {
			if (this.addNew.length < 1) return;
			
			var addNewTitle		= this.addNewTitle.not('.bound');
			var addNewSubmit	= this.addNew.find('.addNewSubmit').not('.bound');
			var postTypeField	= this.postTypeField;
			
			if (addNewTitle.length < 1 || addNewSubmit.length < 1 && postTypeField.length < 1) return;
			
			var widget = this;
			var action = 'addrecord';
			
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
						subType:	postTypeField.val(),
						newTitle:	$.trim(addNewTitle.val()),
						postId:		$('#post_ID').val()
					},
					success:
						function(ajaxData, textStatus, jqXHR) {
							if (ajaxData.error === undefined || ajaxData.error === false) {
								widget._addNewSuccess(ajaxData, textStatus, jqXHR);
								
							} else {
								widget._addNewError(jqXHR, textStatus, 'successError');
							}
						},
					error:
						function(jqXHR, textStatus, errorThrown) {
							widget._addNewError(jqXHR, textStatus, errorThrown);
						}
				};
				
				$.ajax(conf);
			});
		},
		
		/**
		* Called if error.
		* 
		* @param jqXHR jqXHR The jQuery jqXHR object.
		* @param string textStatus The request's text status.
		* @param string errorThrown The error thrown.
		*/
		_addNewError: function(jqXHR, textStatus, errorThrown) {
			var ajaxResponseStr = this.rsAjaxAddError.attr('data-defaultstr');
			
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
			
			var ajaxResponse = this.rsAjaxAddError;
				ajaxResponse.html(ajaxResponseStr).removeClass('hidden');
			
				// Hide message.
			setTimeout(function(){ajaxResponse.addClass('hidden');}, this.options.errTimeout);
				
			this._searchFieldReset();
		},
		
		/**
		* Called if successful.
		* 
		* @param mixed ajaxData The data returned by the server.
		* @param string textStatus The request's text status.
		* @param jqXHR jqXHR The jQuery jqXHR object.
		*/
		_addNewSuccess: function(ajaxData, textStatus, jqXHR) {
			this.current.children().filter('.searchAdded').off().remove();
			this.current.children().not('.deleted').addClass('deleted');
			this.current.append(this._recNew(ajaxData.result)).removeClass('hidden');
			this.real.val(ajaxData.result.ID);
			
			this._searchFieldReset();
			this._addNewTitleFieldReset();
			
		},
		
		/**
		* Clears the add new ttle field.
		*/
		_addNewTitleFieldReset: function() {
			this.addNewTitle.trigger('blur');
			this.addNewTitle.val('');
		},
		
		
		
		/* Term Methods */
		/* ------------ */
		
		/**
		* Binds a record X icon link.
		* 
		* @param jQuery theRecLink The x A tag to bind.
		*/
		_recBindLink: function(theRecLink) {
			if (theRecLink.hasClass('bound')) return;
			
			var theRec			= theRecLink.parent();
			var theInput		= theRec.find('input');
			var widget			= this;
			var isMedia			= this.isMedia;
			var real			= this.real;
			var mediaThumbnail	= this.mediaThumbnail;
			var mtImage			= this.mtImage;
			
			theRecLink.addClass('bound').on('click', function(event){
				event.preventDefault();
				event.stopPropagation();
				
				theRec.toggleClass('deleted');
				theInput.prop('checked', !theInput.prop('checked'));
				widget._searchFieldReset();
				var canToggle = true;
				
				if (theRec.hasClass('searchAdded')) { // If search added, remove it.
						// First show the already existing server rendered record.
					var prevSib = theRec.prev();
					
						// Remove this search added one.
					$(this).off();
					theRec.remove();
					
						// Set real val back to the server rendered ID.
					if (prevSib.length > 0) {
						prevSib.removeClass('deleted');
						widget._recSetThumbnail(prevSib);
						real.val(prevSib.attr('data-recid'));
						
							// Make sure it is not checked.
						prevSib.children('input').prop('checked', false);
						
						if (isMedia) canToggle = false;
						
					} else {
							// There is nothing else to show so we should remove the thumbnail.
						if (isMedia) {
							widget._recDelThumbnail(mediaThumbnail, mtImage);
							real.val(0);
						}
					}
					
				} else if (theRec.hasClass('alreadyAdded')) {
					var searchAddedSibs	= theRec.siblings('.searchAdded');
					var sibLinegth		= searchAddedSibs.length;
					
					searchAddedSibs.find('.bound').off();
					searchAddedSibs.remove();
					
					if (isMedia && sibLinegth > 0) {
							// Set thumbnail back.
						widget._recSetThumbnail(theRec);
						canToggle = false;
					}
					
					if (theRec.hasClass('deleted')) {
						real.val(0);
						
					} else {
						real.val(theRec.attr('data-recid'));
					}
				}
				
					// 'Delete' media thumbnail.
				if (canToggle && isMedia && !mediaThumbnail.hasClass('hidden')) mediaThumbnail.toggleClass('deleted');
			});
		},
		
		/**
		* Returns a new record to add to current.
		* 
		* @param object theItem The new rec object clicked on in the autocomplete.
		* @return jQuery newrec The new record.
		*/
		_recNew: function(theItem) {
			var newRec			= $(this.options.templates.rec.replace(/###ITEM_ID###/g, theItem.ID).replace(/###ITEM_NAME###/g, theItem.post_title));
			var newLink			= $(this.options.templates.recLink.replace(/###ITEM_ID###/g, theItem.ID));
			var newInput		= $(this.options.templates.recInput.replace(/###ITEM_ID###/g, theItem.ID).replace(/###ITEM_TYPE###/g, this.options.subType));
			
			newRec.append(newLink).append('&nbsp;').append(theItem.post_title).append(newInput);
			newRec.addClass('dataAdded').data('recData', theItem);
			
				// Create thumbnail.
			this._recSetThumbnail(newRec, theItem);
			
				// Bind.
			this._recBindLink(newLink);
			
			return newRec;
		},
		
		/**
		* Sets up the thumbnail.
		* 
		* @param jQuery newRec The new record.
		* @param object theItem The new rec object clicked on in the autocomplete. OPTIONAL.
		*/
		_recSetThumbnail: function(newRec, theItem) {
			var mediaThumbnail	= this.mediaThumbnail;
			var mtImage			= this.mtImage;
			
			if (theItem === undefined) theItem = newRec.data('recData');
			
			if (mediaThumbnail.length > 0 && mtImage.length > 0) {
				if (theItem.tnSrc !== undefined) {
					mtImage.attr({
						'alt':		'Attachment ' + theItem.ID,
						'title':	theItem.ID + ': ' + theItem.post_title,
						'src':		theItem.tnSrc
					});
					
					newRec.attr('data-tnsrc', theItem.tnSrc);					
					mediaThumbnail.removeClass('hidden deleted');
					
				} else {
					this._recDelThumbnail(mediaThumbnail, mtImage, newRec);
				}
			}
		},
		
		/**
		* Sets up the thumbnail.
		* 
		* @param jQuery newRec The new record.
		* @param object theItem The new rec object clicked on in the autocomplete. OPTIONAL.
		*/
		_recDelThumbnail: function(mediaThumbnail, mtImage, newRec) {
			if (mediaThumbnail === undefined)	mediaThumbnail	= this.mediaThumbnail;
			if (mtImage === undefined)			mtImage			= this.mtImage;
			
			mediaThumbnail.addClass('hidden');
			mtImage.removeAttr('src').removeAttr('title').attr('alt', 'Attachment');
			if (newRec !== undefined) newRec.attr('data-tnsrc', '');
		}
	});
} (jQuery));