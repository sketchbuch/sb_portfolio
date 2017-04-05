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
 * A widget for handling option box rows. This shows information like the default WP publishing box.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.textList', {
		options: {
			templates: {
				acItem: 	'<li></li>',
				acLink:		'<a><span class="label labelTitle">###ITEM_NAME###</span></a>'
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
			errTimeout:	2000
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			this.textListRows = this.element.children('.sbpTextListRow');
			
			if (this.textListRows.length < 1) return;
			
			var widget = this;
			
			this.textListRows.each(function(){
				var curRow		= $(this);
				var rowValue	= curRow.find('.sbpTextListValue').children('strong');
				var rowField	= curRow.find('.sbpTextListRowField').not('.bound');
				var controls	= curRow.find('a').filter('.sbpTextListControl').not('.bound');
				
				widget._recordBindSearch(curRow, rowValue, rowField, controls);
				widget._controlButtonsBind(curRow, rowValue, rowField, curRow.children('.sbpTextListContent'), controls);
				widget._addNewBindToggle(curRow);
			});
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
			this.element.off().find('.bound').off();
		},
		
		
		
		/* Control Buttons */
		/* --------------- */
		
		/**
		* Binds the record selector search field.
		* 
		* @param jQuery curRow The current row.
		* @param jQuery rowValue The tag that holds the current value.
		* @param jQuery rowField The input that holds the current value.
		* @param jQuery controls The control buttons.
		*/
		_recordBindSearch: function(curRow, rowValue, rowField, controls) {
			var postTypeField = curRow.find('.sbpPostTypeField');
			
			if (postTypeField.length < 1) return; // No search record.
			
			var closer		= controls.filter('[href="#close"]');
			var acItem		= this.options.templates.acItem;
			var acLink		= this.options.templates.acLink;
			var widget		= this;
			var searchField	= curRow.find('.sbpRecordSearchField');
			var labels		= this.options.lables;
			
			searchField.on('reset', function(event){
				$(this).val('');
				
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
							    subType:	postTypeField.val(),
							    postId:		$('#post_ID').val()
							},
							success:
								function (ajaxData) {
									if (ajaxData.error === true) { // Server error
								 		response({});
								 		searchField.trigger('reset');
								   	 
									} else { // Everything went ok with the server
										if (ajaxData.result === undefined || ajaxData.result.length < 1) { // No results
											response({});
										        
										} else { // There are results
							           			// Check if terms are already selected.
							           		var finalResult = [];
							           		
							           			// Filter out any existing terms.
							           		for (var term in ajaxData.result) {
							           				// Term not already there.
												if (ajaxData.result[term].ID != rowField.val()) {
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
						rowField.val(ui.item.ID).attr('data-labelvalue', ui.item.post_title);
						searchField.trigger('reset');
						closer.trigger('click');
						return false; // Don't change the search field value.
					}
			});
		},
		
		
		
		/* Control Buttons */
		/* --------------- */
		
		/**
		* Binds the control buttons.
		* 
		* @param jQuery curRow The current row.
		* @param jQuery rowValue The tag that holds the current value.
		* @param jQuery rowField The input that holds the current value.
		* @param jQuery listContent The tag containing the content for the row.
		* @param jQuery controls The control buttons.
		*/
		_controlButtonsBind: function(curRow, rowValue, rowField, listContent, controls) {
			var opener			= controls.filter('[href="#open"]');
			var searchField		= curRow.find('.sbpRecordSearchField');
			var isNumeric		= rowField.hasClass('isNumeric');
			var settingToUpdate	= $.trim(((rowField.attr('data-updatesetting') !== undefined) ? rowField.attr('data-updatesetting') : ''));
			var hasSetting		= (settingToUpdate != '' && sbpSettings[settingToUpdate] !== undefined) ? true : false;
			
			controls.each(function(){
				var thisBut	= $(this);
				var butType	= thisBut.attr('href').substr(1);
				
				thisBut.addClass('bound');
				
				switch(butType) {
					case 'open':
						thisBut.on('click', function(event){
							event.preventDefault();
							listContent.slideDown('fast');
							$(this).hide();
							
							if (searchField.length > 0) searchField.trigger('reset');
						});
					break;
					
					case 'close':
						thisBut.on('click', function(event){
							event.preventDefault();
							listContent.slideUp('fast');
							opener.show();
							
							var newVal		= (isNumeric) ? parseInt(rowField.val()) : $.trim(rowField.val());
							var newValLabel	= $.trim(rowField.attr('data-labelvalue'));
							
							if (newVal <= 0) {
								rowValue.text(rowValue.attr('data-resettext'));
								rowField.val('').removeAttr('data-lastval');
								rowField.attr('data-labelvalue', '');
								
								if (hasSetting) sbpSettings[settingToUpdate] = sbpSettings['flickrApiKeyDefault'];
								
							} else {
								if (newValLabel != '') {
									rowValue.text(newValLabel);
									
								} else {
									rowValue.text(newVal);
								}
								
								rowField.attr('data-lastval', newVal);
								if (hasSetting) sbpSettings[settingToUpdate] = newVal;
							}
							
							if (searchField.length > 0) searchField.trigger('reset');
						});
					break;
					
					case 'reset':
						thisBut.on('click', function(event){
							event.preventDefault();
							listContent.slideUp('fast', function(){
								if (rowField.attr('data-lastval') !== undefined) {
									rowField.val(rowField.attr('data-lastval'));
									
								} else {
									rowField.val('');
								}
							});
							opener.show();
							
							if (searchField.length > 0) searchField.trigger('reset');
						});
					break;
					
					case 'remove':
						thisBut.on('click', function(event){
							event.preventDefault();
							
							listContent.slideUp('fast');
							opener.show();
							rowValue.text(rowValue.attr('data-removetext'));
							rowField.val('').removeAttr('data-lastval');
							searchField.trigger('reset');
							
							if (hasSetting) sbpSettings[settingToUpdate] = sbpSettings['flickrApiKeyDefault'];
						});
					break;
					
					default:
						console.info('TextList._controlButtonsBind() - unknown butType: "' + butType + '"');
					break;
				}
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
				var thisToggle		= $(this);
				var thisParent		= thisToggle.parent();
				var addNewFields 	= thisToggle.siblings('.sbpAddNewFields');
				var addNewTitle		= addNewFields.children('.addNewTitle').not('.bound');
				var addNewSubmit	= addNewFields.children('.addNewSubmit').not('.bound');
				var posttypeField	= thisParent.find('.sbpLinkPostType');
				var sbpAjaxError	= thisParent.siblings('.sbpAjaxError');
				var searchField		= thisParent.siblings('.sbpRecordSearchField');
				
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
							subType:	posttypeField.val(),
							newTitle:	$.trim(addNewTitle.val()),
							postId:		$('#post_ID').val()
						},
						success:
							function(ajaxData, textStatus, jqXHR) {
								if (ajaxData.error === undefined || ajaxData.error === false) {
										// Add new item.
									/*sbpLinkCurrent.children().filter('.searchAdded').off().removeData().remove();
									sbpLinkCurrent.children().not('.deleted').addClass('deleted');
									sbpLinkCurrent.append(widget._urlValueAddNewRec(ajaxData.result, sbpLinkCurrent)).removeClass('hidden');
									widget._urlValueUpdate();*/
									
										// Hide and reset add new form.
									thisToggle.trigger('reset');
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
				thisToggle.on('reset', function(){
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
			
				// Hide message:
			setTimeout(function(){ajaxResponse.addClass('hidden');}, this.options.errTimeout);
		}
	});
} (jQuery));