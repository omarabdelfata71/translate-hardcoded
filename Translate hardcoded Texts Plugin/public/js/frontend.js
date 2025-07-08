(function($) {
    'use strict';

    // Function to escape special characters in string for use in regex
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Function to replace text in an element and its children
    function replaceTextInElement(element) {
        if (!element) return;

        // Skip elements with these tags
        const skipTags = ['SCRIPT', 'STYLE', 'TEXTAREA', 'INPUT'];
        if (skipTags.includes(element.tagName)) return;

        // Process child nodes
        for (let i = 0; i < element.childNodes.length; i++) {
            const node = element.childNodes[i];

            // If it's a text node, replace the text
            if (node.nodeType === 3) { // Text node
                let content = node.textContent;
                let newContent = content;

                // Replace all matching texts
                Object.keys(wpmlHardcodedTranslations).forEach(function(originalText) {
                    const translatedText = wpmlHardcodedTranslations[originalText];
                    if (originalText !== translatedText) {
                        const regex = new RegExp(escapeRegExp(originalText), 'g');
                        newContent = newContent.replace(regex, translatedText);
                    }
                });

                // Only update if content has changed
                if (newContent !== content) {
                    node.textContent = newContent;
                }
            } else if (node.nodeType === 1) { // Element node
                // Process child element
                replaceTextInElement(node);

                // Also check attributes like title, alt, placeholder
                const attributesToCheck = ['title', 'alt', 'placeholder', 'data-text'];
                attributesToCheck.forEach(function(attr) {
                    if (node.hasAttribute(attr)) {
                        let attrValue = node.getAttribute(attr);
                        let newValue = attrValue;

                        Object.keys(wpmlHardcodedTranslations).forEach(function(originalText) {
                            const translatedText = wpmlHardcodedTranslations[originalText];
                            if (originalText !== translatedText) {
                                const regex = new RegExp(escapeRegExp(originalText), 'g');
                                newValue = newValue.replace(regex, translatedText);
                            }
                        });

                        if (newValue !== attrValue) {
                            node.setAttribute(attr, newValue);
                        }
                    }
                });
            }
        }
    }

    // Function to handle dynamic content changes
    function observeDOMChanges() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        replaceTextInElement(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Replace text in existing content
        replaceTextInElement(document.body);

        // Watch for dynamic content changes
        observeDOMChanges();
    });

})(jQuery);