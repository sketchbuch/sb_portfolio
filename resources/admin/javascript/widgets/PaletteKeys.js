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
 * A widget to create a palette key creator.
 *
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';

	$.widget('bungert.paletteKeys', {
		options: {
			templates: {
				empty:		'<p class="sbpPalKeysEmpty sbpEmptyMsg"></p>',
				controls:	'<div class="sbpPalKeysControls"><a class="button disabled sbpPalEmptyAdd" data-action="addEmptyKey" href="#"></a></div>',
				keyList:	'<div class="sbpList sbpList-noBorder sbpPalKeyList"></div>',
				keyItem:	'<div class="sbpListItem sbpPalKeyItem"> \
								<input type="text" class="regular-text keyLabel delStrikeThrough"> \
								<input type="text" class="regular-text keyText delStrikeThrough"> \
								<span tabindex="0" class="dashicons dashicons-dismiss keyDelete sbpDeleteIcon"></span> \
								<span tabindex="0" class="dashicons dashicons-move keyMove sbpMoveIcon"></span> \
							 </div>'
			},
			labels:		{},
			sortable:	true,
			data:		[]
		},



		/**
		* Constructor, called once when first created
		*/
		_create: function() {
				// Vars:
			this.keyList	= null;
			this.emptyTag	= null;
			this.controls	= null;
			this.keysInput	= null;

				// Go...
			this._keysSetup();
			this._emptySetup();
			this._controlsSetup();
			this._keysAddExisting();

			if (this.options.sortable === true) {
				var widget = this;

				this.keyList.sortable({
					placeholder:	'ui-state-highlight',
					cancel:			'.deleted, input',
					handle:			'.keyMove',
					start:
						function(event, ui){
							ui.helper.addClass('dragged');
						},
					stop:
						function(event, ui){
							widget._keysUpdateValue();
						}
				});
			}

				// Activate buttons and remove loader:
			this.controls.children('a').removeClass('disabled');
			this.element.children('.sbpColoursLoading').remove();
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
		* Sets up the empty message content.
		*/
		_controlsSetup: function() {
			this.controls = $(this.options.templates.controls);
			this.element.append(this.controls);
			this._controlsBind();
		},

		/**
		* Binds control buttons.
		*/
		_controlsBind: function() {
			var buttons = this.controls.children('a').filter('.button').not('.bound');

			if (buttons.length < 1) return;

			var widget = this;

			buttons.each(function(){
				var thisBut		= $(this);
				var butAction	= thisBut.attr('data-action');

				switch(butAction) {
					case 'addEmptyKey':
						thisBut.html(widget.options.labels.add);

					break;

					default:
						console.info('PaletteKeys._controlsBind() - label not found for: "' + butAction + '"');

					break;
				}

				thisBut.addClass('bound').on('click', function(event){
					event.preventDefault();

					switch(butAction) {
						case 'addEmptyKey':
							widget._keysAdd();

						break;

						default:
							console.info('PaletteKeys._controlsBind() - unknown action: "' + butAction + '"');

						break;
					}
				});
			});
		},



		/* Empty Methods */
		/* ------------- */

		/**
		* Sets up the empty message content.
		*/
		_emptySetup: function() {
			this.emptyTag = $(this.options.templates.empty).html(this.options.labels.empty);
			this.element.append(this.emptyTag);
		},



		/* Key Methods */
		/* ----------- */

		/**
		* Sets up the keys content.
		*/
		_keysSetup: function() {
			this.keyList	= $(this.options.templates.keyList);
			this.keysInput	= this.element.children('.sbpKeysInput');

			this.element.append(this.keyList);
		},

		/**
		* Updates the keys value.
		*/
		_keysUpdateValue: function() {
			var newVal = {keys: []};

			this.keyList.children().not('.deleted').each(function(){
				var thisVal = $.trim($(this).children('.keyLabel').val());
				var thisTxt = $.trim($(this).children('.keyText').val());

				if (thisVal != '') newVal['keys'].push({key: thisVal, label: thisTxt});
			});

			this.keysInput.val(JSON.stringify(newVal));
		},

		/**
		* Adds a key.
		*
		* @param string initVal The intial value for the key label.
		* @param string initLabel The intial value for the key text.
		*/
		_keysAdd: function(initVal, initLabel) {
			if (initVal === undefined)		initVal = '';
			if (initLabel === undefined)	initLabel = '';

			var newKey		= $(this.options.templates.keyItem);
			var newLabel	= newKey.children('.keyLabel').attr('placeholder', this.options.labels.keyPlaceholder);
			var newText		= newKey.children('.keyText').attr('placeholder', this.options.labels.keyTextPlaceholder);
			var newDel		= newKey.children('.keyDelete').attr('title', this.options.labels.keyDelTitle);
			var newMov		= newKey.children('.keyMove').attr('title', this.options.labels.keyMovTitle);
			var keysList	= this.keyList;
			var keysInput	= this.keysInput;
			var widget		= this;

			if (this.options.sortable !== true) newMov.addClass('hidden');

			if (initVal != '')		newLabel.val(initVal);
			if (initLabel != '')	newText.val(initLabel);

				// Bind label field:
			newText.addClass('bound').on('updateValue', function(){
				widget._keysUpdateValue();

			}).on('blur', function(){
				if (!newKey.hasClass('deleted')) newLabel.trigger('updateValue');

			}).on('change', function(){
				if (!newKey.hasClass('deleted')) newLabel.trigger('updateValue');
			});

				// Bind key field:
			newLabel.addClass('bound').on('updateValue', function(){
				newLabel.addClass('ignoreUpdate').val(newLabel.val().replace(' ', '-').toLowerCase());
				widget._keysUpdateValue();
				newLabel.removeClass('ignoreUpdate');

			}).on('blur', function(){
				if (!newLabel.hasClass('ignoreUpdate') && !newKey.hasClass('deleted')) newLabel.trigger('updateValue');

			}).on('change', function(){
				if (!newLabel.hasClass('ignoreUpdate') && !newKey.hasClass('deleted')) newLabel.trigger('updateValue');
			});

				// Bind delete button.
			newDel.addClass('bound').on('click', function(){
				if (newKey.hasClass('deleted')) {
					newKey.removeClass('deleted');
					newLabel.prop('disabled', false);

				} else {
					newKey.addClass('deleted');
					newLabel.prop('disabled', true);
				}

				widget._keysUpdateValue();
			});

				// Add and show in DOM.
			this.emptyTag.addClass('hidden');
			this.keyList.append(newKey).removeClass('hidden');
			newLabel.focus();
		},

		/**
		* Updates the display on load with existing keys.
		*/
		_keysAddExisting: function() {
			if (!$.isArray(this.options.data) || this.options.data.length < 1) return;

			for (var key in this.options.data) {
				this._keysAdd(this.options.data[key].key, this.options.data[key].label);
			}

			this._keysUpdateValue();
		}
	});
} (jQuery));