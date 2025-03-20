<?php
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connexion à la base de données
    $host = 'db_lamp';
    $user = 'user';
    $password = 'user';
    $dbname = 'test';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }

    // Récupérer les données du formulaire
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $mdp = isset($_POST['password']) ? $_POST['password'] : '';

    // Validation des champs
    if (empty($login) || empty($mdp)) {
        $message = "Tous les champs sont obligatoires.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\W).{8,}$/', $mdp)) {
        $message = "Le mot de passe doit contenir au moins 8 caractères, une majuscule et un caractère spécial.";
    } else {
        // Vérifier si le login existe déjà
        try {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = :login");
            $stmt->execute(['login' => $login]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $message = "Ce login est déjà utilisé.";
            } else {
                // Hachage du mot de passe
                $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);

                // Insérer l'utilisateur dans la base de données
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, mot_de_passe) VALUES (:login, :mot_de_passe)");
                $stmt->execute(['login' => $login, 'mot_de_passe' => $mdp_hash]);

                // Connexion automatique après l'inscription
                $_SESSION['utilisateur'] = ['login' => $login];

                // Redirection vers la page utilisateur
                header("Location: ../utilisateur/index-utilisateur.php");
                exit();
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="style/login.css">
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>

        <!-- Affichage du message -->
        <?php if (!empty($message)) : ?>
            <p class="message <?php echo strpos($message, 'réussie') !== false ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form method="POST" action="register.php">
            <label for="login">Identifiant :</label>
            <input type="text" id="login" name="login" required>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">S'inscrire</button>
        </form>

        <p>Déjà un compte ? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</body>
</html> 