// Gestion de la barre latérale
let toggleButton = document.getElementById('div_nav');
let sidebar = document.getElementsByClassName('sidebar')[0];
let content = document.getElementsByClassName('content')[0];
let searchInput = document.getElementById('chrch');

// Fonction pour gérer l'affichage de la barre latérale
toggleButton.onclick = () => {
    if (sidebar.style.display !== 'none') {
        sidebar.style.display = 'none';
        content.style.marginLeft = '10px';
    } else {
        sidebar.style.display = 'flex';
        content.style.marginLeft = window.innerWidth <= 768 ? '60px' : '200px';
        sidebar.style.animation = 'slideIn 0.5s forwards';
    }
}

// Gestion des graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des tâches - utilise les données dynamiques
    const tasksCtx = document.getElementById('tasksChart').getContext('2d');
    new Chart(tasksCtx, {
        type: 'pie',
        data: {
            labels: ['Terminées', 'En attente', 'En cours', 'À faire'],
            datasets: [{
                data: [
                    tasksData.completed || 0, 
                    tasksData.on_hold || 0, 
                    tasksData.in_progress || 0, 
                    tasksData.pending || 0
                ],
                backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Graphique du journal de travail
    const workLogCtx = document.getElementById('workLogChart').getContext('2d');
    new Chart(workLogCtx, {
        type: 'doughnut',
        data: {
            labels: ['Projet A', 'Projet B', 'Projet C', 'Projet D'],
            datasets: [{
                data: [25, 25, 25, 25],
                backgroundColor: ['#ff6384', '#ff9f40', '#ffcd56', '#4bc0c0'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Graphique de performance
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [
                {
                    label: 'Réalisé',
                    data: [6, 7, 8, 10, 7, 9],
                    borderColor: '#ff6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Objectif',
                    data: [5, 6, 7, 9, 6, 8],
                    borderColor: '#36a2eb',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

// Gestion des tâches à faire
document.addEventListener('DOMContentLoaded', function() {
    const todoItems = document.querySelectorAll('.todo-item input[type="checkbox"]');
    
    todoItems.forEach(item => {
        item.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.style.textDecoration = 'line-through';
                label.style.color = '#6c757d';
                
                // Ici, on pourrait ajouter une requête AJAX pour mettre à jour le statut de la tâche
                // dans la base de données
            } else {
                label.style.textDecoration = 'none';
                label.style.color = '#212529';
            }
        });
    });
});

// Gestion responsive
window.addEventListener('resize', function() {
    if (window.innerWidth <= 768) {
        if (sidebar.style.display !== 'none') {
            content.style.marginLeft = '60px';
        }
    } else {
        if (sidebar.style.display !== 'none') {
            content.style.marginLeft = '200px';
        }
    }
});

// Recherche de projets/tâches
searchInput.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        const searchTerm = this.value.toLowerCase();
        if (searchTerm.trim() !== '') {
            // Ici, on pourrait ajouter une requête AJAX pour rechercher des projets/tâches
            // et afficher les résultats
            alert('Recherche de: ' + searchTerm + '\nCette fonctionnalité sera implémentée avec le backend.');
        }
    }
});

// Gestion des liens actifs dans la barre latérale
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
