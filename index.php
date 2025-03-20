<?php
// Démarrage de la session pour pouvoir utiliser les variables de session
session_start();

// Activer l'affichage des erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données avec gestion des erreurs
$host = 'db_lamp';  // Hôte de la base de données
$user = 'user';  // Utilisateur de la base de données
$password = 'user';  // Mot de passe pour la base de données
$dbname = 'test';  // Nom de la base de données

// Tentative de connexion à la base de données
try {
    // Création d'une instance PDO pour la connexion
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    // Définir l'option pour gérer les erreurs en mode exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si une erreur survient, afficher le message d'erreur et arrêter le script
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les photos publiques depuis la base de données
try {
    // Requête SQL pour récupérer toutes les photos publiques triées par date d'ajout (récemment ajoutées en premier)
    $stmt = $pdo->query("SELECT * FROM photos WHERE public = 1 ORDER BY date_added DESC");
    // Récupérer les résultats sous forme de tableau associatif
    $public_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si une erreur survient lors de la récupération des photos, afficher l'erreur
    die("Erreur lors de la récupération des photos publiques : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <!-- Lien vers la feuille de style pour le design de la page -->
    <link rel="stylesheet" href="../style/index.css">
</head>
<body>
    <!-- En-tête avec le titre du site et les liens vers la connexion et l'inscription -->
    <header>
        <h1>Voyages & Découvertes</h1>
        <nav>
            <!-- Liens vers les pages de connexion et d'inscription -->
            <a href="login.php" class="btn">Connexion</a>
            <a href="register.php" class="manage-btn">Inscription</a>
        </nav>
    </header>
    
    <main>
        <!-- Section galerie pour afficher les photos publiques -->
        <section class="gallery">
            <h2>Photos publiques</h2>
            <div class="media-container">
                <?php if (empty($public_photos)): ?>
                    <!-- Si aucune photo n'est trouvée, afficher un message -->
                    <p>Aucune photo publique pour le moment.</p>
                <?php else: ?>
                    <!-- Si des photos sont trouvées, les afficher dans la galerie -->
                    <?php foreach ($public_photos as $photo) { ?>
                        <div class="media-item">
                            <!-- Affichage de l'image avec son URL -->
                            <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Photo publique" class="media-img">
                            <!-- Affichage de la description de la photo -->
                            <p class="media-description"><?php echo htmlspecialchars($photo['description']); ?></p>
                        </div>
                    <?php } ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
