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
 * An inline record widget for SB Portfolio for WordPress.
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.inlineRecord', {
		options: {
			templates: {
				loader:		'<div class="sbpLoaderDummyIrContent sbpLoader"><span class="sbpSpinner"></span></div>',
				error:		'<div class="sbpLoaderDummyIrContent hidden sbpLoaderError"></div>',
				control:	'<span class="irControl dashicons">&nbsp;</span>'
			},
			labels: {
				loadError: 		'Unable to load record',
				new:			'Create new',
				controls: {
					new:	'New',
					edit:	'Edit',
					delete:	'Delete'
				}
			},
			controls: {
				new:	'plus-alt',
				edit:	'edit',
				delete:	'trash'
			},
			script:			'inlineRecord',
			table:			'',
			tableType:		'',
			recordId:		'',
			parentTable:	'',
			parentId:		'',
			parentField:	'',
			limit:			''
		},
		
		
		
		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			this._stateInit();
			
			this.selectors.title	= this.element.children('.irTitle');
			this.selectors.content	= this.element.children('.irContent');
			this.selectors.addField	= this.element.children('.inlineAdd');
			
			if (this.selectors.addField.length < 1) this.selectors.addField = null;
			
			this._createTitleButtons();
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
			this.element.find('.bound').off().removeClass('bound');
			this._stateInit();
		},
		
		
		
		/**
		* Creates the title bar control buttons.
		*/
		_stateInit: function() {
			this.loaded				= false;	// Has the record been loaded?
			this.edited				= false;	// Is the record being edited?
			this.selectors			= {};		// Cache of commonly used selectors.
		},
		
		
		
		/**
		* Creates the title bar control buttons.
		*/
		_createTitleButtons: function() {
			var irControls = this.selectors.title.find('.irControls');
			
			if (irControls.length < 1) return;
			
			var isNew = (this.options.recordId < 1) ? true : false;
			
			for (var control in this.options.controls) {
				var newControl = $(this.options.templates.control)
					.addClass('dashicons-' + this.options.controls[control])
					.attr('data-sbp_control', control)
					.attr('title', this.options.labels.controls[control]);
				
				irControls.append(newControl);
			}
			
			var irControlButtons = this.selectors.irControlButtons = irControls.children().addClass('hidden');
			
			if (isNew) {
				irControlButtons.filter('[data-sbp_control="new"]').removeClass('hidden');
				irControls.siblings('.irTitleLabel').text(this.options.labels.new);
				
			} else {
				irControlButtons.not('[data-sbp_control="new"]').removeClass('hidden');
			}
			
			irControlButtons.not('.hidden').last().addClass('last');
			
			this._bindTitleButtons(irControlButtons);
		},
		
		/**
		* Binds the title bar control buttons.
		* 
		* @param jQuery irControlButtons The control buttons.
		*/
		_bindTitleButtons: function(irControlButtons) {
			var widget = this;
			
			irControlButtons.not('.bound').each(function(){
				var controlType = $(this).attr('data-sbp_control');
				
				if (controlType === undefined) return true; // Move along to next control.
				
				$(this).addClass('bound').on('click', function(event){
					event.preventDefault();
					event.stopPropagation();
					
					if (!$(this).hasClass('hidden') && !$(this).hasClass('disabled')) {
						switch (controlType) {
							case 'new':
								if (widget.loaded === false) {
									sbpAdmin.bindFields(widget.element);
									widget._bindTitleField();
									widget.loaded = true;
								}
								
								if (widget.edited) {
									widget._contentHide();
									
								} else {
									
									widget._contentShow();
								}
							
							break;
							
							case 'edit':
								if (widget.edited) {
									widget._contentHide();
									
								} else {
									
									widget._contentShow();
								}
							
							break;
							
							case 'delete':
								widget._deleteRecord();
							
							break;
							
							default:
								console.warn('Unknown controlType: "' + controlType + '" - inlineRecord._bindTitleButtons()');
							
							break;
						}
					}
				});
			});
		},
		
		/**
		* Shows the content pane.
		*/
		_contentShow: function() {
			if (!this.loaded) {
				if (this.selectors.loader === undefined) {
					var loader					= $(this.options.templates.loader);
					var loaderError				= $(this.options.templates.error);
					this.selectors.loader		= loader;
					this.selectors.loaderError	= loaderError;
					this.selectors.content.addClass('hidden').before(loader);
					loader.after(loaderError);
					
				} else if (!this.selectors.loaderError.hasClass('hidden')) {
					this._contentHide();
					
					return;
					
				} else {
					this.selectors.loader.removeClass('hidden');
				}
				
				this._getRecord();
				
			} else {
				this.edited = true;
				
				if (this.selectors.loader !== undefined) {
					this.selectors.loader.addClass('hidden');
					this.selectors.loaderError.addClass('hidden');
				}
				
				this.selectors.content.removeClass('hidden');
				this.selectors.title.children('.irTitleLabel').addClass('hidden').siblings('.irTitleField').removeClass('hidden');
			}
		},
		
		/**
		* Hides the content pane.
		*/
		_contentHide: function() {
			this.edited = false;
			
			this.selectors.content.addClass('hidden');
			
			if (this.selectors.loader !== undefined) {
				this.selectors.loaderError.addClass('hidden');
				this.selectors.loader.addClass('hidden');
			}
			
			this.selectors.title.children('.irTitleField').addClass('hidden').siblings('.irTitleLabel').removeClass('hidden');
		},
		
		/**
		* Gets a record.
		*/
		_getRecord: function() {
			var widget	= this;
			var conf	= {
				url:		this.options.urlPhp + '/' + this.options.script + '.php',
				type:		'GET',
				cache:		false,
				dataType:	'json',
				data:		{
					table:			this.options.table,
					recordId:		this.options.recordId,
					limit:			this.options.limit,
					action:			'get'
				},
				success:
					function(ajaxData, textStatus, jqXHR) {
						if (ajaxData.error === false) {
							widget._recordUpdate(ajaxData);
							widget.loaded = true;
							widget._contentShow();
							
						} else {
							widget._recordError(ajaxData, ajaxData.errorMessage);
						}
					},
				error:
					function(jqXHR, textStatus, errorThrown) {
						widget._recordError(ajaxData, textStatus + ' (' + errorThrown + ')');
					}
			};
			
			$.ajax(conf);
		},
		
		/**
		* Displays an error.
		*  
		*  @param object ajaxData The ajax data.
		*  @param string errMsg The error message.
		*/
		_recordError: function(ajaxData, errMsg) {
			if (errMsg === undefined) errMsg = this.options.labels.loadError;
			
			this.selectors.loader.addClass('hidden');
			this.selectors.loaderError.html('<p>' + errMsg + '</p>').removeClass('hidden');
		},
		
		/**
		* Updates a record with the ajax data.
		*  
		*  @param object ajaxData The ajax data.
		*/
		_recordUpdate: function(ajaxData) {
			if (ajaxData.records === undefined || ajaxData.records.length < 1) return;
			
			var recUid		= ajaxData.records[0]['uid'];
			var setupKey	= this.options.table.replace('sbp_', '') + '_inline';
			
			if (sbpSettings.tables[setupKey] !== undefined) {
				for (var fieldKey in sbpSettings.tables[setupKey]) {
					var fieldData	= sbpSettings.tables[setupKey][fieldKey];
					var tagId		= 'field-' + fieldKey + '_' + this.options.tableType + '-' + recUid;
					var fieldTag	= $('#' + tagId, this.element);
					
					if (fieldTag.length > 0) fieldTag.val(ajaxData.records[0][fieldKey]);
				}
				
				sbpAdmin.bindFields(this.element);
				this._bindTitleField();
			}
		},
		
		/**
		* Updates the UI after deleting a record successfully.
		*  
		*  @param object ajaxData The ajax data.
		*/
		_recordDelete: function(ajaxData) {
			this._contentHide();
			
			this.selectors.irControlButtons.addClass('hidden').removeClass('last').filter('[data-sbp_control="new"]').removeClass('hidden');
			this.selectors.irControlButtons.not('.hidden').last().addClass('last');
			this.selectors.irControlButtons.parent().siblings('.irTitleLabel').text(this.options.labels.new);
			
				// Create empty fields.
			var thisWidgetsFields	= $('.pageField', this.element);
			var widget				= this;
			
			thisWidgetsFields.each(function(){
				var newId	= $(this).attr('id').replace('-' + widget.options.recordId, '-new');
				var newName	= $(this).attr('name').replace('[' + widget.options.recordId + ']', '[new]');
				
				$(this).attr('id', newId).attr('name', newName);
				
				if (!$(this).hasClass('inlineAdd')) {
					$(this).val(0);
					
				} else if (!$(this).hasClass('keepValue')) {
					$(this).val('');
				}
			});
		},
		
		/**
		* Binds the title field.
		
		*  
		*  @param object ajaxData The ajax data.
		*/
		_bindTitleField: function(ajaxData) {
			var titleField = $('.inlineRecTitleField', this.element).not('bound');
			
			if (titleField.length < 1) return;
			
			var addField = this.selectors.addField;
			
			titleField.addClass('bound').each(function() {
				var titleSpan = $(this).closest('.irTitleField').prev('.irTitleLabel');
				
				$(this).on('change', function(event) {
					event.stopPropagation();
					
					var thisVal = $(this).val();
					
					if (thisVal == '') {
						if (addField !== null) addField.val(0); 
						thisVal = '&nbsp;';
						
					} else {
						if (addField !== null) addField.val(1); 
					}
					
					titleSpan.html(thisVal);
					
				}).on('blur', function(event) {
					event.stopPropagation();
					
					var thisVal = $(this).val();
					
					if (thisVal == '') {
						if (addField !== null) addField.val(0); 
						thisVal = '&nbsp;';
						
					} else {
						if (addField !== null) addField.val(1); 
					}
					
					titleSpan.html(thisVal);
					
				}).on('keyup', function(event) {
					event.stopPropagation();
					
					var thisVal = $(this).val();
					
					if (thisVal == '') {
						if (addField !== null) addField.val(0); 
						thisVal = '&nbsp;';
						
					} else {
						if (addField !== null) addField.val(1); 
					}
					
					titleSpan.html(thisVal);
				});
				
				$(this).trigger('change');
			});
		},
		
		/**
		* Deletes a record (marks as deleted).
		*/
		_deleteRecord: function() {
			var widget	= this;
			var conf	= {
				url:		this.options.urlPhp + '/' + this.options.script + '.php',
				type:		'POST',
				cache:		false,
				dataType:	'json',
				data:		{
					table:			this.options.table,
					recordId:		this.options.recordId,
					parentTable:	this.options.parentTable,
					parentId:		this.options.parentId,
					parentField:	this.options.parentField,
					action:			'delete',
					wpNonce:		$('input[name="_wpnonce-sbp"]').val(),
					wpReferer:		$('input[name="_wp_http_referer"]').val()
				},
				success:
					function(ajaxData, textStatus, jqXHR) {
						if (ajaxData.error === false) {
							widget._recordDelete(ajaxData);
							
						} else {
							widget._recordError(ajaxData, ajaxData.errorMessage);
						}
					},
				error:
					function(jqXHR, textStatus, errorThrown) {
						widget._recordError(ajaxData, textStatus + ' (' + errorThrown + ')');
					}
			};
			
			$.ajax(conf);
		}
	});
} (jQuery));