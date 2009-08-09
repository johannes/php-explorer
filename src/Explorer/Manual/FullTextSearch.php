<?php
namespace Explorer\Manual;

class FullTextSearch extends \FilterIterator {
	/**
	 * @var \PharData
	 */
	protected $archive_file;
	protected $needle;
	protected $strip_tags;

	public function __construct(\PharData $archive, $archive_file, $needle, $strip_tags = true) {
		$flags = \RecursiveIteratorIterator::LEAVES_ONLY;
		$it = new \RecursiveIteratorIterator($archive, $flags);
		parent::__construct($it);

		$this->archive_file = $archive_file;
		$this->needle = $needle;
		$this->strip_tags = $strip_tags;
	}

	public function accept() {
		$current = $this->current();
		// This is not 100% clean but should be good enough for this case:
		if (strpos($current->getFilename(), '.htm') === false) {
			return false;
		}

		// This is bad for larger files ...
		$content = file_get_contents($current->getPathname());
		if ($this->strip_tags) {
			$content = strip_tags($content);
		}

		return strpos($content, $this->needle) !== false;
	}

	public function getArchiveFileName() {
		return $this->archive_file;
	}
}
