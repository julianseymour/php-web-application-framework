<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\routine;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

abstract class RoutineStatement extends QueryStatement{

	use CommentTrait;
	use NamedTrait;

	public function setLanguageFlag(bool $value): bool{
		return $this->setFlag("language", $value);
	}

	public function getLanguageFlag(): bool{
		return $this->getFlag("language");
	}

	public function setDeterministicFlag(bool $value = true): bool{
		return $this->setFlag("deterministic", $value);
	}

	public function getDeterministicFlag(): bool{
		return $this->getFlag("deterministic");
	}

	protected function getCharacteristics(){
		$string = "";
		// COMMENT 'string'
		if($this->hasComment()){
			$string .= "comment " . single_quote($this->getComment()) . " ";
		}
		// LANGUAGE SQL
		if($this->getLanguageFlag()){
			$string .= "language SQL ";
		}
		// { CONTAINS SQL | NO SQL | READS SQL DATA | MODIFIES SQL DATA }
		if(false){
			// XXX TODO not implemented
		}
		// SQL SECURITY { DEFINER | INVOKER }
		if($this->hasSQLSecurity()){
			$string .= "SQL security " . $this->getSQLSecurity() . " ";
		}
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->comment, $deallocate);
		$this->release($this->name, $deallocate);
	}
}
