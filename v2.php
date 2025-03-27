
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <title>Document</title>
</head>
<style>
    .task-form-container {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 0 auto;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}

.form-header h4 {
    margin: 0;
    color: #333;
}

.close-button {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: #4a90e2;
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #4a90e2;
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: #3a7bc8;
}

.btn-secondary {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background-color: #e9e9e9;
}
</style>
<body>
    <div class="task-form-container">
        <div class="form-header">
            <h4>Créer une nouvelle tâche</h4>
            <button class="close-button">&times;</button>
        </div>
        
        <form id="taskForm">
            <div class="form-group">
                <label for="taskTitle">Titre de la tâche *</label>
                <input type="text" id="taskTitle" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="taskDescription">Description</label>
                <textarea id="taskDescription" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="taskDeadline">Date limite</label>
                        <input type="date" id="taskDeadline" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="taskStatus">Statut</label>
                        <select id="taskStatus" class="form-control">
                            <option value="backlog">Backlog</option>
                            <option value="in_progress">En cours</option>
                            <option value="completed">Terminé</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="taskAssignee">Assigner à</label>
                <select id="taskAssignee" class="form-control">
                    <option value="">-- Sélectionner un utilisateur --</option>
                    <!-- Options chargées dynamiquement -->
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Créer la tâche</button>
                <button type="button" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>


    <script src="Boostarp/js/bootstrap.bundle.min.js"></script>
    <script>
        // Charger les utilisateurs depuis la table users
async function loadUsers() {
    try {
        const response = await fetch('/api/users');
        const users = await response.json();
        
        const assigneeSelect = document.getElementById('taskAssignee');
        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = `${user.prenom} ${user.nom} (${user.poste})`;
            assigneeSelect.appendChild(option);
        });
    } catch (error) {
        console.error("Erreur chargement utilisateurs:", error);
    }
}

// Gestion de la soumission du formulaire
document.getElementById('taskForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const taskData = {
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDescription').value,
        deadline: document.getElementById('taskDeadline').value,
        assignee_id: document.getElementById('taskAssignee').value,
        status: document.getElementById('taskStatus').value
    };
    
    try {
        const response = await fetch('/api/tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(taskData)
        });
        
        if (response.ok) {
            alert('Tâche créée avec succès!');
            // Recharger la liste des tâches ou fermer le popup
            window.location.reload(); // Optionnel
        } else {
            throw new Error('Erreur lors de la création');
        }
    } catch (error) {
        console.error("Erreur création tâche:", error);
        alert("Erreur lors de la création de la tâche");
    }
});

// Charger les utilisateurs au chargement de la page
document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
</body>

</html>