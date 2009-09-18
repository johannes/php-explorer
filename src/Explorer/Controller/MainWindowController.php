<?php
namespace Explorer\Controller;

include 'Explorer/Model/ClassTree.php';
include 'Explorer/GUI/MainWindow.php';
include 'Explorer/GUI/DocViewer.php';

class InitFuncFilterIterator extends \FilterIterator {
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
    protected $mainWindow;
    /**
     *
     * @var \Explorer\Manual\Manual
     */
    protected $manual;

    public function __construct($file) {
        $this->loadGlade($file);
	$this->mainWindow = new \Explorer\GUI\MainWindow($this);
        $status = $this->glade->get_widget('loadingprogress');

        $r = new \ReflectionObject($this);
        $methods = iterator_to_array(new InitFuncFilterIterator(new \ArrayIterator($r->getMethods())));
        $count = count($methods);
        $i = 0;
        foreach ($methods as $method) {
            $this->$method();

            $status->set_pulse_step(++$i/$count);
            while (\Gtk::events_pending()) {
                \Gtk::main_iteration();
            }
        }

        $this->glade->get_widget('loadingwindow')->set_visible(false);
        $this->glade->get_widget('mainwindow')->set_visible(true);
    }

    public function initFunctionList() {
        $store = new \GtkTreeStore(\GObject::TYPE_STRING, \GObject::TYPE_PHP_VALUE);
        addFunctions($store, NULL, get_defined_functions());
        $this->mainWindow->fillFunctionTree($store);
    }

    public function initClassTree() {
        $store = new \Explorer\Model\ClassTree(\GObject::TYPE_STRING, \GObject::TYPE_PHP_VALUE);
        $this->mainWindow->fillClassTree($store);
    }

    public function initExtensionTree() {
        $store = new \GtkTreeStore(\GObject::TYPE_STRING, \GObject::TYPE_PHP_VALUE);
        foreach (get_loaded_extensions() as $ext) {
            $re = new \ReflectionExtension($ext);
            $extensions = $store->append(NULL, array($ext, $re));

            addFunctions($store, $extensions, $re->getFunctions());
            addClasses($store, $extensions, $re->getClasses());
        }
        $this->mainWindow->fillExtensionTree($store);
    }

    public function initBrowser() {
        $container = $this->glade->get_widget('docscrolledwindow');
        if (class_exists('GtkHTML')) {
            $config = \Explorer\Config::getInstance();
            $this->manual = new \Explorer\Manual\Manual($config['datadir'], $config['language']);
            $this->viewer = new \Explorer\GUI\HTMLManualViewer($this->manual);
        } else {
            $this->viewer = new \Explorer\GUI\TextDocViewer();
        }
        $container->add($this->viewer->getWidget());
    }

    private function loadGlade($file) {
        $glade = file_get_contents($file);
        $glade = \GladeXML::new_from_buffer($glade);
         //$glade = new \GladeXML($file);

        $glade->signal_autoconnect_instance($this);
        $this->glade = $glade;
    }

    public function getGlade() {
        return $this->glade;
    }

    public function onAboutClick() {
        $this->glade->get_widget('aboutdialog1')->show();
    }

    public function onFullTextSearchClick() {
        if (!$this->manual) {
            // TODO: One might think aobut using an external browser or the online docs...
            $dialog = new \GtkMessageDialog($this->glade->get_widget('mainwindow'), 0, \Gtk::MESSAGE_ERROR, \Gtk::BUTTONS_OK,
                                  'GtkHTML needed');
            $dialog->set_markup('For doing full text searches GtkHTML support is required in your PHP configuration.');
            $dialog->run();
            $dialog->destroy();
            return;
        }
        $input = trim($this->glade->get_widget('searchentry')->get_text());
        if (strlen($input) == 0) {
            $dialog = new \GtkMessageDialog($this->glade->get_widget('mainwindow'), 0, \Gtk::MESSAGE_ERROR, \Gtk::BUTTONS_OK,
                'No input');
            $dialog->set_markup('No search term entered');
            $dialog->run();
            $dialog->destroy();
            return;
        }
        $results = $this->manual->searchFulltext($input);
        $store = new \GtkTreeStore(\GObject::TYPE_STRING, \GObject::TYPE_PHP_VALUE);
        foreach($results as $title=>$found) {
            $man_container = $store->append(null, array($title, null));
            $basenamelen = strlen('phar://'.$found->getArchiveFileName());
            echo 'phar://'.$found->getArchiveFileName(), "\n";
            foreach($found as $item) {
	        /** @var $item \SplFileObject */
                $doc = \DomDocument::loadHTMLFile($item->getPathname());
                $caption = $doc->getElementsByTagName('title')->item(0)->firstChild->wholeText;
                $store->append($man_container, array($caption, $item));
            }
        }
        $tree = $this->glade->get_widget('searchtreeview');
        $tree->set_model($store);
        $tree->get_selection()->connect('changed', array($this, 'onSelectHandler'));

        $cell_renderer = new \GtkCellRendererText();
        $colExt = new \GtkTreeViewColumn('', $cell_renderer, 'text', 0);
        $tree->append_column($colExt);
    }

    public function onSelectHandler($selection) {
        list($model, $iter) = $selection->get_selected();

        $ref = $model->get_value($iter, 1);
        $text = '';
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
            case 'PharFileInfo':
                /* TODO: Fix architecture */
                if ($this->viewer instanceof \Explorer\GUI\HTMLManualViewer) {
                    $this->viewer->displayString(file_get_contents($ref->getPathinfo()));
                    return;
                }
                throw \Exception("Shouldn't happen - fix the architecture");
                break;
            default:
                $text = "Can't display element of class ".get_class($ref);
                break;
            }
        }

        $this->glade->get_widget('datalabel')->set_text($text);
        if ($ref instanceof \Reflector) {
            $this->viewer->showDocumentation($ref);
        }
    }

}

?>
