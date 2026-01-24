<?php

/*****
** This file contains the routing from any request to the correct view and controller
*****/

// Objets pour partager les informations de routeur avec les contrôleurs et vues.
$controller = new stdClass;
$view = new stdClass;

// URL complète demandée par le client.
$controller->full_url = $_SERVER['REQUEST_URI'];
$controller->url_no_param = explode('?',$controller->full_url);

// URL without ?parameters and /subfolder/
$controller->base_url=str_replace('RACINE'.$config['rel_root_folder'],'','RACINE'.$controller->url_no_param[0]);
$controller->splitted_url = explode ('/',$controller->base_url);

// By default we use the desktop 
$view->prefix = "d.";
$controller->prefix = "d.";
// Flags de gestion d'erreurs et de session.
$notfound = 0;
$session = 1;

// La racine du site redirige vers la page d'accueil.
if($controller->splitted_url[0]=="") $controller->splitted_url[0]="index";

// Routing to the correct page from the correct link
switch ($controller->splitted_url[0])
{
    case "index":
    case "community" :
        $controller->name="";
        $view->name=$controller->splitted_url[0];
        break;
    case "user" :
        $controller->name="users";
        $view->name="";
        break;
    case "contact" :
    case "wiki" :
    case "blog" :
    case "map" :
    case "poi" :
    case "admin" :
        $controller->name=$controller->splitted_url[0];
        $view->name="";
        break;
    default : 
        $controller->name="";
        $view->name="";
        $notfound = 1;
        break;
}

// Initialisation de la session utilisateur.
if($session==1) {
	require_once($config['includes_folder']."session.php");
}
// Exécution du contrôleur correspondant si défini.
if($controller->name != "") {
	include ($config['controllers_folder'].$controller->prefix.$controller->name.".php");
}
// Affichage de la vue statique si définie.
if($view->name != "") {
	include ($config['views_folder'].$view->prefix.$view->name.".html");
}

// Rendu de la page 404 via le wiki si aucune route n'a correspondu.
if($notfound) {
    require_once($config['includes_folder']."session.php");
    require_once($config['models_folder']."d.wiki.php");
    $wikiPage = new Kabano\WikiPage();
    $wikiPage->checkPermalink('404');
    $wikiPage->md2html();
    $head['css'] = "d.index.css;d.wiki.css";
    $head['title'] = $wikiPage->name;
    include ($config['views_folder']."d.wiki.view.html");
}
