<?php

// Démarrage de la session pour pouvoir gérer les sessions utilisateur
session_start();

// Paramètres de connexion à la base de données MySQL
$host = 'db_lamp';  // Hôte de la base de données
$user = 'user';  // Nom d'utilisateur de la base de données
$password = 'user';  // Mot de passe de la base de données
$dbname = 'test';  // Nom de la base de données

try {
    // Connexion à la base de données sans spécifier la base, puis création de celle-ci si elle n'existe pas
    $pdo = new PDO("mysql:host=$host", $user, $password);
    // Définir le mode d'erreur pour afficher les exceptions en cas de problème
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Création de la base de données si elle n'existe pas déjà
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    // Sélection de la base de données à utiliser
    $pdo->exec("USE $dbname");

    // Création de la table 'utilisateurs' si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        login VARCHAR(50) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // Si une erreur survient lors de la connexion ou de la création de la base de données, afficher l'erreur
    die("Erreur de connexion ou de création de la base de données : " . $e->getMessage());
}

// Gestion de la soumission des formulaires (inscription ou connexion)
$message = "";  // Message d'erreur ou de succès à afficher
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si l'action est 'inscription', procéder à l'inscription de l'utilisateur
    if (isset($_POST['action']) && $_POST['action'] == 'inscription') {
        $login = isset($_POST['login']) ? trim($_POST['login']) : '';  // Récupération du login
        $mdp = isset($_POST['mdp']) ? $_POST['mdp'] : '';  // Récupération du mot de passe
        
        // Validation des champs (login et mot de passe)
        if (empty($login) || empty($mdp)) {
            $message = "Tous les champs sont obligatoires.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\W).{8,}$/', $mdp)) {  // Validation du mot de passe (minimum 8 caractères, majuscule et caractère spécial)
            $message = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un caractère spécial.";
        } else {
            // Hachage du mot de passe pour le sécuriser
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
            
            // Tentative d'insertion de l'utilisateur dans la base de données
            try {
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, mot_de_passe) VALUES (:login, :mot_de_passe)");
                $stmt->execute([
                    'login' => $login,  // Paramètre pour le login
                    'mot_de_passe' => $mdp_hash,  // Paramètre pour le mot de passe haché
                ]);
                $message = "Inscription réussie !";  // Message de succès

                // Récupérer l'utilisateur après l'inscription
                $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = :login");
                $stmt->execute(['login' => $login]);
                $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

                // Créer une session pour l'utilisateur connecté
                $_SESSION['utilisateur'] = $utilisateur;

                // Rediriger vers une page protégée après l'inscription (page de bienvenue)
                header("Location: bienvenue.php");
                exit();  // Arrêter le script après la redirection
            } catch (PDOException $e) {
                // Si une erreur survient lors de l'insertion (par exemple, violation de contrainte unique)
                if ($e->getCode() == 23000) {
                    $message = "Ce login est déjà utilisé.";  // Message si le login existe déjà
                } else {
                    $message = "Erreur lors de l'inscription : " . $e->getMessage();  // Autres erreurs
                }
            }
        }
    }

    // Si l'action est 'connexion', procéder à la connexion de l'utilisateur
    elseif (isset($_POST['action']) && $_POST['action'] == 'connexion') {
        $login = isset($_POST['login_connexion']) ? trim($_POST['login_connexion']) : '';  // Récupération du login de connexion
        $mdp = isset($_POST['mdp_connexion']) ? $_POST['mdp_connexion'] : '';  // Récupération du mot de passe de connexion
        
        // Vérifier si l'utilisateur a renseigné les champs de connexion
        if (empty($login) || empty($mdp)) {
            $message = "Tous les champs sont obligatoires.";
        } else {
            // Vérifier si l'utilisateur existe dans la base de données
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = :login");
            $stmt->execute(['login' => $login]);
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérifier si le mot de passe fourni correspond au mot de passe haché dans la base
            if ($utilisateur && password_verify($mdp, $utilisateur['mot_de_passe'])) {
                // Créer une session pour l'utilisateur connecté
                $_SESSION['utilisateur'] = $utilisateur;
                // Rediriger vers la page utilisateur après la connexion réussie
                header("Location: ../utilisateur/index-utilisateur.php");
                exit();  // Arrêter le script après la redirection
            } else {
                $message = "Login ou mot de passe incorrect.";  // Message d'erreur si les identifiants sont incorrects
            } 
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <!-- Lien vers la feuille de style -->
    <link rel="stylesheet" href="style/login.css"> 
</head>
<body>
    <div class="container">
        <h2>Portail d'accès</h2>

        <!-- Affichage du message d'erreur ou de succès -->
        <?php if (!empty($message)) : ?>
            <p class="message <?php echo strpos($message, 'réussie') !== false ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="post" action="">
            <h3>Connexion</h3>
            <label for="login_connexion">Login :</label>
            <input type="text" id="login_connexion" name="login_connexion" required>
            <label for="mdp_connexion">Mot de passe :</label>
            <input type="password" id="mdp_connexion" name="mdp_connexion" required>
            <button type="submit" name="action" value="connexion">Se connecter</button>
        </form>
    </div>
</body>
</html>
