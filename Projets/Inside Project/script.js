document.addEventListener('DOMContentLoaded', () => {
    // View switching functionality
    const kanbanView = document.querySelector('.kanban_view');
    const listView = document.querySelector('.list_view');
    const viewDropdownItems = document.querySelectorAll('.dropdown-item');

    // Set default view (Kanban)
    kanbanView.style.display = 'none';  // Using flex instead of block for Kanban
    listView.style.display = 'block';

    // Add click event listeners to dropdown items
    viewDropdownItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const selectedView = e.target.textContent.trim().toLowerCase();
            
            if (selectedView === 'kanban view') {
                kanbanView.style.display = 'flex';  // Using flex for Kanban
                listView.style.display = 'none';
            } else if (selectedView === 'list view') {
                kanbanView.style.display = 'none';
                listView.style.display = 'block';
            }
        });
    });

    // Existing drag and drop functionality
    const cards = document.querySelectorAll('.kanban_card');
    const containers = document.querySelectorAll('.kanban_card_container');

    cards.forEach(card => {
        card.addEventListener('dragstart', (e) => {
            card.classList.add('dragging');
        });

        card.addEventListener('dragend', (e) => {
            card.classList.remove('dragging');
        });
    });

    containers.forEach(container => {
        container.addEventListener('dragover', (e) => {
            e.preventDefault();
            container.classList.add('drag-over');
            const draggingCard = document.querySelector('.dragging');
            const siblings = [...container.querySelectorAll('.kanban_card:not(.dragging)')];
            
            const nextSibling = siblings.find(sibling => {
                const box = sibling.getBoundingClientRect();
                return e.clientY <= box.top + box.height / 2;
            });

            if (nextSibling) {
                container.insertBefore(draggingCard, nextSibling);
            } else {
                container.appendChild(draggingCard);
            }
        });

        container.addEventListener('dragleave', () => {
            container.classList.remove('drag-over');
        });

        container.addEventListener('drop', () => {
            container.classList.remove('drag-over');
        });
    });

    // Initialize all status badges with 'todo' status
    const statusBadges = document.querySelectorAll('#status');
    statusBadges.forEach(badge => {
        badge.classList.remove('status-completed');
        badge.classList.add('status-todo');
        badge.textContent = 'A Faire';
    });

    // Handle status changes through dropdown
    document.addEventListener('click', (e) => {
        const statusItem = e.target.closest('.status-dropdown-item');
        if (!statusItem) return;

        e.preventDefault();
        const card = statusItem.closest('.task_card');
        const statusBadge = card.querySelector('#status');
        const status = statusItem.dataset.status;

        // Remove existing status classes
        statusBadge.classList.remove('status-todo', 'status-doing', 'status-done', 'status-completed');

        // Add new status class and update text
        switch(status) {
            case 'todo':
                statusBadge.classList.add('status-todo');
                statusBadge.textContent = 'A Faire';
                break;
            case 'doing':
                statusBadge.classList.add('status-doing');
                statusBadge.textContent = 'Encours';
                break;
            case 'done':
                statusBadge.classList.add('status-done');
                statusBadge.textContent = 'Terminer';
                break;
        }
    });
    
});

