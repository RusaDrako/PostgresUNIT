<?php

if (class_exists('PG_UNIT', false)) { return; }

$classMap = [
	'RusaDrako\\pgunit\\testSQL'            => 'PGUNIT_Test',
	'RusaDrako\\pgunit\\testSQLfunctions'   => 'PGUNIT_Test_Functions',
	'RusaDrako\\pgunit\\testSQLtriggers'    => 'PGUNIT_Test_Triggers',
];

foreach ($classMap as $class => $alias) {
	class_alias($class, $alias);
}
