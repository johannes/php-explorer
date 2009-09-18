<?php
namespace Explorer\Model;

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FunctionTree
 *
 * @author johannes
 */
class FunctionTree extends PHPItemTreeWithFunctions {
    public function __construct() {
	parent::__construct();
	$this->addFunctions(NULL, get_defined_functions());
    }
}
?>
