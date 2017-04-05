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
 * A widget for metaboxes in SB Portfolio for WordPress that should show a list of terms with an add new term feature (like the WP category metabox).
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.taxonomyRecordLister', {
		options: {
			templates: {
				newTerm: '<li class="widgetAdded" id="###TERM_TYPE###-all-"><label class="selectit" for="###TERM_TYPE###-all-"><input type="checkbox" id="###TERM_TYPE###-all-" name="sb_portfolio[###TERM_TYPE###][]"></label></li>',
				newlist: '<ul class="###TERM_TYPE###checklist form-no-clear widgetAdded"></ul>'
			},
			termType:	'category',
			taxonomy:	'category',
			errTimeout:	2000
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
				// Cache a few things.
			this.ajaxResponse	= this.element.find('.ajaxResponse');
			this.listAll		= this.element.find('.tabs-panel-all').children('ul');
			this.listPop		= this.element.find('.tabs-panel-pop').children('ul');
			this.selectTag		= this.element.find('select');
			this.submitButton	= this.element.find('input.termSubmitButton');
			this.newTermName	= this.element.find('input.newTermName');
			
				// Fix templates
			for (var t in this.options.templates) {
				this.options.templates[t] = this.options.templates[t].replace(/###TERM_TYPE###/g, this.options.termType);
			}
			
				// Get default AJAX response.
			if (this.ajaxResponse.length > 0) this.ajaxResponse.attr('data-defaultStr', this.ajaxResponse.text()); 
			
				// Create and bind widget.
			this._termTabs();
			this._termCheckboxes();
			this._addMoreTerms();
			this._addMoreTerms_submitBind();
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
			this.element.find('.widgetAdded').remove();
		},
		
		
		
			/* Checkbox Methods */
			/* ---------------- */
			
		/**
		* Binds the category tabs.
		*/
		_termCheckboxes: function() {
			var allInputs		= this.listAll.find('input').filter('[type="checkbox"]').not('.termDeleteInput').not('.bound');
			var popInputs		= this.listPop.find('input').filter('[type="checkbox"]').not('.termDeleteInput').not('.bound');
			var combinedInputs	= allInputs.add(popInputs.addClass('termPop'));
			
			if (combinedInputs.length < 1) return;
				
			combinedInputs.addClass('bound').each(function(){
				var thisInput 	= $(this);
				var delInput	= thisInput.siblings('.termDeleteInput');
				var hasDelete	= (delInput.length > 0) ? true : false;
				var thisId		= thisInput.val();
				var isPop		= thisInput.hasClass('termPop');
				
				thisInput.addClass('bound').on('click', function(event){
					if (hasDelete) delInput.prop('checked', !thisInput.prop('checked'));
					
						// Check other tabs.
					if (isPop) {
						allInputs.filter('[value="' + thisId + '"]').trigger('sibclick');
						
					} else {
						popInputs.filter('[value="' + thisId + '"]').trigger('sibclick');
					}
					
				}).on('sibclick', function(event){
					thisInput.prop('checked', !thisInput.prop('checked')); // This is not a click event so we need to change this ourselves.
					
					if (thisInput.prop('checked') === true) {
						if (hasDelete) delInput.prop('checked', false);
						
					} else {
						if (hasDelete) delInput.prop('checked', true);
					}
				});
			});
		},
		
		
		
			/* Tab Methods */
			/* ----------- */
			
		/**
		* Binds the term tabs.
		*/
		_termTabs: function() {
			var termTabsUl = this.element.find('ul.termTabs');
			
			if (termTabsUl.length < 1) return;
			
			var termTabsLinks = termTabsUl.find('a').not('.bound');
			
			if (termTabsLinks.length < 1) return;
			
				// Based on: link.js
			termTabsLinks.addClass('bound').on('click', function(event){
				event.preventDefault();
				
					// Highlight tab.
				$(this).parent().addClass('tabs').siblings('li').removeClass('tabs');
				
				var target = termTabsUl.siblings('.tabs-panel[id="' + $(this).attr('href').substr(1) + '"]');
				
				if (target.length > 0) {
					target.siblings('.tabs-panel').addClass('hidden');
					target.removeClass('hidden');
				}
			});
		},
		
		
		
			/* Add More Cats Methods */
			/* --------------------- */
			
		/**
		* Binds the add more terns toggle.
		*/
		_addMoreTerms: function() {
			var addMoreTerms = this.element.find('a.metaTerm').not('.bound');
			
			if (addMoreTerms.length < 1) return;
			
			var addMoreTermsParent	= addMoreTerms.parent();
			var newTermNameField	= this.newTermName;
			var allTermsTabLink		= this.element.find('.termTabs').find('a.termAllLink');
			
				// Based on: link.js
			addMoreTerms.addClass('bound').on('click', function(event){
				event.preventDefault();
				
				if (addMoreTermsParent.hasClass('wp-hidden-children')) {
					addMoreTermsParent.removeClass('wp-hidden-children');
					allTermsTabLink.trigger('click'); // Show all terms.
					newTermNameField.focus();
					
				} else {
					addMoreTermsParent.addClass('wp-hidden-children');
				}
			});
		},
			
		/**
		* Binds the add more terms submit button.
		*/
		_addMoreTerms_submitBind: function() {
			var addMoreTermsSubmit = this.submitButton.not('.bound');
			
			if (addMoreTermsSubmit.length < 1) return;
			
			var newTerm = this.newTermName;
			
			if (newTerm.length < 1) return;
			
			var newTermParent	= this.selectTag;
			var widget			= this;
			
			addMoreTermsSubmit.addClass('.bound').on('click', function(event){
				event.preventDefault();
				
				widget.ajaxResponse.addClass('hidden');
				
				var conf	= {
					url:		ajaxurl,
					type:		'POST',
					cache:		false,
					dataType:	'json',
					data:		{
						action:		'addterm',
						security:	$('#_wpnonce-sbp').val(),
						taxonomy:	$.trim(widget.options.taxonomy),
						termName:	$.trim(newTerm.val())
					},
					success:
						function(ajaxData, textStatus, jqXHR) {
							if (ajaxData.error === undefined || ajaxData.error === false) {
								widget._addMoreTerms_submitSuccess(ajaxData, textStatus, jqXHR);
								
							} else {
								widget._addMoreTerms_submitError(jqXHR, textStatus, 'successError');
							}
						},
					error:
						function(jqXHR, textStatus, errorThrown) {
							widget._addMoreTerms_submitError(jqXHR, textStatus, errorThrown);
						}
				};
				
				if (newTermParent.length > 0) conf.data.termParent = parseInt(newTermParent.val());
				
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
		_addMoreTerms_submitError: function(jqXHR, textStatus, errorThrown) {
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
		_addMoreTerms_submitSuccess: function(ajaxData, textStatus, jqXHR) {
			if (ajaxData.result === undefined) return;
			
			var appendTarget = this.listAll;
			var termPanel	 = this.listAll.parent();
			
			if (ajaxData.result.parent !== undefined && ajaxData.result.parent > 0) appendTarget = $('#' + this.options.termType + '-all-' + ajaxData.result.parent);
			
			if (appendTarget.length < 0) return;
			
				// Create new cat checklist item.
			var newTerm		= $(this.options.templates.newTerm);
			var inputTag	= newTerm.find('input');
			var labelTag	= newTerm.children('label');
			
			newTerm.attr('id', newTerm.attr('id') + ajaxData.result.term_id);
			labelTag.attr('for', labelTag.attr('for') + ajaxData.result.term_id);
			
			inputTag.attr({
				'id':		inputTag.attr('id') + ajaxData.result.term_id,
				'value':	ajaxData.result.term_id,
				'checked':	'checked'
			}).after(ajaxData.result.name);
			
				// Add to select tag.
			if (ajaxData.result.parent !== undefined) {
				var newOpt = $('<option></option>').val(ajaxData.result.term_id).html(ajaxData.result.name);
				this.selectTag.append(newOpt);
			}
						
				// Add to correct location and scroll into view.
			if (ajaxData.result.parent !== undefined && ajaxData.result.parent > 0) {
				if (appendTarget.children('ul').length > 0) { // Add into already existing sublist.
					appendTarget.children('ul').prepend(newTerm);
					
				} else { // Add new sublist.
					var newList = $(this.options.templates.newlist);
					
					appendTarget.children('label').after(newList.append(newTerm));
				}
				
				termPanel.scrollTop(newTerm.position().top);
				
			} else {
				appendTarget.prepend(newTerm);
				termPanel.scrollTop(0);
			}
			
				// Animate yellow to show user... not quite the way WP does it, but it will do!
			newTerm.addClass('dimPrep').addClass('dim').removeClass('dimPrep');
			setTimeout(function(){newTerm.removeClass('dim');}, 150);
		}
	});
} (jQuery));