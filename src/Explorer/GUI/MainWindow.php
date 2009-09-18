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

    public function __construct(\Explorer\Controller\MainWindowController $controller) {
	$this->controller = $controller;
        $this->glade = $controller->getGlade();
    }

    public function onSelectHandler($selection) {
	$this->controller->onSelectHandler($selection);
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
