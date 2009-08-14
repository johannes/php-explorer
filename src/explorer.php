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
    gtk_die('PHP Explorer requires PHP 5.3.0 or newer.');
}

if (!extension_loaded('phar')) {
    gtk_die('PHAR Extension not loaded! Terminating.');
}

if(!phar::canCompress()) {
    gtk_die('PHAR Extension has no compression support.');
}

if (basename(__FILE__) == 'explorer.php') {
    ini_set('include_path', __DIR__.PATH_SEPARATOR.ini_get('include_path'));
    $gladefile = __DIR__.'/data/explorer.glade';
} else {
    ini_set('include_path', 'phar://'.__FILE__.PATH_SEPARATOR.ini_get('include_path'));
    $gladefile = 'phar://'.__FILE__.'/data/explorer.glade';
}


include 'stuff.php';
include 'Explorer/Controller/MainWindowController.php';
include 'Explorer/Manual/Manual.php';


$main = new Explorer\Controller\MainWindowController($gladefile);
Gtk::Main();

__HALT_COMPILER(); ?>
