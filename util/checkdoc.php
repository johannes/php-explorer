#!/usr/bin/env php
<?php
/**
 * An simple script to check the manual for missing stuff
 */

error_reporting(E_ALL);
ini_set('include_path', __DIR__.'/../src'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'Explorer/Manual/Manual.php';

$manuals = new Explorer\Manual\Manual( __DIR__.'/../data', 'en');

foreach (get_loaded_extensions() as $ext) {
    try {
	$manuals->get(new ReflectionExtension($ext));
    } catch (Explorer\Manual\ManualPageNotFoundException $e) {
	echo "missing function: $ext\n";
    }
}

$funcs = get_defined_functions();
foreach ($funcs['internal'] as $func) {
    try {
	$manuals->get(new ReflectionFunction($func));
    } catch (Explorer\Manual\ManualPageNotFoundException $e) {
	echo "missing function: $func\n";
    }
}

foreach (get_declared_classes() as $c) {
    $rc = new ReflectionClass($c);
    if (!$rc->isInternal()) {
	continue;
    }

    try {
	$manuals->get($rc);
    } catch (Explorer\Manual\ManualPageNotFoundException $e) {
	echo "missing class: $c\n";
    }

    foreach ($rc->getMethods() as $name => $method) {
	try {
	    $manuals->get($method);
        } catch (Explorer\Manual\ManualPageNotFoundException $e) {
	    echo "missing method: $c::{$method->getName()}\n";
	}
    }
}