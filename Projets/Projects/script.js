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
                console.log('Searching for:', this.value);
            }
        });
    });

    // Status badge colors
    document.querySelectorAll('.status-badge').forEach(badge => {
        const status = badge.textContent.trim().toLowerCase().replace(/\s+/g, '');
        badge.classList.add(`status-${status}`);
    });

    // Delete project functionality
    let currentProjectId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Set up delete buttons with proper event handling
    document.querySelectorAll('.delete-project-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent event from bubbling to card
            currentProjectId = this.getAttribute('data-project-id');
            deleteModal.show();
        });
    });
    
    // Confirm delete action
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (currentProjectId) {
            fetch(`delete_project.php?id=${currentProjectId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Delete failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting project: ' + error.message);
            });
            deleteModal.hide();
        }
    });

    // Make project cards clickable (NEW CODE)
    document.querySelectorAll('.project-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Only proceed if not clicking on delete/edit buttons or their children
            if (!e.target.closest('.delete-project-btn') && !e.target.closest('.edit-button')) {
                const projectId = this.closest('.col-md-4').querySelector('.delete-project-btn').getAttribute('data-project-id');
                window.location.href = `./Tasks/index.php?project_id=${projectId}`;
            }
        });
        
        // Add hover effect
        card.style.cursor = 'pointer';
        card.style.transition = 'all 0.2s ease';
        card.addEventListener('mouseenter', () => card.style.backgroundColor = 'rgba(0,0,0,0.03)');
        card.addEventListener('mouseleave', () => card.style.backgroundColor = '');
    });

    // Toggle navbar
    const toggleButton = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    toggleButton.addEventListener('click', function() {
        navbarCollapse.classList.toggle('show');
    });

    // Close navbar when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.navbar') && 
            navbarCollapse.classList.contains('show')) {
            navbarCollapse.classList.remove('show');
        }
    });

    // Mobile navigation toggle
    const sidebar = document.querySelector('.sidebar');

    toggleButton.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });

    // Close mobile nav when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.sidebar') && 
            !event.target.closest('.navbar-collapse') && 
            !event.target.closest('.navbar-toggler')) {
            sidebar.classList.remove('active');
            navbarCollapse.classList.remove('show');
        }
    });
});