<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use Exception;

trait ElementalCommandTrait
{

	protected $element;

	protected $id;

	public function setElement($element)
	{
		$f = __METHOD__; //"ElementalCommandTrait(".static::getShortClass().")->setElement()";
		$print = false;
		if(is_string($element)) {
			if($print) {
				Debug::print("{$f} element is a string");
			}
			$this->setId($element);
		}elseif($element instanceof Element) {
			if($element->hasIdAttribute()) {
				$this->setId($element->getIdAttribute());
			}elseif($element->hasAttribute("temp_id")) {
				$this->setId($element->getAttribute("temp_id"));
			}
			if($print) {
				Debug::print("{$f} element class is " . $element->getClass());
			}
		}elseif($print) {
			$gottype = gettype($element);
			Debug::print("{$f} setting a \"{$gottype}\" as an element");
		}
		return $this->element = $element;
	}

	public function getElement()
	{
		return $this->element;
	}

	public function getIdCommandString()
	{
		$f = __METHOD__; //"ElementalCommandTrait(".static::getShortClass().")->getIdCommandString()";
		try{
			$print = false;
			if($this->hasElement()) {
				if($print) {
					Debug::print("{$f} element is defined");
				}
				$element = $this->getElement();
				if($element instanceof ValueReturningCommandInterface) {
					if($print) {
						Debug::print("{$f} element is a value-returning command interface");
					}
					return $element;
				}elseif(is_object($element) && $element->hasIdOverride()) {
					if($print) {
						Debug::print("{$f} element is an object with ID override");
					}
					return $element->getIdOverride();
				}elseif($print) {
					Debug::print("{$f} element is not a value-returning command interface, and it does not have ID override");
				}
			}elseif($print) {
				Debug::print("{$f} element is undefined");
			}
			return $this->getId();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getId()
	{
		$f = __METHOD__; //"ElementalCommandTrait(".static::getShortClass().")->getId()";
		$print = false;
		if(!$this->hasId()) {
			if($print) {
				Debug::print("{$f} element ID is undefined");
			}
			if($this instanceof InsertElementCommand) {
				Debug::printStackTrace();
			}elseif($this->hasElement()) {
				$element = $this->getElement();
				if($element instanceof ValueReturningCommandInterface) {
					if($print) {
						Debug::print("{$f} element is a value returning command interface");
					}
					// return $element;
				}
				Debug::print("{$f} element is defined");
				$class = $element->getClass();
				Debug::print("{$f} element is class is \"{$class}\"");
				// $element->debugPrintRootElement();
			}else{
				Debug::error("{$f} element is undefined also");
			}
		}
		return $this->id;
	}

	public function setId($id)
	{
		$f = __METHOD__; //"ElementalCommandTrait(".static::getShortClass().")->setId()";
		return $this->id = $id;
	}

	public function hasElement()
	{
		return isset($this->element);
	}

	public function hasId()
	{
		$f = __METHOD__; //"ElementalCommandTrait(".static::getShortClass().")->hasId()";
		return isset($this->id);
		if(isset($this->id)) {
			return true;
		}elseif(!$this->hasElement()) {
			return false;
		}
		$element = $this->getElement();
		if($element instanceof Element) {
			return $element->hasIdAttribute();
		}elseif($element instanceof ConcatenateCommand) {
			Debug::print("{$f} yes, this object has an ID, and it's a concatenate media command");
			return true;
		}
		return false;
	}
}
