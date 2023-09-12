<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class GetColumnValueCommand extends ColumnValueCommand{

	protected $format;

	protected $fallbackValue;

	public static function getCommandId(): string{
		return "getColumnValue";
	}

	public function __construct($context = null, $vn = null){
		$f = __METHOD__;
		if(isset($context)) {
			if($context instanceof GetColumnValueCommand) {
				Debug::error("{$f} context is another GetColumnValueCommand");
			}
		}
		parent::__construct($context, $vn);
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"disallowNull"
		]);
	}

	public function setFallbackValue($value){
		if($value === null) {
			unset($this->fallbackValue);
			return null;
		}
		return $this->fallbackValue = $value;
	}

	public function hasFallbackValue():bool{
		return isset($this->fallbackValue);
	}

	public function getFallbackValue(){
		$f = __METHOD__;if(!$this->hasFallbackValue()) {
			Debug::error("{$f} fallback value is undefined");
		}
		return $this->fallbackValue;
	}

	public static function concatIndex($prefix, $context, $index, $suffix = null){
		$cmd = new ConcatenateCommand($prefix, new GetColumnValueCommand($context, $index));
		if(! isset($suffix)) {
			return $cmd;
		}
		return new ConcatenateCommand($cmd, $suffix);
	}

	public function setFormat($i){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($print) {
			Debug::print("{$f} setting format to \"{$i}\"");
		}
		return $this->format = $i;
	}

	public function getFormat(){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if(!$this->hasFormat()) {
			if($print) {
				Debug::print("{$f} format is undefined");
			}
			return READABILITY_READABLE;
		}elseif($print) {
			Debug::print("{$f} returning {$this->format}");
		}
		return $this->format;
	}

	public function hasFormat():bool{
		return isset($this->format);
	}

	public function setDisallowNullFlag(bool $value = true):bool{
		return $this->setFlag("disallowNull", $value);
	}

	public function getDisallowNullFlag():bool{
		return $this->getFlag("disallowNull");
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		try{
			$vn = $this->getColumnName();
			$print = $this->getDebugFlag();
			$context = $this->getDataStructure();
			while ($context instanceof ValueReturningCommandInterface) {
				$class = $context->getClass();
				if($print) {
					$decl = $context->getDeclarationLine();
					Debug::print("{$f} context is a media command of class \"{$class}\", declared {$decl}");
				}
				if($class === static::class) {
					$vn = $this->getColumnName();
					Debug::error("{$f} you probably meant to pass a GetForeignDataStructureCommand; variable name is \"{$vn}\"");
				}
				$context = $context->evaluate();
			}
			while ($vn instanceof ValueReturningCommandInterface) {
				$vn = $vn->evaluate();
			}
			if(!is_object($context)) {
				if($this->hasFallbackValue()) {
					$fb = $this->getFallbackValue();
					if($fb instanceof ValueReturningCommandInterface) {
						while ($fb instanceof ValueReturningCommandInterface) {
							$fb = $fb->evaluate();
						}
					}
					return $fb;
				}
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} context \"{$context}\" is not an object; column name is \"{$vn}\". This was declared {$decl}");
			}
			if($print) {
				Debug::print("{$f} context is now a " . $context->getClass() . "; about to get value of column {$vn}");
			}
			$format = $this->getFormat();
			switch ($format) {
				case READABILITY_WRITABLE:
					if($print) {
						Debug::print("{$f} writable column value");
					}
					$value = $context->getColumn($vn)->getHumanWritableValue();
					break;
				case READABILITY_READABLE:
					$value = $context->getColumn($vn)->getHumanReadableValue();
					if($print) {
						Debug::print("{$f} readable column value");
					}
					break;
				case READABILITY_UNDEFINED:
				default:
					if($print) {
						Debug::print("{$f} undefined formatting");
					}
					$value = $context->getColumnValue($vn);
					break;
			}
			if($print) {
				if(is_string($value)) {
					Debug::print("{$f} before evaluation, value is the string \"{$value}\"");
				}else{
					$gottype = is_object($value) ? $value->getClass() : gettype($value);
					Debug::print("{$f} before evaluation, value is a {$gottype}");
				}
			}
			while ($value instanceof ValueReturningCommandInterface) {
				if($print) {
					Debug::print("{$f} evaluating a " . $value->getClass());
				}
				$value = $value->evaluate();
			}
			if($print) {
				Debug::print("{$f} after evaluation");
				if(is_string($value)) {
					Debug::print("{$f} value is the string \"{$value}\"");
				}else{
					$gottype = is_object($value) ? $value->getClass() : gettype($value);
					Debug::print("{$f} value is a {$gottype}");
				}
			}
			if($value === null && $this->getDisallowNullFlag()) {
				Debug::error("{$f} null value is disallowed");
			}
			return $value;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->fallbackValue);
		unset($this->format);
	}
}
