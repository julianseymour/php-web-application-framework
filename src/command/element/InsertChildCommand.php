<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class InsertChildCommand extends InsertElementCommand implements JavaScriptInterface, ServerExecutableCommandInterface
{

	protected $parentNode;

	public function __construct($insert_here, ...$inserted_elements)
	{
		$f = __METHOD__; //InsertChildCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($insert_here, ...$inserted_elements);
		if($insert_here instanceof Element) {
			$this->setParentNode($insert_here);
		}
	}

	public function setParentNode($p)
	{
		return $this->parentNode = $p;
	}

	public function hasParentNode()
	{
		return isset($this->parentNode);
	}

	public function getParentNode()
	{
		$f = __METHOD__; //InsertChildCommand::getShortClass()."(".static::getShortClass().")->getParentNode()";
		if(!$this->hasParentNode()) {
			Debug::error("{$f} parent node is undefined");
		}
		return $this->parentNode;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->parentNode);
	}
}