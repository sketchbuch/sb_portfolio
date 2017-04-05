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
 * A widget to create portfolio palettes.
 *
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';

	$.widget('bungert.palettes', {
		options: {
			templates: {
				empty:		'<p class="sbpPalEmpty sbpEmptyMsg"></p>',
				controls:	'<div class="sbpPalControls"><a class="button disabled sbpPalEmptyAdd" data-action="addEmpty" href="#"></a></div>',
				swatches:	'<div class="sbpList sbpPalSwatches"></div>',
				swatch:		'<div class="sbpListItem sbpPalSwatch"> \
								<span class="sbpPalSwatchColour"></span> \
								#<input maxlength="6" type="text" class="sbpPalSwatchText delStrikeThrough"> \
								<span tabindex="0" class="sbpPalSwatchKeyWrap"><span class="sbpKeyLabel">Key:</span> <select class="sbpPalSwatchKey delStrikeThrough"></select></span> \
								<span tabindex="0" class="dashicons dashicons-dismiss swatchDelete sbpDeleteIcon"></span> \
								<span tabindex="0" class="dashicons dashicons-move swatchMove sbpMoveIcon"></span> \
							 </div>'
			},
			sortable:	true,
			labels:		{},
			data:		[],
			keys:		[]
		},



		/**
		* Constructor, called once when first created
		*/
		_create: function() {
				// Vars:
			this.swatchTag		= null;
			this.emptyTag		= null;
			this.controls		= null;
			this.coloursInput	= null;

				// Go...
			this._swatchSetup();
			this._emptySetup();
			this._controlsSetup();

			if ($.isArray(this.options.data) && this.options.data.length > 0) {
				this._swatchAddExisting();
				this.emptyTag.addClass('hidden');
				this.swatchTag.removeClass('hidden');

			} else {
				this.emptyTag.removeClass('hidden');
				this.swatchTag.addClass('hidden');
			}

			if (this.options.sortable === true) {
				var widget = this;

				this.swatchTag.sortable({
					placeholder:	'ui-state-highlight',
					cancel:			'.deleted, input',
					handle:			'.swatchMove',
					start:
						function(event, ui){
							ui.helper.addClass('dragged');
						},
					stop:
						function(event, ui){
							widget._swatchUpdateValue();
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



		/* Swatch Methods */
		/* -------------- */

		/**
		* Sets up the swatches content.
		*/
		_swatchSetup: function() {
			this.swatchTag		= $(this.options.templates.swatches);
			this.coloursInput	= this.element.children('.sbpColoursInput');
			this.element.append(this.swatchTag);
		},

		/**
		* Adds a swatch.
		*
		* @param object initVal The intial values for the swatch: {
		* 	swatchText: string The html colour.
		* 	swatchKey: string The value of the option selected.
		* }
		*/
		_swatchAdd: function(initVal) {
			if (initVal === undefined) initVal = '';

			var newSwatch		= $(this.options.templates.swatch);
			var swatchText		= newSwatch.children('.sbpPalSwatchText');
			var swatchColour	= newSwatch.children('.sbpPalSwatchColour');
			var swatchDelete	= newSwatch.children('.swatchDelete');
			var swatchMove		= newSwatch.children('.swatchMove');
			var swatchKey		= newSwatch.find('.sbpPalSwatchKey');
			var swatchKeyWrap	= swatchKey.parent();
			var allowedChars	= ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];
			var widget			= this;

			if (this.options.sortable !== true) swatchMove.addClass('hidden');

			if (initVal != '') {
				swatchText.val(initVal.swatchText);
				swatchColour.css('background', '#' + initVal.swatchText);
			}

				// Bind the hex field:
			swatchText.addClass('bound').on('keyup', function(event){
					// Validate value:
				var val = $.trim($(this).val());

				if (val == '') return;

				var chars	= val.toLowerCase().split('');
				var newVal	= '';

				for (var char in chars) {
					if (allowedChars.indexOf(chars[char]) > -1) newVal += chars[char];
				}

				$(this).val(newVal);

					// Update colour "swatch":
				swatchColour.css('background', '#' + newVal);

			}).on('updateValue', function(event){
				widget._swatchUpdateValue();

			}).on('blur', function(event){
				swatchText.trigger('updateValue');

			}).on('change', function(event){
				swatchText.trigger('updateValue');
			});

				// Add keys:
			if (this.options.keys.length > 0) {
				var swatchFound		= false;
				var swatchKeyOpts	= '';

				for (var key in this.options.keys){
					if (initVal != '' && this.options.keys[key].key == initVal.swatchKey) swatchFound = this.options.keys[key].key;

					swatchKeyOpts += '<option value="' + this.options.keys[key].key + '">' + this.options.keys[key].label + '</option>';
				}

					// Add actual key if not exists.
				if (initVal != '' && swatchFound === false) swatchKeyOpts += '<option value="' + initVal.swatchKey + '">' + this.options.labels['invalidKey'].replace('%s', initVal.swatchKey) + '</option>';
				swatchKey.append(swatchKeyOpts);

					// Mark selected:
				if (initVal != '') swatchKey.val(initVal.swatchKey);

				swatchKey.addClass('bound').on('change', function(event){
					swatchText.trigger('updateValue');
				});
			}

				// Bind delete button.
			swatchDelete.addClass('bound').on('click', function(){
				if (newSwatch.hasClass('deleted')) {
					newSwatch.removeClass('deleted');
					swatchKey.prop('disabled', false);
					swatchText.prop('disabled', false);

				} else {
					newSwatch.addClass('deleted');
					swatchKey.prop('disabled', true);
					swatchText.prop('disabled', true);
				}

				widget._swatchUpdateValue();
			});

				// Add data:
			newSwatch.addClass('dataAdded').data({
				'sbpSwatchText':	swatchText,
				'sbpSwatchColour':	swatchColour,
				'sbpSwatchKey':		swatchKey,
				'sbpSwatchKey':		swatchKey,
			});

			this.emptyTag.addClass('hidden');
			this.swatchTag.append(newSwatch).removeClass('hidden');
			swatchText.focus();
		},

		/**
		* Updates the swatches value.
		*/
		_swatchUpdateValue: function() {
			var newVal = {swatches: []};

			this.swatchTag.children().not('.deleted').each(function(){
				var thisItem	= $(this);
				var thisVal		= {
					'colour':	thisItem.data().sbpSwatchText.val(),
					'key':		thisItem.data().sbpSwatchKey.val()
				};

				newVal['swatches'].push(thisVal);
			});

			this.coloursInput.val(JSON.stringify(newVal));
		},

		/**
		* Updates the display on load with existing swatches.
		*/
		_swatchAddExisting: function() {
			for (var swatch in this.options.data) {
				this._swatchAdd({
					swatchText:	this.options.data[swatch].colour,
					swatchKey:	this.options.data[swatch].key
				});
			}

			this._swatchUpdateValue();
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
					case 'add':
					case 'addEmpty':
						thisBut.html(widget.options.labels.add);

					break;

					default:
						console.info('Palettes._controlsBind() - label not found for: "' + butAction + '"');

					break;
				}

				thisBut.addClass('bound').on('click', function(event){
					event.preventDefault();

					switch(butAction) {
						case 'add':
						case 'addEmpty':
							widget._swatchAdd();

						break;

						default:
							console.info('Palettes._controlsBind() - unknown action: "' + butAction + '"');

						break;
					}
				});
			});
		}
	});
} (jQuery));