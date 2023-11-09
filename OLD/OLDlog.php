<?php
session_start();

$email = $_POST["email"];
$motdepasse = $_POST["motdepasse"]; // Le mot de passe n'est pas encore haché

$requete = "SELECT * FROM utilisateur WHERE email = :login";
$prepared_request = $pdo->prepare($requete);
$prepared_request->bindParam(':login', $email);
$prepared_request->execute();

$user = $prepared_request->fetchAll(PDO::FETCH_ASSOC);

if ($user && password_verify($motdepasse, $user['motdepasse'])) {
    $_SESSION["id_user"] = $user["id_user"];
    $_SESSION["nom"] = $user["nom"];
    $_SESSION["prenom"] = $user["prenom"];

    header("location:menu.php");
} else {
    header("location:userfail.php");
}

$pdo = null;
?>
