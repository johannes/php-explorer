#!/usr/bin/env php
<?php
error_reporting(E_ALL);

function gtk_die($message) {
    $dialog = new \GtkMessageDialog(null, 0, \Gtk::MESSAGE_ERROR, \Gtk::BUTTONS_OK, $message);
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

$filename = 'Explorer/Controller/MainWindowController.php';
include 'stuff.php';
include $filename;
include 'Explorer/Manual/Manual.php';

$main = new Explorer\Controller\MainWindowController('phar://'.__FILE__.'/data/explorer.glade');
Gtk::Main();
__HALT_COMPILER(); ?>
