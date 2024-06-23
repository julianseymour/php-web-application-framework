<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

interface DisposableInterface{

	function dispose();
	
	function setDisableDeallocationFlag(bool $value=true):bool;
	
	function getDisableDeallocationFlag():bool;
	
	function disableDeallocation():DisposableInterface;
}
