<?php

namespace Kabano;

function sql_connect() {
	global $config;

	return pg_connect(
		"host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass']
	) or die ("Could not connect to server\n");
}

?>
