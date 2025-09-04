// Initialize tabs with URL synchronization
(function () {
    function TabInit() {
        document.querySelectorAll('.wcf-tabs').forEach(function (tabContainer) {
            var tabLinks = tabContainer.querySelectorAll('.wcf-tabs-nav a');
            var tabContents = tabContainer.querySelectorAll('.wcf-tab-content');

            // Hide all except first
            tabContents.forEach(function (c, i) {
                c.style.display = i === 0 ? 'block' : 'none';
            });
            tabContainer.querySelectorAll('.wcf-tabs-nav li').forEach(function (li, i) {
                if (i === 0) li.classList.add('is-active');
            });

            // Click event
            tabLinks.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var tabId = this.getAttribute('href');
                    if (!tabId || tabId === '#') return;

                    // Reset all
                    tabContainer.querySelectorAll('.wcf-tabs-nav li').forEach(function (li) {
                        li.classList.remove('is-active');
                    });
                    tabContents.forEach(function (c) {
                        c.style.display = 'none';
                    });

                    // Activate selected
                    this.parentElement.classList.add('is-active');
                    var targetTab = tabContainer.querySelector(tabId);
                    if (targetTab) targetTab.style.display = 'block';
                });
            });
        });
    }
    document.addEventListener('DOMContentLoaded', TabInit);

})();
