// Initialize tabs with localStorage persistence
(function () {
    // Function to get a unique key for each tab container
    function getStorageKey(tabContainer) {
        const containerId = tabContainer.id || Array.from(document.querySelectorAll('.wcf-tabs')).indexOf(tabContainer);
        const page = new URLSearchParams(window.location.search).get('page') || '';
        return `wcf-tab-${window.location.pathname}-${page}-${containerId}-active`;
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
            try {
                localStorage.setItem(getStorageKey(tabContainer), tabId);
            } catch (error) {
                // Tabs still work when the browser does not allow storage.
            }
        }
    }

    function TabInit() {
        document.querySelectorAll('.wcf-tabs').forEach(function (tabContainer) {
            const tabLinks = tabContainer.querySelectorAll('.wcf-tabs-nav a');
            const tabContents = tabContainer.querySelectorAll('.wcf-tab-content');
            
            // Check for saved active tab
            let savedTab = null;
            try {
                savedTab = localStorage.getItem(getStorageKey(tabContainer));
            } catch (error) {
                // Use the first tab when storage is unavailable.
            }
            const savedLink = Array.from(tabLinks).find((link) => link.getAttribute('href') === savedTab);
            const defaultTab = savedLink ? savedTab : (tabLinks[0] ? tabLinks[0].getAttribute('href') : '');

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

    // Initialize tabs when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', TabInit);
    } else {
        TabInit();
    }
})();
