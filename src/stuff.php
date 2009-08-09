<?php
include 'Explorer/GUI/DocViewer.php';

class InitFuncFilterIterator extends FilterIterator {
	public function current() {
		return parent::current()->getName();
	}
	public function accept() {
		return strpos($this->current(), 'init') === 0;
	}
}

class MainWindowController {
	protected $glade;
	protected $viewer;

	public function __construct($file) {
		$this->loadGlade($file);

		$r = new ReflectionObject($this);
		foreach (new InitFuncFilterIterator(new ArrayIterator($r->getMethods())) as $method) {
			$this->$method();
		}
	}

	public function initExtensionTree() {
		$store = new GtkTreeStore(GObject::TYPE_STRING, GObject::TYPE_PHP_VALUE);
		foreach (get_loaded_extensions() as $ext) {
			$re = new ReflectionExtension($ext);
			$extensions = $store->append(NULL, array($ext, $re));

			addFunctions($store, $extensions, $re->getFunctions());
			addClasses($store, $extensions, $re->getClasses());
		}
		fillTreeView($this, $this->glade, 'extensiontreeview', $store);
	}

	public function initBrowser() {
		$container = $this->glade->get_widget('docscrolledwindow');
		if (class_exists('GtkHTML')) {
			$manual = new \Explorer\Manual\Manual('data', 'en');
			$this->viewer = new \Explorer\GUI\HTMLManualViewer($manual);
		} else {
			$this->viewer = new \Explorer\GUI\TextDocViewer();
		}
		$container->add($this->viewer->getWidget());
	}

	private function loadGlade($file) {
		$glade = file_get_contents($file);
		$glade = GladeXML::new_from_buffer($glade);
		//$glade = new GladeXML($file);

		$glade->signal_autoconnect_instance($this);
		$this->glade = $glade;
	}

	public function getGlade() {
		return $this->glade;
	}

	public function onAboutClick() {
		$this->glade->get_widget('aboutdialog1')->show();
	}

	public function onSelectHandler($selection) {
		list($model, $iter) = $selection->get_selected();

		$ref = $model->get_value($iter, 1);
		if ($ref) {
			switch(get_class($ref)) {
			case 'ReflectionClass':
				$text = $ref->getName();
				if ($ext = $ref->getExtension()) {
					$text = $ext->getName().' | '.$text;
				}
				break;
			case 'ReflectionMethod':
				$text = $ref->getDeclaringClass()->getName().getFunctionString($ref);
				if ($ext = $ref->getExtension()) {
					$text = $ext->getName().' | '.$text;
				}
				break;
			case 'ReflectionFunction':
				$text = getFunctionString($ref);
				if ($ext = $ref->getExtension()) {
					$text = $ext->getName().' | '.$text;
				}
				break;
			case 'ReflectionExtension':
				$text = $ref->getName();
				break;
			}
		}

		$this->glade->get_widget('datalabel')->set_text($text);
		$this->viewer->showDocumentation($ref);
	}

}

function addClasses(GtkTreeStore $store, $parent, array $classes) {
	foreach ($classes as $rc) {
		if (is_string($rc)) {
			$rc = new ReflectionClass($rc);
		}
		if (!$rc->isInternal()) {
			return;
		}
		$class = $store->append($parent, array($rc->getName(), $rc));
		addFunctions($store, $class, $rc->getMethods());
	}
}
function getParamString(ReflectionFunctionAbstract $rf) {
	$params = array();
	foreach ($rf->getParameters() as $param) {
		$p = '$'.$param->getName();
		if ($param->isPassedByReference()) {
			$p = '&'.$p;
		}
		if ($param->isDefaultValueAvailable()) {
			$p = ' = '.var_export($param->getDefaultValue(), true);
		}
		$params[] = $p;
	}
	return implode(', ', $params);
}

function getFunctionString(ReflectionFunctionAbstract $ref) {
	$name = $ref->getName().'(';
	if ($ref instanceof ReflectionMethod) {
		if ($ref->isStatic()) {
			$name = '::'.$name;
		} else {
			$name = '->'.$name;
		}
	}
	$name .= getParamString($ref);
	$name .= ')';
	return $name;
}

function addFunctions(GtkTreeStore $store, $parent, array $functions) {
	if (!empty($functions['internal'])) {
		$functions = $functions['internal'];
	}
	foreach ($functions as $ref) {
		if (is_string($ref)) {
			$ref = new ReflectionFunction($ref);
		}
		if (!$ref->isInternal()) {
			return;
		}
		$name = getFunctionString($ref);
		$store->append($parent, array($name, $ref));
	}
}

function fillTreeView($main, GladeXML $glade, $treeview, $store) {
	$tree = $glade->get_widget($treeview);

	$store->set_sort_column_id(0, Gtk::SORT_ASCENDING);
	$tree->set_model($store);
	$tree->get_selection()->connect('changed', array($main, 'onSelectHandler'));

	$cell_renderer = new GtkCellRendererText();
	$colExt = new GtkTreeViewColumn('', $cell_renderer, 'text', 0);
	$tree->append_column($colExt);
}

