<?php
session_start();
require 'connexion.php';

if(isset($_POST['louer'])) {
    $date_emprunt = $_POST['emprunt'];
    $date_retour = $_POST['retour'];
    $id_exemplaire = $_POST['no_book']; // Récupérer l'id_exemplaire

    if (!empty($date_emprunt) && !empty($date_retour)) {
        $date_emprunt = date('Y-m-d', strtotime($_POST['emprunt']));
        $date_retour = date('Y-m-d', strtotime($_POST['retour']));

        // Verif disponibilité
        $check_dispo = $pdo->prepare("SELECT disponible FROM exemplaire WHERE id_exemplaire = :id_exemplaire");
        $check_dispo->bindParam(':id_exemplaire', $id_exemplaire);
        $check_dispo->execute();
        $res = $check_dispo->fetch();

        if($res && $res['disponible'] == 1) {
            // Update dispo
            $update_dispo = $pdo->prepare("UPDATE exemplaire SET disponible = 0 WHERE id_exemplaire = :id_exemplaire");
            $update_dispo->bindParam(':id_exemplaire', $id_exemplaire);
            $update_dispo->execute();

            // Insert l'emprunt
            $stmt = $pdo->prepare("INSERT INTO emprunt (id_exemplaire, id_user, date_emprunt, date_retour) VALUES (:id_exemplaire, :id_user, :date_emprunt, :date_retour)");
            $stmt->bindParam(':id_exemplaire', $id_exemplaire);
            $stmt->bindParam(':id_user', $_SESSION['id_user']);
            $stmt->bindParam(':date_emprunt', $date_emprunt);
            $stmt->bindParam(':date_retour', $date_retour);

            if ($stmt->execute()) {
                echo "Livre loué avec succès!";
            } else {
                echo "Erreur lors de la location du livre : " . $stmt->errorInfo()[2];
            }
        } else {
            echo "L'exemplaire n'est pas disponible.";
        }
    } else {
        echo "Les champs de date sont requis.";
    }
}
?>
