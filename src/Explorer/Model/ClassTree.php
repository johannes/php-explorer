<?php
namespace Explorer\Model;

class ClassTree extends \GtkTreeStore {
    private $all = array();

    public function __construct() {
	parent::__construct(\GObject::TYPE_STRING, \GObject::TYPE_PHP_VALUE);

        foreach (get_declared_classes() as $c) {
            $this->all[get_parent_class($c)][] = $c;
        }
        $this->build(null, '0');
    }

    private function build($parent, $items) {
        foreach ($this->all[$items] as $item) {
            $ref = new \ReflectionClass($item);
            if ($ref->isUserDefined()) {
                continue;
            }
            $p = $this->append($parent, array($item, $ref));
            if (!empty($this->all[$item])) {
                $this->build($p, $item);
            }
        }
    }

}
?>
