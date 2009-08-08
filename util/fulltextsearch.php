#!/usr/bin/env php
<?php
/**
 * An simple script to search for manual pages...
 */

error_reporting(E_ALL);
include __DIR__.'/../manual.php';

if ($argc != 2) {
	die("Usage: $argv[0] expression\n");
}

$manuals = new Explorer\Manual\Manual('.', 'en');
$s = $manuals->searchFulltext($argv[1], true);
foreach ($s as $title => $search) {
	echo $title,"\n";
	foreach ($search as $result) {
		echo $result->getPathname(), "\n";
	}
}
