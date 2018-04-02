<?php
/*
*
*Ce fichier est un morceau du livre d'or
*
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
@session_start();
function sendfile($filename)
{
	//N'utilisez pas cette function sur un vrai site internet!
    
    $startPath=$_SERVER['DOCUMENT_ROOT']."/uploads/";
    header('X-Sendfile: '.$startPath.$filename);
    header('Content-type: '.mime_content_type($startPath.$filename));
    //on indique le nom du fichier
    header('Content-Disposition: attachment; filename="'.basename($startPath.$filename));
    //on envoie le fichier source
    readfile($startPath.$filename);

}
if(isset($_SESSION['user_id'])){
  sendfile($_GET['f']);
}
