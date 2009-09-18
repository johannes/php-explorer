<?php
namespace Explorer\Model;

/**
 * Description of PHPItemTreeWithFunctions
 *
 * @author johannes
 */
abstract class PHPItemTreeWithFunctions extends PHPItemTree {
    function addFunctions($parent, array $functions) {
        if (!empty($functions['internal'])) {
            $functions = $functions['internal'];
        }
        foreach ($functions as $ref) {
            if (is_string($ref)) {
                $ref = new \ReflectionFunction($ref);
            }
            if (!$ref->isInternal()) {
                return;
            }
            $name = getFunctionString($ref);
            $this->append($parent, array($name, $ref));
        }
    }
}
?>
