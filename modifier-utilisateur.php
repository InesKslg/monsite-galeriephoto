<?php
// Démarrage de la session pour gérer les informations de l'utilisateur connecté
session_start();

// Affichage des erreurs pour le débogage (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
$host = 'db_lamp';  
$user = 'user';
$password = 'user';
$dbname = 'test';

try {
    // Création de la connexion avec PDO en spécifiant le jeu de caractères UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    // Activation du mode exception pour gérer les erreurs proprement
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Affichage de l'erreur en cas d'échec de connexion à la base de données
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérification si l'utilisateur est connecté en vérifiant l'existence de son ID de session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirection vers la page de connexion si non connecté
    exit;
}

// Vérification si un ID de photo est fourni dans l'URL pour modification
if (isset($_GET['id'])) {
    $photo_id = $_GET['id'];

    try {
        // Préparation de la requête SQL pour récupérer les informations de la photo
        $stmt = $pdo->prepare("SELECT * FROM photos WHERE id = :id");
        $stmt->bindParam(':id', $photo_id, PDO::PARAM_INT);
        $stmt->execute();
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification si la photo existe dans la base de données
        if (!$photo) {
            die("Photo non trouvée.");
        }
    } catch (PDOException $e) {
        // Gestion des erreurs en cas de problème avec la requête SQL
        die("Erreur lors de la récupération de la photo : " . $e->getMessage());
    }
}

// Traitement du formulaire si une mise à jour est demandée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description']; // Récupération de la nouvelle description
    $public = isset($_POST['public']) ? 1 : 0; // Vérification si la case "public" est cochée

    try {
        // Requête SQL pour mettre à jour la photo avec la nouvelle description et visibilité
        $stmt = $pdo->prepare("UPDATE photos SET description = :description, public = :public WHERE id = :id");
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':public', $public, PDO::PARAM_INT);
        $stmt->bindParam(':id', $photo_id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirection vers la galerie après la mise à jour
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        // Gestion des erreurs en cas de problème lors de la mise à jour
        die("Erreur lors de la mise à jour de la photo : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la photo</title>
    <link rel="stylesheet" href="../style/index.css"> <!-- Inclusion du fichier CSS -->
</head>
<body>
    <header>
        <h1>Modifier la photo</h1>
        <nav>
            <a href="index.php" class="btn">Retour à la galerie</a> <!-- Lien pour retourner à la galerie -->
        </nav>
    </header>

    <main>
        <section class="form-section">
            <h3>Modifier la description et la visibilité de la photo</h3>

            <!-- Formulaire de mise à jour des informations de la photo -->
            <form action="modifier_photo.php?id=<?php echo $photo['
