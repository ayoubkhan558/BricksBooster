
document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    document.querySelectorAll('.bb-admin-nav-tab-wrapper .bb-admin-nav-tab').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove active class from all tabs
            document.querySelectorAll('.bb-admin-nav-tab-wrapper .bb-admin-nav-tab').forEach(function (t) {
                t.classList.remove('bb-admin-nav-tab-active');
            });

            // Add active class to clicked tab
            tab.classList.add('bb-admin-nav-tab-active');

            // Hide all tab panes
            document.querySelectorAll('.bb-admin-tab-pane').forEach(function (pane) {
                pane.classList.remove('active');
            });

            // Show target pane
            const tabId = tab.getAttribute('data-tab');
            const activePane = document.getElementById(tabId);
            if (activePane) {
                activePane.classList.add('active');
            }
        });
    });

    // No need for AJAX or JS form handling – form submits normally and saves options on reload
});