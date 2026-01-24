<?php

namespace Kabano;

function sql_escape_connection_value($value) {
	$value = (string)$value;
	$value = str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
	return "'".$value."'";
}

function sql_connect() {
	global $config;

	$connection = "host=".sql_escape_connection_value($config['SQL_host'])
		." dbname=".sql_escape_connection_value($config['SQL_db'])
		." user=".sql_escape_connection_value($config['SQL_user'])
		." password=".sql_escape_connection_value($config['SQL_pass']);

	$con = @pg_connect($connection);
	if (!$con) {
		$error = error_get_last();
		$message = $error && isset($error['message']) ? $error['message'] : "unknown error";
		die("Could not connect to server: ".$message."\n");
	}

	return $con;
}

?>
