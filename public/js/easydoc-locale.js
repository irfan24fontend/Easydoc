(function () {
    'use strict';

    var messages = window.easyDocMessages || {};
    var normalize = function (value) {
        return String(value).replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim().toLocaleLowerCase();
    };
    var dictionary = new Map();
    Object.keys(messages).forEach(function (source) {
        var target = messages[source];
        if (typeof target === 'string' && target !== source) {
            dictionary.set(normalize(source), target);
        }
    });

    function translate(value) {
        var original = String(value);
        var leading = (original.match(/^\s*/) || [''])[0];
        var trailing = (original.match(/\s*$/) || [''])[0];
        var trimmed = original.trim();
        if (!trimmed) return original;

        var exact = dictionary.get(normalize(trimmed));
        if (exact) return leading + exact + trailing;
        // Translate whole phrases only. Replacing individual words inside an
        // unmatched sentence creates broken Bengali/English mixtures.
        return original;
    }

    var ignoredTextTags = { SCRIPT: true, STYLE: true, NOSCRIPT: true, TEXTAREA: true, INPUT: true, PRE: true, CODE: true, SVG: true };
    var ignoredAttributeTags = { SCRIPT: true, STYLE: true, NOSCRIPT: true, TEXTAREA: true, PRE: true, CODE: true, SVG: true };
    var translateNode = function (node) {
        if (node.nodeType !== Node.TEXT_NODE || !node.nodeValue.trim()) return;
        var parent = node.parentElement;
        if (!parent || parent.isContentEditable || ignoredTextTags[parent.tagName]) return;
        var updated = translate(node.nodeValue);
        if (updated !== node.nodeValue) node.nodeValue = updated;
    };
    var translateAttributes = function (element) {
        if (!element || element.nodeType !== Node.ELEMENT_NODE || ignoredAttributeTags[element.tagName]) return;
        ['placeholder', 'title', 'aria-label', 'alt', 'data-bs-title', 'data-bs-original-title'].forEach(function (name) {
            if (!element.hasAttribute(name)) return;
            var value = element.getAttribute(name);
            var updated = translate(value);
            if (updated !== value) element.setAttribute(name, updated);
        });
        if (element.tagName === 'INPUT' && ['submit', 'button', 'reset'].includes((element.type || '').toLowerCase())) {
            var buttonValue = element.value;
            var translatedValue = translate(buttonValue);
            if (translatedValue !== buttonValue) element.value = translatedValue;
        }
    };
    var scan = function (root) {
        if (root.nodeType === Node.TEXT_NODE) {
            translateNode(root);
            return;
        }
        if (root.nodeType !== Node.ELEMENT_NODE && root.nodeType !== Node.DOCUMENT_NODE) return;
        if (root.nodeType === Node.ELEMENT_NODE) translateAttributes(root);
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        var node;
        while ((node = walker.nextNode())) translateNode(node);
        if (root.querySelectorAll) {
            root.querySelectorAll('[placeholder], [title], [aria-label], [alt], [data-bs-title], [data-bs-original-title], input[type="submit"], input[type="button"], input[type="reset"]').forEach(translateAttributes);
        }
    };

    var nativeAlert = window.alert;
    var nativeConfirm = window.confirm;
    window.alert = function (message) { return nativeAlert.call(window, translate(message)); };
    window.confirm = function (message) { return nativeConfirm.call(window, translate(message)); };

    function start() {
        scan(document.head);
        scan(document.body);
        var observer = new MutationObserver(function (records) {
            records.forEach(function (record) {
                if (record.type === 'characterData') {
                    translateNode(record.target);
                    return;
                }
                record.addedNodes.forEach(function (node) { scan(node); });
                if (record.type === 'attributes') translateAttributes(record.target);
            });
        });
        observer.observe(document.body, {
            subtree: true,
            childList: true,
            characterData: true,
            attributes: true,
            attributeFilter: ['placeholder', 'title', 'aria-label', 'alt', 'data-bs-title', 'data-bs-original-title']
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
})();
