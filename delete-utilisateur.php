<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../login.php");
    exit();
}

// Connexion à la base de données
$host = 'db_lamp';  // Nom du serveur de base de données
$user = 'user';      // Nom d'utilisateur de la base de données
$password = 'user';  // Mot de passe de la base de données
$dbname = 'test';    // Nom de la base de données

try {
    // Création d'une instance PDO pour interagir avec la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Afficher un message d'erreur en cas d'échec de connexion
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer le login de l'utilisateur connecté
$login = $_SESSION['utilisateur']['login'];

try {
    // Sélectionner les photos associées à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM photos WHERE login = :login ORDER BY date_added DESC");
    $stmt->execute(['login' => $login]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer une éventuelle erreur lors de la récupération des photos
    die("Erreur lors de la récupération des photos : " . $e->getMessage());
}

// Vérifier si une demande de suppression est envoyée via l'URL
if (isset($_GET['delete'])) {
    $photo_id = $_GET['delete']; // Récupération de l'ID de la photo à supprimer

    try {
        // Vérifier si la photo appartient à l'utilisateur
        $stmt = $pdo->prepare("SELECT photo_url FROM photos WHERE id = :id AND login = :login");
        $stmt->execute(['id' => $photo_id, 'login' => $login]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($photo) {
            // Supprimer le fichier image du serveur si existant
            if (file_exists($photo['photo_url'])) {
                unlink($photo['photo_url']);
            }

            // Supprimer l'entrée de la photo dans la base de données
            $stmt = $pdo->prepare("DELETE FROM photos WHERE id = :id AND login = :login");
            $stmt->execute(['id' => $photo_id, 'login' => $login]);

            // Redirection après suppression de la photo
            header("Location: delete-utilisateur.php");
            exit();
        } else {
            // Affichage d'un message si la photo n'existe pas ou ne peut pas être supprimée
            die("Photo introuvable ou vous n'avez pas l'autorisation de la supprimer.");
        }
    } catch (PDOException $e) {
        // Gestion d'erreur lors de la suppression de la photo
        die("Erreur lors de la suppression de la photo : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer une photo</title>
    <link rel="stylesheet" href="../style/index.css">
</head>
<body>
    <header>
        <h1>Supprimer une photo</h1>
        <nav>
            <a href="index-utilisateur.php" class="btn">Retour à la galerie</a>
        </nav>
    </header>
    
    <main>
        <section class="gallery">
            <h2>Vos photos</h2>
            <div class="media-container">
                <?php if (empty($photos)): ?>
                    <p>Aucune photo à afficher.</p>
                <?php else: ?>
                    <?php foreach ($photos as $photo): ?>
                        <div class="media-item">
                            <!-- Affichage de l'image de l'utilisateur -->
                            <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Photo" class="media-img">
                            <p class="media-description"><?php echo htmlspecialchars($photo['description']); ?></p>
                            <!-- Lien pour supprimer la photo, avec une confirmation -->
                            <a href="?delete=<?php echo $photo['id']; ?>" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')">Supprimer</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
