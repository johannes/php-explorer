<?php
namespace Explorer\Model;

/**
 * Description of PHPItemTree
 *
 * @author johannes
 */
abstract class PHPItemTree extends \GtkTreeStore {
    public function __construct() {
	parent::__construct(\GObject::TYPE_STRING, \GObject::TYPE_PHP_VALUE);
    }
}

