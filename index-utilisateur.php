<?php
session_start(); // Démarre la session pour gérer la connexion de l'utilisateur

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
    // Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
    header("Location: ../login.php");
    exit();
}

// Connexion à la base de données
$host = 'db_lamp';  // Hôte de la base de données (à modifier si besoin)
$user = 'user';      // Nom d'utilisateur de la base de données
$password = 'user';  // Mot de passe de la base de données
$dbname = 'test';    // Nom de la base de données

try {
    // Création de l'objet PDO pour la connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Active le mode d'affichage des erreurs
} catch (PDOException $e) {
    // En cas d'erreur de connexion, on affiche un message d'erreur et on arrête l'exécution du script
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les photos de l'utilisateur connecté
$login = $_SESSION['utilisateur']['login'];  // Récupération du login de l'utilisateur connecté

try {
    // Préparation de la requête SQL pour récupérer les photos de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM photos WHERE login = :login ORDER BY date_added DESC");
    $stmt->execute(['login' => $login]); // Exécution de la requête avec le login comme paramètre
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupération des résultats sous forme de tableau associatif
} catch (PDOException $e) {
    // En cas d'erreur lors de l'exécution de la requête
    die("Erreur lors de la récupération des photos : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Galerie</title>
    <link rel="stylesheet" href="../style/index.css"> <!-- Lien vers la feuille de style CSS -->
</head>
<body>
    <header>
        <h1>Ma Galerie</h1>
        <nav>
            <!-- Liens de navigation -->
            <a href="../logout.php" class="btn">Déconnexion</a>
            <a href="upload-utilisateur.php" class="manage-btn">Ajouter une photo</a>
            <a href="delete-utilisateur.php" class="manage-btn">Supprimer une photo</a>
        </nav>
    </header>
    
    <main>
        <section class="gallery">
            <button id="edit-toggle-btn" class="manage-btn">Modifier les photos</button>
            <?php if (empty($photos)): ?>
                <p>Aucune photo pour le moment.</p>
            <?php else: ?>
                <div class="media-container">
                    <?php foreach ($photos as $photo): ?>
                        <div class="media-item">
                            <!-- Affichage de l'image -->
                            <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Photo ajoutée" class="media-img">
                            <!-- Affichage de la description -->
                            <p class="media-description"><?php echo htmlspecialchars($photo['description']); ?></p>
                            
                            <!-- Bouton Modifier (initialement caché) -->
                            <a href="upload-utilisateur.php?id=<?php echo $photo['id']; ?>" class="edit-icon" style="display: none;">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Ajout de Font Awesome pour les icônes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <script>
        // Sélection du bouton de modification et des icônes Modifier
        const editToggleBtn = document.getElementById('edit-toggle-btn');
        const editIcons = document.querySelectorAll('.edit-icon');
        
        // Ajout d'un événement au clic sur le bouton Modifier
        editToggleBtn.addEventListener('click', () => {
            editIcons.forEach(icon => {
                // Alterne entre affichage et masquage des icônes de modification
                icon.style.display = icon.style.display === 'none' ? 'block' : 'none';
            });
        });
    </script>

    <style>
        /* Conteneur principal des images */
        .media-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px; /* Espacement entre les images */
        }

        /* Style des éléments contenant une image */
        .media-item {
            position: relative;
            text-align: center;
        }

        /* Style des images */
        .media-img {
            width: 200px;
            height: 200px;
            object-fit: cover; /* Assure que l'image garde ses proportions */
        }

        /* Style de la description */
        .media-description {
            font-size: 14px;
            margin-top: 5px;
        }

        /* Icône de modification */
        .edit-icon {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 5px;
            border-radius: 5px;
            text-decoration: none;
        }

        /* Changement de couleur au survol de l'icône Modifier */
        .edit-icon:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        /* Style du bouton pour afficher/masquer les modifications */
        #edit-toggle-btn {
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
        }

        /* Effet au survol du bouton Modifier */
        #edit-toggle-btn:hover {
            background-color: #555;
        }
    </style>
</body>
</html>
