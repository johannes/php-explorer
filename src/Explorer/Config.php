<?php
namespace Explorer;

class Config implements \ArrayAccess {
    protected static $instance;

    protected $data = array();

    private function parseFile($filename) {
	$data = parse_ini_file($filename, false);
	if (is_array($data)) {
	    $this->data[] = $data;
	}
    }
    /**
     *
     * @param string $overridefile file path to an file overriding settings
     */
    private function __construct($overridefile = null) {
	$order = array(
	    getenv('HOME'),
	    '/etc',
	    BASEDIR.'/data'
	);
	if ($overridefile) {
	    array_unshift($order, $overridefile);
	} else {
	    // At index 0 I expect the user config for making it writable
	    $this->data[] = array();
	}

	foreach ($order as $dir) {
	    $filename = $dir.DIRECTORY_SEPARATOR.'phpexplorer.ini';
	    if (file_exists($filename)) {
		$this->parseFile($filename);
	    }
	}
    }

    static function getInstance($overridefile = null) {
	if (self::$instance) {
	    return $instance;
	} else {
	    $class = get_called_class();
	    return new $class($overridefile);
	}
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
