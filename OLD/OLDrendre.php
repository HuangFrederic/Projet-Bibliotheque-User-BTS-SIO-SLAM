<?php
session_start();
require 'connexion.php';

if(isset($_POST['rendre'])) {
    $id_exemplaire = $_POST['no_book']; // Récupérer l'ID de l'exemplaire sélectionné

        // Vérifier disponibilité
        $check_emprunt = $pdo->prepare("SELECT disponible FROM exemplaire WHERE id_exemplaire = :id_exemplaire");
        $check_emprunt->bindParam(':id_exemplaire', $id_exemplaire);
        $check_emprunt->execute();
        $res = $check_emprunt->fetch();

        if($res && $res['disponible'] == 0) {
            // Update disponibilité
            $update_dispo = $pdo->prepare("UPDATE exemplaire SET disponible = 1 WHERE id_exemplaire = :id_exemplaire");
            $update_dispo->bindParam(':id_exemplaire', $id_exemplaire);
            $update_dispo->execute();

            // Delete l'emprunt
            $stmt = $pdo->prepare("DELETE FROM emprunt WHERE id_exemplaire = :id_exemplaire AND id_user = :id_user ");
            $stmt->bindParam(':id_exemplaire', $id_exemplaire);
            $stmt->bindParam(':id_user', $_SESSION['id_user']);       

            if ($stmt->execute()) {
                echo "Livre rendu avec succès.<br> Merci pour votre confiance";
            } else {
                echo "Erreur pour rendre le livre: " . $stmt->errorInfo()[2];
            }
        } else {
            echo "L'exemplaire n'as pas été emprunté.";
        }
    } 
?>
