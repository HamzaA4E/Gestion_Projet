// VARIABLES GLOBALES
let draggedTask = null;
let taskCounter = 7; // Commence à 7 car il y a déjà 6 tâches existantes
let currentTaskColumn = null;
let temporaryComments = []; // Stocker les commentaires avant la création de la tâche

// INITIALISATION
document.addEventListener('DOMContentLoaded', async function() {
    console.log('DOM chargé - initialisation');
    
    // Initialiser le sélecteur de vue
    const viewSelector = document.getElementById('viewSelector');
    if (viewSelector) {
        viewSelector.value = 'liste';
        viewSelector.addEventListener('change', function() {
            if (this.value === 'colonne') {
                window.location.href = 'index.php';
            }
        });
    }

    // Charger les tâches
    await loadTasks();
    setupTaskEditing();
    
    // Initialiser les autres fonctionnalités
    initializeSearch();
    initializeAddTaskButtons();
    setupTaskEditing();
    setInterval(updateAllTaskTimers, 60000);
});

// FONCTIONS DE CHARGEMENT
async function loadTasks() {
    try {
        const tasks = await loadTasksFromDB();
        const taskListContainer = document.getElementById('task-list-container');
        
        if (!taskListContainer) {
            console.error('Conteneur des tâches introuvable');
            return;
        }

        taskListContainer.innerHTML = '';
        taskCounter = tasks.length;

        tasks.forEach(task => {
            const taskElement = createTaskElement(task);
            taskListContainer.appendChild(taskElement);
        });
    } catch (error) {
        console.error('Erreur chargement tâches:', error);
    }
}

async function loadTasksFromDB() {
    try {
        const response = await fetch('api/tasks.php?table=tasks'); // Changé de 'test' à 'tasks'
        if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
        
        const data = await response.json();
        
        return data.map(task => ({
            id: task.id,
            title: task.titre,
            description: task.description,
            due_date: task.date_echeance,
            priority: task.priorite,
            status: task.status,
            assigned_to: task.assigned_to,
            project_id: task.project_id,
            date_creation: task.date_creation,
            date_modification: task.date_modification
        }));
    } catch (error) {
        console.error('Erreur lors du chargement:', error);
        return [];
    }
}

function createTaskElement(task) {
    const taskElement = document.createElement('div');
    taskElement.className = 'task-item';
    taskElement.id = 'task-' + task.id;
    
    // Stockage des données - adapter aux champs de votre table
    taskElement.dataset.dueDate = task.due_date || '';
    taskElement.dataset.priority = task.priority || 'moyenne';
    taskElement.dataset.status = task.status || 'pending';
    taskElement.dataset.projectId = task.project_id || '';
    taskElement.dataset.assignedTo = task.assigned_to || '';

    // Construction du HTML
    taskElement.innerHTML = `
        <div class="task-header">
            <h5>${task.title || 'Nouvelle tâche'}</h5>
            <span class="task-status">${task.status || 'pending'}</span>
        </div>
        <div class="task-description">
            <p>${task.description || ''}</p>
        </div>
        <div class="task-footer">
            <span class="deadline">${formatDeadline(task.due_date)}</span>
            <span class="priority-badge priority-${task.priority || 'moyenne'}">
                ${task.priority || 'moyenne'}
            </span>
        </div>
    `;
    
    return taskElement;
}

// Fonction helper pour formater la date
function formatDeadline(due_date) {
    if (!due_date || due_date === '0000-00-00' || due_date === '1970-01-01') {
        return 'Pas de date limite';
    }

    try {
        const formattedDate = new Date(due_date);
        
        if (isNaN(formattedDate.getTime())) {
            return 'Date invalide';
        }

        return formattedDate.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (e) {
        console.error('Erreur de formatage de date:', e);
        return 'Date invalide';
    }
}
// FONCTIONS DE RECHERCHE
function initializeSearch() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    
    if (searchForm) {
        searchForm.addEventListener('submit', searchTasks);
    }
    
    if (searchInput) {
        // Recherche en temps réel avec délai
        searchInput.addEventListener('input', function() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(searchTasks, 300);
        });
        
        // Bouton de réinitialisation de recherche
        const clearButton = document.querySelector('.search-clear');
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                resetSearch();
            });
        }
    }
}

function searchTasks(event) {
    if (event && event.preventDefault) {
        event.preventDefault();
    }
    
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const searchQuery = searchInput.value.trim().toLowerCase();
    
    if (searchQuery === '') {
        resetSearch();
        return;
    }
    
    const tasks = document.querySelectorAll('.task');
    let visibleTasksCount = 0;
    
    tasks.forEach(task => {
        const taskTitle = task.querySelector('h5')?.textContent.toLowerCase() || '';
        const taskDescription = task.querySelector('.my-2 p')?.textContent.toLowerCase() || '';
        const taskDeadline = task.querySelector('.TaskHead p')?.textContent.toLowerCase() || '';
        const taskContent = `${taskTitle} ${taskDescription} ${taskDeadline}`;
        
        if (taskContent.includes(searchQuery)) {
            task.style.display = 'block';
            task.classList.add('search-highlight');
            visibleTasksCount++;
        } else {
            task.style.display = 'none';
            task.classList.remove('search-highlight');
        }
    });
    
    // Gestion du message "aucun résultat"
    handleNoResultsMessage(visibleTasksCount, searchQuery);
}

function handleNoResultsMessage(visibleCount, query) {
    let noResultsMsg = document.getElementById('noResultsMessage');
    const shouldShowMessage = visibleCount === 0 && query !== '';
    
    if (shouldShowMessage) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.id = 'noResultsMessage';
            noResultsMsg.className = 'alert alert-info mt-3';
            noResultsMsg.textContent = 'Aucune tâche ne correspond à votre recherche.';
            document.querySelector('.search-container').appendChild(noResultsMsg);
        } else {
            noResultsMsg.style.display = 'block';
        }
    } else if (noResultsMsg) {
        noResultsMsg.style.display = 'none';
    }
}

function resetSearch() {
    const tasks = document.querySelectorAll('.task');
    tasks.forEach(task => {
        task.style.display = 'block';
        task.classList.remove('search-highlight');
    });
    
    const noResultsMsg = document.getElementById('noResultsMessage');
    if (noResultsMsg) {
        noResultsMsg.style.display = 'none';
    }
}

// FONCTIONS DE GESTION DES TÂCHES
function initializeAddTaskButtons() {
    document.querySelectorAll('.add-task-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentTaskColumn = this.closest('.task-container');
            loadPopup('./popup.html', 'Ajouter une tâche', null, addPopupEventListeners);
        });
    });
}

function setupTaskEditing() {
    document.addEventListener('click', function(event) {
        // Vérifier si on clique sur une tâche existante
        const taskElement = event.target.closest('.task-item');
        if (!taskElement) return;

        // Ignorer les clics sur les éléments d'action
        if (event.target.closest('.move-up, .move-down, .delete-task')) {
            return;
        }

        // Rafraîchir la référence de la tâche à chaque clic
        window.taskBeingEdited = taskElement;
        
        // Charger les données à partir du DOM actuel
        loadTaskDataForPopup(taskElement);
    });
}

function loadTaskDataForPopup(taskElement) {
    // Vérifier que taskElement est bien un élément DOM
    if (!(taskElement instanceof Element)) {
        console.error('taskElement doit être un élément DOM', taskElement);
        return;
    }

    // Extraire les données de l'élément DOM
    const taskData = {
        id: taskElement.id.replace('task-', ''),
        title: taskElement.querySelector('.task-header h5')?.textContent || '',
        description: taskElement.querySelector('.task-description p')?.textContent || '',
        due_date: taskElement.dataset.dueDate || '',
        priority: taskElement.dataset.priority || 'medium',
        status: taskElement.querySelector('.task-status')?.textContent || 'backlog'
    };

    showTaskPopup('Modifier la tâche', taskData);
}

function createNewTask(title, description, commentText, deadlineDate, deadlineTime) {
    if (!currentTaskColumn) {
        console.error("Impossible de déterminer la colonne pour la nouvelle tâche");
        return;
    }

    taskCounter++;

    const newTask = document.createElement('div');
    newTask.className = 'task-item';
    newTask.id = 'task-' + taskCounter;
    
    // Stocker les données
    newTask.dataset.dueDate = deadlineDate || '';
    newTask.dataset.priority = 'moyenne';
    newTask.dataset.status = 'pending';
    
    // Construction du HTML
    newTask.innerHTML = `
        <div class="task-header">
            <h5>${title || 'Nouvelle tâche'}</h5>
            <span class="task-status">pending</span>
        </div>
        <div class="task-description">
            <p>${description || ''}</p>
        </div>
        <div class="task-footer">
            <span class="deadline">${formatDeadline(deadlineDate)}</span>
            <span class="priority-badge priority-moyenne">
                moyenne
            </span>
        </div>
    `;

    currentTaskColumn.appendChild(newTask);
    
    // Ici, vous devriez aussi envoyer les données au serveur
    // via une requête AJAX pour créer la tâche dans la base de données
}

function addCommentToTask(taskElement, commentText, shouldIncrementCount = true) {
    // Stocker les commentaires dans les données de la tâche
    const comments = JSON.parse(taskElement.dataset.comments || '[]');
    comments.push(commentText);
    taskElement.dataset.comments = JSON.stringify(comments);

    // Mettre à jour le nombre de commentaires
    if (shouldIncrementCount) {
        const commentCountElement = taskElement.querySelector('.fa-comment-dots').nextSibling;
        const currentCommentCount = parseInt(commentCountElement.textContent.trim()) || 0;
        commentCountElement.textContent = ` ${currentCommentCount + 1}`;
    }
}

function moveTaskUp(arrow) {
    const task = arrow.closest('.task');
    const previousTask = task.previousElementSibling;

    if (previousTask && !previousTask.classList.contains('add-task-btn')) {
        task.parentElement.insertBefore(task, previousTask);
    }
}

function moveTaskDown(arrow) {
    const task = arrow.closest('.task');
    const nextTask = task.nextElementSibling;

    if (nextTask) {
        task.parentElement.insertBefore(nextTask, task);
    }
}

function deleteTask(taskId, event) {
    if (event) {
        event.stopPropagation();
    }

    const taskToDelete = document.getElementById(taskId);

    if (taskToDelete) {
        const confirmDelete = confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?");
        if (confirmDelete) {
            taskToDelete.remove();
        }
    }
}

// FONCTIONS DE GESTION DU TEMPS
function calculateTimeLeft(deadlineDate, deadlineTime) {
    if (!deadlineDate) {
        return "0 Minutes";
    }

    const deadline = new Date(`${deadlineDate}T${deadlineTime || '23:59'}`);
    const now = new Date();
    const diff = deadline - now;

    if (diff < 0) {
        return "En retard";
    }

    // Convertir la différence en jours, heures et minutes
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    // Formater la durée restante
    if (days > 0) {
        return `${days}j ${hours}h`;
    } else if (hours > 0) {
        return `${hours}h ${minutes}m`;
    } else {
        return `${minutes} Minutes`;
    }
}

function updateAllTaskTimers() {
    const tasks = document.querySelectorAll('.task[data-deadline-date]');

    tasks.forEach(task => {
        const deadlineDate = task.dataset.deadlineDate;
        const deadlineTime = task.dataset.deadlineTime;

        if (deadlineDate) {
            const timeLeftText = calculateTimeLeft(deadlineDate, deadlineTime);
            const timerElement = task.querySelector('.TaskHead p');

            if (timerElement) {
                timerElement.innerHTML = `<i class="fa-regular fa-clock me-1"></i>${timeLeftText}`;
            }
        }
    });
}

// FONCTIONS DE GESTION DES POPUPS
function ensurePopupOverlayExists() {
    let popupOverlay = document.getElementById('popupOverlay');
    
    if (!popupOverlay) {
        popupOverlay = document.createElement('div');
        popupOverlay.id = 'popupOverlay';
        popupOverlay.className = 'popup-overlay';
        document.body.appendChild(popupOverlay);
    }
    
    return popupOverlay;
}

function showTaskPopup(title, taskData) {
    closePopup();
    
    const popupOverlay = ensurePopupOverlayExists();
    popupOverlay.innerHTML = createPopupHTML(title, taskData);
    popupOverlay.classList.add('active');
    
    // Stocker les données pour la sauvegarde
    window.currentTaskData = taskData;
    
    // Configurer les écouteurs d'événements immédiatement après la création du popup
    setupPopupEventListeners(taskData.id ? 'edit' : 'add');
}
function createPopupHTML(title, taskData = {}) {
    // Valeurs par défaut
    const defaults = {
        title: '',
        description: '',
        due_date: '',
        priority: 'moyenne',
        status: 'pending',
        project_id: ''
    };
    
    // Fusionner avec les données fournies
    taskData = {...defaults, ...taskData};

    // Formater la date
    const formattedDeadline = taskData.due_date 
        ? new Date(taskData.due_date).toLocaleDateString('fr-FR')
        : 'Non définie';

    return `
        <div class="task-form-container">
            <div class="form-header">
                <div class="project-info">Projet: ${taskData.project_id || 'Non assigné'}</div>
                <div class="close-button">&times;</div>
            </div>
            
            <div class="scrollable-content">
                <div class="task-header">
                    <input type="text" placeholder="Titre de la tâche" 
                           value="${escapeHtml(taskData.title)}" class="task-title-input">
                </div>
                
                <div class="task-meta">
                    <div class="status-display">
                        <span class="meta-label">Statut:</span>
                        <select class="status-select">
                            <option value="pending" ${taskData.status === 'pending' ? 'selected' : ''}>En attente</option>
                            <option value="in_progress" ${taskData.status === 'in_progress' ? 'selected' : ''}>En cours</option>
                            <option value="on_hold" ${taskData.status === 'on_hold' ? 'selected' : ''}>En pause</option>
                            <option value="completed" ${taskData.status === 'completed' ? 'selected' : ''}>Terminé</option>
                        </select>
                    </div>
                    <div class="priority-display">
                        <span class="meta-label">Priorité:</span>
                        <select class="priority-select">
                            <option value="basse" ${taskData.priority === 'basse' ? 'selected' : ''}>Basse</option>
                            <option value="moyenne" ${taskData.priority === 'moyenne' ? 'selected' : ''}>Moyenne</option>
                            <option value="haute" ${taskData.priority === 'haute' ? 'selected' : ''}>Haute</option>
                            <option value="urgente" ${taskData.priority === 'urgente' ? 'selected' : ''}>Urgente</option>
                        </select>
                    </div>
                </div>
                
                <div class="deadline-container">
                    <span class="deadline-label">Date limite:</span>
                    <input type="date" class="deadline-date" 
                           value="${taskData.due_date.split(' ')[0] || ''}">
                </div>
                
                <div class="form-fields">
                    <div class="form-field">
                        <div class="description-header">
                            <span class="field-icon">📝</span>
                            <span class="field-label">Description:</span>
                        </div>
                        <textarea class="task-description-input">${escapeHtml(taskData.description)}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="button" class="btn btn-primary btn-save">Enregistrer</button>
                <button type="button" class="btn btn-secondary btn-cancel">Annuler</button>
            </div>
        </div>
    `;
}

function setupPopupEventListeners(mode) {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) return;

    // Bouton de fermeture
    popupOverlay.querySelector('.close-button')?.addEventListener('click', closePopup);
    
    // Bouton Annuler
    popupOverlay.querySelector('.btn-cancel')?.addEventListener('click', closePopup);
    
    // Bouton Enregistrer
    popupOverlay.querySelector('.btn-save')?.addEventListener('click', () => {
        const formData = getTaskFormData();
        
        if (!formData.title.trim()) {
            alert('Veuillez entrer un titre pour la tâche.');
            return;
        }
        
        if (mode === 'add') {
            createNewTask(
                formData.title,
                formData.description,
                formData.comment,
                formData.deadlineDate,
                formData.deadlineTime
            );
        } else {
            updateExistingTask(formData);
        }
        
        closePopup();
    });
    
    // Empêcher la propagation des clics dans le popup
    popupOverlay.querySelector('.task-form-container')?.addEventListener('click', (e) => {
        e.stopPropagation();
    });
}

function updateExistingTask(formData) {
    if (!window.currentTaskData?.id) return;
    
    const taskElement = document.getElementById(`task-${window.currentTaskData.id}`);
    if (!taskElement) return;
    
    // Mettre à jour le titre
    const titleElement = taskElement.querySelector('.task-header h5');
    if (titleElement) titleElement.textContent = formData.title;
    
    // Mettre à jour la description
    const descElement = taskElement.querySelector('.task-description p');
    if (descElement) descElement.textContent = formData.description;
    
    // Mettre à jour la date limite
    if (formData.due_date) {
        taskElement.dataset.dueDate = formData.due_date;
        const deadlineElement = taskElement.querySelector('.deadline');
        if (deadlineElement) {
            deadlineElement.textContent = formatDeadline(formData.due_date);
        }
    }
    
    // Mettre à jour le statut
    const statusElement = taskElement.querySelector('.task-status');
    if (statusElement) {
        statusElement.textContent = formData.status;
        taskElement.dataset.status = formData.status;
    }
    
    // Mettre à jour la priorité
    const priorityElement = taskElement.querySelector('.priority-badge');
    if (priorityElement) {
        priorityElement.textContent = formData.priority;
        priorityElement.className = `priority-badge priority-${formData.priority}`;
        taskElement.dataset.priority = formData.priority;
    }
}
function loadPopup(url, title, initialData, setupFunction) {
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const popupOverlay = document.getElementById('popupOverlay');
            popupOverlay.innerHTML = html;

            if (initialData) {
                popupOverlay.querySelector('.task-header input').value = initialData.title;
                popupOverlay.querySelector('#description').value = initialData.description;
                popupOverlay.querySelector('#task-deadline').value = initialData.deadlineDate;
                popupOverlay.querySelector('#task-deadline-time').value = initialData.deadlineTime;

                const commentDisplay = popupOverlay.querySelector('#commentDisplay');
                if (commentDisplay) {
                    commentDisplay.innerHTML = '';
                    initialData.comments.forEach(comment => {
                        const commentItem = document.createElement('div');
                        commentItem.className = 'comment-item mb-2 p-3 bg-light rounded';
                        commentItem.textContent = comment;
                        commentDisplay.appendChild(commentItem);
                    });
                }
            }

            popupOverlay.classList.add('active');

            if (setupFunction) setupFunction();
        })
        .catch(error => console.error('Erreur lors du chargement du popup :', error));
}

function closePopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (popupOverlay) {
        popupOverlay.classList.remove('active');
        document.body.classList.remove('popup-open');
        popupOverlay.innerHTML = ''; // Nettoyer le contenu
    }
    
    // Réinitialiser complètement la référence
    if (window.taskBeingEdited) {
        window.taskBeingEdited = null;
    }
    currentTaskColumn = null;
    temporaryComments = [];
}

// FONCTIONS DE SAUVEGARDE/MISE À JOUR
function saveTask() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) return;

    const title = popupOverlay.querySelector('.task-title-input')?.value;
    const description = popupOverlay.querySelector('.task-description-input')?.value;
    
    if (!title) {
        alert('Veuillez entrer un titre pour la tâche.');
        return;
    }

    console.log('Tâche sauvegardée:', { title, description });
    closePopup();
}

function updateTask() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) return;

    const title = popupOverlay.querySelector('.task-title-input')?.value;
    const description = popupOverlay.querySelector('.task-description-input')?.value;
    
    if (!title) {
        alert('Veuillez entrer un titre pour la tâche.');
        return;
    }

    // Trouver la tâche à mettre à jour dans le DOM
    const taskId = window.currentTaskData.id; // Supposons que vous avez un ID
    const taskElement = document.getElementById(`task-${taskId}`);
    
    if (taskElement) {
        // Mettre à jour les éléments du DOM directement
        const titleElement = taskElement.querySelector('.task-header h5');
        const descElement = taskElement.querySelector('.task-description p');
        
        if (titleElement) titleElement.textContent = title;
        if (descElement) descElement.textContent = description;
        
        // Mettre à jour les data-attributs si nécessaire
        taskElement.dataset.lastUpdated = new Date().toISOString();
    }

    closePopup();
}

function getTaskFormData() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) return {};
    
    return {
        title: popupOverlay.querySelector('.task-title-input')?.value || '',
        description: popupOverlay.querySelector('.task-description-input')?.value || '',
        status: popupOverlay.querySelector('.status-select')?.value || 'pending',
        priority: popupOverlay.querySelector('.priority-select')?.value || 'moyenne',
        due_date: popupOverlay.querySelector('.deadline-date')?.value || '',
        project_id: window.currentTaskData?.project_id || ''
    };
}

// FONCTIONS DE GESTION DES COMMENTAIRES
function setupCommentToolbar() {
    const boldButton = document.querySelector(".Bold-button");
    const italicButton = document.querySelector(".Italic-button");
    const underlineButton = document.querySelector(".Underline-button");
    const listButton = document.querySelector(".toolbar-button:first-child");
    const hashtagButton = document.querySelector(".toolbar-button:nth-child(2)");
    const comment = document.getElementById('comment');

    if (!comment) return;

    function formatSelectedText(tag) {
        if (comment && (comment.tagName === 'TEXTAREA' || comment.tagName === 'INPUT')) {
            const start = comment.selectionStart;
            const end = comment.selectionEnd;
            const text = comment.value;
            const selectedText = text.substring(start, end);
            const isWrapped = selectedText.startsWith(`<${tag}>`) && selectedText.endsWith(`</${tag}>`);

            let newText;
            if (isWrapped) {
                newText = text.substring(0, start) + selectedText.slice(tag.length + 2, -(tag.length + 3)) + text.substring(end);
            } else {
                newText = text.substring(0, start) + `<${tag}>` + selectedText + `</${tag}>` + text.substring(end);
            }

            comment.value = newText;
            
            const newStart = isWrapped ? start : start + tag.length + 2;
            const newEnd = isWrapped ? end - (tag.length + 3) : end + tag.length + 2;
            comment.setSelectionRange(newStart, newEnd);
        }
    }

    if (boldButton) boldButton.addEventListener('click', e => { e.preventDefault(); formatSelectedText('strong'); });
    if (italicButton) italicButton.addEventListener('click', e => { e.preventDefault(); formatSelectedText('em'); });
    if (underlineButton) underlineButton.addEventListener('click', e => { e.preventDefault(); formatSelectedText('u'); });
    
    if (listButton) {
        listButton.addEventListener('click', e => {
            e.preventDefault();
            formatSelectedText('li');
        });
    }
    
    if (hashtagButton) {
        hashtagButton.addEventListener('click', e => {
            e.preventDefault();
            const start = comment.selectionStart;
            const end = comment.selectionEnd;
            const text = comment.value;
            const selectedText = text.substring(start, end);
            
            const newText = text.substring(0, start) + '#' + selectedText + text.substring(end);
            comment.value = newText;
            comment.setSelectionRange(start + 1, end + 1);
        });
    }
}

function sendComment() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) return;

    const commentInput = popupOverlay.querySelector('#comment');
    if (!commentInput) return;

    const commentText = commentInput.innerText.trim();
    if (!commentText) return;

    const commentDisplay = popupOverlay.querySelector('#commentDisplay');
    if (commentDisplay) {
        const commentItem = document.createElement('div');
        commentItem.className = 'comment-item';
        commentItem.innerHTML = `<div class="comment-content">${commentText}</div>`;
        commentDisplay.appendChild(commentItem);
    }

    commentInput.innerText = '';
}

// FONCTIONS DE GLISSER-DÉPOSER
function allowDrop(event) {
    event.preventDefault();
}

function drag(event) {
    draggedTask = event.target;
    event.target.classList.add("dragging");
}

function drop(event) {
    event.preventDefault();
    const dropTarget = event.target.closest(".task-container");

    if (dropTarget && draggedTask) {
        dropTarget.appendChild(draggedTask);
        draggedTask.classList.remove("dragging");
        draggedTask = null;
    }
}

// FONCTIONS UTILITAIRES
function escapeHtml(unsafe) {
    return unsafe
        ? unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
        : '';
}