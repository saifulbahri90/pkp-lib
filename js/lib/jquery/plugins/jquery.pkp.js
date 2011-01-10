/**
 * @defgroup js_lib_jquery_plugins
 */

/**
 * @file js/lib/jquery/plugins/jquery.pkp.js
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup js_lib_jquery_plugins
 *
 * @brief PKP jQuery extensions.
 */

/** @param {jQuery} $ jQuery closure. */
(function($) {


	/**
	 * Handler plug-in.
	 * @this {jQuery}
	 * @param {string} handlerName The handler to be instantiated
	 *  and attached to the target HTML element(s).
	 * @param {Object=} options Parameters to be passed on
	 *  to the handler.
	 * @return {jQuery} Selected HTML elements for chaining.
	 */
	$.fn.pkpHandler = function(handlerName, options) {
		// Go through all selected elements.
		this.each(function() {
			var $element = $(this);

			// Instantiate the handler and bind it
			// to the element.
			options = options || {};
			var handler = $.pkp.classes.Helper.objectFactory(
					handlerName, [$element, options]);
		});

		// Allow chaining.
		return this;
	};


	/**
	 * Re-implementation of jQuery's html() method
	 * with a remote source.
	 * @param {string} url the AJAX endpoint from which to
	 *  retrieve the HTML to be inserted.
	 * @return {jQuery} Selected HTML elements for chaining.
	 */
	$.fn.pkpAjaxHtml = function(url) {
		var $element = this.first();
		$.getJSON(url, function(jsonData) {
			$element.find('#loading').hide();
			if (jsonData.status === true) {
				// Replace the element content with
				// the remote content.
				$element.html(jsonData.content);
			} else {
				// Alert that the remote call failed.
				throw (jsonData.content);
			}
		});
		$element.html("<div id='loading' class='throbber'></div>");
		return this;
	};


})(jQuery);