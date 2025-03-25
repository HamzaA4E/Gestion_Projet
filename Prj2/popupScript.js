/**
 * Popup Task Manager - Enhanced Version
 * Ce script gère les interactions du popup pour un système de gestion de tâches.
 * Il inclut la gestion des tâches, des commentaires, et des erreurs.
 */

// Variable globale pour stocker la tâche en cours d'édition
let taskBeingEdited = null;

// Initialisation du popup
function initializePopup() {
    setupPopupControls();
    setupCommentToolbar();
    setupCommentSending();
    setupTaskCreation();
    setupTaskEditing();
}

// Configurer les boutons du popup (Save, Cancel, Close)
function setupPopupControls() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) {
        console.error('Popup overlay non trouvé');
        return;
    }

    // Bouton Close
    const closeButton = popupOverlay.querySelector('.close-button');
    if (closeButton) {
        closeButton.addEventListener('click', (e) => {
            e.preventDefault();
            closePopup();
        });
    }

    // Bouton Save
    const saveButton = popupOverlay.querySelector('.btn-primary');
    if (saveButton) {
        saveButton.addEventListener('click', (e) => {
            e.preventDefault();
            saveTask();
        });
    }

    // Bouton Cancel
    const cancelButton = popupOverlay.querySelector('.btn-secondary');
    if (cancelButton) {
        cancelButton.addEventListener('click', (e) => {
            e.preventDefault();
            closePopup();
        });
    }
}

// Fermer le popup
function closePopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (popupOverlay) {
        popupOverlay.classList.remove('active');
        taskBeingEdited = null; // Réinitialiser la tâche en cours d'édition
    }
}

// Sauvegarder la tâche
function saveTask() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) {
        console.error('Popup overlay non trouvé');
        return;
    }

    // Récupérer les éléments du formulaire
    const titleInput = popupOverlay.querySelector('[data-task-title]');
    const descriptionInput = popupOverlay.querySelector('[data-task-description]');
    const deadlineDateInput = popupOverlay.querySelector('[data-task-deadline-date]');
    const deadlineTimeInput = popupOverlay.querySelector('[data-task-deadline-time]');
    const commentInput = popupOverlay.querySelector('[data-task-comment]');

    // Valider les champs obligatoires
    if (!titleInput || !descriptionInput) {
        console.error('Champs obligatoires manquants');
        return;
    }

    const taskTitle = titleInput.value.trim();
    const taskDescription = descriptionInput.value.trim();
    const deadlineDate = deadlineDateInput ? deadlineDateInput.value : '';
    const deadlineTime = deadlineTimeInput ? deadlineTimeInput.value : '';
    const comment = commentInput ? commentInput.innerText.trim() : '';

    if (!taskTitle) {
        alert('Veuillez entrer un titre pour la tâche.');
        return;
    }

    if (!taskDescription) {
        alert('Veuillez entrer une description pour la tâche.');
        return;
    }

    // Mettre à jour la tâche en cours d'édition ou en créer une nouvelle
    if (taskBeingEdited) {
        updateTask(taskBeingEdited, taskTitle, taskDescription, deadlineDate, deadlineTime, comment);
    } else {
        createNewTask(taskTitle, taskDescription, deadlineDate, deadlineTime, comment);
    }

    // Fermer le popup
    closePopup();
}

// Mettre à jour une tâche existante
function updateTask(taskElement, title, description, deadlineDate, deadlineTime, comment) {
    if (!taskElement) return;

    taskElement.querySelector('h5').textContent = title;
    taskElement.querySelector('.my-2 p').textContent = description;

    if (deadlineDate) {
        const timeLeftText = calculateTimeLeft(deadlineDate, deadlineTime);
        taskElement.querySelector('.TaskHead p').innerHTML = `<i class="fa-regular fa-clock me-1"></i>${timeLeftText}`;
        taskElement.dataset.deadlineDate = deadlineDate;
        taskElement.dataset.deadlineTime = deadlineTime || '23:59';
    }

    if (comment) {
        addCommentToTask(taskElement, comment);
    }
}

// Créer une nouvelle tâche
function createNewTask(title, description, deadlineDate, deadlineTime, comment) {
    const taskContainer = document.querySelector('.task-container');
    if (!taskContainer) return;

    const newTask = document.createElement('div');
    newTask.className = 'task mt-4 p-3 rounded-4';
    newTask.draggable = true;
    newTask.innerHTML = `
        <div class="TaskHead d-flex justify-content-between">
            <h5>${title}</h5>
            <p><i class="fa-regular fa-clock me-1"></i>${calculateTimeLeft(deadlineDate, deadlineTime)}</p>
        </div>
        <div class="my-2">
            <p>${description}</p>
        </div>
        <div class="d-flex gap-4 align-items-center">
            <p><i class="fa-solid fa-paperclip me-1"></i> 0</p>
            <p><i class="fa-regular fa-comment-dots me-1"></i> 0</p>
            <i class="fa-solid fa-user-plus ms-auto"></i>
            <div class="d-flex flex-column gap-1">
                <i class="fa-solid fa-arrow-up move-up" onclick="moveTaskUp(this)"></i>
                <i class="fa-solid fa-arrow-down move-down" onclick="moveTaskDown(this)"></i>
            </div>
            <i class="fa-solid fa-trash delete-task" onclick="deleteTask('${newTask.id}')"></i>
        </div>
        <div class="comment-list mt-3"></div>
    `;

    taskContainer.appendChild(newTask);

    if (comment) {
        addCommentToTask(newTask, comment);
    }
}

// Ajouter un commentaire à une tâche
function addCommentToTask(taskElement, commentText) {
    const commentList = taskElement.querySelector('.comment-list');
    const commentDisplay = document.getElementById('commentDisplay');

    if (!commentList || !commentDisplay) {
        console.error('Conteneur de commentaires non trouvé');
        return;
    }

    console.log('Commentaire à ajouter :', commentText); // Log pour débogage

    const commentItem = document.createElement('div');
    commentItem.className = 'comment-item mb-2 p-3 bg-light rounded';
    commentItem.textContent = commentText;

    commentList.appendChild(commentItem);
    commentDisplay.appendChild(commentItem.cloneNode(true)); // Ajouter également dans le popup

    console.log('Commentaire ajouté dans :', commentDisplay); // Log pour débogage

    // Mettre à jour le nombre de commentaires
    const commentCountElement = taskElement.querySelector('.fa-comment-dots').nextSibling;
    const currentCommentCount = parseInt(commentCountElement.textContent.trim()) || 0;
    commentCountElement.textContent = ` ${currentCommentCount + 1}`;
}

// Calculer le temps restant pour une tâche
function calculateTimeLeft(deadlineDate, deadlineTime) {
    if (!deadlineDate) return '0 Minutes';
  
    const deadline = new Date(`${deadlineDate}T${deadlineTime || '23:59'}`);
    const now = new Date();
    const diff = deadline - now;
  
    if (diff < 0) return 'En retard';
  
    // Convert to days, hours, minutes for better readability
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    if (days > 0) {
      return `${days}j ${hours}h`;
    } else if (hours > 0) {
      return `${hours}h ${minutes}m`;
    } else {
      return `${minutes} Minutes`;
    }
  }
// Configurer la barre d'outils des commentaires
function setupCommentToolbar() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) return;

    const boldButton = popupOverlay.querySelector('.Bold-button');
    const italicButton = popupOverlay.querySelector('.Italic-button');
    const underlineButton = popupOverlay.querySelector('.Underline-button');

    if (boldButton) {
        boldButton.addEventListener('click', (e) => {
            e.preventDefault();
            formatSelectedText('**', '**');
        });
    }

    if (italicButton) {
        italicButton.addEventListener('click', (e) => {
            e.preventDefault();
            formatSelectedText('*', '*');
        });
    }

    if (underlineButton) {
        underlineButton.addEventListener('click', (e) => {
            e.preventDefault();
            formatSelectedText('__', '__');
        });
    }
}

// Formater le texte sélectionné dans le commentaire
function formatSelectedText(prefix, suffix) {
    const commentElement = document.getElementById('comment');
    if (!commentElement) return;

    const selection = window.getSelection();
    if (!selection.rangeCount || selection.isCollapsed) {
        alert('Veuillez sélectionner du texte à formater.');
        return;
    }

    const range = selection.getRangeAt(0);
    const selectedText = range.toString();
    const formattedText = prefix + selectedText + suffix;

    range.deleteContents();
    const textNode = document.createTextNode(formattedText);
    range.insertNode(textNode);

    // Restaurer la sélection
    range.setStartAfter(textNode);
    range.setEndAfter(textNode);
    selection.removeAllRanges();
    selection.addRange(range);

    commentElement.focus();
}

// Configurer l'envoi des commentaires

// Envoyer un commentaire
// Envoyer un commentaire
function sendComment(event) {
    // Empêcher le comportement par défaut du formulaire (rechargement de la page)
    event.preventDefault();

    const commentInput = document.getElementById('comment');
    const commentText = commentInput.innerText.trim(); // Récupérer le texte du commentaire

    // Vérifier si le commentaire est vide
    if (!commentText) {
        //alert('Veuillez ajouter un commentaire avant d\'envoyer.');
        return; // Arrêter l'exécution si le commentaire est vide
    }

    // Ajouter le commentaire dans le conteneur #commentDisplay
    const commentDisplay = document.getElementById('commentDisplay');
    if (!commentDisplay) {
        console.error('Conteneur de commentaires non trouvé');
        return;
    }

    // Créer un nouveau bloc de commentaire
    const commentItem = document.createElement('div');
    commentItem.className = 'comment-item mb-2 p-3 bg-light rounded';
    commentItem.textContent = commentText;

    // Ajouter le bloc de commentaire dans #commentDisplay
    commentDisplay.appendChild(commentItem);

    // Réinitialiser le champ de commentaire
    commentInput.innerText = '';

    console.log('Commentaire envoyé avec succès :', commentText); // Log pour débogage
}

function setupCommentSending() {
    const sendButton = document.getElementById('sendCommentButton');
    if (sendButton) {
        sendButton.addEventListener('click', function (event) {
            sendComment(event); // Appeler la fonction pour envoyer le commentaire
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    setupCommentSending(); // Configurer l'envoi des commentaires
});


// Configurer la création de nouvelles tâches
function setupTaskCreation() {
    document.querySelectorAll('.add-task-btn').forEach(button => {
        button.addEventListener('click', function () {
            taskBeingEdited = null; // Réinitialiser la tâche en cours d'édition
            openEditPopup(null); // Ouvrir le popup sans tâche existante
        });
    });
}

// Configurer l'édition des tâches
function setupTaskEditing() {
    document.querySelectorAll('.task').forEach(task => {
        task.addEventListener('click', function () {
            taskBeingEdited = task;
            openEditPopup(task);
        });
    });
}

// Ouvrir le popup pour éditer une tâche
function openEditPopup(taskElement) {
    const popupOverlay = document.getElementById('popupOverlay');
    if (!popupOverlay) return;

    if (taskElement) {
        const taskTitle = taskElement.querySelector('h5').textContent;
        const taskDescription = taskElement.querySelector('.my-2 p').textContent;
        const deadlineDate = taskElement.dataset.deadlineDate || '';
        const deadlineTime = taskElement.dataset.deadlineTime || '';

        const titleInput = popupOverlay.querySelector('[data-task-title]');
        const descriptionInput = popupOverlay.querySelector('[data-task-description]');
        const deadlineDateInput = popupOverlay.querySelector('[data-task-deadline-date]');
        const deadlineTimeInput = popupOverlay.querySelector('[data-task-deadline-time]');

        if (titleInput) titleInput.value = taskTitle;
        if (descriptionInput) descriptionInput.value = taskDescription;
        if (deadlineDateInput) deadlineDateInput.value = deadlineDate;
        if (deadlineTimeInput) deadlineTimeInput.value = deadlineTime;

        // Charger les commentaires existants
        const commentList = taskElement.querySelector('.comment-list');
        const commentDisplay = document.getElementById('commentDisplay');
        if (commentList && commentDisplay) {
            commentDisplay.innerHTML = ''; // Effacer les commentaires actuels
            commentList.childNodes.forEach(comment => {
                commentDisplay.appendChild(comment.cloneNode(true));
            });
        }
    }

    popupOverlay.classList.add('active');
}

// Initialiser le script lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', initializePopup);