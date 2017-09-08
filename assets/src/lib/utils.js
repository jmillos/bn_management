import _ from 'lodash'
import areIntlLocalesSupported from 'intl-locales-supported'

export default class Utils {
  static strToJson(str) {
    try {
      str = eval("(" + str + ")")
    } catch (_error) {}

    return str;
  }

  static getAttrsFromElement(el) {
    let props = {}
    _.forEach(el.attributes, function(attr, i) {
      if (attr.nodeName != "class")
        props[attr.nodeName] = Utils.strToJson(attr.nodeValue)
    })

    return props
  }
}

export function dateTimeFormat(){
  let DateTimeFormat;

  /**
   * Use the native Intl.DateTimeFormat if available, or a polyfill if not.
   */
  if (areIntlLocalesSupported(['es-CO'])) {
    DateTimeFormat = global.Intl.DateTimeFormat;
  } else {
    // const IntlPolyfill = require('intl');
    // DateTimeFormat = IntlPolyfill.DateTimeFormat;
    // require('intl/locale-data/jsonp/fr');
    // require('intl/locale-data/jsonp/fa-IR');
  }

  return DateTimeFormat
}
