(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
	typeof define === 'function' && define.amd ? define(['exports'], factory) :
	(factory((global.vCopy = {})));
}(this, (function (exports) { 'use strict';

var copyTextToClipboard$1 = function (input) {
	var el = document.createElement('textarea');

	el.value = input;

	// Prevent keyboard from showing on mobile
	el.setAttribute('readonly', '');

	el.style.contain = 'strict';
	el.style.position = 'absolute';
	el.style.left = '-9999px';
	el.style.fontSize = '12pt'; // Prevent zooming on iOS

	var selection = getSelection();
	var originalRange = false;
	if (selection.rangeCount > 0) {
		originalRange = selection.getRangeAt(0);
	}

	document.body.appendChild(el);
	el.select();

	// Explicit selection workaround for iOS
	el.selectionStart = 0;
	el.selectionEnd = input.length;

	var success = false;
	try {
		success = document.execCommand('copy');
	} catch (err) {}

	document.body.removeChild(el);

	if (originalRange) {
		selection.removeAllRanges();
		selection.addRange(originalRange);
	}

	return success;
};

var copy = {
  bind: function bind(el, ref) {
    var value = ref.value;
    var arg = ref.arg;

    if (arg === 'callback') {
      el.$copyCallback = value;
    } else {
      el.$copyValue = value;
      var handler = function () {
        copyTextToClipboard$1(el.$copyValue);
        if (el.$copyCallback) {
          el.$copyCallback(el.$copyValue);
        }
      };
      el.addEventListener('click', handler);
      el.$destroyCopy = function () { return el.removeEventListener('click', handler); };
    }
  },

  unbind: function unbind(el) {
    el.$destroyCopy();
  },

  componentUpdated: function componentUpdated(el, ref) {
    var value = ref.value;
    var arg = ref.arg;

    if (arg === 'callback') {
      el.$copyCallback = value;
    } else {
      el.$copyValue = value;
    }
  }
};

var install = function (Vue) {
  Vue.directive('copy', copy);
};

if (typeof window !== 'undefined' && window.Vue) {
  install(window.Vue);
}

exports.copy = copy;
exports['default'] = install;

Object.defineProperty(exports, '__esModule', { value: true });

})));
