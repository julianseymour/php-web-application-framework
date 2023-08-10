<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

/**
 * this element has only global attributes
 *
 * @author j
 *        
 */
class MainElement extends Element
{

	/*
	 * public function __construct($mode=ALLOCATION_MODE_UNDEFINED, $context=null){
	 * parent::__construct($mode, $context);
	 * $this->setFlag("requireClassAttribute", true);
	 * }
	 */
	public static function getElementTagStatic(): string
	{
		return "main";
	}
}
