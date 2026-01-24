<?php

namespace Kabano;

function sql_connect() {
	global $config;

	$connection = "host='".pg_escape_string((string)$config['SQL_host'])."'"
		." dbname='".pg_escape_string((string)$config['SQL_db'])."'"
		." user='".pg_escape_string((string)$config['SQL_user'])."'"
		." password='".pg_escape_string((string)$config['SQL_pass'])."'";

	$con = pg_connect($connection);
	if (!$con) {
		$error = error_get_last();
		$message = $error && isset($error['message']) ? $error['message'] : "unknown error";
		die("Could not connect to server: ".$message."\n");
	}

	return $con;
}

?>
