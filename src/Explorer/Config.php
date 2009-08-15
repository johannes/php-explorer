<?php
namespace Explorer;

class Config implements \ArrayAccess {
    protected static $instance;

    protected $data = array();

    private function parseFile($filename) {
	$data = parse_ini_file($filename, false);
	if (is_array($data)) {
	    $this->data[$filename] = $data;
	}
    }

    /**
     * Returns a list of directories to search, can be overloaded for tests and such
     *
     * The first directory in the array (index 0) has highest priority, the last the least
     *
     * @return array
     */
    protected function getSearchList() {
	return array(
	    getenv('HOME').'/.phpexplorer.ini',
	    '/etc/phpexplorer.ini',
	    BASEDIR.'/data/phpexplorer.ini'
	);
    }

    /**
     *
     * @param string $overridefile file path to an file overriding settings
     */
    private function __construct($overridefile = null) {
	$order = $this->getSearchList();
	if ($overridefile) {
	    array_unshift($order, $overridefile);
	} else {
	    // At index 0 I expect the user config for making it writable
	    $this->data[] = array();
	}

	foreach ($order as $filename) {
	    if (file_exists($filename)) {
		$this->parseFile($filename);
	    }
	}
    }

    /**
     *
     * @param string $overridefile file path to an file overriding settings
     * @return Explorer\config
     */
    static function getInstance($overridefile = null) {
	if (self::$instance) {
	    return $instance;
	} else {
	    $class = get_called_class();
	    return new $class($overridefile);
	}
    }

    public function getLoadedFiles() {
	$data = $this->data;
	if (isset($data[0])) {
	    unset($data[0]);
	}
	return array_keys($data);
    }

    public function offsetGet($offset) {
	foreach ($this->data as $data) {
	    if (isset($data[$offset])) {
		return $data[$offset];
	    }
	}

	throw new ConfigValueNotFoundException($offset);
    }

    public function offsetExists($offset) {
	foreach ($this->data as $data) {
	    if (isset($data[$offset])) {
		return true;
	    }
	}

	return false;
    }

    public function offsetSet($offset, $value) {
	$this->data[0][$offset] = $value;
    }

    
    public function offsetUnset($offset) {
	unset($this->data[0][$offset]);
    }
}

class ConfigValueNotFoundException extends \Exception {}
