'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

function _interopDefault (ex) { return (ex && (typeof ex === 'object') && 'default' in ex) ? ex['default'] : ex; }

var mcopy = _interopDefault(require('modern-copy'));

var copy = {
  bind: function bind(el, ref) {
    var value = ref.value;
    var arg = ref.arg;

    if (arg === 'callback') {
      el.$copyCallback = value;
    } else {
      el.$copyValue = value;
      var handler = function () {
        mcopy(el.$copyValue);
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
