document.addEventListener('DOMContentLoaded', function() {
    // Menu item active state
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            menuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Search functionality
    const searchInputs = document.querySelectorAll('.search-box input');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                // Implement search functionality here
                console.log('Searching for:', this.value);
            }
        });
    });

    // Pagination interaction
    const pageLinks = document.querySelectorAll('.page-link');
    pageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.parentElement.classList.contains('active') && 
                !this.getAttribute('aria-label')) {
                document.querySelectorAll('.page-item').forEach(item => {
                    item.classList.remove('active');
                });
                this.parentElement.classList.add('active');
                // Implement pagination functionality here
                console.log('Page changed to:', this.textContent);
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Update status badge colors dynamically
        document.querySelectorAll('.status-badge').forEach(badge => {
            const status = badge.textContent.toLowerCase().replace(' ', '');
            badge.className = `status-badge status-${status}`;
        });
    });

});