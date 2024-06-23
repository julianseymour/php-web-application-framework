<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

interface ParentNodeInterface{
	
	function hasParentNode(): bool;
	function getParentNode();
	function setParentNode($node);
}