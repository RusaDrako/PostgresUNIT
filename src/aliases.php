<?php

if (class_exists('PG_UNIT', false)) { return; }

$classMap = [
	'RusaDrako\\pgunit\\testSQL'            => 'PG_UNIT_Test',
	'RusaDrako\\pgunit\\testSQLfunctions'   => 'PG_UNIT_Test_Functions',
	'RusaDrako\\pgunit\\testSQLtriggers'    => 'PG_UNIT_Test_Triggers',
];

foreach ($classMap as $class => $alias) {
	class_alias($class, $alias);
}
