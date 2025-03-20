<?php
// Démarrage de la session pour vérifier si l'utilisateur est connecté
session_start();

// Vérifier si l'utilisateur est connecté. Si non, rediriger vers la page de connexion
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ../login.php");
    exit();
}

// Connexion à la base de données avec gestion des erreurs
$host = 'db_lamp';  // Hôte de la base de données
$user = 'user';  // Utilisateur de la base de données
$password = 'user';  // Mot de passe pour la base de données
$dbname = 'test';  // Nom de la base de données

// Tentative de connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    // Activer les exceptions pour les erreurs de base de données
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si une erreur de connexion survient, afficher le message d'erreur
    die("Erreur de connexion : " . $e->getMessage());
}

// Initialisation des variables pour la photo à modifier
$photo = null;
$photo_id = null;

// Vérifier si un ID de photo est fourni pour modification
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $photo_id = (int) $_GET['id'];  // Cast de l'ID de la photo en entier
    try {
        // Requête pour récupérer la photo avec l'ID spécifié
        $stmt = $pdo->prepare("SELECT * FROM photos WHERE id = :id");
        $stmt->bindParam(':id', $photo_id, PDO::PARAM_INT);  // Bind de l'ID
        $stmt->execute();
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);  // Récupération des données sous forme de tableau associatif

        // Si aucune photo n'est trouvée, afficher un message d'erreur
        if (!$photo) {
            die("Photo non trouvée.");
        }
    } catch (PDOException $e) {
        // Si une erreur survient lors de la récupération de la photo, afficher l'erreur
        die("Erreur lors de la récupération de la photo : " . $e->getMessage());
    }
}

// Traitement du formulaire d'upload ou de mise à jour de la photo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des données du formulaire et sécurisation des entrées
    $description = htmlspecialchars($_POST['description'] ?? '');
    $login = $_SESSION['utilisateur']['login'];  // Récupération du login de l'utilisateur connecté
    $public = isset($_POST['public']) ? 1 : 0;  // Vérifier si la photo est publique
    $photo_url = $photo['photo_url'] ?? null;  // Conserver l'URL de l'ancienne photo si présente

    // Vérification si un fichier image a été téléchargé
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $photo_file = $_FILES['photo'];  // Récupération du fichier image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];  // Types de fichiers autorisés

        // Vérifier si le type de fichier est valide
        if (!in_array($photo_file['type'], $allowed_types)) {
            die("Le fichier doit être une image JPEG, PNG ou GIF.");
        }

        // Générer un nom de fichier unique pour éviter les conflits
        $file_name = uniqid('photo_', true) . '.' . pathinfo($photo_file['name'], PATHINFO_EXTENSION);
        $upload_dir = '../uploads/';  // Dossier de destination pour l'upload
        $photo_url = $upload_dir . $file_name;  // Chemin complet du fichier

        // Déplacer le fichier téléchargé dans le répertoire cible
        if (!move_uploaded_file($photo_file['tmp_name'], $photo_url)) {
            die("Erreur lors du téléchargement de l'image.");
        }
    }

    try {
        if ($photo) {
            // Si la photo existe, effectuer une mise à jour
            $stmt = $pdo->prepare("UPDATE photos SET description = :description, public = :public, photo_url = :photo_url WHERE id = :id");
            $stmt->bindParam(':id', $photo_id, PDO::PARAM_INT);  // Bind de l'ID pour l'update
        } else {
            // Si c'est une nouvelle photo, l'ajouter à la base de données
            $stmt = $pdo->prepare("INSERT INTO photos (login, photo_url, description, date_added, public) VALUES (:login, :photo_url, :description, NOW(), :public)");
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);  // Bind du login pour l'ajout
        }

        // Bind des autres paramètres
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':public', $public, PDO::PARAM_INT);
        $stmt->bindParam(':photo_url', $photo_url, PDO::PARAM_STR);
        $stmt->execute();  // Exécution de la requête

        // Redirection vers la galerie après la mise à jour ou l'ajout
        header("Location: index-utilisateur.php");
        exit();
    } catch (PDOException $e) {
        // Gestion des erreurs si l'ajout ou la mise à jour échoue
        die("Erreur lors de l'ajout ou de la mise à jour de la photo : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $photo ? 'Modifier' : 'Ajouter'; ?> une Photo</title>
    <link rel="stylesheet" href="../style/index.css">
</head>
<body>
    <header>
        <h1><?php echo $photo ? 'Modifier la photo' : 'Ajouter une photo'; ?></h1>
        <nav>
            <a href="index-utilisateur.php" class="btn">Retour à la galerie</a>
        </nav>
    </header>

    <main>
        <!-- Formulaire de téléchargement ou de mise à jour de la photo -->
        <form action="upload-utilisateur.php<?php echo $photo ? '?id=' . $photo['id'] : ''; ?>" method="POST" enctype="multipart/form-data">
            <label for="photo">Sélectionner une photo :</label>
            <input type="file" name="photo" id="photo">
            <!-- Si une photo existe, afficher l'image actuelle -->
            <?php if ($photo): ?>
                <div>
                    <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Photo actuelle" width="100">
                </div>
            <?php endif; ?>
            <br>

            <label for="description">Description :</label>
            <textarea name="description" id="description" required><?php echo $photo ? htmlspecialchars($photo['description']) : ''; ?></textarea>
            <br>

            <label for="public">
                <!-- Case à cocher pour rendre la photo publique -->
                <input type="checkbox" name="public" id="public" <?php echo $photo && $photo['public'] ? 'checked' : ''; ?>> Rendre cette photo publique
            </label>
            <br>

            <!-- Bouton pour soumettre le formulaire -->
            <button type="submit"><?php echo $photo ? 'Mettre à jour' : 'Télécharger'; ?></button>
        </form>
    </main>
</body>
</html>
