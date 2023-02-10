<?php

namespace RusaDrako\pgunit;

$arr_load = [
	'/testSQL.php',
	'/testSQLfunctions.php',
	'/testSQLtriggers.php',
];

foreach($arr_load as $k => $v) {
	require_once(__DIR__ . '/' . $v);
}



require_once('aliases.php');
