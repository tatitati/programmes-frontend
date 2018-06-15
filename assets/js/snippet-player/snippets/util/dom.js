define(function() {

    var element = document.createElement('div');

    var elementMatches = (element.matches ||
        element.matchesSelector ||
        element.webkitMatchesSelector ||
        element.mozMatchesSelector ||
        element.msMatchesSelector ||
        element.oMatchesSelector ||
        function(selector) {
            var element = this;
            var matches = (element.document || element.ownerDocument).querySelectorAll(selector);
            var i = 0;

            while (matches[i] && matches[i] !== element) i++;

            return matches[i] ? true : false;
        });

    var addClasses = (element.classList ? function(element, classNames) {
        for (var i = 0; i < classNames.length; i++)
            element.classList.add(classNames[i]);
    } : function(element, classNames) {
        element.className += ' ' + classNames.join(' ');
    });

    var removeClasses = (element.classList ? function(element, classNames) {
        for (var i = 0; i < classNames.length; i++)
            element.classList.remove(classNames[i]);
    } : function(element, classNames) {
        element.className = element.className.replace(new RegExp('(^|\\b)' + classNames.join('|') + '(\\b|$)', 'gi'), '');
    });

    var dom = {
        indexOf: function(list, element) {

            if (!list) {
                throw new Error('Must be a list of elements');
            }

            return Array.prototype.indexOf.call(list, element);
        },

        querySelectorParents: function(element, selector) {
            while (element.parentElement && !dom.matches(element.parentElement, selector))
                element = element.parentElement;

            return element.parentElement;
        },

        matches: function(element, selector) {
            return elementMatches.call(element, selector);
        },

        addClass: function(element, className) {
            addClasses(element, className.split(' '));
            return element;
        },

        removeClass: function(element, className) {
            removeClasses(element, className.split(' '));
            return element;
        },

        getData: function(element, key, defaultValue) {
            if (element.getAttribute('data-' + key) === null)
                return defaultValue;
            return element.getAttribute('data-' + key);
        },

        addEventListener: function(element, type, listener) {
            if (element.addEventListener) {
                element.addEventListener(type, listener, false);
            } else {
                element.attachEvent('on' + type, listener);
            }
        },

        removeEventListener: function(element, type, listener) {
            if (element.removeEventListener) {
                element.removeEventListener(type, listener, false);
            } else {
                element.detachEvent('on' + type, listener);
            }
        }
    };

    return dom;

});