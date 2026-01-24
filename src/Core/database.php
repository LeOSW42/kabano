<?php

namespace Kabano;

/**
 * Ouvre une connexion PostgreSQL avec les paramètres de configuration.
 */
function sql_connect() {
	global $config;

	// Chaîne de connexion PostgreSQL.
	$connection = "host=".$config['SQL_host']
		." dbname=".$config['SQL_db']
		." user=".$config['SQL_user']
		." password=".$config['SQL_pass'];

	// Connexion et gestion d'erreurs.
	$con = pg_connect($connection);
	if (!$con) {
		$error = error_get_last();
		$message = $error && isset($error['message']) ? $error['message'] : "unknown error";
		die("Could not connect to server: ".$message."\n");
	}

	return $con;
}
