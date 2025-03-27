<?php
// Désactivation des avertissements pour une meilleure expérience utilisateur
error_reporting(E_ERROR | E_PARSE);

// Paramètres de session sécurisés (définis avant session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS

// Démarrage de la session
session_start();

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Inclusion des fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/group_functions.php';

// Récupération des informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Traitement du formulaire de mise à jour du profil
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $poste = trim($_POST['poste']);
    
    // Validation des données
    if (empty($nom) || empty($prenom) || empty($email)) {
        $error_message = "Les champs Nom, Prénom et Email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "L'adresse email n'est pas valide.";
    } else {
        // Vérification si l'email existe déjà pour un autre utilisateur
        if ($email !== $user['email'] && emailExists($email)) {
            $error_message = "Cette adresse email est déjà utilisée par un autre compte.";
        } else {
            // Traitement de l'upload de l'image de profil
            $profile_image = $user['profile_image']; // Valeur par défaut
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                    $error_message = "Le format de l'image n'est pas pris en charge. Utilisez JPG, PNG ou GIF.";
                } elseif ($_FILES['profile_image']['size'] > $max_size) {
                    $error_message = "L'image est trop volumineuse. Taille maximale: 2MB.";
                } else {
                    $upload_dir = 'img/profiles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                        $profile_image = $upload_path;
                    } else {
                        $error_message = "Erreur lors de l'upload de l'image.";
                    }
                }
            }
            
            // Mise à jour du mot de passe si nécessaire
            $password_updated = false;
            if (!empty($_POST['new_password'])) {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                if (empty($current_password)) {
                    $error_message = "Veuillez saisir votre mot de passe actuel.";
                } elseif (!password_verify($current_password, $user['password'])) {
                    $error_message = "Le mot de passe actuel est incorrect.";
                } elseif (strlen($new_password) < 8) {
                    $error_message = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                } elseif ($new_password !== $confirm_password) {
                    $error_message = "Les nouveaux mots de passe ne correspondent pas.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $password_updated = true;
                }
            }
            
            // Si aucune erreur, mise à jour du profil
            if (empty($error_message)) {
                if (updateUserProfile($user_id, $nom, $prenom, $email, $telephone, $poste, $profile_image, $password_updated ? $hashed_password : null)) {
                    $success_message = "Votre profil a été mis à jour avec succès.";
                    // Mettre à jour les données de l'utilisateur après modification
                    $user = getUserById($user_id);
                } else {
                    $error_message = "Une erreur est survenue lors de la mise à jour du profil.";
                }
            }
        }
    }
}

// Récupération des groupes de l'utilisateur
$user_groups = getUserGroups($user_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilisateur - Gestion de Projets</title>
    <link rel="stylesheet" href="Boostarp/css/bootstrap.min.css" />
    <link rel="stylesheet" href="Boostarp/css/all.min.css" />
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<div id="er">
<div id="er00">
        <div id="er1">
        <a class="navbar-brand fs-3" href="#">AProjectO</a>
            <div id="em2">
                
                <h6 id="iduser_name"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h6>
                <a href="profile.php" id="profile-link"><img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'img/default-avatar.jpg'; ?>" alt="image de profil" class="img_prof"></a>
            </div>
        </div>
    </div>
    
    <div class="sidebar px-3">
            <ul class="ulSidebar ">
            <div class="d-flex">
    <i class="fa-solid fa-gauge"></i>
    <a href="/Gestion_Projet/Dashboard/dashboard.php" 
       class="Dashboard fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Dashboard
    </a>
</div>
<div class="d-flex">
    <i class="fa-solid fa-folder"></i>
    <a href="/Gestion_Projet/Projets/Projects/index.php" 
       class="Projects fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Projects
    </a>
</div>
<div class="d-flex" id="tasksBtn">
    <i class="fa-solid fa-square-check"></i>
    <a href="/Gestion_Projet/Tasks/index.php" 
       class="Tasks fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Tasks
    </a>
</div>
<div class="d-flex">
    <i class="fa-solid fa-comment"></i>
    <a href="/Gestion_Projet/Dashboard/group_chat.php" 
       class="Tasks fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Discussion
    </a>
</div>
<div class="d-flex">
    <i class="fa-solid fa-users-line"></i>
    <a href="/Gestion_Projet/Dashboard/groups.php" 
       class="Tasks fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Groupes
    </a>
</div>

                
<div class="d-flex">
    <i class="fa-solid fa-gears"></i>
    <a href="/Gestion_Projet/Dashboard/profile.php" 
       class="Settings fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Settings
    </a>
</div>
<div class="d-flex">
<i class="fa-solid fa-right-from-bracket"></i>
    <a href="/Gestion_Projet/Dashboard/php/logout.php" 
       class="Settings fs-5 fw-bold"
       style="text-decoration: none; color: inherit;">
       Déconnexion
    </a>
</div>
            </ul>
        </div>
    <div class="content">
        <h3 class="text-dark dashboard-title">Profil Utilisateur</h3>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card profile-card">
                    <div class="profile-header">
                        <img src="<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'img/default-avatar.jpg'; ?>" alt="Photo de profil" class="profile-image">
                        <h4><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['poste']); ?></p>
                        <div class="user-id-badge">
                            ID: <span class="user-id"><?php echo $user['id']; ?></span>
                            <button class="btn btn-sm btn-outline-secondary copy-id-btn" data-id="<?php echo $user['id']; ?>" title="Copier l'ID">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="profile-info">
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Téléphone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['telephone']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Membre depuis:</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($user['date_creation'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- Groupes de l'utilisateur -->
                    <div class="profile-groups">
                        <h5>Mes Groupes</h5>
                        <?php if (!empty($user_groups)): ?>
                            <ul class="groups-list">
                                <?php foreach ($user_groups as $group): ?>
                                    <li>
                                        <span class="group-name"><?php echo htmlspecialchars($group['nom']); ?></span>
                                        <span class="group-role <?php echo $group['role']; ?>"><?php echo $group['role'] === 'admin' ? 'Admin' : 'Membre'; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="groups.php" class="btn btn-sm btn-outline-primary mt-2">Gérer mes groupes</a>
                        <?php else: ?>
                            <p class="text-muted">Vous n'êtes membre d'aucun groupe.</p>
                            <a href="groups.php" class="btn btn-sm btn-outline-primary">Créer ou rejoindre un groupe</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <h5>Modifier votre profil</h5>
                    <form action="profile.php" method="post" enctype="multipart/form-data">
                        <div class="form-tabs">
                            <button type="button" class="tab-btn active" data-tab="personal-info">Informations personnelles</button>
                            <button type="button" class="tab-btn" data-tab="password">Mot de passe</button>
                        </div>
                        
                        <div class="tab-content active" id="personal-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nom">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prenom">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="telephone">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="poste">Poste / Fonction</label>
                                <input type="text" class="form-control" id="poste" name="poste" value="<?php echo htmlspecialchars($user['poste']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="profile_image">Photo de profil</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="profile_image" name="profile_image" accept="image/jpeg, image/png, image/gif">
                                    <label class="custom-file-label" for="profile_image">Choisir une image</label>
                                </div>
                                <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB</small>
                            </div>
                        </div>
                        
                        <div class="tab-content" id="password">
                            <div class="form-group">
                                <label for="current_password">Mot de passe actuel</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="form-group">
                                <label for="new_password">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <small class="form-text text-muted">8 caractères minimum</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets du formulaire
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Désactiver tous les onglets
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Activer l'onglet sélectionné
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Afficher le nom du fichier sélectionné pour l'upload
    const fileInput = document.getElementById('profile_image');
    const fileLabel = document.querySelector('.custom-file-label');
    
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileLabel.textContent = this.files[0].name;
            } else {
                fileLabel.textContent = 'Choisir une image';
            }
        });
    }
    
    // Copier l'ID utilisateur dans le presse-papiers
    const copyIdBtn = document.querySelector('.copy-id-btn');
    
    if (copyIdBtn) {
        copyIdBtn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            
            // Créer un élément temporaire pour copier le texte
            const tempInput = document.createElement('input');
            tempInput.value = userId;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // Changer temporairement le texte du bouton pour indiquer que l'ID a été copié
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            this.classList.add('btn-success');
            this.classList.remove('btn-outline-secondary');
            
            setTimeout(() => {
                this.innerHTML = originalHTML;
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-secondary');
            }, 2000);
        });
    }
});
</script>
</body>
</html>
