// Constants

document.getElementById('viewSelector').addEventListener('change', function() {
    const selectedView = this.value;

    if (selectedView === 'liste') {
        // Redirection vers index.php
        window.location.href = 'http://localhost/Prj2/liste.php';
    }
});
const TASK_STATUS = {
    BACKLOG: 'Backlog',
    IN_PROGRESS: 'In Progress',
    COMPLETED: 'Completed'
  };
  

  // Fonction pour charger le contenu du projet
 
  // State management
  const state = {
    draggedTask: null,
    taskCounter: 6,
    currentTaskColumn: null,
    temporaryComments: [],
    searchTimeout: null
  };

  
  
  // Initialization
  function init() {
    setupEventListeners();
    updateAllTaskTimers();
    setInterval(updateAllTaskTimers, 60000);
  }

  function allowDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over'); // Optionnel : feedback visuel
  }
  
  
  
  function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    const draggedTask = document.getElementById(e.dataTransfer.getData('text'));
    if (draggedTask) {
        e.currentTarget.appendChild(draggedTask);
        updateTaskStatus(draggedTask, getColumnStatus(e.currentTarget));
    }
  }
  
  // Event Listeners
  function setupEventListeners() {
    // Search functionality
    const searchForm = document.querySelector('form[onsubmit="searchTasks(event)"]');
    const searchInput = document.getElementById('searchInput');
    
    if (searchForm) {
      searchForm.addEventListener('submit', handleSearchSubmit);
    }
    
    if (searchInput) {
      searchInput.addEventListener('input', handleSearchInput);
    }
  
    // Add task buttons
    document.querySelectorAll('.add-task-btn').forEach(button => {
      button.addEventListener('click', handleAddTaskClick);
    });
  
    // Task editing
    document.addEventListener('click', handleTaskClick);

     document.querySelectorAll('.task-container, .column-empty-area').forEach(container => {
        container.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        container.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });
        
        container.addEventListener('drop', drop);
    
  });
  
  document.querySelectorAll('.task').forEach(task => {
      task.addEventListener('dragstart', drag);
  });

  document.addEventListener('dragend', function() {
    // Nettoyer les styles quand le drag se termine (même sans drop)
    document.querySelectorAll('.task-container, .column-empty-area').forEach(el => {
        el.classList.remove('drag-over');
    });
    
    if (state.draggedTask) {
        state.draggedTask.classList.remove("dragging");
        state.draggedTask = null;
    }
});
  }
  
  // Search Functions
  function handleSearchSubmit(e) {
    e.preventDefault();
    searchTasks();
  }
  
  function handleSearchInput() {
    clearTimeout(state.searchTimeout);
    state.searchTimeout = setTimeout(searchTasks, 300);
  }
  
  function searchTasks() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const searchQuery = searchInput.value.trim().toLowerCase();
    const tasks = document.querySelectorAll('.task');
    let visibleTasksCount = 0;
    
    tasks.forEach(task => {
      const taskData = getTaskData(task);
      const isVisible = Object.values(taskData).some(value => 
        value.toLowerCase().includes(searchQuery)
      );
      
      task.style.display = isVisible ? 'block' : 'none';
      if (isVisible) visibleTasksCount++;
    });
    
    handleNoResultsMessage(visibleTasksCount, searchQuery);
  }


  // Déplacez cette fonction au niveau racine (au même niveau que les autres fonctions)
function closePopup() {
    const popupOverlay = document.getElementById('popupOverlay');
    if (popupOverlay) {
        popupOverlay.classList.remove('active');
    }
    document.body.classList.remove('popup-open');
    window.taskBeingEdited = null;
    state.temporaryComments = [];
}
  
  function getTaskData(task) {
    return {
      title: task.querySelector('h5')?.textContent || '',
      description: task.querySelector('.my-2 p')?.textContent || '',
      deadline: task.querySelector('.TaskHead p')?.textContent || ''
    };
  }
  
  function handleNoResultsMessage(visibleCount, query) {
    const noResultsMsg = document.getElementById('noResultsMessage') || createNoResultsMessage();
    noResultsMsg.style.display = (visibleCount === 0 && query !== '') ? 'block' : 'none';
  }
  
  function createNoResultsMessage() {
    const msg = document.createElement('div');
    msg.id = 'noResultsMessage';
    msg.className = 'alert alert-info mt-3';
    msg.textContent = 'Aucune tâche ne correspond à votre recherche.';
    document.querySelector('.Overview').appendChild(msg);
    return msg;
  }
  
  // Drag and Drop Functions
  function drop(e) {
    e.preventDefault();
    
    // Retirer la classe drag-over de tous les conteneurs
    document.querySelectorAll('.task-container, .column-empty-area').forEach(el => {
        el.classList.remove('drag-over');
    });

    const dropTarget = e.target.closest(".task-container") || e.target.closest('.column-empty-area');
    
    if (dropTarget && state.draggedTask) {
        // Empêcher le drop sur le bouton "Ajouter une tâche"
        if (e.target.closest('.add-task-btn')) return;
        
        // Vérifier si la tâche est déplacée vers une nouvelle colonne
        const newStatus = getColumnStatus(dropTarget);
        
        // Mettre à jour le statut visuel
        updateTaskStatus(state.draggedTask, newStatus);
        
        // Déplacer la tâche dans le DOM
        dropTarget.appendChild(state.draggedTask);
        
        // Mettre à jour le statut en base de données
        updateTaskStatusInDB(state.draggedTask.id.replace('task-', ''), newStatus);
        
        state.draggedTask.classList.remove("dragging");
        state.draggedTask = null;
    }
}
function updateTaskStatus(taskElement, newStatus) {
  // Mettre à jour l'apparence selon le statut
  const statusElement = taskElement.querySelector('.task-status');
  if (statusElement) {
      statusElement.textContent = newStatus;
      
      // Changer la couleur selon le statut
      switch(newStatus) {
          case TASK_STATUS.BACKLOG:
              statusElement.style.backgroundColor = '#e3f2fd';
              statusElement.style.color = '#2196F3';
              break;
          case TASK_STATUS.IN_PROGRESS:
              statusElement.style.backgroundColor = '#fff8e1';
              statusElement.style.color = '#FFC107';
              break;
          case TASK_STATUS.COMPLETED:
              statusElement.style.backgroundColor = '#e8f5e9';
              statusElement.style.color = '#4CAF50';
              break;
      }
  }
}
async function updateTaskStatusInDB(taskId, newStatus) {
  try {
      const response = await fetch(`api/tasks.php?id=${taskId}`, {
          method: 'PUT',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({
              status: newStatus
          })
      });

      if (!response.ok) {
          throw new Error('Échec de la mise à jour du statut');
      }
  } catch (error) {
      console.error("Erreur mise à jour statut:", error);
      // Optionnel: annuler le changement visuel si l'API échoue
  }
}
  function drag(e) {
    state.draggedTask = e.target.closest('.task');
    if (state.draggedTask) {
      state.draggedTask.classList.add("dragging");
    }
  }
  
  
  // Task Management
  function handleAddTaskClick() {
    state.currentTaskColumn = this.closest('.task-container');
    if (!state.currentTaskColumn) {
        console.error('Colonne introuvable');
        return;
    }
    showTaskPopup('Ajouter une tâche');
}
  
  function handleTaskClick(e) {
    const taskElement = e.target.closest('.task');
    const actionElement = e.target.closest('.move-up, .move-down, .fa-user-plus, .delete-task');
    
    if (taskElement && !actionElement) {
      showTaskPopup('Modifier la tâche', taskElement);
    }
  }
  
  function showTaskPopup(title, taskElement = null) {
    const popupOverlay = document.getElementById('popupOverlay');
    popupOverlay.innerHTML = createPopupHTML(title, taskElement);
    popupOverlay.classList.add('active');
    document.body.classList.add('popup-open');
    
    // Focus sur le premier champ input quand le popup s'ouvre
    setTimeout(() => {
        const firstInput = popupOverlay.querySelector('input');
        if (firstInput) firstInput.focus();
    }, 100);
    
    window.taskBeingEdited = taskElement;
    setupPopupEventListeners(taskElement ? 'edit' : 'add');
}

  
  function createPopupHTML(title, taskElement) {
    const taskData = taskElement ? {
      title: taskElement.querySelector('h5').textContent,
      description: taskElement.querySelector('.my-2 p').textContent,
      deadlineDate: taskElement.dataset.deadlineDate || '',
      deadlineTime: taskElement.dataset.deadlineTime || '23:59',
      comments: JSON.parse(taskElement.dataset.comments || '[]')
    } : null;
  
    return `
      <div class="task-form-container">
        <div class="form-header">
          <div class="project-info">Projet: AProjectO</div>
          <div class="close-button">&times;</div>
        </div>
        <div class="scrollable-content"> <!-- Nouvelle div wrapper -->
        <div class="task-header">
          <input type="text" placeholder="Titre de la tâche" value="${taskData?.title || ''}">
        </div>
        <div class="deadline-container">
          <span class="deadline-label">Date limite:</span>
          <input type="date" id="task-deadline" class="deadline-date" value="${taskData?.deadlineDate || ''}">
          <input type="time" id="task-deadline-time" class="deadline-time" value="${taskData?.deadlineTime || '23:59'}">
        </div>
        <div class="form-fields">
          <div class="form-field">
            <div class="description-header">
              <span class="field-icon">📝</span>
              <span class="field-label">Description:</span><br><br>
            </div>
            <div><textarea id="description" class="full-width-description mt-5">${taskData?.description || ''}</textarea></div>
          </div>
        </div>
        <div class="comment-section">
          <div id="commentDisplay">
            ${taskData?.comments.map(c => `<div class="comment-item">${c}</div>`).join('') || ''}
          </div>
          <div class="comment-box">
            <div class="comment-input-container">
              <div class="comment-toolbar">
                <button type="button" class="toolbar-button">📋</button>
                <button type="button" class="toolbar-button">#</button>
              </div>
              <div id="comment" contenteditable="true" placeholder="Ajouter un commentaire..."></div>
              <button type="button" class="btn btn-send-comment">Envoyer</button>
            </div>
          </div>
        </div>
        </div>
        <div class="action-buttons">
          <button type="button" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary">Annuler</button>
        </div>
      </div>
    `;
}
  function setupPopupEventListeners(mode) {
    const popupOverlay = document.getElementById('popupOverlay');
    const closeButton = popupOverlay.querySelector('.close-button');
    const cancelButton = popupOverlay.querySelector('.btn-secondary');
    const saveButton = popupOverlay.querySelector('.btn-primary');
  
   
  
    closeButton.addEventListener('click', closePopup);
    cancelButton.addEventListener('click', closePopup);
    
    popupOverlay.addEventListener('click', e => {
      if (e.target === popupOverlay) closePopup();
    });
  
    saveButton.addEventListener('click', () => {
      if (mode === 'add') {
        saveTask();
      } else {
        updateTask();
      }
    });
  
    setupCommentToolbar();

    const sendCommentBtn = popupOverlay.querySelector('.btn-send-comment');
    if (sendCommentBtn) {
        sendCommentBtn.addEventListener('click', () => {
            const commentInput = popupOverlay.querySelector('#comment');
            const commentText = commentInput.innerText.trim();
            
            if (commentText) {
                // Si on est en mode édition, ajouter le commentaire à la tâche existante
                if (mode === 'edit' && window.taskBeingEdited) {
                    addCommentToTask(window.taskBeingEdited, commentText);
                } else {
                    // Sinon, stocker le commentaire temporairement
                    state.temporaryComments.push(commentText);
                }
                
                // Ajouter le commentaire à l'affichage
                const commentDisplay = popupOverlay.querySelector('#commentDisplay');
                if (commentDisplay) {
                    const commentItem = document.createElement('div');
                    commentItem.className = 'comment-item';
                    commentItem.textContent = commentText;
                    commentDisplay.appendChild(commentItem);
                }
                
                // Vider le champ de commentaire
                commentInput.innerText = '';
            }
        })
    }
  }
  



// Fonction pour sauvegarder une tâche en base de données
async function saveTaskToDB(taskData) {
  console.log("Envoi à l'API:", taskData); // Debug
  
  try {
      const response = await fetch('api/tasks.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({
              title: taskData.title,
              description: taskData.description,
              status: taskData.status,
              deadline_date: taskData.deadlineDate,
              deadline_time: taskData.deadlineTime || '23:59'
          })
      });

      console.log("Réponse API:", response); // Debug

      if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`Erreur HTTP: ${response.status} - ${errorText}`);
      }

      return await response.json();
  } catch (error) {
      console.error("Échec sauvegarde:", error);
      throw error;
  }
}

// Fonction pour charger les tâches depuis la base
async function loadTasksFromDB() {
  try {
      const response = await fetch('api/tasks.php');
      if (!response.ok) throw new Error('Erreur de chargement');
      return await response.json();
  } catch (error) {
      console.error('Erreur chargement tâches:', error);
      return [];
  }
}
















  function saveTask() {
    const formData = getFormData();
    
    if (!formData.title) {
      alert('Veuillez entrer un titre pour la tâche.');
      return;
    }
  
    createNewTask(formData);
    closePopup();
  }
  
  function updateTask() {
    const taskElement = window.taskBeingEdited;
    if (!taskElement) return;
  
    const formData = getFormData();
    
    if (!formData.title) {
      alert('Veuillez entrer un titre pour la tâche.');
      return;
    }
  
    updateTaskElement(taskElement, formData);
    closePopup();
  }
  
  function getFormData() {
    const popupOverlay = document.getElementById('popupOverlay');
    
    return {
      title: popupOverlay.querySelector('.task-header input').value.trim(),
      description: popupOverlay.querySelector('#description').value.trim(),
      comment: popupOverlay.querySelector('#comment').innerText.trim(),
      deadlineDate: popupOverlay.querySelector('#task-deadline').value,
      deadlineTime: popupOverlay.querySelector('#task-deadline-time').value
    };
  }
  
  async function createNewTask({ title, description, comment, deadlineDate, deadlineTime }) {
    if (!state.currentTaskColumn) return;
  
    try {
        // Sauvegarde en base de données
        const dbTask = await saveTaskToDB({
            title,
            description,
            deadlineDate,
            deadlineTime
        });

        // Création de l'élément DOM avec l'ID de la base
        state.taskCounter++;
        const timeLeftText = calculateTimeLeft(deadlineDate, deadlineTime);
        const commentCount = state.temporaryComments.length + (comment ? 1 : 0);

        const newTask = document.createElement('div');
        newTask.className = 'task mt-4 p-3 rounded-4';
        newTask.draggable = true;
        newTask.id = `task-${dbTask.id}`;
        newTask.dataset.taskId = dbTask.id;
        newTask.setAttribute('ondragstart', 'drag(event)');

        newTask.innerHTML = `
            <div class="TaskHead d-flex justify-content-between">
                <h5 class="mb-0">${title}</h5>
                <p class="mb-0"><i class="fa-regular fa-clock me-1"></i>${timeLeftText}</p>
            </div>
            <div class="my-2">
                <p class="mb-1">${description}</p>
            </div>
            <div class="d-flex gap-4 align-items-center">
                <p class="mb-0"><i class="fa-solid fa-paperclip me-1"></i> 0</p>
                <p class="mb-0"><i class="fa-regular fa-comment-dots me-1"></i> ${commentCount}</p>
                <i class="fa-solid fa-user-plus ms-auto"></i>
                <div class="d-flex flex-column gap-1">
                    <i class="fa-solid fa-arrow-up move-up"></i>
                    <i class="fa-solid fa-arrow-down move-down"></i>
                </div>
                <i class="fa-solid fa-trash delete-task"></i>
            </div>
            <div class="comment-list mt-3"></div>
        `;

        if (deadlineDate) {
            newTask.dataset.deadlineDate = deadlineDate;
            newTask.dataset.deadlineTime = deadlineTime || '23:59';
            newTask.dataset.comments = '[]';
        }

        addTaskEventListeners(newTask);
        state.currentTaskColumn.appendChild(newTask);

        // Ajouter les commentaires
        state.temporaryComments.forEach(c => addCommentToTask(newTask, c, false));
        if (comment) addCommentToTask(newTask, comment, true);

        state.temporaryComments = [];
        
    } catch (error) {
        console.error("Erreur création tâche:", error);
        alert("Erreur lors de la création: " + error.message);
    }
}
  
  function addTaskEventListeners(taskElement) {
    taskElement.querySelector('.move-up').addEventListener('click', () => moveTaskUp(taskElement));
    taskElement.querySelector('.move-down').addEventListener('click', () => moveTaskDown(taskElement));
    taskElement.querySelector('.delete-task').addEventListener('click', (e) => deleteTask(taskElement.id, e));
  }
  
  function updateTaskElement(taskElement, { title, description, comment, deadlineDate, deadlineTime }) {
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
  
  function addCommentToTask(taskElement, commentText, shouldIncrementCount = true) {
    const comments = JSON.parse(taskElement.dataset.comments || '[]');
    comments.push(commentText);
    taskElement.dataset.comments = JSON.stringify(comments);
  
    if (shouldIncrementCount) {
      const commentCountElement = taskElement.querySelector('.fa-comment-dots').nextSibling;
      const currentCount = parseInt(commentCountElement.textContent.trim()) || 0;
      commentCountElement.textContent = ` ${currentCount + 1}`;
    }
  
    // Add comment to UI
    // const commentList = taskElement.querySelector('.comment-list');
    // if (commentList) {
    //   const commentItem = document.createElement('div');
    //   commentItem.className = 'comment-item';
    //   commentItem.textContent = commentText;
    //   commentList.appendChild(commentItem);
    // }
  }
  
  // Utility Functions
  function calculateTimeLeft(deadlineDate, deadlineTime) {
    if (!deadlineDate) return "0 Minutes";
  
    const deadline = new Date(`${deadlineDate}T${deadlineTime || '23:59'}`);
    const now = new Date();
    const diff = deadline - now;
  
    if (diff < 0) return "En retard";
  
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  
    if (days > 0) return `${days}j ${hours}h`;
    if (hours > 0) return `${hours}h ${minutes}m`;
    return `${minutes} Minutes`;
  }
  
  function updateAllTaskTimers() {
    document.querySelectorAll('.task[data-deadline-date]').forEach(task => {
      const deadlineDate = task.dataset.deadlineDate;
      const deadlineTime = task.dataset.deadlineTime;
      const timeLeftText = calculateTimeLeft(deadlineDate, deadlineTime);
      
      const timerElement = task.querySelector('.TaskHead p');
      if (timerElement) {
        timerElement.innerHTML = `<i class="fa-regular fa-clock me-1"></i>${timeLeftText}`;
      }
    });
  }
  
  // Task Order Management
  function moveTaskUp(taskElement) {
    const previousTask = taskElement.previousElementSibling;
  
    if (previousTask && !previousTask.classList.contains('add-task-btn')) {
      taskElement.parentNode.insertBefore(taskElement, previousTask);
    }
  }
  
  function moveTaskDown(taskElement) {
    const nextTask = taskElement.nextElementSibling;
  
    if (nextTask) {
      taskElement.parentNode.insertBefore(nextTask, taskElement);
    }
  }
  
  function deleteTask(taskId, event) {
    if (event) event.stopPropagation();
    
    if (confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?")) {
      const task = document.getElementById(taskId);
      if (task) task.remove();
    }
  }
  
  // Comment Management
  function setupCommentToolbar() {
    const commentInput = document.getElementById('comment');
    if (!commentInput) return;
  
    document.querySelectorAll('.toolbar-button').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        formatCommentText(this.textContent.trim());
      });
    });
  }
  
  function formatCommentText(format) {
    const commentInput = document.getElementById('comment');
    if (!commentInput) return;
  
    const selection = window.getSelection();
    if (!selection.rangeCount) return;
  
    const range = selection.getRangeAt(0);
    const selectedText = range.toString();
    
    if (format === '#') {
      const formattedText = selectedText ? `#${selectedText}` : '#';
      range.deleteContents();
      range.insertNode(document.createTextNode(formattedText));
    } else {
      const wrapper = document.createElement('span');
      wrapper.className = `formatted-${format.toLowerCase()}`;
      wrapper.textContent = selectedText || 'Texte';
      
      range.deleteContents();
      range.insertNode(wrapper);
    }
    
    // Reset cursor position
    const newRange = document.createRange();
    newRange.selectNodeContents(commentInput);
    newRange.collapse(false);
    selection.removeAllRanges();
    selection.addRange(newRange);
  }

  
  document.addEventListener('DOMContentLoaded', async () => {
    await loadTasksFromDB();
    init();
});




function setupDragDropListeners() {
  const containers = document.querySelectorAll('.task-container');
  
  containers.forEach(container => {
      container.addEventListener('dragover', allowDrop);
      container.addEventListener('dragleave', () => {
          container.classList.remove('drag-over');
      });
      container.addEventListener('drop', handleDrop);
  });
}



function getColumnStatus(columnElement) {
  if (!columnElement) return TASK_STATUS.BACKLOG;
  
  if (columnElement.id.includes('backlog')) return TASK_STATUS.BACKLOG;
  if (columnElement.id.includes('progress')) return TASK_STATUS.IN_PROGRESS;
  if (columnElement.id.includes('completed')) return TASK_STATUS.COMPLETED;
  
  return TASK_STATUS.BACKLOG;
}
  // Initialize the application
  document.addEventListener('DOMContentLoaded', init);