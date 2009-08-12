#!/usr/bin/env php
<?php
error_reporting(E_ALL);

function gtk_die($message) {
    $dialog = new \GtkMessageDialog(null, 0, \Gtk::MESSAGE_ERROR, \Gtk::BUTTONS_OK, 'Extension missing');
    $dialog->set_markup($message);
    $dialog->run();
    $dialog->destroy();
    die($message."\n");
}

if (!extension_loaded('php-gtk')) {
    die("ERROR: PHP Gtk2 not loaded!\n");
}

if (version_compare('5.3.0', PHP_VERSION, '>')) {
    gtk_die('PHP Explorer requires PHP 5.3.0 ornewer.');
}

if (!extension_loaded('phar')) {
    $message = 'PHAR Extension not loaded! Terminating.';
    gtk_die($message);
}

if(!phar::canCompress()) {
    $message = 'PHAR Extension has no compression support.';
    gtk_die($message);
}

ini_set('include_path', 'phar://'.__FILE__.PATH_SEPARATOR.ini_get('include_path'));

include 'stuff.php';
include 'Explorer/Manual/Manual.php';

$main = new MainWindowController('phar://'.__FILE__.'/data/explorer.glade');
$glade = $main->getGlade();

$status = $glade->get_widget('loadingprogress');
$status->set_pulse_step(1/4);
while (Gtk::events_pending()) {
	Gtk::main_iteration();
}

/*$store = new GtkTreeStore(GObject::TYPE_STRING, GObject::TYPE_PHP_VALUE);
addClasses($store, NULL, get_declared_classes());
fillTreeView('classtreeview', $store);

while (Gtk::events_pending()) {
	    Gtk::main_iteration();
}*/

$store = new GtkTreeStore(GObject::TYPE_STRING, GObject::TYPE_PHP_VALUE);
addFunctions($store, NULL, get_defined_functions());
fillTreeView($main, $glade, 'functiontreeview', $store);

$status->set_fraction(2/4);
while (Gtk::events_pending()) {
	Gtk::main_iteration();
}


$children = array();
foreach (get_declared_classes() as $c) {
	$children[get_parent_class($c)][] = $c;
}

$store = new GtkTreeStore(GObject::TYPE_STRING, GObject::TYPE_PHP_VALUE);
fillTreeView($main, $glade, 'classtreeview', class_tree($store, null, $children, '0'));

function class_tree($store, $parent, $all, $items) {
	foreach ($all[$items] as $item) {
		$p = $store->append($parent, array($item, new ReflectionClass($item)));
		if (!empty($all[$item])) {
			class_tree($store, $p, $all, $item);
		}
	}
	return $store;
}

$status->set_fraction(3/4);
while (Gtk::events_pending()) {
	Gtk::main_iteration();
}       

echo "init done\n";

$glade->get_widget('loadingwindow')->set_visible(false);
$glade->get_widget('mainwindow')->set_visible(true);
Gtk::Main();
__HALT_COMPILER(); ?>
