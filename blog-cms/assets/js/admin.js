// Blog CMS Admin JS
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('adminSidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
});
