<?php
session_start();
require_once 'connexion.php';

// Traitement de la soumission du formulaire
if (isset($_GET['chercher'])) {
    $termeRecherche = $_GET['search'];
    $resultats = rechercher($termeRecherche);
}
?>

<!-- Formulaire de recherche -->
<form class="form-inline my-2 my-lg-0" method="GET" action="#">
    <input class="form-control mr-sm-2" type="search" placeholder="Chercher Livre ou Auteur" aria-label="Search" name="search">
    <button class="btn btn-outline-secondary text-white" type="submit" name="chercher">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
        </svg>
    </button>
</form>

<?php
if(isset($_SESSION['search_results'])) {
    $results = $_SESSION['search_results'];
    foreach($results as $result) {
        // Afficher les détails du résultat
        echo "Titre du livre : " . $result['titre'] . "<br>";
        echo "Nom de l'auteur : " . $result['prenom'] . "<br>";
        echo "Prenom de l'auteur : " . $result['nom'] . "<br>";
        // Ajoutez d'autres détails selon vos besoins
    }
} else {
    echo "Aucun résultat trouvé.";
}
?>
