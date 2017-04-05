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
 * A widget for SB Portfolio for WordPress that lets you select existing taxonomy terms by searching and then listing them like the tags metabox.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.taxonomyRecordSelector', {
		options: {
			templates: {
				acItem: 	'<li></li>',
				acLink:		'<a><span class="label labelTitle">###ITEM_NAME###</span></a>',
				term:		'<span data-termid="###ITEM_ID###" data-termlabel="###ITEM_NAME###" class="searchAdded"></span>',
				termLink:	'<a data-value="###ITEM_ID###" class="ntdelbutton">X</a>',
				termInput:	'<input type="checkbox" class="alwaysHidden recAddInput" checked="checked" id="###ITEM_TYPE###-all-###ITEM_ID###" name="sb_portfolio[###ITEM_TYPE###][]" value="###ITEM_ID###">'
			},
			termType:	'tag',
			taxonomy:	'post_tag',
			limit:		10,
			errTimeout:	2000
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			var current			= this.current = this.element.siblings('.rsCurrent');
			var currentXlinks	= current.children('span');
			var numOfLinks		= currentXlinks.length;
			this.tagCloud		= this.element.siblings('.the-tagcloud');
			this.ajaxResponse	= this.element.siblings('.ajaxResponse');
			
				// Bind existing terms.
			if (numOfLinks > 0) {
				for (var link = 0; link < numOfLinks; link ++) {
					this._termBindLink(currentXlinks.eq(link).children('a'));
				}	
			}
			
				// Get default AJAX response.
			if (this.ajaxResponse.length > 0) this.ajaxResponse.attr('data-defaultStr', this.ajaxResponse.text()); 
			
			this._searchFieldBind(current);
			this._searchFieldButtonBind();
			this._termBindPopularLinks(current);
			this._termBindPopularToggle();
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
			this.element.find('.bound').off();
			this.element.autocomplete('destroy');
		},
		
		
		
		/* Search Field Methods */
		/* -------------------- */
		
		/**
		* Binds the search field "Add" button.
		*/
		_searchFieldButtonBind: function() {
			var adderButton = this.element.siblings('.adderButton').not('.bound');
			
			if (adderButton.length < 1) return;
			
			var widget = this;
			
			adderButton.addClass('bound').on('click', function(event, searchCreate){
				event.preventDefault();
				event.stopPropagation();
				
				var conf	= {
					url:		ajaxurl,
					type:		'POST',
					cache:		false,
					dataType:	'json',
					data:		{
						action:		'addterm',
						security:	$('#_wpnonce-sbp').val(),
						taxonomy:	$.trim(widget.options.taxonomy),
						termName:	$.trim(widget.element.val())
					},
					success:
						function(ajaxData, textStatus, jqXHR) {
							if (ajaxData.error === undefined || ajaxData.error === false) {
								widget._searchFieldAdder_success(ajaxData, textStatus, jqXHR);
								
							} else {
								widget._searchFieldAdder_error(jqXHR, textStatus, 'successError');
							}
						},
					error:
						function(jqXHR, textStatus, errorThrown) {
							widget._searchFieldAdder_error(jqXHR, textStatus, errorThrown);
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
		_searchFieldAdder_error: function(jqXHR, textStatus, errorThrown) {
			var ajaxResponseStr = this.ajaxResponse.attr('data-defaultStr');
			
				// Get error thrown by WP.
			if (jqXHR.responseJSON !== undefined && jqXHR.responseJSON.result !== undefined) {
				if (jqXHR.responseJSON.result.errors !== undefined && jqXHR.responseJSON.result.error_data !== undefined) {
					ajaxResponseStr = '';
					
					for (var err in jqXHR.responseJSON.result.errors) {
						for (var errTxt in jqXHR.responseJSON.result.errors[err]) {
							if (ajaxResponseStr != '') ajaxResponseStr += '<br /><br />';
							
							ajaxResponseStr += '<strong>' + jqXHR.responseJSON.result.errors[err][errTxt] + '</strong>';
						}
					}
				}
			}
			
			var ajaxResponse = this.ajaxResponse;
				ajaxResponse.html(ajaxResponseStr).removeClass('hidden');
			
				// Hide message.
			setTimeout(function(){ajaxResponse.addClass('hidden');}, this.options.errTimeout);
		},
		
		/**
		* Called if successful.
		* 
		* @param mixed ajaxData The data returned by the server.
		* @param string textStatus The request's text status.
		* @param jqXHR jqXHR The jQuery jqXHR object.
		*/
		_searchFieldAdder_success: function(ajaxData, textStatus, jqXHR) {
			this.current.append(this._termNew(ajaxData.result)).removeClass('hidden');
			this._searchFieldReset();
		},
		
		/**
		* Binds the search field.
		* 
		* @param jQuery current The rsCurrent tag.
		*/
		_searchFieldBind: function(current) {
			if (this.element.hasClass('bound')) return;
			
			var widget			= this;
			var acItem			= this.options.templates.acItem;
			var acLink			= this.options.templates.acLink;
			var theTagCloud		= this.tagCloud;
			
				// Create search box autocmplete.
			this.element.addClass('bound').on('keyup', function(event){
				if ($(this).val().indexOf(',') > -1) $(this).val($(this).val().replace(/,/g, ''));
				
			}).on('blur', function(event){
				if ($(this).val().indexOf(',') > -1) $(this).val($(this).val().replace(/,/g, ''));
				
			}).on('change', function(event){
				if ($(this).val().indexOf(',') > -1) $(this).val($(this).val().replace(/,/g, ''));
				
			}).autocomplete({
				minLength: 1,
				source:
					function (request, response) {
						$.ajax({
							url:		ajaxurl,
							type:		'GET',
							cache:		false,
							dataType:	'json',
							data: {
								action:		'searchterm',
								security:	$('#_wpnonce-sbp').val(),
								limit:		parseInt(widget.options.limit),
								taxonomy:	$.trim(widget.options.taxonomy),
								termName:	$.trim(request.term)
							},
							success:
								function (ajaxData) {
									if (ajaxData.error === true) { // Server error
								 		response({});
								   	 
									} else { // Everything went ok with the server
										if (ajaxData.result.length < 1) { // No results
								   			response({});
							                
							           } else { // There are results
							           			// Check if terms are already selected.
							           		var finalResult = [];
							           		
							           			// Filter out any existing terms.
							           		for (var term in ajaxData.result) {
							           				// Term not already there.
												if (current.children().not('.deleted').filter('[data-termid="' + ajaxData.result[term].term_id + '"]').length < 1) {
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
				create:
					function() {
							// Item creation callback
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							return $(acItem)
								.attr('data-value', item.term_id).html(acLink.replace(/###ITEM_NAME###/g, item.name))
								.appendTo(ul);
						};
	
							// Menu creation callback
						$(this).data('ui-autocomplete')._renderMenu = function(ul, items) {
							var self = this;
							ul.addClass('sbpAutoComplete');
	
							$.each(items, function(index, item) {
								self._renderItemData(ul, item); // Add this item to the autocomplete ul
							});
						};
					},
				select:
					function(event, ui) {
							// Check if there is a deleted term already, if there is: Show it.
						var deletedTerm = current.children('.deleted').filter('[data-termid="' + ui.item.term_id + '"]');
						
						if (deletedTerm.length > 0) {
							deletedTerm.children('a').trigger('click');
							
						} else {
								// Check if there is a popular term and click that instead.
							var popTerm = theTagCloud.children('a').not('.added').filter('[data-termid="' + ui.item.term_id + '"]');
						
							if (popTerm.length > 0) {
								popTerm.trigger('click', [true]);
								
							} else { // Add new term to DOM.
								current.append(widget._termNew(ui.item)).removeClass('hidden');
							}
						}
						
						widget._searchFieldReset();
						
						return false; // Don't change the search field value.
					}
			});
		},
		
		/**
		* Clears the search field.
		*/
		_searchFieldReset: function() {
			this.element.trigger('blur');
			this.element.val('');
		},
		
		
		
		/* Term Methods */
		/* ------------ */
		
		/**
		* Binds the popular terms links.
		* 
		* @param jQuery current The rsCurrent tag.
		*/
		_termBindPopularLinks: function(current) {
			if (this.tagCloud.length < 1) return;
			
			var popularTerms = this.tagCloud.children('a').not('.bound');
			
			if (popularTerms.length < 1) return;
			
			var widget = this;
			
			popularTerms.addClass('bound').on('click', function(event, searchCreate){
				event.preventDefault();
				event.stopPropagation();
				
				var thisId = parseInt($(this).attr('data-termid'));
				
				if (!$(this).hasClass('added')) {
					$(this).addClass('added');
					
					var newTerm = widget._termNew({
						name: 		$(this).attr('title'),
						term_id:	thisId
					});
					
					if (searchCreate !== true) newTerm.removeClass('searchAdded');
					current.append(newTerm.addClass('popAdded'));
					
				} else {
					$(this).removeClass('added');
					
					current.children('.popAdded').filter('[data-termid="' + thisId + '"]').off().remove();
				}
			});
		},
		
		/**
		* Binds the popular terms opener toggle.
		*/
		_termBindPopularToggle: function() {
			var popularToggle	= this.element.siblings('.popularTerms').not('.bound');
			var theTagCloud		= this.tagCloud;
			
			if (popularToggle.length < 1) return;
			
			popularToggle.addClass('bound').on('click', function(event){
				event.preventDefault();
				event.stopPropagation();
				
				theTagCloud.toggleClass('hidden');
			});
		},
		
		/**
		* Binds a term X icon link.
		* 
		* @param jQuery theTermLink The x A tag to bind.
		*/
		_termBindLink: function(theTermLink) {
			if (theTermLink.hasClass('bound')) return;
			
			var theTerm		= theTermLink.parent();
			var theInput	= theTerm.find('input');
			var widget		= this;
			
			theTermLink.addClass('bound').on('click', function(event){
				event.preventDefault();
				event.stopPropagation();
				
				theTerm.toggleClass('deleted');
				theInput.prop('checked', !theInput.prop('checked'));
				widget._searchFieldReset();
				
					// If this was a popular tag, re-enable it.
				if (theTerm.hasClass('popAdded')) {
					widget.tagCloud.children('a').filter('[data-termid="' + $(this).attr('data-value') + '"]').trigger('click');
				
				} else if (theTerm.hasClass('searchAdded')) { // If search added, remove it.
					$(this).off();
					theTerm.remove();
				}
			});
		},
		
		/**
		* Returns a new term to add to current.
		* 
		* @param object theItem The new term object clicked on in the autocomplete.
		* @return jQuery newTerm The new term.
		*/
		_termNew: function(theItem) {
			var newTerm		= $(this.options.templates.term.replace(/###ITEM_ID###/g, theItem.term_id).replace(/###ITEM_NAME###/g, theItem.name));
			var newLink		= $(this.options.templates.termLink.replace(/###ITEM_ID###/g, theItem.term_id));
			var newInput	= $(this.options.templates.termInput.replace(/###ITEM_ID###/g, theItem.term_id).replace(/###ITEM_TYPE###/g, this.options.termType));
			
			newTerm.append(newLink).append('&nbsp;').append(theItem.name).append(newInput);
			
				// Bind.
			this._termBindLink(newLink);
			
			return newTerm;
		}
	});
} (jQuery));