<?php

/**
 * Exemple de configuration globale pour l'application Kabano.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
locale_set_default("fr_FR");
date_default_timezone_set("UTC"); // Default tz for date manipulation is UTC. Display tz is in session.php


/*****
** Management of folder names
*****/

// Définition des chemins absolus de l'application.
$config['core_folder'] = rtrim(realpath(__DIR__), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
$config['src_folder'] = rtrim(realpath(dirname($config['core_folder'])), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
$config['abs_root_folder'] = rtrim(realpath(dirname($config['src_folder'])), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
$config['public_folder'] = $config['abs_root_folder']."public/";

// This is the relative folder to the root of the website from the DocumentRoot (can also be called subfolder)
$document_root = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR);
$public_root = rtrim(realpath($config['public_folder']), DIRECTORY_SEPARATOR);
$config['rel_root_folder'] = "";
if ($document_root && $public_root && strpos($public_root, $document_root) === 0) {
	$config['rel_root_folder'] = substr($public_root, strlen($document_root));
}
$config['web_root_folder']="https://kabano.test/";
if($config['rel_root_folder']=="") {
	$config['rel_root_folder']="/";
} else {
	$config['rel_root_folder'] = "/".trim($config['rel_root_folder'],"/")."/";
}

// Here all the absolute paths to specific folders
$config['views_folder'] = $config['public_folder']."views/";
$config['controllers_folder'] = $config['src_folder']."Controllers/";
$config['models_folder'] = $config['src_folder']."Models/";
$config['medias_folder'] = $config['public_folder']."medias/";
$config['includes_folder'] = $config['core_folder'];
$config['third_folder'] = $config['src_folder']."Thirds/";
$config['logs_folder'] = $config['abs_root_folder']."logs/";

// Here all the relative url to specific folders
$config['views_url'] = $config['rel_root_folder']."views/";


/*****
** SQL Database configuration
*****/

// Paramètres PostgreSQL utilisés par sql_connect().
$config['SQL_host'] = "localhost";
$config['SQL_user'] = "kabano";
$config['SQL_pass'] = "PASSWORD";
$config['SQL_db'] = "postgres";

/*****
** Mail configuration
*****/

$config['admin_mail'] = "leo@lstronic.com";
$config['bot_mail'] = "robot@kabano.com";
