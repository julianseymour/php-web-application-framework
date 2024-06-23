<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\load;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ExportOptionsTrait{

	use FlagBearingTrait;

	protected $columnTerminatorString;
	protected $enclosureCharacter;
	protected $escapeCharacter;
	
	public function setOptionallyEnclosedFlag($value = true){
		return $this->setFlag("optionallyEnclosed", $value);
	}

	public function getOptionallyEnclosedFlag(){
		return $this->getFlag("optionallyEnclosed");
	}

	public function setColumnTerminator($s){
		$f = __METHOD__;
		if(!is_string($s)){
			Debug::error("{$f} column terminator must be a string");
		}elseif($this->hasColumnTerminator()){
			$this->release($this->columnTerminatorString);
		}
		return $this->columnTerminatorString = $this->claim($s);
	}

	public function hasColumnTerminator():bool{
		return isset($this->columnTerminatorString);
	}

	public function getColumnTerminator(){
		$f = __METHOD__;
		if(!$this->hasColumnTerminator()){
			Debug::error("{$f} column terminator is undefined");
		}
		return $this->columnTerminatorString;
	}

	public function columnsTerminatedBy($s){
		$this->setColumnTerminator($s);
		return $this;
	}

	public function setEnclosureCharacter($c){
		$f = __METHOD__;
		if(is_string($c)){
			if(strlen($c) !== 1){
				Debug::error("{$f} string length must be 1");
			}
		}elseif(is_int($c)){
			Debug::error("{$f} to do: convert integers < 255 to char");
		}
		if($this->hasEnclosureCharacter()){
			$this->release($this->enclosureCharacter);
		}
		return $this->enclosureCharacter = $this->claim($c);
	}

	public function hasEnclosureCharacter():bool{
		return isset($this->enclosureCharacter);
	}

	public function getEnclosureCharacter(){
		$f = __METHOD__;
		if(!$this->hasEnclosureCharacter()){
			Debug::error("{$f} enclosure character is undefined");
		}
		return $this->enclosureCharacter;
	}

	public function enclosedBy($c){
		$this->setEnclosureCharacter($c);
		return $this;
	}

	public function optionallyEnclosedBy($c){
		$this->setOptionallyEnclosedFlag(true);
		return $this->enclosedBy($c);
	}

	public function setEscapeCharacter($c){
		$f = __METHOD__;
		if(is_string($c)){
			if(strlen($c) !== 1){
				Debug::error("{$f} string length must be 1");
			}
		}elseif(is_int($c)){
			Debug::error("{$f} to do: convert integers < 255 to char");
		}
		if($this->hasEscapeCharacter()){
			$this->release($this->escapeCharacter);
		}
		return $this->escapeCharacter = $this->claim($c);
	}

	public function hasEscapeCharacter():bool{
		return isset($this->escapeCharacter);
	}

	public function getEscapeCharacter(){
		$f = __METHOD__;
		if(!$this->hasEscapeCharacter()){
			Debug::error("{$f} escape character is undefined");
		}
		return $this->escapeCharacter;
	}

	public function escapedBy($c){
		$this->setEscapeCharacter($c);
		return $this;
	}

	public function hasExportOptions():bool{
		return $this->hasColumnTerminator() || $this->hasEnclosureCharacter() || $this->hasEscapeCharacter();
	}

	public function fieldsTerminatedBy($terminator){
		$this->setColumnTerminator($terminator);
		return $this;
	}

	public function getExportOptions(): string{
		$f = __METHOD__;
		if(!$this->hasExportOptions()){
			Debug::error("{$f} export options are undefined");
		}
		// {FIELDS | COLUMNS}
		$string = " columns";
		// [TERMINATED BY 'string']
		if($this->hasColumnTerminator()){
			$term = single_quote($this->getColumnTerminator());
			$string .= " terminated by {$term}";
			unset($term);
		}
		// [[OPTIONALLY] ENCLOSED BY 'char']
		if($this->hasEnclosureCharacter()){
			if($this->getOptionallyEnclosedFlag()){
				$string .= " optionally";
			}
			$enclosure = single_quote($this->getEnclosureCharacter());
			$string .= " enclosed by {$enclosure}";
			unset($enclosure);
		}
		// [ESCAPED BY 'char']
		if($this->hasEscapeCharacter()){
			$esc = single_quote($this->getEscapeCharacter());
			$string .= " escaped by {$esc}";
			unset($esc);
		}
		return $string;
	}
}