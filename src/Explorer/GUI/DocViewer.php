<?php
namespace Explorer\GUI;

abstract class DocViewer {
	protected $widget;
	public function getWidget() {
		return $this->widget;
	}

	abstract public function showDocumentation(\Reflector $r);
}

class TextDocViewer extends DocViewer {
	public function __construct() {
		$this->widget = new \GtkTextView();
		$this->widget->get_buffer()->set_text(<<<EOT
Welcome to the PHP Explorer

For the full experience you need to enable GtkHTML for your
PHP-Gtk Module, please refer to the documentation on
http://gtk.php.net/ for further information.
EOT
);
	}
	public function showDocumentation(\Reflector $r) {
		$this->widget->get_buffer()->set_text((string)$r);
	}
}

class HTMLManualViewer extends DocViewer {
	private $manual;

	public function __construct(\Explorer\Manual\Manual $manual) {
		$this->manual = $manual;
		$this->widget = new \GtkHTML();

		$manuals = $manual->getLoadedManuals();
		if (count($manuals)) {
			$index = '<table><tr><th>Title</th><th>Filename</th><th>Files</th></tr>';
			foreach ($manuals as $m) {
				$index .= '<tr><td>'.htmlentities($m['title']).'</td>'.
					'<td>'.htmlentities($m['filename']).'</td>'.
					'<td>'.count($m['archive']).'</td></tr>';
			}
			$index .= '</table>';
		} else {
			$index = '<p><b>No Manuals found!</b><p><p>Please place some manuals in <i>'.
				getcwd().DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.
				'</i> and restart the explorer!</p>';
		}
		$this->widget->load_from_string(file_get_contents('data/index.html', FILE_USE_INCLUDE_PATH).$index);
		$this->widget->show_all();
	}

	public function showDocumentation(\Reflector $r) {
		try {
			$html_text = $this->manual->get($r)->getContent();
		} catch (\Exception $e) {
			echo $e."\n\n";
			$html_text = '<pre>'.$r.'</pre>';
		}
		$this->widget->load_from_string($html_text);
	}

	public function displayString($content) {
	    $this->widget->load_from_string($content);
	}
}
