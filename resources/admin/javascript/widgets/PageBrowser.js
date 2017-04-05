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
 * A generic page browser widget to create page browsers for other GUI elements.
 * This widget is designed to not be used on its own but as a utility widget by other widgets (i.e. clientItems)
 * 
 * @author Stephen Bungert <hello@stephenbungert.com>
 * @package WordPress
 * @subpackage SB Portfolio
 */
(function($, undefined) {
	'use_strict';
	
	$.widget('bungert.pageBrowser', {
		options: {
			templates: {
				displayMsg:	'<p class="pbDisplayMsg"><span class="displayLabel"></span> <span class="currentPages"></span> / <span class="maxPages"></span></p>',
				loadingMsg:	'<p class="pbLoadingMsg hidden"><span class="loadingText"></span><span class="spinner sbpSpinner is-active"></span></p>',
				buttons:	'<div class="pageBrowserButtons"></div>',
				button:		'<a class="pageBrowserbutton button" href="#"></a>'
			},
			labels: {
					first:			'',
					last:			'',
					next:			'',
					prev:			'',
					displayLabel:	'',
					loadingLabel:	''
			},
			initItemCount:		-1,
			showDisplayInfo:	true,
			showFirst:			true,
			showLast:			true,
			showNext:			true,
			showPrev:			true,
			pages:				5,		// How many page buttons should be displayed in the center?
			perPage:			10,		// How many items should be displayed per page?
			total:				0,		// How many elements in total are there?

				/* Events */
				/* ------ */

				/**
				* Called when the page changes.
				*
				* @param jQuery Widget pbWidget This widget.
				* @param jQuery pBut The button clicked.
				* @param string pPage The type of page button clicked.
				* @param integer pNum The number of the page if pPage = 'page' or -1.
				*/
			change:
				function(pbWidget, pBut, pPage, pNum) {
				}
		},



		/**
		* Constructor, called once when first created
		*/
		_create: function() {
			this.prevButtons	= null;
			this.nextButtons	= null;
			this.pageButtons	= null;
			this.loadingMsg		= null;
			this.displayMsg		= null;
			this.displayMsgLab	= null;
			this.displayMsgCur	= null;
			this.displayMsgMax	= null;
			this.curPage		= 1;
			this.lastPage		= Math.ceil(this.options.total / this.options.perPage);
			this.maxInit		= this.lastPage - (this.options.pages - 1);

			if (this.options.total < 1) {
				this.element.addClass('hidden');

			} else {
				this._bpCreate();
				this.element.removeClass('hidden').addClass('sbpPageBrowser');
			}
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
			this.element.empty();
		},



			/* PB Methods */
			/* ---------- */

		/**
		* Creates the page browser.
		*/
		_bpCreate: function() {
			var prevButtons	= this.prevButtons = $(this.options.templates.buttons).attr('data-type', 'prev');
			var nextButtons	= this.nextButtons = $(this.options.templates.buttons).attr('data-type', 'next');
			var pageButtons	= this.pageButtons = $(this.options.templates.buttons).attr('data-type', 'page');
			var butFirst	= $(this.options.templates.button).attr({'data-action': 'first', 'title': this.options.labels.first}).text('<<').addClass('disabled');
			var butPrev		= $(this.options.templates.button).attr({'data-action': 'prev', 'title': this.options.labels.prev}).text('<').addClass('disabled');
			var butLast		= $(this.options.templates.button).attr({'data-action': 'last', 'title': this.options.labels.last}).text('>>');
			var butNext		= $(this.options.templates.button).attr({'data-action': 'next', 'title': this.options.labels.next}).text('>');
			
			
				// Add display message
			if (this.options.showDisplayInfo === true) {
				this.displayMsg		= $(this.options.templates.displayMsg);
				this.displayMsgLab	= this.displayMsg.children('.displayLabel').text(this.options.labels.displayLabel);
				this.displayMsgCur	= this.displayMsg.children('.currentPages');
				this.displayMsgMax	= this.displayMsg.children('.maxPages').text(this.options.total);
				this.element.parent().prepend(this.displayMsg);
			}
			
				// Add loading msg
			this.loadingMsg = $(this.options.templates.loadingMsg);
			this.loadingMsg.children('.loadingText').text(this.options.labels.loadingLabel);
			this.element.parent().prepend(this.loadingMsg);
			
				// Build everything and add it:
			prevButtons.append(butFirst).append(butPrev);
			nextButtons.append(butNext).append(butLast);
			this.element.append(prevButtons).append(nextButtons).append(pageButtons);
			this.update({bindPageButtons: false, itemCount: this.options.initItemCount});
			this._bpBind();
		},

		/**
		* Creates thr "page" buttons.
		* 
		* @param boolean bindButtons Should buttons be bound?
		*/
		_bpAddPageButtons: function(bindButtons) {
			if (bindButtons !== true) bindButtons = false;
			
			this.pageButtons.children().remove();
			var initPage = this.curPage;
			
				// See if we need to try and keep the active page in the center, 
				// and make sure that the required number of pages are showing.
			if (initPage > 1) {
				initPage -= Math.floor(this.options.pages / 2); // Init page - mid offset.
				
				if (initPage > this.maxInit)	initPage = this.maxInit;
				if (initPage < 1)				initPage = 1;
			}
			
			var maxPages = (initPage > 1) ? initPage + this.options.pages : this.options.pages;
			if (maxPages > this.lastPage) maxPages = this.lastPage;
			
			for (var page = initPage; page <= maxPages; page ++) {
				var newButton = $(this.options.templates.button).attr({
					'data-action':	'page',
					'data-page':	page,
					'title':		page
				});

				if (page == this.curPage) newButton.addClass('active');

				this.pageButtons.append(newButton.text(page));
			}
			
			if (bindButtons === true) this._bpBind(this.pageButtons.children('a').not('.bound'));
		},

		/**
		* Binds the page browser buttons.
		* 
		* @param jQuery searchTargets The buttons to bind. 
		*/
		_bpBind: function(searchTargets) {
			if (searchTargets === undefined)	searchTargets = this.element.find('a').not('.bound');
			if (searchTargets.length < 1)		return;
			var widget = this;

			searchTargets.addClass('bound').each(function(){
				var thisBut		= $(this);
				var thisAction	= thisBut.attr('data-action');

				thisBut.on('click', function(event){
					event.preventDefault();

					if (thisBut.hasClass('disabled') || thisBut.hasClass('active')) return;

					if (thisAction == 'page' || thisAction == 'first' || thisAction == 'prev' || thisAction == 'last' || thisAction == 'next') {
							// Work out the current page number:
						var pageNum = 1;

						switch (thisAction) {
							case 'page':
								pageNum = parseInt(thisBut.attr('data-page'));
							break;

							case 'first':
								pageNum = 1;
							break;

							case 'last':
								pageNum = widget.lastPage;
							break;

							case 'prev':
								pageNum = widget.curPage - 1;
							break;

							case 'next':
								pageNum = widget.curPage + 1;
							break;

							default:
							break;
						}

							// Make sure it is valide:
						if (pageNum < 1) pageNum = 1;
						if (pageNum > widget.lastPage) pageNum = widget.lastPage;
							
							// Disable buttons.
						widget.nextButtons.children().addClass('disabled');
						widget.prevButtons.children().addClass('disabled');
						widget.pageButtons.children().addClass('disabled');
						
							// Show loading msg:
						widget.displayMsg.addClass('hidden');
						widget.loadingMsg.removeClass('hidden');
							
							// Get new items via callback.
						widget.curPage = pageNum;
						widget.options.change(widget, thisBut, thisAction, pageNum);
						thisBut.blur();

					} else {
						console.info('pageBrowser._bpBind() - unknown action: "' + thisAction + '"');
					}
				});
			});
		},

		/**
		* Updates the UI, publically callable.
		* 
		* @param object confObj An object with additional options/data.
		*/
		update: function(confObj) {
			if (confObj === undefined)					confObj					= {};
			if (confObj.bindPageButtons === undefined)	confObj.bindPageButtons	= true;
			
				// Update buttons:
			this._bpAddPageButtons(confObj.bindPageButtons);

				// Enable/disable NP buttons:
			if (this.curPage <= 1) {
				this.prevButtons.children().addClass('disabled');
				this.nextButtons.children().removeClass('disabled');

			} else if (this.curPage >= this.lastPage) {
				this.nextButtons.children().addClass('disabled');
				this.prevButtons.children().removeClass('disabled');

			} else {
				this.prevButtons.children().removeClass('disabled');
				this.nextButtons.children().removeClass('disabled');
			}
			
				// Update display:
			if (this.options.showDisplayInfo === true && confObj.itemCount !== undefined) {
				var countMsg	= confObj.itemCount;
				var minItem		= ((this.options.perPage * (this.curPage - 1)) + 1);
				var maxItem		= ((this.options.perPage * (this.curPage - 1)) + countMsg);
				
				if (minItem == maxItem) {
					countMsg = minItem;
					
				} else {
					countMsg = minItem + ' - ' + maxItem;
				}
				
				this.displayMsgCur.text(countMsg);
				this.loadingMsg.addClass('hidden');
				this.displayMsg.removeClass('hidden');
			}
		},

		/**
		* Updates the UI if there was an error.
		*/
		error: function() {
			this.pageButtons.children().removeClass('disabled');
			
				// Enable/disable NP buttons:
			if (this.curPage <= 1) {
				this.prevButtons.children().addClass('disabled');
				this.nextButtons.children().removeClass('disabled');

			} else if (this.curPage >= this.lastPage) {
				this.nextButtons.children().addClass('disabled');
				this.prevButtons.children().removeClass('disabled');

			} else {
				this.prevButtons.children().removeClass('disabled');
				this.nextButtons.children().removeClass('disabled');
			}
			
				// Update display:
			if (this.options.showDisplayInfo === true) {
				this.loadingMsg.addClass('hidden');
				this.displayMsg.removeClass('hidden');
			}
		}
	});
} (jQuery));