<?php
namespace Explorer\Model;
/**
 * Description of ExtensionTree
 *
 * @author johannes
 */
class ExtensionTree extends PHPItemTreeWithFunctions {
    public function __construct() {
        parent::__construct();

        foreach (get_loaded_extensions() as $ext) {
            $re = new \ReflectionExtension($ext);
            $extensions = $this->append(NULL, array($ext, $re));

            $this->addFunctions($extensions, $re->getFunctions());
            $this->addClasses($extensions, $re->getClasses());
        }
    }

    function addClasses($parent, array $classes) {
        foreach ($classes as $rc) {
            if (is_string($rc)) {
                $rc = new \ReflectionClass($rc);
            }
            if (!$rc->isInternal()) {
                return;
            }
            $class = $this->append($parent, array($rc->getName(), $rc));
            $this->addFunctions($class, $rc->getMethods());
        }
    }
}
?>
