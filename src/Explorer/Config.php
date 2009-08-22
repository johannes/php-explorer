<?php
namespace Explorer;

class Config implements \ArrayAccess {
    private $changed = false;
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
     * The first directory in the array (index 0) has highest priority, the last the least,
     * The first item should be the user's home so it can be written, the last
     * should hold default values.
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
            //$this->data[] = array();
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
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class($overridefile);
        }
	return self::$instance;
    }

    public function getLoadedFiles() {
        $data = $this->data;
        if (isset($data[0])) {
            unset($data[0]);
        }
        return array_keys($data);
    }

    public function isDefault($offset) {
        $local = reset($this->data);
        $default = end($this->data);

        if (!isset($local[$offset])) {
	    return true;
	}
        if (!isset($default[$offset])) {
            throw new ConfigValueNotFoundException($offset);
        }

        return $local[$offset] == $default[$offset];
    }

    public function save($location = null) {
	if (!$this->changed) {
	    return;
	}
        if (!$location) {
	    $filelist = $this->getSearchList();
            reset($filelist);
            $location = current($filelist);
        }

        $fp = fopen($location, 'w');
        fwrite($fp, "[PHP Explorer]\n");
        $merged = array();
        foreach ($this->data as $values) {
            $merged = array_merge($merged, $values);
        }
        $keys = array_keys($merged);

        foreach ($keys as $key) {
            if ($this->isDefault($key)) {
                continue;
            }

            fwrite($fp, $key.' = '.$this[$key]."\n");
        }
        fclose($fp);
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
        $this->changed = true;
        $this->data[0][$offset] = $value;
    }

    
    public function offsetUnset($offset) {
        $this->changed = true;
        unset($this->data[0][$offset]);
    }
}

class ConfigValueNotFoundException extends \Exception {}
