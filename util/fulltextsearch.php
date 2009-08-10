#!/usr/bin/env php
<?php
/**
 * An simple script to search for manual pages...
 */

error_reporting(E_ALL);
ini_set('include_path', __DIR__.'/../src'.PATH_SEPARATOR.ini_get('include_path'));
require_once 'PHPUnit/Framework.php';

require_once 'Explorer/Manual/Manual.php';

if ($argc != 2) {
	die("Usage: $argv[0] expression\n");
}

$manuals = new Explorer\Manual\Manual( __DIR__.'/../data', 'en');
$s = $manuals->searchFulltext($argv[1], true);
foreach ($s as $title => $search) {
	echo $title,"\n";
	foreach ($search as $result) {
		echo $result->getPathname(), "\n";
	}
}
