<?php
session_start();
require 'connexion.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du livre - Bibliothèque Delacroix</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
</head>
<body>

<!-- navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="menu.php">Bibliothèque</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

<!-- menu -->
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="book.php">Liste des livres<span class="sr-only">(current)</span></a>
      </li>

      <li class="nav-item active">
        <a class="nav-link" href="auteur.php">Liste des auteurs<span class="sr-only">(current)</span></a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="louer.php">Louer un livre</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="rendre.php">Rendre un livre</a>
      </li>

    </ul>

<!-- bouton chercher -->
<form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Chercher Livre" aria-label="Search" name="search">
      <button class="btn btn-outline-secondary text-white" type="submit" name="search2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
</svg></button>  

<!-- avatar deroulante -->
<ul class="navbar-nav ms-auto">
<li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <img
            src="../images/blank.jpg"
            class="rounded-circle"
            height="35"
            width="37"
            alt="Blank Portrait"
            loading="lazy"
          />
        </a>

        <div class="dropdown-menu dropdown-menu-right">
    <div class="text-center font-weight-bold"><?php echo "$_SESSION[prenom] ".$_SESSION["nom"].""; ?> </div>        
    <div class="dropdown-divider"></div>
    <button class="dropdown-item" type="button"><a href="profil.php">Profil</a></button>
    <button class="dropdown-item" type="button"><a href="historique.php">Historique des livres empruntés</a></button>
    <button class="dropdown-item" type="button"><a href="logout.php">Se déconnecter</a></button>
  </div>
      </li>
</ul>

    </form>
  </div>
</nav>

<h3 class="mx-auto p-2"></h3>


<!-- RESULTAT DU LIEN-->
<div class="container mt-4">
    <?php
    if(isset($_GET['id'])) {
        $id_livre = $_GET['id'];
        $detailsLivre = getDetailsLivre($id_livre);

        if($detailsLivre) {
            echo "<h3 class='mb-4'>Détails du Livre</h3>";
            echo "<div class='card mb-3'>";
            echo "<div class='card-body'>";
            echo "<img src='" . $detailsLivre['image'] . "' style='max-width: 100px;>"; 
            echo "<h5 class='card-title'>Titre du livre : " . $detailsLivre['titre'] . "</h5>";
            echo "<p class='card-text'>Auteur : " . $detailsLivre['prenom'] . " " . $detailsLivre['nom'] . "</p>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<p class='alert alert-info'>Aucun détail trouvé pour ce livre.</p>";
        }
?>
    <br>

        <!----COMMENTAIRE---->
        <div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <?php
            if (aEmprunte($_SESSION['id_user'], $id_livre)) {
                echo '<h4 class="mb-3">Ajouter un commentaire</h4>
                <form method="post" action="#">
                    <input type="hidden" name="id_livre" value="' . $id_livre . '">
                    <div class="form-group">
                        <label for="commentaire">Commentaire</label>
                        <textarea class="form-control" name="commentaire" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="note">Note (de 1 à 5)</label>
                        <input type="number" name="note" min="1" max="5" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="comm">Envoyer</button>
                </form>';
            } else {
                echo "<p class='mt-4'>Vous devez emprunter ce livre pour pouvoir ajouter un commentaire.</p>";
                echo "<a href='louer.php?id=$id_livre'>Voulez-vous louer ce livre?</a>";
            }
            ?>
        </div>

        <div class="col-md-6">
            <?php
            $tabComm = getLivreComm($id_livre);

            echo "<h4>Commentaires</h4>";

            if(empty($tabComm)) {
                echo "<p>Aucun commentaire pour ce livre.</p>";
            } else {
                echo "<div class='list-group'>";
                foreach ($tabComm as $Comm) {
                    echo "<div class='list-group-item list-group-item-action'>
                            <div class='d-flex w-100 justify-content-between'>
                                <h5 class='mb-1'>" . $Comm['nom'] . " " . $Comm['prenom'] . "</h5>
                                <small>" . $Comm['date_com'] . "</small>
                            </div>
                            <p class='mb-1'>Note : " . $Comm['note'] . "</p>
                            <p class='mb-1'>" . $Comm['commentaire'] . "</p>
                        </div>";
                }
                echo "</div>";
            }
            ?>
        </div>
    </div>
</div>

<?php
} else {
    echo "Aucun ID de livre spécifié.";
}
?>

<form action="" method="post" enctype="multipart/form-data">
        <div>
            <label for="id_livre">ID du Livre :</label>
            <input type="text" id="id_livre" name="id_livre" required>
        </div>
        <div>
            <label for="image">Image :</label>
            <input type="file" name="image" accept="image/*" required>
        </div>
        <input type="submit" value="Envoyer">
    </form>

<div class="container">
  <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
    <div class="col-md-4 d-flex align-items-center">
      <span class="mb-3 mb-md-0 text-body-secondary">© 2023 Biblio, Inc</span>
    </div>
  </footer>
</div>

</form>
</body>
</html>