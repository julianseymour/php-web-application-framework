<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;

class AttributeSelector extends Selector{

	use NamedTrait;
	use ValuedTrait;

	public static function echoQuotes():void{
		echo "\"";
	}

	public function echoValue():void{
		$this->echoQuotes();
		echo $this->getValue();
		$this->echoQuotes();
	}

	public static function echoOperator():void{
		echo "=";
	}

	public function echo(bool $destroy = false):void{
		echo "[";
		echo $this->getName();
		if($this->hasValue()){
			static::echoOperator();
			$this->echoValue();
		}
		echo "]";
	}

	public function __construct($name = null, $value = null){
		parent::__construct();
		if(isset($name)){
			$this->setName($name);
			if(isset($value)){
				$this->setValue($value);
			}
		}
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
		$this->release($this->value, $deallocate);
	}
}
