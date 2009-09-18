<?php

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
