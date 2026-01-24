<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
locale_set_default("fr_FR");
date_default_timezone_set("UTC"); // Default tz for date manipulation is UTC. Display tz is in session.php


/*****
** Management of folder names
*****/

// It is the include folder name
$config['include_folder']=basename(__DIR__);
// This is the absolute folder to the root of the website
$config['abs_root_folder']=str_replace($config['include_folder'],"",__DIR__);
// This is the relative folder to the root of the website from the DocumentRoot (can also be called subfolder)
$config['rel_root_folder']=str_replace($_SERVER['DOCUMENT_ROOT'],"",$config['abs_root_folder']);
$config['web_root_folder']="https://kabano.test/";
if($config['rel_root_folder']=="") $config['rel_root_folder']="/";

// Here all the absolute paths to specific folders
$config['views_folder'] = $config['abs_root_folder']."views/";
$config['controllers_folder'] = $config['abs_root_folder']."controllers/";
$config['models_folder'] = $config['abs_root_folder']."models/";
$config['medias_folder'] = $config['abs_root_folder']."medias/";
$config['includes_folder'] = $config['abs_root_folder']."includes/";
$config['third_folder'] = $config['abs_root_folder']."third/";
$config['logs_folder'] = $config['abs_root_folder']."logs/";

// Here all the relative url to specific folders
$config['views_url'] = $config['rel_root_folder']."views/";


/*****
** SQL Database configuration
*****/

$config['SQL_host'] = "localhost";
$config['SQL_user'] = "kabano";
$config['SQL_pass'] = "PASSWORD";
$config['SQL_db'] = "postgres";

/*****
** Mail configuration
*****/

$config['admin_mail'] = "leo@lstronic.com";
$config['bot_mail'] = "robot@kabano.com";
