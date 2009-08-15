<?php
namespace Explorer;

include 'stuff.php';
include 'Explorer/Config.php';
include 'Explorer/Controller/MainWindowController.php';
include 'Explorer/Manual/Manual.php';

class Application {
    public function __construct() {
	$this->readArgs();
    }

    protected function printVersion() {
	echo "PHP Explorer\n";
    }

    protected function printHelp() {
	global $argv;
	echo "$argv[0] [-h] [-v] [-d] [-c configfile]\n";
    }

    protected function dumpDefaultConfig() {
	readfile(BASEDIR.'/data/phpexplorer.ini');
    }

    protected function readArgs() {
	$opts = getopt('c:vdh');

	if (isset($opts['h'])) {
	    $this->printHelp();
	    exit;
	}

	if (isset($opts['v'])) {
	    $this->printVersion();
	    exit;
	}

	if (isset($opts['d'])) {
	    $this->dumpDefaultConfig();
	    exit;
	}

	if (isset($opts['c'])) {
	    \Explorer\Config::getInstance($opts['c']);
	}
    }

    public function run() {
	$gladefile = BASEDIR.'/data/explorer.glade';
	$config =\Explorer\Config::getInstance();
	$main = new \Explorer\Controller\MainWindowController($gladefile);
	\Gtk::Main();
    }
}
?>
