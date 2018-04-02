<?php
/*
* "livre d'or" avec des failles mises volontairement. 
* L'objectif de ce site est de s'entrainer à l'exploitation de failles en 
* injection SQL, directory transversal etc...
* Et surtout de comprendre la nécessité de se protéger de ce genre de négligences
* car la destruction du système de la machine est surement ce que le hacker peut faire 
* de moins méchant.
* 
* Ne pas utiser sur un serveur en production!
*
*Codé à la va vite par Gnieark http://blog-du-grouik.tinad.fr avril 2013
*
*/

//***** Parametres de connexion MySQL ****
$mysql_host='localhost';
$mysql_user='';
$mysql_password='';
$mysql_database='';
//***** FIN des parametres ****

 
session_start(); 
//y a t-il une action?
if (isset($_POST['act'])){
  $act=$_POST['act'];
}else{
  $act="";
}

//connexion mysql
if (!mysql_connect($mysql_host, $mysql_user, $mysql_password)) {
    echo "Impossible de se connecter à MySQL";
    die;
}
mysql_query("USE ".$mysql_database);

switch($act){
  case "auth":
    //authentifier ou non l'user
    $rs=mysql_query("SELECT 1 FROM users 
      WHERE pseudo='".mysql_real_escape_string($_POST['identifiant'])."'
      AND password='".mysql_real_escape_string($_POST['password'])."'");
      
      if($r=mysql_fetch_row($rs)){
	$_SESSION['user_id']=$_POST['identifiant'];
      }else{
	echo "l'authentification a raté";
	die;
      }
    
    break;
       
  case "sinscrire":
  //inscrire
    //verifier le mot de passe
    if(($_POST['password'] !== $_POST['repeatPassword']) || ($_POST['password']=="")){
      echo "ERREUR Le mot de passe doit etre identique à sa confirmation et ne peut pas etre vide";
      die;
    }
    //verifier si le pseudo est libre
    $rs=mysql_query("SELECT 1 FROM users WHERE pseudo='".mysql_real_escape_string($_POST['identifiant'])."'");
    if($r=mysql_fetch_row($rs)){
      echo "ce pseudo est déja pris, désolé";
      die;
    }
    
    mysql_query("
      INSERT INTO users (pseudo, password) 
      VALUES ('".mysql_real_escape_string($_POST['identifiant'])."',
	'".mysql_real_escape_string($_POST['password'])."')");
    
    break;
  case "postUnMessage":
    //Verifier que l'user soit bien connecté
    if(!isset($_SESSION['user_id'])){
      echo "Vous n'etes pas (plus) connecté";
      die;
    }
    
    //s'il y a un fichier
    $filename="";
      $tmp_file = $_FILES['piecejointe']['tmp_name'];
      //echo $_FILES['fichieroriginal']['tmp_name'];
      if( is_uploaded_file($tmp_file) )
      {
	  //déplacer le fichier dans un dossier
	  
	  if( !move_uploaded_file($tmp_file, $_SERVER['DOCUMENT_ROOT']."/uploads/".$_FILES['piecejointe']['name']) )
	  {
		  echo "Impossible de copier le fichier dans ".$_SERVER['DOCUMENT_ROOT']."/uploads/".$_FILES['piecejointe']['name'];
		  die;
	  }else{
	    $filename=$_FILES['piecejointe']['name'];
	  }
      }
 
    //Une requete pour enregistrer:
    //avec des groses failles

    mysql_query("INSERT INTO messages(time,pseudo,message,couleur,piecejointe) VALUES (
	NOW(),
	'".$_SESSION['user_id']."',
	'".mysql_real_escape_string(htmlentities($_POST['message']))."',
	'".$_POST['couleur']."',
	'".$filename."');");

    
    
    
    
    break;
  case "":
    //y'avait pas d'action définie, rien
    break;
  default:
    //ça c'est pas normal, on kill la suite
    die;
    break;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="KEYWORDS" content="le Livre D'or"/>
  <meta name="author" content="Gnieark">
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="livredor.css" />
</head>
<body>
<h1>Le livre d'or de la DCRI</h1>
<p>Ce livre d'or utilise une base de donnée mySQL (évidemment), et j'ai fait exprès de ne pas protéger certains champs contre les tentatives d'injection SQL. Il y a aussi des failles XSS, et une possibilité de faire du directory transversal.</p>
	<p>Vos missions sont les suivantes:</p>
<ul>
	<li>Réussir à récupérer la liste des utilisateurs et de leurs mots de passe</li>
	<li>Nuire au site en modifiant /créant un fichier robots.txt interdisant aux robots de l'indexer</li>
	<li>ajouter du javascript sur la page d'accueil</li>
</ul>
<?php
//tester si l'user est authentifié
if((!isset($_SESSION['user_id'])) OR ($_SESSION['user_id']== '0')){
?>
<div id="containerConnect">
  <h2>Vous devez vous identifier pour poster sur le livre d'or</h2>
  <fieldset id="auth"><legend>S'identifier</legend>
  <form method="post" action="index.php">
	  <input type="hidden" name="act" value="auth"/>
	  <p><label for="identifiant">Identifiant:</label><input type="text" name="identifiant" id="identifiant"/></p>
	  <p><label for="password">Mot de passe</label><input type="password" name="password" id="password"/></p>
	  <p><label></label><input type="submit" value="S'authentifier"/></p>

  </form>
  </fieldset>
  <fieldset id="inscrire"><legend>S'inscrire</legend>
  <form method="post" action="index.php">
	  <input type="hidden" name="act" value="sinscrire"/>
	  <p><label for="inscrirePseudo">Choississez votre identifiant:</label><input type="text" name="identifiant" id="inscrirePseudo"/></p>
	  <p><label for="inscPass">Votre mot de passe</label><input type="password" name="password" id="inscPass"/></p>
	  <p><label for="insRepeatPass"> Confirmez votre mot de passe</label><input type="password" name="repeatPassword" id="insRepeatPass"/></p>
	  <p><label></label><input type="submit" value="S'inscrire"/></p>
  </form>
  </fieldset>
</div>
<?php
}else{
  echo "<h2>Vous êtes connecté en tant que ".$_SESSION['user_id'].".</h2>";
  ?>
  <fieldset id="postUnMessage"><legend>Mettre un message sur le livre d'or</legend>
    <form method="POST" enctype="multipart/form-data" action="index.php">
      <input type="hidden" name="act" value="postUnMessage"/>
      <p><label for="message">Message:</label><textarea id="message" name="message"></textarea></p>
      <p><label for="couleur">Couleur du message:</label>
	<select id="couleur" name="couleur" style="color: #fff;">
	  <option style="background-color:#000000;" value="#000000;">Noir</option>
	  <option style="background-color:#333333;" value="#333333;">GRIS</option>
	  <option style="background-color:#FF358B;" value="#FF358B;">ROSE</option>
	  <option style="background-color:#01B0F0;" value="#01B0F0;">BLEU</option>
	  <option style="background-color:#AEEE00;" value="#AEEE00;">jaune pipi</option> 
	</select>
      <p><label for="piecejointe">Pièce jointe:</label><input type="file" name="piecejointe" id="piecejointe"/></p>
      <p><label></label><input type="submit" value="poster"/></p>
      
    
    </form>
  </fieldset>
  <?php
}

//efficher la liste des messages
$rs=mysql_query("SELECT time,pseudo,message,couleur,pieceJointe FROM messages ORDER BY time DESC");
while($r=mysql_fetch_row($rs)){
    echo "<p>De: ".$r[1]." le ".$r[0]."</p><p style=\"border-bottom: dotted; color:".$r[3].";\">".nl2br($r[2])."<br/>";
     //pièce jointe
     if($r[4]==""){
      echo "<i>Pas de pièce jointe</i>";
     }else{
      if(!isset($_SESSION['user_id'])){
	echo "vous devez etre connecté pour voir la pièce jointe.";
      }else{
	echo '<a href="file.php?f='.$r[4].'">'.$r[4].'</a>';
      }
     }
     echo "</p>";
    
}

?>
</body>
</html>
