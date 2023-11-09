<?php

$host = 'localhost';
$user = 'root'; 
$password = ''; 
$dbname = 'biblio'; 
$pdo = '';

try{
    $pdo = 'mysql:host='.$host. ';dbname='.$dbname;

    $pdo = new PDO($pdo, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo 'connection failed: '.$e->getMessage();
}
function connectDB(){

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=biblio', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        echo "Erreur de connexion : " . $e->getMessage();
        return null;
    }
}

function disconnectDB($pdo){
	$pdo=null;
}

function register($data)
{
// REGISTER.PHP
    $pdo=connectDB();
    if(!$pdo)
    {
		echo "Server down";
		return false;
	}
    else{
        array_unshift($data,null);
        var_dump($data);
             
        $req = "INSERT INTO user VALUES(?,?,?,?,?)";
        $stmt = $pdo->prepare($req);
		$rep = $stmt->execute(array_values($data));
        if(!$rep)
            {
			var_dump($stmt->errorInfo());
			return false;
		    }
            else{
                return true;		
                }   
        }

}
    function getLivre(){
        // BOOK.PHP	
            $pdo = connectDB();
            if(!$pdo){
                echo "Server down";
                return false;
            }
            else{
                $req = "SELECT livre.titre, auteur.id_auteur, auteur.nom, auteur.prenom, exemplaire.id_exemplaire, exemplaire.disponible, livre.id_livre, livre.image
                        FROM livre
                        JOIN oeuvre_de ON livre.id_livre = oeuvre_de.id_livre
                        JOIN auteur ON oeuvre_de.id_auteur = auteur.id_auteur
                        JOIN exemplaire ON livre.id_livre = exemplaire.id_livre;";
                $stmt = $pdo->prepare($req);
                $rep = $stmt->execute();
                if(!$rep){
                    var_dump($stmt->errorInfo());
                    return false;
                }
                else{
                $tabLivre = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $tabLivre;		
                }
            }
            
        }

        function getAuteur(){
            // BOOK.PHP	
                $pdo = connectDB();
                if(!$pdo){
                    echo "Server down";
                    return false;
                }
                else{
                    $req = "SELECT DISTINCT  auteur.id_auteur, auteur.nom, auteur.prenom FROM auteur";
                    $stmt = $pdo->prepare($req);
                    $rep = $stmt->execute();
                    if(!$rep){
                        var_dump($stmt->errorInfo());
                        return false;
                    }
                    else{
                    $tabAuteur = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $tabAuteur;		
                    }
                }
                
            }

            function getAllLivre(){
                // LOUER.PHP afficher livre louable
                    $pdo = connectDB();
                    if(!$pdo){
                        echo "Server down";
                        return false;
                    }
                    else{
                        $req = "SELECT livre.titre, exemplaire.id_exemplaire, livre.id_livre
                        FROM livre 
                        JOIN exemplaire ON livre.id_livre = exemplaire.id_livre
                        WHERE exemplaire.disponible = 1
                        ";
                        $stmt = $pdo->prepare($req);
                        $rep = $stmt->execute();
                        if(!$rep){
                            var_dump($stmt->errorInfo());
                            return false;
                        }
                        else{
                        $tabBook = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $tabBook;		
                        }
                    }	
                }

function getAllEmprunt(){
// RENDRE.PHP
	$pdo = connectDB();
	if(!$pdo){
		echo "Server down";
		return false;
	}
	else{
        $req = "SELECT livre.titre, emprunt.id_exemplaire 
        FROM livre 
        JOIN exemplaire ON livre.id_livre = exemplaire.id_livre
        JOIN emprunt ON exemplaire.id_exemplaire = emprunt.id_exemplaire
        WHERE emprunt.id_user = :id_user AND emprunt.statut = 1";
        $stmt = $pdo->prepare($req);
        $stmt->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
		$rep = $stmt->execute();
		if(!$rep){
			var_dump($stmt->errorInfo());
			return false;
		}
		else{
		$tabEmprunt = $stmt->fetchAll(PDO::FETCH_ASSOC);
return $tabEmprunt;		
		}
	}	
}

function LouerLivre(){
    // LOUER.PHP
    $pdo = connectDB();
    if(!$pdo){
        echo "Server down";
        return false;
    } else {
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

                    // Insert l'emprunt avec statut = 1
                    $stmt = $pdo->prepare("INSERT INTO emprunt (id_exemplaire, id_user, date_emprunt, date_retour, statut) VALUES (:id_exemplaire, :id_user, :date_emprunt, :date_retour, 1)");
                    $stmt->bindParam(':id_exemplaire', $id_exemplaire);
                    $stmt->bindParam(':id_user', $_SESSION['id_user']);
                    $stmt->bindParam(':date_emprunt', $date_emprunt);
                    $stmt->bindParam(':date_retour', $date_retour);

                    if ($stmt->execute()) {
                        header('location:menu.php');
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
    }
}


function RendreLivre(){
    // RENDRE.PHP
    $pdo = connectDB();
    if(!$pdo){
        echo "Server down";
        return false;
    } else {
        if(isset($_POST['rendre'])) {
            $id_exemplaire = $_POST['no_book']; // Récupérer l'ID de l'exemplaire sélectionné

            // Vérifier disponibilité
            $check_emprunt = $pdo->prepare("SELECT disponible FROM exemplaire WHERE id_exemplaire = :id_exemplaire");
            $check_emprunt->bindParam(':id_exemplaire', $id_exemplaire);
            $check_emprunt->execute();
            $res = $check_emprunt->fetch();

            // Vérifier statut
            $check_statut = $pdo->prepare("SELECT statut FROM emprunt WHERE id_exemplaire = :id_exemplaire AND statut = 1");
            $check_statut->bindParam(':id_exemplaire', $id_exemplaire);
            $check_statut->execute();
            $res2 = $check_statut->fetch();

            if($res && $res['disponible'] == 0) {
                // Update disponibilité
                $update_dispo = $pdo->prepare("UPDATE exemplaire SET disponible = 1 WHERE id_exemplaire = :id_exemplaire");
                $update_dispo->bindParam(':id_exemplaire', $id_exemplaire);
                $update_dispo->execute();

                if($res2) {
                    // Update le statut
                    $stmt = $pdo->prepare("UPDATE emprunt SET statut = 0 WHERE id_exemplaire = :id_exemplaire AND statut = 1");
                    $stmt->bindParam(':id_exemplaire', $id_exemplaire);
                    $stmt->execute();

                    header('location:menu.php');
                } else {
                    echo "L'exemplaire n'a pas été emprunté ou a déjà été rendu.";
                }
            } else {
                echo "L'exemplaire n'est pas emprunté ou est déjà disponible.";
            }
        }
    }
}


function ListAll() {
// MENU.PHP
    $pdo = connectDB();
    if(!$pdo) {
        echo "Server down";
        return false;
    } else {
        // Récupérer id_user
        $userId = $_SESSION['id_user'];

        $req = "SELECT livre.id_livre, livre.titre, auteur.nom, auteur.prenom, emprunt.date_emprunt, emprunt.date_retour, emprunt.id_user, exemplaire.id_exemplaire, livre.image
                FROM livre
                JOIN oeuvre_de ON livre.id_livre = oeuvre_de.id_livre
                JOIN auteur ON oeuvre_de.id_auteur = auteur.id_auteur
                JOIN exemplaire ON livre.id_livre = exemplaire.id_livre
                JOIN emprunt ON exemplaire.id_exemplaire = emprunt.id_exemplaire
                WHERE emprunt.id_user = :userId AND emprunt.statut = 1";

        $stmt = $pdo->prepare($req);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $rep = $stmt->execute();

        if(!$rep){
            var_dump($stmt->errorInfo());
            return false;
        } else {
            $tabList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $tabList;
        }
    }
}

function comm($id_livre, $id_user, $commentaire, $note){
// AJOUTER COMMENTAIRE DANS DETAILSPLUS.PHP
    $pdo = connectDB();
    if(!$pdo) {
        echo "Server down";
        return false;
    } else {
        $stmt = $pdo->prepare("INSERT INTO commentaire (id_livre, id_user, commentaire, note, date_com) VALUES (?, ?, ?, ?, NOW())");

        $stmt->bindParam(1, $id_livre, PDO::PARAM_INT);
        $stmt->bindParam(2, $id_user, PDO::PARAM_INT);
        $stmt->bindParam(3, $commentaire, PDO::PARAM_STR);
        $stmt->bindParam(4, $note, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt) {
            return true;
        } else {
            $errorInfo = $pdo->errorInfo();
            echo "Erreur lors de l'exécution de la requête : " . $errorInfo[2];
            return false;
        }
    }
}

    if(isset($_POST['comm'])) {
        $id_livre = $_POST['id_livre'];
        $id_user = $_SESSION['id_user'];
        $commentaire = $_POST['commentaire'];
        $note = $_POST['note'];

    if (comm($id_livre, $id_user, $commentaire, $note)) {
        header("Location: detailsplus.php?id=$id_livre");
        exit();
    } else {
        echo "Erreur lors de l'ajout du commentaire.";
    }
}

function getLivreComm($id_livre){
// AFFICHER COMMENTAIRE DANS DETAILSPLUS.PHP
    $pdo = connectDB();
    if(!$pdo) {
        echo "Server down";
        return false;
    } else {
        $stmt = $pdo->prepare("SELECT user.nom, user.prenom, commentaire.note, commentaire.commentaire, date_com
        FROM commentaire 
        JOIN user ON commentaire.id_user = user.id_user
        WHERE id_livre = :id_livre");
        $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function rechercher($terme) {
//CHERCHER DES LIVRES DETAILS2.PHP
    $pdo = connectDB();
    if (!$pdo) {
        echo "Server down";
        return false;
    } else {

        if (empty($terme)) {
            // Rafraîchir la page
            header('location:menu.php');
            exit();
        }

        $stmt = $pdo->prepare("SELECT * FROM livre 
        JOIN oeuvre_de ON Livre.id_livre = oeuvre_de.id_livre
        JOIN Auteur ON oeuvre_de.id_auteur = Auteur.id_auteur
        WHERE titre LIKE :terme");
        $terme = "%$terme%";
        $stmt->bindParam(':terme', $terme, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            header("Location: detailsplus.php?id={$result['id_livre']}");
            exit();
        } elseif ($stmt->rowCount() > 1) {
            //stock les resultats
            $_SESSION['search_results'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            header('location:details2.php');
            exit();
        } else {
            return 'Aucun livre trouvé';
        }
    }
}

    if (isset($_GET['search2'])) {
        $termeRecherche = $_GET['search'];
        $resultats = rechercher($termeRecherche);
}

function getDetailsLivre($id_livre) {
// AFFICHER LES DETAILS DANS DETAILSPLUS.PHP APRES CLIQUER LIEN
    $pdo = connectDB();
    if(!$pdo) {
        echo "Server down";
        return false;
    } else {
        $stmt = $pdo->prepare("SELECT livre.titre, auteur.nom, auteur.prenom 
        FROM livre
        JOIN oeuvre_de ON livre.id_livre = oeuvre_de.id_livre
        JOIN auteur ON oeuvre_de.id_auteur = auteur.id_auteur  
        WHERE livre.id_livre = :id_livre");
        $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

function getLivresAuteur($id_auteur) {
    //details3.php 
    $pdo = connectDB();
    if(!$pdo) {
        echo "Server down";
        return false;
    } else {
        $stmt = $pdo->prepare("SELECT livre.titre, livre.id_livre
            FROM auteur
            JOIN oeuvre_de ON auteur.id_auteur = oeuvre_de.id_auteur
            JOIN livre ON oeuvre_de.id_livre = livre.id_livre  
            WHERE auteur.id_auteur = :id_auteur");

        $stmt->bindParam(':id_auteur', $id_auteur, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
    
function getProfil() {
    // AFFICHER PROFIL DANS PROFIL.PHP
        $pdo = connectDB();
        if(!$pdo) {
            echo "Server down";
            return false;
        } else {
            $stmt = $pdo->prepare("SELECT nom, prenom, email, motdepasse FROM user
            WHERE id_user = :id_user");
            $stmt->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
            $rep = $stmt->execute();
        }

        if(!$rep){
			var_dump($stmt->errorInfo());
			return false;
		}
		else{
		$tabProfil = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $tabProfil;		
		}

    }
    
    function Profil($nom, $prenom, $email, $mdp) {
        // PROFIL.PHP MODIFIER LE PROFIL
        $pdo = connectDB();
        if(!$pdo) {
            echo "Server down";
            return false;
        } else {
            $stmt = "UPDATE user SET ";
            $params = array();
    
            if(!empty($nom)) {
                $stmt .= "nom = :nom, ";
                $params[':nom'] = $nom;
            }
    
            if(!empty($prenom)) {
                $stmt .= "prenom = :prenom, ";
                $params[':prenom'] = $prenom;
            }
    
            if(!empty($email)) {
                $stmt .= "email = :email, ";
                $params[':email'] = $email;
            }
    
            if(!empty($mdp)) {
                $stmt .= "motdepasse = :mdp, ";
                $hashedPassword = password_hash($mdp, PASSWORD_DEFAULT);
                $params[':mdp'] = $hashedPassword;
            }
    
            // Supprimer la virgule et l'espace supplémentaires à la fin de la requête
            $stmt = rtrim($stmt, ', ');
    
            $stmt .= " WHERE id_user = :id_user";
            $params[':id_user'] = $_SESSION['id_user'];
    
            $req = $pdo->prepare($stmt);
    
            foreach($params as $key => &$value) {
                $req->bindParam($key, $value);
            }
    
            $rep = $req->execute();
            if(!$rep){
                var_dump($req->errorInfo());
                return false;
            }  
        }
        return true;
    }
        
    if(isset($_POST['modif'])){   
        // Récupérez les valeurs du formulaire
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $mdp = $_POST['mdp'];
    
        $hashedPassword = password_hash($mdp, PASSWORD_DEFAULT);
       
        // Mettez à jour les informations dans la base de données
        $rep = Profil($nom, $prenom, $email, $mdp);
    
        if($rep){
            header('location:profil.php?success=1');
            exit();
        }
        else{
            echo "Erreur, veuillez réessayer";
        }
    }

    function aEmprunte($id_utilisateur, $id_livre) {
        $pdo = connectDB();
    
        if (!$pdo) {
            echo "Server down";
            return false;
        } else {
            $req = "SELECT COUNT(*) FROM emprunt e
                    JOIN exemplaire ex ON e.id_exemplaire = ex.id_exemplaire
                    WHERE e.id_user = ? AND ex.id_livre = ? AND e.statut = 1";
            $stmt = $pdo->prepare($req);
            $stmt->execute([$id_utilisateur, $id_livre]);
    
            $result = $stmt->fetchColumn();
    
            return $result > 0;
        }
    }    

    function ListHistorique() {
        // HISTOIQUE.PHP
            $pdo = connectDB();
            if(!$pdo) {
                echo "Server down";
                return false;
            } else {
                // Récupérer id_user
                $userId = $_SESSION['id_user'];
        
                $req = "SELECT livre.id_livre, livre.titre, auteur.nom, auteur.prenom, emprunt.date_emprunt, emprunt.date_retour, emprunt.id_user, exemplaire.id_exemplaire, emprunt.statut
                        FROM livre
                        JOIN oeuvre_de ON livre.id_livre = oeuvre_de.id_livre
                        JOIN auteur ON oeuvre_de.id_auteur = auteur.id_auteur
                        JOIN exemplaire ON livre.id_livre = exemplaire.id_livre
                        JOIN emprunt ON exemplaire.id_exemplaire = emprunt.id_exemplaire
                        WHERE emprunt.id_user = :userId";
        
                $stmt = $pdo->prepare($req);
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $rep = $stmt->execute();
        
                if(!$rep){
                    var_dump($stmt->errorInfo());
                    return false;
                } else {
                    $tabHist = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $tabHist;
                }
            }
        }

        function getCountExemplaire($id_livre){
            $pdo = connectDB();
            if(!$pdo) {
                echo "Server down";
                return false;
            } else {
                $req = "SELECT COUNT(*) AS nombre_exemplaires_disponibles
                FROM exemplaire
                WHERE disponible = 1
                AND id_livre = :id_livre";

            $stmt = $pdo->prepare($req);
            $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
            $stmt->execute();
    
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            return $result['nombre_exemplaires_disponibles'];
        } 
    }

    function nbLocations() {
            $pdo = connectDB();
            if(!$pdo) {
                echo "Server down";
                return false;
            } else {
                $req = "SELECT 
                            livre.*,
                            (SELECT COUNT(*) 
                             FROM emprunt 
                             JOIN exemplaire ON emprunt.id_exemplaire = exemplaire.id_exemplaire
                             WHERE exemplaire.id_livre = livre.id_livre) as nombre_emprunts
                        FROM livre 
                        ORDER BY nombre_emprunts DESC";
    
                $stmt = $pdo->prepare($req);
                $stmt->execute();
    
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                return $result;
            }
        }

        function addImageToDB($id_livre, $imagePath) {
            $pdo = connectDB();
            if(!$pdo) {
                echo "Server down";
                return false;
            } else {
                $req = "UPDATE livre SET image = :image WHERE id_livre = :id_livre";
                $stmt = $pdo->prepare($req);
                $stmt->bindParam(':image', $imagePath, PDO::PARAM_STR);
                $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    
        if(isset($_POST['id_livre'])) {
            $id_livre = $_POST['id_livre'];
    
            if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $targetDir = "../biblio/images/";
                $targetFile = $targetDir . basename($_FILES['image']['name']);
                $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
    
                if(getimagesize($_FILES["image"]["tmp_name"]) !== false) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                        addImageToDB($id_livre, $targetFile);
                        echo "Image ajoutée avec succès pour le livre avec l'ID $id_livre.";
                    } else {
                        echo "Une erreur s'est produite lors du téléchargement du fichier.";
                    }
                } else {
                    echo "Le fichier n'est pas une image valide.";
                }
            }
        }
    ?>

