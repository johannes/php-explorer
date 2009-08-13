<?php
namespace Explorer\Manual;

include('Explorer/Manual/FullTextSearch.php');

class Manual {
	private $descriptors = array(
		'PHP'     => array('lookup_class' => 'Explorer\Manual\PHP_Manual_Lookup',
		                   'basename'     => 'php_manual_'),
		'PHP Gtk' => array('lookup_class' => 'Explorer\Manual\PHP_Gtk_Manual_Lookup',
		                   'basename'     => 'php_gtk_manual_'));

	private $filetypes = array('tar.bz2', 'tar.gz', 'zip');

	/*@var array */
	protected $archives = array();

	/**
	 *
	 * @param string $directory
	 * @param string $language
	 */
	public function __construct($directory, $language) {
		foreach ($this->descriptors as $title => $descriptor) {
			$lookup_class = $descriptor['lookup_class'];
			foreach ($this->filetypes as $filetype) {
				$filename = $directory.DIRECTORY_SEPARATOR.$descriptor['basename'].$language.'.'.$filetype;
				if (file_exists($filename)) {
					try {
						$archive = new \PharData($filename);
						$lookup = new $lookup_class($archive);
						$this->archives[$title] = array(
							'lookup'   => $lookup,
							'filename' => $filename,
							'archive'  => $archive,
						);
						break;
					} catch (\Exception $e) {
						echo 'NOTICE: '.$filename.' seems to be no '.$title." manual.\n";
					}
				}
			}
		}
	}

	/**
	 *
	 * @param Reflector $ref
	 * @return \SplFileObject
	 */
	public function get(\Reflector $ref) {
		$exceptions = array();
		foreach ($this->archives as $title => $archive) {
			try {
				return $archive['lookup']->get($ref);
			} catch (\Exception $e) {
				$exceptions[$title] = $e;
			}
		}

		throw new ManualPageNotFoundException($ref, $exceptions);
	}

	/**
	 *
	 * @return array
	 */
	public function getLoadedManuals() {
		$retval = array();
		foreach ($this->archives as $name => $data) {
			$retval[] = array(
				'title'    => $name,
				'filename' => $data['filename'],
				'archive'  => $data['archive'],
			);
		}
		return $retval;
	}

	/**
	 *
	 * @param string $needle
	 * @param bool $strip_tags
	 * @return array
	 */
	public function searchFulltext($needle, $strip_tags = true) {
		$retval = array();
		foreach ($this->archives as $name => $data) {
			 $retval[$name] = new FullTextSearch($data['archive'], $data['filename'], $needle, $strip_tags);
		}
		return $retval;
	}
}

////////////////////////////////////////////////////////////////////////////////
//                              INTERNAL CLASSES                              //
////////////////////////////////////////////////////////////////////////////////
class NoManualArchiveException extends \Exception {}

class ManualPageNotFoundException extends \Exception {
	/**
	 * @var \Reflector
	 */
	protected $ref;
	/**
	 * @var \Exception
	 */
	protected $orig;
	public function __construct(\Reflector $ref, array $exceptions) {
		parent::__construct(sprintf('No Manual page found for %s (%s) in %s', $ref->getName(), get_class($ref), implode(', ', array_keys($exceptions))), 0, end($exceptions));
		$ref = $ref;
		$this->orig = $exceptions;
	}
}

/* private */ abstract class Lookup {
	protected $archive;
	public function __construct(\PharData $archive) {
		if (!$this->verifyArchive($archive)) {
			throw new NoManualArchiveException();
		}
		$this->archive = $archive;
	}

	abstract public function verifyArchive(\PharData $archive);

	private function makeFilename($name) {
		static $search  = array('__construct', '_');
		static $replace = array('construct',   '-');
		return str_replace($search, $replace, strtolower($name));
	}	

	public function getFunction($func, $class = null) {
		if (!$class) {
			$class = 'function';
		}
		$class = self::makeFilename($class);
		$func = self::makeFilename($func);
		$filename = 'html/'.$class.'.'.$func.'.html';
		return $this->archive[$filename];
	}

	public function getClass($class) {
		$class = self::makeFilename($class);
		$filename = 'html/class.'.$class.'.html';
		return $this->archive[$filename];
	}

	public function getExtension($ext) {
		switch ($ext) {
		case 'bcmath':
			$ext = 'bc';
			break;
		case 'bz2':
			$ext = 'bzip2';
			break;
		default:
			$ext = self::makeFilename($ext);
			break;
		}
		$filename = 'html/ref.'.$ext.'.html';
		return $this->archive[$filename];
	}

	public function get(\Reflector $ref) {
		switch (get_class($ref)) {
		case 'ReflectionFunction':
			return $this->getFunction($ref->getName());
		case 'ReflectionMethod':
			return $this->getFunction($ref->getName(), $ref->getDeclaringClass()->getName());
		case 'ReflectionClass':
			return $this->getClass($ref->getName());
		case 'ReflectionExtension':
			return $this->getExtension($ref->getName());
		default:
			throw new \InvalidArgumentException(get_class($ref).' is not expected');
		}
	}

	public function getArchive() {
		return $this->archive;
	}
}

/* private */ class PHP_Manual_Lookup extends Lookup {
	public function verifyArchive(\PharData $archive) {
		return isset($archive['html/about.html']);
	}
}

/* private */ class PHP_Gtk_Manual_Lookup extends Lookup {
	public function getClass($class) {
		$it = new GtkLookupFilterIterator($this->archive, $class);
		foreach ($it as $match) {
			return $match;
		}
		return parent::getClass($class);
	}

	public function verifyArchive(\PharData $archive) {
		return isset($archive['html/gobject.html']);
	}
}

/* private */ class GtkLookupFilterIterator extends \FilterIterator {
	protected $name;

	public function __construct(\PharData $archive, $name) {
		parent::__construct(new \RecursiveIteratorIterator($archive));
		$this->name = strtolower($name);
	}
	public function accept() {
		/* TODO: Use a better rule ... */
		return strpos(strtolower($this->current()->getFilename()), $this->name) !== false;
	}
}