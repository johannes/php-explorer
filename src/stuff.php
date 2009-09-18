<?php
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

