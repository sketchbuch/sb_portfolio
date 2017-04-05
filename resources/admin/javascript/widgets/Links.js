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
 * A widget for SB Portfolio for WordPress for the links metabox.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.sbpLinks', {
		options: {
			templates: {
				acItem: 	'<li></li>',
				acLink:		'<a><span class="label labelTitle">###ITEM_NAME###</span></a>',
				rec:		'<span data-recid="###ITEM_ID###" data-posttype="###ITEM_TYPE###" data-reclabel="###ITEM_NAME###" class="searchAdded"></span>',
				recLink:	'<a data-value="###ITEM_ID###" class="ntdelbutton">X</a>',
				urlMedia:	'<span class="sbpThumbnail"><img width="75" height="auto" alt="" /></span>'
			},
			lables: {
				'status_publish':		'Published',
				'status_pending':		'Pending',
				'status_draft':			'Draft',
				'status_auto-draft':	'Auto Draft',
				'status_future':		'Future',
				'status_private':		'Private',
				'status_inherit':		'Inherit',
				'status_trash':			'Deleted'
			},
			sortable:	true,
			errTimeout:	2000
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			this.linkControls	= this.element.children('.linkControls');
			this.linkListDummy	= this.element.children('.linkListDummy');
			this.linkList		= this.element.children('.linkList');
			this.linkListInput	= this.element.children('input.linkListInput');
			this.urlList		= this.linkList.find('.urlList');
			this.addLinkBut		= this.linkControls.children('[href="#addLink"]');
			this.empty			= this.element.children('.sbpEmptyMsg');
			
			this.linkListInput.removeAttr('disabled');
			
			if (this.options.sortable === true) {
				this.linkList.sortable({
					placeholder:	'ui-state-highlight',
					cancel:			'.deleted, input, select, .linkAction, .llItemControl, .tabListButton, .tabListButtonLink',
					start:
						function(event, ui){
							ui.helper.addClass('dragged');
						}
				});
			}
			
			this._linkItemsBind(true);
			this._addLinkBind();
			this._urlValueUpdate();
			this._tabsSetup();
			this._urlValueBind();
			
				// Activate add link button.
			this.addLinkBut.removeClass('disabled');
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
			this.element.find('.dataAdded').removeClass('dataAdded').removeData();
			this.element.find('.bound').removeClass('bound').off();
		},
		
		
		
		/* Tabs Methods */
		/* ------------ */
		
		/**
		* Binds the add link button.
		* 
		* qparam jQuery context The context of where to look for tabs.
		*/
		_tabsSetup: function(context) {
			if (context === undefined) context = this.linkList;
			
			if (context.length < 1) return;
			
			var tabs = $('.sbpTabList', context).not('.initialised');
			
			if (tabs.length < 1) return;
			
			var widget = this;
			
			tabs.addClass('initialised').each(function(){
				var thisTabs			= $(this);
				var tabListButtons		= thisTabs.children('.tabListButtons');
				var tabButtons			= tabListButtons.children();
				var llItemContent		= thisTabs.closest('.llItemContent');
				var thisDashIcon		= llItemContent.siblings('.llItemHeader').children('.dashicons');
				var tabContent			= thisTabs.children('.tabListContentPane');
				var hideOnNonUrl		= llItemContent.find('.hideOnNonUrl');
				
				if (tabButtons.length < 1 || tabContent.length < 1) return;
				
				tabButtons.each(function(){
					var thisBut		= $(this);
					var thisLink	= thisBut.children();
					
					if (thisLink.hasClass('bound')) return true; 
					
					var dashIcon	= thisBut.attr('data-icon');
					var tabId		= thisLink.attr('href').substr(1);
					var thisContent	= tabContent.filter('[id="' + tabId + '"]');
					
					if (thisContent.length > 0) {
						var thisRadio	= thisContent.children('label').children('input');
						var sibButtons	= thisBut.siblings();
						var sibContent	= tabContent.not(thisContent);
						
						thisLink.addClass('bound').on('click', function(event){
							event.preventDefault();
							
							if (!thisBut.hasClass('selected')) {
								sibButtons.removeClass('selected');
								thisBut.addClass('selected');
								thisRadio.click();
								
								sibContent.hide();
								thisContent.show();
								thisBut.trigger('toggleUrl');
								thisBut.trigger('updateIcon');
								widget._urlValueUpdate();
							}
						});
						
					} else {
						thisBut.addClass('disabled');
						thisLink.addClass('bound').on('click', function(event){
							event.preventDefault();
						});
					}
					
						// Show hide the use record title checkbox.
					thisBut.on('toggleUrl', function(){
						if ($(this).hasClass('urlTab')) {
							hideOnNonUrl.hide();
							
						} else {
							hideOnNonUrl.show();
						}
					});
					
						// Stup update icon.
					thisBut.on('updateIcon', function(){
						thisDashIcon.removeAttr('style').attr('class', 'dashicons ' + dashIcon);
					});
				});
				
					// Hide buttons if required.
				var disabledCount = tabButtons.filter('.disabled').length;
				
				if (tabButtons.length < 2 || tabButtons.length - disabledCount < 2) tabListButtons.hide();
				
					// Decide if the use rec title checkbox should be visible.
				tabButtons.filter('.selected').trigger('toggleUrl').trigger('updateIcon');
				
			});
			
			this._addNewBindToggle(tabs);
			this._mediaBindLinks(tabs);
		},
		
		
		
		/* URL Value Methods */
		/* ----------------- */
		
		/**
		* Updates the list value.
		* 
		* @param jQuery context The context of where to look for search fields.
		*/
		_urlValueBind: function(context) {
			if (context === undefined) context = this.linkList;
			
			var linkFields		= $('input.linkUrlUrl', context).not('bound');
			var searchFields	= $('input.sbpRecordSearchTerm', context).not('bound');
			var widget			= this;
			
			if (linkFields.length > 0) {
				linkFields.addClass('bound').on('change blur keyup', function(event){
					widget._urlValueUpdate();
				});
			}
			
			if (searchFields.length > 0) {
				var acItem	= this.options.templates.acItem;
				var acLink	= this.options.templates.acLink;
				var labels	= this.options.lables;
				
				searchFields.addClass('bound').each(function(){
					var searchField		= $(this);
					var subTypeField	= searchField.siblings('.sbpLinkPostType');
					var current			= searchField.siblings('.sbpLinkCurrent');
					var addNewToggle	= searchField.siblings('.sbpAddNew').children('.sbpAddNewToggle');
					
						// Bind selects tags
					if (subTypeField.prop('tagName') == 'SELECT') {
							// Add default placeholder.
						searchField.attr('placeholder', subTypeField.children(':selected').attr('data-placeholder'));
						
							// Bind.
						subTypeField.on('change', function(event){
							searchField.attr('placeholder', $(this).children(':selected').attr('data-placeholder'));
						});
					}
					
						// Bind searchfield.
					searchField.on('reset', function(event){
						$(this).val('');
						
					}).on('error', function(event){
						$(this).trigger('reset');
						
					}).autocomplete({
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
										function (ajaxData) {
											if (ajaxData.error === true) { // Server error
										 		response({});
										   	 
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
									// Item creation callback
								$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
									return	$(acItem).attr({
												'data-value':	item.ID,
												'data-type':	item.post_status,
												'title':		'[' + ((labels['status_' + item.post_status] !== undefined) ? labels['status_' + item.post_status] : item.post_status) + '] ' + item.post_title
											}).html(acLink.replace(/###ITEM_NAME###/g, item.post_title)).appendTo(ul);
								};
			
									// Menu creation callback
								$(this).data('ui-autocomplete')._renderMenu = function(ul, items) {
									var that = this;
									ul.addClass('sbpAutoComplete');
			
									$.each(items, function(index, item) {
													console.log(item);
										that._renderItemData(ul, item); // Add this item to the autocomplete ul
									});
								};
							},
						select:
							function(event, ui) {
								searchField.trigger('reset');
								current.children().filter('.searchAdded').off().removeData().remove();
								current.children().not('.deleted').addClass('deleted');
								current.append(widget._urlValueAddNewRec(ui.item, current)).removeClass('hidden');
								widget._urlValueUpdate();
								addNewToggle.trigger('reset');
								return false; // Don't change the search field value.
							}
					});
				});
			}
		},
		
		/**
		* Returns a new record to add to current.
		* 
		* @param object theItem The new rec object clicked on in the autocomplete, or added via addnew.
		* @param jQuery appendTarget The append target.
		* @return jQuery newrec The new record.
		*/
		_urlValueAddNewRec: function(theItem, appendTarget) {
			var newRec			= $(this.options.templates.rec.replace(/###ITEM_ID###/g, theItem.ID).replace(/###ITEM_NAME###/g, theItem.post_title).replace(/###ITEM_TYPE###/g, theItem.post_type));
			var newLink			= $(this.options.templates.recLink.replace(/###ITEM_ID###/g, theItem.ID));

			newRec.append(newLink).append(theItem.post_title);
			newRec.addClass('dataAdded').data('recData', theItem);
			
				// Bind.
			this._urlValueBindNewRec(newLink, appendTarget);
			this._urlValueSetUpdateTitle(theItem.post_title, appendTarget);
			
			return newRec;
		},
		
		/**
		* Sets up the title of the current record and preps the item to display the title.
		* 
		* @param string linkRecTitle The record title.
		* @param jQuery appendTarget The append target.
		*/
		_urlValueSetUpdateTitle: function(linkRecTitle, appendTarget) {
			var linkListItem	= null;
			
				// Find link list item:
			if (appendTarget.hasClass('linkItemFound')) {
				linkListItem = appendTarget.data('linkItem');
				
			} else {
				linkListItem = appendTarget.closest('.linkListItem');
				
				if (linkListItem.length > 0) appendTarget.addClass('linkItemFound dataAdded').data('linkItem', linkListItem);
			}
			
				// Log record title, force the title field to update header text.
			if (linkListItem.length > 0) linkListItem.addClass('dataAdded').data('linkRecTitle', linkRecTitle).trigger('updateTitle');
		},
		
		/**
		* Binds a record X icon link.
		* 
		* @param jQuery theRecLink The x A tag to bind.
		* @param jQuery appendTarget The append target.
		*/
		_urlValueBindNewRec: function(theRecLink, appendTarget) {
			if (theRecLink.hasClass('bound')) return;
			
			var theRec			= theRecLink.parent();
			var theInput		= theRec.find('input');
			var postTypeField	= appendTarget.closest('.tabListContentPane').find('.sbpLinkPostType');
			var isSelect		= (postTypeField.prop('tagName') == 'SELECT') ? true : false;
			var widget			= this;
			
			theRecLink.addClass('bound').on('click', function(event){
				event.preventDefault();
				event.stopPropagation();
				
				theRec.toggleClass('deleted');
				theInput.prop('checked', !theInput.prop('checked'));
				
				if (theRec.hasClass('searchAdded')) { // If search added, remove it.
						// First show the already existing server rendered record.
					var prevSib = theRec.prev();
					
						// Remove this search added one.
					$(this).off();
					theRec.remove();
					
						// Set real val back to the server rendered ID.
					if (prevSib.length > 0) {
						prevSib.removeClass('deleted');
						
							// Set select back to correct option.
						if (isSelect) postTypeField.val(prevSib.attr('data-posttype')).trigger('change');
					}
					
				} else if (theRec.hasClass('alreadyAdded')) {
					var searchAddedSibs	= theRec.siblings('.searchAdded');
					var sibLinegth		= searchAddedSibs.length;
					
					searchAddedSibs.find('.bound').off();
					searchAddedSibs.remove();
				}
				
				widget._urlValueUpdate();
			});
		},
		
		/**
		* Updates the list value.
		*/
		_urlValueUpdate: function() {
			var items		= this.linkList.children().not('.deleted');
			var numOfItems	= items.length;
			
			if (numOfItems < 1) {
				this.linkListInput.val('');
				
			} else {
				var newVal = {items: []};
				
				for (var theVal = 0; theVal < numOfItems; theVal ++) {
					var thisItem		= items.eq(theVal);
					var updateFields	= thisItem.data('updateFields');
					
					if (updateFields === undefined) continue;
					
					var urlVal	= this._urlValueGet(updateFields['urlList']);
					var urlType	= urlVal.split('|')[0];
					var newItem = {
						'text':			$.trim(updateFields['title'].val()),
						'url':			urlVal,
						'target':		$.trim(updateFields['target'].val()),
						'nofollow':		(updateFields['noFollow'].is(':checked')) ? 1 : 0,
						'urlImage':		'',
						'linkType':		$.trim(updateFields['linkType'].val()),
						'description':	$.trim(updateFields['description'].val())
					};
					
					if (urlType == 'url') {
						var sbpMediaImg = updateFields['urlList'].filter('[data-tabkey="url"]').find('.sbpMediaBox').children('.sbpThumbnail').children('img');
						
						if (sbpMediaImg.length > 0) newItem['urlImage'] = sbpMediaImg.attr('data-postid') + '|' + sbpMediaImg.attr('data-posttype');
					}
					
					newVal['items'].push(newItem);
				}
				
				this.linkListInput.val(JSON.stringify(newVal));
			}
		},
		
		/**
		* Returns the correct URL value.
		* 
		* @param jQuery urlList The URL List with fields to bind.
		* @return string The string for the URL value.
		*/
		_urlValueGet: function(urlList) {
			var optSel		= urlList.children('.tabContentTitle').children('input:checked');
			var optSelVal	= optSel.val();
			var optValBox	= optSel.parent().parent().children('div');
			var optVal		= '';
			var typeVal		= $.trim(optValBox.find('.sbpLinkPostType').val());
			
			if (typeVal == 'url') {
				optVal = $.trim(optValBox.children('input').not('.sbpLinkPostType').val());
				
			} else {
				var selRec = optValBox.find('.sbpLinkCurrent').children().not('.deleted').eq(0);
				
				if (selRec.length > 0 && selRec.attr('data-recid') != '') optVal = parseInt(selRec.attr('data-recid'));
			}
			
			return typeVal + '|' + optVal;
		},
		
		
		
		/* Add Link Methods */
		/* ---------------- */
		
		/**
		* Binds the add link button.
		*/
		_addLinkBind: function() {
			if (this.addLinkBut.length < 1)			return;
			if (this.linkListDummy.length < 1)		return;
			if (this.addLinkBut.hasClass('bound'))	return;
			
			var linkTemplate = this.linkListDummy.children().eq(0);
			if (linkTemplate.length < 1) return;
			
			var widget = this;
			
			this.addLinkBut.addClass('bound').on('click', function(event){
				event.preventDefault();
				event.stopPropagation();
				
				if ($(this).hasClass('disabled')) return;
				
				var currentNum	= widget.linkList.children().length;
				var newLinkItem = linkTemplate.clone(false);
				
					// Adjust:
				var labels	= newLinkItem.find('label');
				var inputs	= newLinkItem.find('input, select');
				var tabs	= newLinkItem.find('.sbpTabList');
				
				if (labels.length > 0) {
					labels.each(function(){
						var thisLabel = $(this);
						
						if (thisLabel.attr('for') !== undefined) thisLabel.attr('for', thisLabel.attr('for').replace('dummy', currentNum));
					});
				}
				
				if (inputs.length > 0) {
					inputs.each(function(){
						var thisInput = $(this);
						
						if (thisInput.attr('id') !== undefined)		thisInput.attr('id', thisInput.attr('id').replace('dummy', currentNum));
						if (thisInput.attr('name') !== undefined)	thisInput.attr('name', thisInput.attr('name').replace('dummy', currentNum));
					});
				}
				
				if (tabs.length > 0) {
					var tabLinks	= tabs.children('.tabListButtons').find('a');
					var tabContents	= tabs.children('.tabListContentPane');
					
					if (tabLinks.length > 0) {
						tabLinks.each(function(){
							var thisLink = $(this);
							
							if (thisLink.attr('href') !== undefined) thisLink.attr('href', thisLink.attr('href').replace('dummy', currentNum));
						});
					}
					
					if (tabContents.length > 0) {
						tabContents.each(function(){
							var thisContent = $(this);
							
							if (thisContent.attr('id') !== undefined) thisContent.attr('id', thisContent.attr('id').replace('dummy', currentNum));
						});
					}
				}
				
					// Add to DOM and bind.
				widget.empty.addClass('hidden');
				widget.linkList.append(newLinkItem);
				widget._linkItemsBind();
				widget._tabsSetup(newLinkItem);
				widget._urlValueBind(newLinkItem);
				
				newLinkItem.children('.llItemHeader').trigger('click');
				newLinkItem.children('.llItemContent').children('.llItemField').eq(0).children('input').focus();
			});
		},
		
		
		
		/* Link Item Methods */
		/* ----------------- */
		
		/**
		* Binds link items.
		* 
		* @param boolean isInitialBind Is this the initial bind? Means are we binding the already existing items on page load?
		*/
		_linkItemsBind: function(isInitialBind) {
			if (isInitialBind !== true) isInitialBind = false;
			if (this.linkList.length < 1) return;
			
			var linkListItems = this.linkListItems = this.linkList.children('.linkListItem');
			if (this.linkListItems.length < 1) return;
			
			var widget = this;
			
			this.linkListItems.not('.bound').each(function(event){
				var thisItem		= $(this).addClass('bound');
				var thisHeader		= $(this).children('.llItemHeader');
				var thisContent		= $(this).children('.llItemContent');
				var untitledLink	= (thisHeader.attr('data-notitle') !== undefined) ? $.trim(thisHeader.attr('data-notitle')) : '&nbsp;';
				
					// Bind fields.
				var headerTitle			= thisHeader.children('.llItemLabel');
				var titleField			= thisContent.find('input.linkTitle');
				var descriptionField	= thisContent.find('input.linkDescription');
				var targetField			= thisContent.find('select.linkTarget');
				var linkTypeField		= thisContent.find('select.linkType');
				var noFollowField		= thisContent.find('input.linkNoFollow');
				var urlField			= thisContent.find('input.linkUrl');
				var urlList				= urlField.siblings('.sbpTabList').children('.tabListContentPane');
				var removeLink			= thisContent.find('a.linkAction').filter('[data-action="removeLink"]');
				
					// Store the update fields for later.
				thisItem.addClass('dataAdded bound').data('updateFields', {
					'title':		titleField,
					'url':			urlField,
					'target':		targetField,
					'noFollow':		noFollowField,
					'urlList':		urlList,
					'linkType':		linkTypeField,
					'description':	descriptionField
					
				}).on('updateTitle', function(event){
					titleField.trigger('titleChange');
					
				});
				
					// Title field.
				if (!titleField.hasClass('bound')) {
					//appendTarget.data('linkRecTitle', theItem.post_title);
					
						// Bind header.
					titleField.addClass('bound').on('titleChange', function(event){
						var newTitle = $.trim($(this).val());
						if (newTitle == '') newTitle = untitledLink;
						
							// See if we need to add the title.
						if (newTitle.indexOf('%t%') > -1 && thisItem.data('linkRecTitle') !== undefined) newTitle = newTitle.replace('%t%', thisItem.data('linkRecTitle'));
						
						headerTitle.html(newTitle);
						thisHeader.attr('title', newTitle);
						
					}).on('keyup', function(event){
						$(this).trigger('titleChange');
						
					}).on('change', function(event){
						$(this).trigger('titleChange');
						widget._urlValueUpdate();
						
					}).on('blur', function(event){
						$(this).trigger('titleChange');
					});
				}
				
					// Description field.
				if (!descriptionField.hasClass('bound')) {
					descriptionField.addClass('bound').on('change', function(event){
						widget._urlValueUpdate();
						
					}).on('blur', function(event){
						widget._urlValueUpdate();
					});
				}
				
					// Target select.
				if (!targetField.hasClass('bound')) {
					targetField.addClass('bound').on('change', function(event){
						widget._urlValueUpdate();
					});
				}
				
					// Link type select.
				if (!linkTypeField.hasClass('bound')) {
					linkTypeField.addClass('bound').on('change', function(event){
						widget._urlValueUpdate();
					});
				}
				
					// URL field.
				if (!urlField.hasClass('bound')) {
					urlField.addClass('bound').on('change', function(event){
						widget._urlValueUpdate();
					});
				}
				
					// No follow checkbox.
				if (!noFollowField.hasClass('bound')) {
					noFollowField.addClass('bound').on('change', function(event){
						widget._urlValueUpdate();
					});
				}
				
					// Header.
				if (!thisHeader.hasClass('bound')) {
					thisHeader.addClass('bound').on('click', function(event){
						event.preventDefault();
						
						if (thisItem.hasClass('dragged')) {
							thisItem.removeClass('dragged');
							widget._urlValueUpdate();
							
						} else {
							if (thisItem.hasClass('open')) {
								thisContent.hide();
								widget._urlValueUpdate();
							
							} else {
								if (thisItem.hasClass('deleted')) {
									thisItem.removeClass('deleted');
									widget._urlValueUpdate();
									return;
									
								} else {
									thisContent.show();
								}
							}
							
							thisItem.toggleClass('open');
						}
					});
				}
				
					// Remove link.
				if (!removeLink.hasClass('bound')) {
					removeLink.addClass('bound').on('click', function(event){
						event.preventDefault();
						event.stopPropagation();
						
						if (thisItem.hasClass('deleted')) {
							thisItem.removeClass('deleted');
							
						} else {
							thisItem.addClass('deleted');
							thisHeader.trigger('click');
						}
					});
				}
				
				widget._linkItemsUrlListBind(widget, urlList);
				
				if (isInitialBind) {
					var currentRs		= thisContent.children('.llItemField-urlList').children('.sbpTabList').find('.sbpLinkCurrent').not('hidden');
					var currentRsItem	= currentRs.children('span');
					
					if (currentRsItem.length > 0) {
						widget._urlValueBindNewRec(currentRsItem.children('a'), currentRs);
						widget._urlValueSetUpdateTitle(currentRsItem.attr('data-reclabel'), currentRs);
					}
				}
			});
		},
		
		/**
		* Binds fields in the URL list.
		* 
		* @param jQuery Widget widget This widget.
		* @param jQuery urlList The URL List with fields to bind.
		*/
		_linkItemsUrlListBind: function(widget, urlList) {
			var urlListItems	= urlList.children('.urlListItem');
			var urlListDivs		= urlListItems.children('div');
			var urlListRadios	= urlListItems.children('label').children('input').not('.bound');
			var linkUrlUrl		= urlListDivs.find('input.linkUrlUrl').not('bound');
			
			if (urlListRadios.length > 0) {
				urlListRadios.addClass('bound').on('change', function(event){
					widget._urlValueUpdate();
				});
			}
			
			if (linkUrlUrl.length > 0) {
				linkUrlUrl.addClass('bound').on('change', function(event){
					widget._urlValueUpdate();
				});
			}
		},
		
		
		
		/* Media Browser Methods */
		/* --------------------- */
		
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
				library: {
					type: 'image'
				},
				button: {
					text: opener.attr('data-buttontxt'),
				},
				multiple: false
			});
				
				// Bind the select method.
			this.mediaBrowser.on('select', function() {
				attachment = widget.mediaBrowser.state().get('selection').toJSON(); // Get the attachments as a JSON object.
				
					// Now process the data.
				widget._mediaBrowserAdd(attachment, opener);
			});
			
				// Finally, open the modal.
			this.mediaBrowser.open();
		},
		
		/**
		* Adds attachment thumbnails to the post media.
		* 
		* @param object attachments The attachments object returned by the media browser.
		* @param jQuery opener The button that wants to open the media player.
		*/
		_mediaBrowserAdd: function(attachments, opener) {
			for (var media in attachments) {
				var thisMedia	= attachments[media];
				var thisId		= thisMedia.id;
				var postType	= 'attachment';
				var postTitle	= thisMedia.title;
				var newImage	= $(this.options.templates.urlMedia);
				
				newImage.children('img').attr({
					alt:				thisMedia.alt,
					src:				thisMedia.sizes.thumbnail.url,
					'data-postid':		thisId,
					'data-posttype':	postType
				});
				
				opener.parent().before(newImage);
				opener.addClass('hidden').siblings('.sbpMediaLink').removeClass('hidden');
				break; // There is only one.
			}
			
			this._urlValueUpdate();
		},
		
		/**
		* Binds media browser opener/remover links.
		* 
		* @param jQuery mediaLinksTargets Where to look for media add links.
		*/
		_mediaBindLinks: function(mediaLinksTargets) {
			if (mediaLinksTargets.length < 1) return;
			
			var mediaLinks = mediaLinksTargets.find('.sbpMediaLink').not('.bound');
			
			if (mediaLinks.length < 1) return;
			
			var widget = this;
			
			mediaLinks.addClass('bound').each(function(){
				var thisLink	= $(this);
				var sibLink		= thisLink.siblings('.sbpMediaLink');
				var isRemover	= thisLink.hasClass('sbpMediaRemover');
				var sbpMediaBox	= thisLink.closest('.sbpMediaBox');
				
				thisLink.on('click', function(event){
					event.preventDefault();
					
					if (isRemover) {
						sbpMediaBox.children('.sbpThumbnail').off().remove();
						thisLink.addClass('hidden');
						sibLink.removeClass('hidden');
						widget._urlValueUpdate();
						
					} else {
						widget._mediaBrowserCreate(thisLink);
					}
				});
			});
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
				var tabListContentPane	= thisAddNewTarget.closest('.tabListContentPane');
				var subTypeField		= tabListContentPane.find('.sbpLinkPostType');
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
									sbpLinkCurrent.children().filter('.searchAdded').off().removeData().remove();
									sbpLinkCurrent.children().not('.deleted').addClass('deleted');
									sbpLinkCurrent.append(widget._urlValueAddNewRec(ajaxData.result, sbpLinkCurrent)).removeClass('hidden');
									widget._urlValueUpdate();
									
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