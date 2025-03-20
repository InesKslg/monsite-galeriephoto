<?php
// Démarre une session pour permettre la gestion de l'utilisateur connecté
session_start();

// Paramètres de connexion à la base de données
$host = 'db_lamp';  // Nom de l'hôte de la base de données
$user = 'user';     // Nom d'utilisateur pour se connecter à la base
$password = 'user'; // Mot de passe pour la connexion
$dbname = 'test';   // Nom de la base de données

try {
    // Création de la connexion PDO à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    // Définition du mode d'erreur pour gérer les exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la connexion échoue, affiche un message d'erreur et arrête l'exécution du script
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les photos publiques depuis la base de données
try {
    // Exécution de la requête SQL pour obtenir les photos dont le champ 'public' est égal à 1, triées par date d'ajout
    $stmt = $pdo->query("SELECT * FROM photos WHERE public = 1 ORDER BY date_added DESC");
    // Récupération des résultats sous forme de tableau associatif
    $public_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En cas d'erreur lors de la récupération des photos, un message d'erreur est affiché
    die("Erreur lors de la récupération des photos publiques : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <!-- Lien vers la feuille de style CSS pour la page -->
    <link rel="stylesheet" href="style/index.css">
</head>

<body>
    <!-- Entête de la page avec le titre et la navigation -->
    <header>
        <h1>Voyages & Découvertes</h1>
        <nav>
            <!-- Lien vers la page de connexion -->
            <a href="login.php" class="btn">Connexion</a>
            <!-- Lien vers la page d'inscription -->
            <a href="register.php" class="manage-btn">Inscription</a>
            <br><br>
            <!-- Message de bienvenue -->
            <p>A bientôt !</p>
        </nav>
    </header>

    <!-- Section principale de la page, affichage de la galerie de photos -->
    <main>
        <section class="gallery">
            <!-- Conteneur pour les éléments multimédia (photos) -->
            <div class="media-container">
                <!-- Vérification si aucune photo n'est présente -->
                <?php if (empty($public_photos)): ?>
                    <!-- Message affiché si aucune photo n'est disponible -->
                    <p>Aucune photo publique pour le moment.</p>
                <?php else: ?>
                    <!-- Si des photos sont présentes, affichage de chaque photo -->
                    <?php foreach ($public_photos as $photo): ?>
                        <div class="media-item">
                            <!-- Affichage de l'image avec la source et une description -->
                            <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Photo publique" class="media-img">
                            <p class="media-description"><?php echo htmlspecialchars($photo['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
