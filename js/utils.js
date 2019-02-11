'use strict';
moment.lang('fr', {
    months : "janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre".split("_"),
    monthsShort : "janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.".split("_"),
    weekdays : "dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi".split("_"),
    weekdaysShort : "dim._lun._mar._mer._jeu._ven._sam.".split("_"),
    weekdaysMin : "Di_Lu_Ma_Me_Je_Ve_Sa".split("_"),
    longDateFormat : {
        LT : "HH:mm",
        LTS : "HH:mm:ss",
        L : "DD/MM/YYYY",
        LL : "D MMMM YYYY",
        LLL : "D MMMM YYYY LT",
        LLLL : "dddd D MMMM YYYY LT"
    },
    calendar : {
        sameDay: "[Aujourd'hui à] LT",
        nextDay: '[Demain à] LT',
        nextWeek: 'dddd [à] LT',
        lastDay: '[Hier à] LT',
        lastWeek: 'dddd [dernier à] LT',
        sameElse: 'L'
    },
    relativeTime : {
        future : "dans %s",
        past : "il y a %s",
        s : "quelques secondes",
        m : "une minute",
        mm : "%d minutes",
        h : "une heure",
        hh : "%d heures",
        d : "un jour",
        dd : "%d jours",
        M : "un mois",
        MM : "%d mois",
        y : "une année",
        yy : "%d années"
    },
    ordinalParse : /\d{1,2}(er|ème)/,
    ordinal : function (number) {
        return number + (number === 1 ? 'er' : 'ème');
    },
    meridiemParse: /PD|MD/,
    isPM: function (input) {
        return input.charAt(0) === 'M';
    },
    // in case the meridiem units are not separated around 12, then implement
    // this function (look at locale/id.js for an example)
    // meridiemHour : function (hour, meridiem) {
    //     return /* 0-23 hour, given meridiem token and hour 1-12 */
    // },
    meridiem : function (hours, minutes, isLower) {
        return hours < 12 ? 'PD' : 'MD';
    },
    week : {
        dow : 1, // Monday is the first day of the week.
        doy : 4  // The week that contains Jan 4th is the first week of the year.
    }
});
moment.lang('fr_heure', {
    months : "janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre".split("_"),
    monthsShort : "janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.".split("_"),
    weekdays : "dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi".split("_"),
    weekdaysShort : "dim._lun._mar._mer._jeu._ven._sam.".split("_"),
    weekdaysMin : "Di_Lu_Ma_Me_Je_Ve_Sa".split("_"),
    longDateFormat : {
        LT : "HH:mm",
        LTS : "HH:mm:ss",
        L : "DD/MM/YYYY LT",
        LL : "D MMMM YYYY LT",
        LLL : "D MMMM YYYY LT",
        LLLL : "dddd D MMMM YYYY LT"
    },
    calendar : {
        sameDay: "[Aujourd'hui à] LT",
        nextDay: '[Demain à] LT',
        nextWeek: 'dddd [à] LT',
        lastDay: '[Hier à] LT',
        lastWeek: 'dddd [dernier à] LT',
        sameElse: 'L'
    },
    relativeTime : {
        future : "dans %s",
        past : "il y a %s",
        s : "quelques secondes",
        m : "une minute",
        mm : "%d minutes",
        h : "une heure",
        hh : "%d heures",
        d : "un jour",
        dd : "%d jours",
        M : "un mois",
        MM : "%d mois",
        y : "une année",
        yy : "%d années"
    },
    ordinalParse : /\d{1,2}(er|ème)/,
    ordinal : function (number) {
        return number + (number === 1 ? 'er' : 'ème');
    },
    meridiemParse: /PD|MD/,
    isPM: function (input) {
        return input.charAt(0) === 'M';
    },
    // in case the meridiem units are not separated around 12, then implement
    // this function (look at locale/id.js for an example)
    // meridiemHour : function (hour, meridiem) {
    //     return /* 0-23 hour, given meridiem token and hour 1-12 */
    // },
    meridiem : function (hours, minutes, isLower) {
        return hours < 12 ? 'PD' : 'MD';
    },
    week : {
        dow : 1, // Monday is the first day of the week.
        doy : 4  // The week that contains Jan 4th is the first week of the year.
    }
});
moment.lang('fr_sans_heure', {
    months : "janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre".split("_"),
    monthsShort : "janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.".split("_"),
    weekdays : "dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi".split("_"),
    weekdaysShort : "dim._lun._mar._mer._jeu._ven._sam.".split("_"),
    weekdaysMin : "Di_Lu_Ma_Me_Je_Ve_Sa".split("_"),
    longDateFormat : {
        LT : "HH:mm",
        LTS : "HH:mm:ss",
        L : "DD/MM/YYYY",
        LL : "D MMMM YYYY",
        LLL : "D MMMM YYYY LT",
        LLLL : "dddd D MMMM YYYY LT"
    },
    calendar : {
        sameDay: "[Aujourd'hui]",
        nextDay: '[Demain]',
        nextWeek: 'dddd',
        lastDay: '[Hier]',
        lastWeek: 'dddd [dernier]',
        sameElse: 'L'
    },
    relativeTime : {
        future : "dans %s",
        past : "il y a %s",
        s : "quelques secondes",
        m : "une minute",
        mm : "%d minutes",
        h : "une heure",
        hh : "%d heures",
        d : "un jour",
        dd : "%d jours",
        M : "un mois",
        MM : "%d mois",
        y : "une année",
        yy : "%d années"
    },
    ordinalParse : /\d{1,2}(er|ème)/,
    ordinal : function (number) {
        return number + (number === 1 ? 'er' : 'ème');
    },
    meridiemParse: /PD|MD/,
    isPM: function (input) {
        return input.charAt(0) === 'M';
    },
    // in case the meridiem units are not separated around 12, then implement
    // this function (look at locale/id.js for an example)
    // meridiemHour : function (hour, meridiem) {
    //     return /* 0-23 hour, given meridiem token and hour 1-12 */
    // },
    meridiem : function (hours, minutes, isLower) {
        return hours < 12 ? 'PD' : 'MD';
    },
    week : {
        dow : 1, // Monday is the first day of the week.
        doy : 4  // The week that contains Jan 4th is the first week of the year.
    }
});
moment.lang('fr');

var defaultDiacriticsRemovalap = [
        {'base':'A', 'letters':'\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F'},
        {'base':'AA','letters':'\uA732'},
        {'base':'AE','letters':'\u00C6\u01FC\u01E2'},
        {'base':'AO','letters':'\uA734'},
        {'base':'AU','letters':'\uA736'},
        {'base':'AV','letters':'\uA738\uA73A'},
        {'base':'AY','letters':'\uA73C'},
        {'base':'B', 'letters':'\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181'},
        {'base':'C', 'letters':'\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E'},
        {'base':'D', 'letters':'\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779'},
        {'base':'DZ','letters':'\u01F1\u01C4'},
        {'base':'Dz','letters':'\u01F2\u01C5'},
        {'base':'E', 'letters':'\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E'},
        {'base':'F', 'letters':'\u0046\u24BB\uFF26\u1E1E\u0191\uA77B'},
        {'base':'G', 'letters':'\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E'},
        {'base':'H', 'letters':'\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D'},
        {'base':'I', 'letters':'\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197'},
        {'base':'J', 'letters':'\u004A\u24BF\uFF2A\u0134\u0248'},
        {'base':'K', 'letters':'\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2'},
        {'base':'L', 'letters':'\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780'},
        {'base':'LJ','letters':'\u01C7'},
        {'base':'Lj','letters':'\u01C8'},
        {'base':'M', 'letters':'\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C'},
        {'base':'N', 'letters':'\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4'},
        {'base':'NJ','letters':'\u01CA'},
        {'base':'Nj','letters':'\u01CB'},
        {'base':'O', 'letters':'\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C'},
        {'base':'OE','letters':'\u0152'},
	{'base':'OI','letters':'\u01A2'},
        {'base':'OO','letters':'\uA74E'},
        {'base':'OU','letters':'\u0222'},
        {'base':'P', 'letters':'\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754'},
        {'base':'Q', 'letters':'\u0051\u24C6\uFF31\uA756\uA758\u024A'},
        {'base':'R', 'letters':'\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782'},
        {'base':'S', 'letters':'\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784'},
        {'base':'T', 'letters':'\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786'},
        {'base':'TZ','letters':'\uA728'},
        {'base':'U', 'letters':'\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244'},
        {'base':'V', 'letters':'\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245'},
        {'base':'VY','letters':'\uA760'},
        {'base':'W', 'letters':'\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72'},
        {'base':'X', 'letters':'\u0058\u24CD\uFF38\u1E8A\u1E8C'},
        {'base':'Y', 'letters':'\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE'},
        {'base':'Z', 'letters':'\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762'},
        {'base':'a', 'letters':'\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250'},
        {'base':'aa','letters':'\uA733'},
        {'base':'ae','letters':'\u00E6\u01FD\u01E3'},
        {'base':'ao','letters':'\uA735'},
        {'base':'au','letters':'\uA737'},
        {'base':'av','letters':'\uA739\uA73B'},
        {'base':'ay','letters':'\uA73D'},
        {'base':'b', 'letters':'\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253'},
        {'base':'c', 'letters':'\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184'},
        {'base':'d', 'letters':'\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A'},
        {'base':'dz','letters':'\u01F3\u01C6'},
        {'base':'e', 'letters':'\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD'},
        {'base':'f', 'letters':'\u0066\u24D5\uFF46\u1E1F\u0192\uA77C'},
        {'base':'g', 'letters':'\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F'},
        {'base':'h', 'letters':'\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265'},
        {'base':'hv','letters':'\u0195'},
        {'base':'i', 'letters':'\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131'},
        {'base':'j', 'letters':'\u006A\u24D9\uFF4A\u0135\u01F0\u0249'},
        {'base':'k', 'letters':'\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3'},
        {'base':'l', 'letters':'\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747'},
        {'base':'lj','letters':'\u01C9'},
        {'base':'m', 'letters':'\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F'},
        {'base':'n', 'letters':'\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5'},
        {'base':'nj','letters':'\u01CC'},
        {'base':'o', 'letters':'\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275'},
        {'base':'oe','letters':'\u0153'},
	{'base':'oi','letters':'\u01A3'},
        {'base':'ou','letters':'\u0223'},
        {'base':'oo','letters':'\uA74F'},
        {'base':'oe','letters':'\u0153'},
	{'base':'p','letters':'\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755'},
        {'base':'q','letters':'\u0071\u24E0\uFF51\u024B\uA757\uA759'},
        {'base':'r','letters':'\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783'},
        {'base':'s','letters':'\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B'},
        {'base':'t','letters':'\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787'},
        {'base':'tz','letters':'\uA729'},
        {'base':'u','letters': '\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289'},
        {'base':'v','letters':'\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C'},
        {'base':'vy','letters':'\uA761'},
        {'base':'w','letters':'\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73'},
        {'base':'x','letters':'\u0078\u24E7\uFF58\u1E8B\u1E8D'},
        {'base':'y','letters':'\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF'},
        {'base':'z','letters':'\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763'},
        {'base':' ','letters':'"\'-'}
    ];
    
// build a map to feed to the function

var diacriticsMap = {};

for (var i=0; i < defaultDiacriticsRemovalap.length; i++){
var letters = defaultDiacriticsRemovalap[i].letters.split("");
for (var j=0; j < letters.length ; j++){
    diacriticsMap[letters[j]] = defaultDiacriticsRemovalap[i].base;
}
}

// actual operation

var removeDiacritics = function (str) {
   return str.replace(/[^A-Za-z0-9\s]/g, function(a){ 
     return diacriticsMap[a] || a; 
   });
}
var mergeStringArrays = function (a, b){
    var hash = {};
    var ret = [];

    for(var i=0; i < a.length; i++){
        var e = a[i];
        if (!hash[e]){
            hash[e] = true;
            ret.push(e);
        }
    }

    for(var i=0; i < b.length; i++){
        var e = b[i];
        if (!hash[e]){
            hash[e] = true;
            ret.push(e);
        }
    }

    return ret;
}

function debounce(fn, delay) {
  var timer = null;
  return function () {
    var context = this, args = arguments;
    clearTimeout(timer);
    timer = setTimeout(function () {
      fn.apply(context, args);
    }, delay);
  };
}

/*
jQuery Redirect v1.0.1

Copyright (c) 2013-2015 Miguel Galante
Copyright (c) 2011-2013 Nemanja Avramovic, www.avramovic.info

Licensed under CC BY-SA 4.0 License: http://creativecommons.org/licenses/by-sa/4.0/

This means everyone is allowed to:

Share - copy and redistribute the material in any medium or format
Adapt - remix, transform, and build upon the material for any purpose, even commercially.
Under following conditions:

Attribution - You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.
ShareAlike - If you remix, transform, or build upon the material, you must distribute your contributions under the same license as the original.

*/
(function ($) {
    'use strict';

    /**
     * jQuery Redirect
     * @param {string} url - Url of the redirection
     * @param {Object} values - (optional) An object with the data to send. If not present will look for values as QueryString in the target url.
     * @param {string} method - (optional) The HTTP verb can be GET or POST (defaults to POST)
     * @param {string} target - (optional) The target of the form. "_blank" will open the url in a new window.
     */
    $.redirect = function (url, values, method, target) {
        method = (method && method.toUpperCase() === 'GET') ? 'GET' : 'POST';

        if (!values) {
            var obj = $.parseUrl(url);
            url = obj.url;
            values = obj.params;
        }

        var form = $('<form>')
          .attr("method", method)
          .attr("action", url);

        if (target) {
          form.attr("target", target);
        }

        iterateValues(values, [], form);
        $(document).find('body').append(form);
        form[0].submit();
    };

    //Utility Functions
    /**
     * Url and QueryString Parser.
     * @param {string} url - a Url to parse.
     * @returns {object} an object with the parsed url with the following structure {url: URL, params:{ KEY: VALUE }}
     */
    $.parseUrl = function (url) {
        if (url.indexOf('?') === -1) {
            return {
                url: url,
                params: {}
            };
        }
        var parts = url.split('?'),
            query_string = parts[1],
            elems = query_string.split('&');
        url = parts[0];

        var i, pair, obj = {};
        for (i = 0; i < elems.length; i+= 1) {
            pair = elems[i].split('=');
            obj[pair[0]] = pair[1];
        }

        return {
            url: url,
            params: obj
        };
    };

    //Private Functions
    var getInput = function (name, value, parent, array) {
        var parentString;
        if (parent.length > 0) {
            parentString = parent[0];
            var i;
            for (i = 1; i < parent.length; i += 1) {
                parentString += "[" + parent[i] + "]";
            }

            if (array) {
              name = parentString + "[]";
            } else {
              name = parentString + "[" + name + "]";
            }
        }

        return $("<input>").attr("type", "hidden")
            .attr("name", name)
            .attr("value", value);
    };

    var iterateValues = function (values, parent, form, array) {
        var i, iterateParent = [];
        for (i in values) {
            if (typeof values[i] === "object") {
                iterateParent = parent.slice();
                if (array) {
                  iterateParent.push('');
                } else {
                  iterateParent.push(i);
                }
                iterateValues(values[i], iterateParent, form, Array.isArray(values[i]));
            } else {
                form.append(getInput(i, values[i], parent, array));
            }
        }
    };
}(angular.element));

function waitUntil(f,c){
    if (f()) {
        c();
    } else {
        setTimeout(function(){waitUntil(f,c);},10);
    }
}
function departement(n) {
	var departements=[];
	departements['1']={nom:'Ain', prefecture:'Bourg-en-Bresse', region:'Rhône-Alpes'};
	departements['2']={nom:'Aisne', prefecture:'Laon', region:'Picardie'};
	departements['3']={nom:'Allier', prefecture:'Moulins', region:'Auvergne'};
	departements['4']={nom:'Alpes de Hautes-Provence', prefecture:'Digne', region:'Provence-Alpes-Côte d\'Azur'};
	departements['5']={nom:'Hautes-Alpes', prefecture:'Gap', region:'Provence-Alpes-Côte d\'Azur'};
	departements['6']={nom:'Alpes-Maritimes', prefecture:'Nice', region:'Provence-Alpes-Côte d\'Azur'};
	departements['7']={nom:'Ardèche', prefecture:'Privas', region:'Rhône-Alpes'};
	departements['8']={nom:'Ardennes', prefecture:'Charleville-Mézières', region:'Champagne-Ardenne'};
	departements['9']={nom:'Ariège', prefecture:'Foix', region:'Midi-Pyrénées'};
	departements['10']={nom:'Aube', prefecture:'Troyes', region:'Champagne-Ardenne'};
	departements['11']={nom:'Aude', prefecture:'Carcassonne', region:'Languedoc-Roussillon'};
	departements['12']={nom:'Aveyron', prefecture:'Rodez', region:'Midi-Pyrénées'};
	departements['13']={nom:'Bouches-du-Rhône', prefecture:'Marseille', region:'Provence-Alpes-Côte d\'Azur'};
	departements['14']={nom:'Calvados', prefecture:'Caen', region:'Basse-Normandie'};
	departements['15']={nom:'Cantal', prefecture:'Aurillac', region:'Auvergne'};
	departements['16']={nom:'Charente', prefecture:'Angoulême', region:'Poitou-Charentes'};
	departements['17']={nom:'Charente-Maritime', prefecture:'La Rochelle', region:'Poitou-Charentes'};
	departements['18']={nom:'Cher', prefecture:'Bourges', region:'Centre'};
	departements['19']={nom:'Corrèze', prefecture:'Tulle', region:'Limousin'};
	departements['2A']={nom:'Corse-du-Sud', prefecture:'Ajaccio', region:'Corse'};
	departements['2B']={nom:'Haute-Corse', prefecture:'Bastia', region:'Corse'};
	departements['21']={nom:'Côte-d\'Or', prefecture:'Dijon', region:'Bourgogne'};
	departements['22']={nom:'Côtes d\'Armor', prefecture:'Saint-Brieuc', region:'Bretagne'};
	departements['23']={nom:'Creuse', prefecture:'Guéret', region:'Limousin'};
	departements['24']={nom:'Dordogne', prefecture:'Périgueux', region:'Aquitaine'};
	departements['25']={nom:'Doubs', prefecture:'Besançon', region:'Franche-Comté'};
	departements['26']={nom:'Drôme', prefecture:'Valence', region:'Rhône-Alpes'};
	departements['27']={nom:'Eure', prefecture:'Évreux', region:'Haute-Normandie'};
	departements['28']={nom:'Eure-et-Loir', prefecture:'Chartres', region:'Centre'};
	departements['29']={nom:'Finistère', prefecture:'Quimper', region:'Bretagne'};
	departements['30']={nom:'Gard', prefecture:'Nîmes', region:'Languedoc-Roussillon'};
	departements['31']={nom:'Haute-Garonne', prefecture:'Toulouse', region:'Midi-Pyrénées'};
	departements['32']={nom:'Gers', prefecture:'Auch', region:'Midi-Pyrénées'};
	departements['33']={nom:'Gironde', prefecture:'Bordeaux', region:'Aquitaine'};
	departements['34']={nom:'Hérault', prefecture:'Montpellier', region:'Languedoc-Roussillon'};
	departements['35']={nom:'Ille-et-Vilaine', prefecture:'Rennes', region:'Bretagne'};
	departements['36']={nom:'Indre', prefecture:'Châteauroux', region:'Centre'};
	departements['37']={nom:'Indre-et-Loire', prefecture:'Tours', region:'Centre'};
	departements['38']={nom:'Isère', prefecture:'Grenoble', region:'Rhône-Alpes'};
	departements['39']={nom:'Jura', prefecture:'Lons-le-Saunier', region:'Franche-Comté'};
	departements['40']={nom:'Landes', prefecture:'Mont-de-Marsan', region:'Aquitaine'};
	departements['41']={nom:'Loir-et-Cher', prefecture:'Blois', region:'Centre'};
	departements['42']={nom:'Loire', prefecture:'Saint-Étienne', region:'Rhône-Alpes'};
	departements['43']={nom:'Haute-Loire', prefecture:'Le Puy-en-Velay', region:'Auvergne'};
	departements['44']={nom:'Loire-Atlantique', prefecture:'Nantes', region:'Pays de la Loire'};
	departements['45']={nom:'Loiret', prefecture:'Orléans', region:'Centre'};
	departements['46']={nom:'Lot', prefecture:'Cahors', region:'Midi-Pyrénées'};
	departements['47']={nom:'Lot-et-Garonne', prefecture:'Agen', region:'Aquitaine'};
	departements['48']={nom:'Lozère', prefecture:'Mende', region:'Languedoc-Roussillon'};
	departements['49']={nom:'Maine-et-Loire', prefecture:'Angers', region:'Pays de la Loire'};
	departements['50']={nom:'Manche', prefecture:'Saint-Lô', region:'Basse-Normandie'};
	departements['51']={nom:'Marne', prefecture:'Châlons-en-Champagne', region:'Champagne-Ardenne'};
	departements['52']={nom:'Haute-Marne', prefecture:'Chaumont', region:'Champagne-Ardenne'};
	departements['53']={nom:'Mayenne', prefecture:'Laval', region:'Pays de la Loire'};
	departements['54']={nom:'Meurthe-et-Moselle', prefecture:'Nancy', region:'Lorraine'};
	departements['55']={nom:'Meuse', prefecture:'Bar-le-Duc', region:'Lorraine'};
	departements['56']={nom:'Morbihan', prefecture:'Vannes', region:'Bretagne'};
	departements['57']={nom:'Moselle', prefecture:'Metz', region:'Lorraine'};
	departements['58']={nom:'Nièvre', prefecture:'Nevers', region:'Bourgogne'};
	departements['59']={nom:'Nord', prefecture:'Lille', region:'Nord-Pas-de-Calais'};
	departements['60']={nom:'Oise', prefecture:'Beauvais', region:'Picardie'};
	departements['61']={nom:'Orne', prefecture:'Alençon', region:'Basse-Normandie'};
	departements['62']={nom:'Pas-de-Calais', prefecture:'Arras', region:'Nord-Pas-de-Calais'};
	departements['63']={nom:'Puy-de-Dôme', prefecture:'Clermont-Ferrand', region:'Auvergne'};
	departements['64']={nom:'Pyrénées-Atlantiques', prefecture:'Pau', region:'Aquitaine'};
	departements['65']={nom:'Hautes-Pyrénées', prefecture:'Tarbes', region:'Midi-Pyrénées'};
	departements['66']={nom:'Pyrénées-Orientales', prefecture:'Perpignan', region:'Languedoc-Roussillon'};
	departements['67']={nom:'Bas-Rhin', prefecture:'Strasbourg', region:'Alsace'};
	departements['68']={nom:'Haut-Rhin', prefecture:'Colmar', region:'Alsace'};
	departements['69']={nom:'Rhône', prefecture:'Lyon', region:'Rhône-Alpes'};
	departements['70']={nom:'Haute-Saône', prefecture:'Vesoul', region:'Franche-Comté'};
	departements['71']={nom:'Saône-et-Loire', prefecture:'Mâcon', region:'Bourgogne'};
	departements['72']={nom:'Sarthe', prefecture:'Le Mans', region:'Pays de la Loire'};
	departements['73']={nom:'Savoie', prefecture:'Chambéry', region:'Rhône-Alpes'};
	departements['74']={nom:'Haute-Savoie', prefecture:'Annecy', region:'Rhône-Alpes'};
	departements['75']={nom:'Paris', prefecture:'Paris', region:'Ile-de-France'};
	departements['76']={nom:'Seine-Maritime', prefecture:'Rouen', region:'Haute-Normandie'};
	departements['77']={nom:'Seine-et-Marne', prefecture:'Melun', region:'Ile-de-France'};
	departements['78']={nom:'Yvelines', prefecture:'Versailles', region:'Ile-de-France'};
	departements['79']={nom:'Deux-Sèvres', prefecture:'Niort', region:'Poitou-Charentes'};
	departements['80']={nom:'Somme', prefecture:'Amiens', region:'Picardie'};
	departements['81']={nom:'Tarn', prefecture:'Albi', region:'Midi-Pyrénées'};
	departements['82']={nom:'Tarn-et-Garonne', prefecture:'Montauban', region:'Midi-Pyrénées'};
	departements['83']={nom:'Var', prefecture:'Toulon', region:'Provence-Alpes-Côte d\'Azur'};
	departements['84']={nom:'Vaucluse', prefecture:'Avignon', region:'Provence-Alpes-Côte d\'Azur'};
	departements['85']={nom:'Vendée', prefecture:'La Roche-sur-Yon', region:'Pays de la Loire'};
	departements['86']={nom:'Vienne', prefecture:'Poitiers', region:'Poitou-Charentes'};
	departements['87']={nom:'Haute-Vienne', prefecture:'Limoges', region:'Limousin'};
	departements['88']={nom:'Vosges', prefecture:'Épinal', region:'Lorraine'};
	departements['89']={nom:'Yonne', prefecture:'Auxerre', region:'Bourgogne'};
	departements['90']={nom:'Territoire-de-Belfort', prefecture:'Belfort', region:'Franche-Comté'};
	departements['91']={nom:'Essonne', prefecture:'Évry', region:'Ile-de-France'};
	departements['92']={nom:'Hauts-de-Seine', prefecture:'Nanterre', region:'Ile-de-France'};
	departements['93']={nom:'Seine-Saint-Denis', prefecture:'Bobigny', region:'Ile-de-France'};
	departements['94']={nom:'Val-de-Marne', prefecture:'Créteil', region:'Ile-de-France'};
	departements['95']={nom:'Val-d\'Oise', prefecture:'Pontoise', region:'Ile-de-France'};
	departements['971']={nom:'Guadeloupe', prefecture:'Basse-Terre', region:'Guadeloupe'};
	departements['972']={nom:'Martinique', prefecture:'Fort-de-France', region:'Martinique'};
	departements['973']={nom:'Guyane', prefecture:'Cayenne', region:'Guyane'};
	departements['974']={nom:'La Réunion', prefecture:'Saint-Denis', region:'La Réunion'};
	departements['976']={nom:'Mayotte', prefecture:'Dzaoudzi', region:'Mayotte'};
	var res={};
    res= departements[n] ? departements[n] : {nom:'département incorrect!'};
	res.n=n;
	return res;
}


