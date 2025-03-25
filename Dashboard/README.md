# Gestion de Projets - Application avec Système de Groupes

Cette application de gestion de projets permet de gérer des projets, des tâches, et inclut un système de groupes d'utilisateurs avec une salle de discussion.

## Nouvelles fonctionnalités

### Système de Groupes
- Création et gestion de groupes d'utilisateurs
- Chaque utilisateur dispose d'un espace personnel par défaut
- Invitation de membres via leur ID utilisateur
- Affichage des statistiques au niveau du groupe

### Salle de Discussion
- Chat en temps réel entre les membres d'un groupe
- Fonctionnalité de mention d'utilisateurs (@utilisateur)
- Historique des conversations

### Dashboard Amélioré
- Sélection de groupe pour afficher les statistiques correspondantes
- Affichage des projets et tâches du groupe sélectionné
- Espace pour gérer les invitations en attente

### Profil Utilisateur
- Affichage de l'ID utilisateur pour faciliter les invitations
- Liste des groupes dont l'utilisateur est membre
- Fonctionnalité de copie de l'ID dans le presse-papiers

## Installation

1. Décompressez l'archive dans le dossier `htdocs` de votre serveur XAMPP
2. Configurez les paramètres de connexion à la base de données dans `includes/config.php`
3. Accédez à `http://localhost/projet_gestion/install.php` pour créer la base de données et les tables principales
4. Accédez à `http://localhost/projet_gestion/install_groups.php` pour ajouter les tables nécessaires au système de groupes
5. Connectez-vous avec les identifiants de test :
   - Email : jean.dupont@example.com
   - Mot de passe : password123

## Structure des fichiers

- `dashboard.php` : Page d'accueil avec statistiques et sélection de groupe
- `groups.php` : Gestion des groupes (création, invitation, etc.)
- `group_chat.php` : Salle de discussion de groupe
- `profile.php` : Profil utilisateur avec ID et liste des groupes
- `includes/group_functions.php` : Fonctions pour la gestion des groupes
- `install_groups.sql` : Script SQL pour créer les tables de groupes

## Utilisation

### Créer un groupe
1. Accédez à la page "Groupes"
2. Cliquez sur le bouton "Créer un nouveau groupe"
3. Remplissez le formulaire avec le nom et la description du groupe

### Inviter un membre
1. Accédez à la page "Groupes"
2. Cliquez sur la flèche à côté d'un groupe dont vous êtes administrateur
3. Entrez l'ID de l'utilisateur à inviter dans le champ prévu à cet effet
4. Cliquez sur "Inviter"

### Rejoindre un groupe
1. Les invitations apparaissent sur le dashboard et la page "Groupes"
2. Cliquez sur "Accepter" pour rejoindre le groupe ou "Refuser" pour décliner l'invitation

### Utiliser la discussion de groupe
1. Accédez à la page "Discussion"
2. Sélectionnez un groupe dans le menu déroulant
3. Écrivez votre message et cliquez sur "Envoyer"
4. Pour mentionner un utilisateur, tapez @ suivi du nom de l'utilisateur

### Voir les statistiques d'un groupe
1. Accédez au dashboard
2. Sélectionnez un groupe dans le menu déroulant
3. Les statistiques, projets et tâches du groupe s'afficheront

## Notes techniques

- L'application utilise PHP avec MySQL (PDO) pour le backend
- Les sessions sont sécurisées avec des paramètres HTTP-only
- Les mots de passe sont hashés avec la fonction password_hash() de PHP
- L'interface est responsive et s'adapte aux appareils mobiles
