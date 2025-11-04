// Initialize tabs with localStorage persistence
(function () {
    // Function to get a unique key for each tab container
    function getStorageKey(tabContainer) {
        const containerId = tabContainer.id || 'default';
        return `wcf-tab-${containerId}-active`;
    }

    // Function to activate a tab
    function activateTab(tabContainer, tabId) {
        // Reset all
        tabContainer.querySelectorAll('.wcf-tabs-nav li').forEach(function (li) {
            li.classList.remove('is-active');
        });
        tabContainer.querySelectorAll('.wcf-tab-content').forEach(function (c) {
            c.style.display = 'none';
        });

        // Activate selected
        const link = tabContainer.querySelector(`.wcf-tabs-nav a[href="${tabId}"]`);
        if (link) {
            link.parentElement.classList.add('is-active');
            const targetTab = tabContainer.querySelector(tabId);
            if (targetTab) targetTab.style.display = 'block';
            
            // Store active tab in localStorage
            localStorage.setItem(getStorageKey(tabContainer), tabId);
        }
    }

    function TabInit() {
        document.querySelectorAll('.wcf-tabs').forEach(function (tabContainer) {
            const tabLinks = tabContainer.querySelectorAll('.wcf-tabs-nav a');
            const tabContents = tabContainer.querySelectorAll('.wcf-tab-content');
            
            // Check for saved active tab
            const savedTab = localStorage.getItem(getStorageKey(tabContainer));
            const defaultTab = savedTab || (tabLinks[0] ? tabLinks[0].getAttribute('href') : '');

            // Hide all tabs first
            tabContents.forEach(function (c) {
                c.style.display = 'none';
            });
            tabContainer.querySelectorAll('.wcf-tabs-nav li').forEach(function (li) {
                li.classList.remove('is-active');
            });

            // Activate saved tab or first tab
            if (defaultTab) {
                activateTab(tabContainer, defaultTab);
            }

            // Click event
            tabLinks.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const tabId = this.getAttribute('href');
                    if (tabId && tabId !== '#') {
                        activateTab(tabContainer, tabId);
                    }
                });
            });
        });
    }

    // Clear storage only when actually navigating away (not during form submission)
    let formSubmitted = false;
    
    // Detect form submission
    document.addEventListener('submit', function() {
        formSubmitted = true;
    }, true);

    // Only clear storage if it wasn't a form submission
    window.addEventListener('beforeunload', function(e) {
        if (!formSubmitted) {
            document.querySelectorAll('.wcf-tabs').forEach(function(tabContainer) {
                localStorage.removeItem(getStorageKey(tabContainer));
            });
        }
    });

    // Initialize tabs when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', TabInit);
    } else {
        TabInit();
    }
})();
