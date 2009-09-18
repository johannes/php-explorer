<?php
namespace Explorer\GUI;
/**
 * Description of MainWindow
 *
 * @author johannes
 */
class MainWindow {
    /*
    * @var \Explorer\MainWindow\MainWindowController
    */
    private $controller;
    private $glade;
    private $viewer;

    public function __construct(\Explorer\Controller\MainWindowController $controller) {
	$this->controller = $controller;
        $this->glade = $controller->getGlade();
	$this->initBrowser();
    }

    public function initBrowser() {
        $container = $this->glade->get_widget('docscrolledwindow');
        if (class_exists('GtkHTML')) {
            $config = \Explorer\Config::getInstance();
	    $manual = $this->controller->getManual();
            $this->viewer = new \Explorer\GUI\HTMLManualViewer($manual);
        } else {
            $this->viewer = new \Explorer\GUI\TextDocViewer();
        }
        $container->add($this->viewer->getWidget());
    }

    public function viewerCanHTML() {
	return $this->viewer->canHTML();
    }

    public function show() {
	$this->glade->get_widget('mainwindow')->set_visible(true);
    }

    public function showDocumentation(\Reflector $r) {
	$this->viewer->showDocumentation($r);
    }

    public function onSelectHandler($selection) {
	list($model, $iter) = $selection->get_selected();

        $ref = $model->get_value($iter, 1);
	if ($ref) {
	    $this->controller->showElementInfo($ref);
	}
    }

    function fillFunctionTree($store) {
	$this->fillTreeView('functiontreeview', $store);
    }

    function fillExtensionTree($store) {
	$this->fillTreeView('extensiontreeview', $store);
    }

    function fillClassTree($store) {
	$this->fillTreeView('classtreeview', $store);
    }

    private function fillTreeView($treeview, $store) {
        $tree = $this->glade->get_widget($treeview);

        $store->set_sort_column_id(0, \Gtk::SORT_ASCENDING);
        $tree->set_model($store);
        $tree->get_selection()->connect('changed', array($this, 'onSelectHandler'));

        $cell_renderer = new \GtkCellRendererText();
        $colExt = new \GtkTreeViewColumn('', $cell_renderer, 'text', 0);
        $tree->append_column($colExt);
    }
}
?>
