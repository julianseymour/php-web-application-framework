<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\AbstractDatum;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\claim;

/**
 * shared behavior between ForeignKeyConstraint and ForeignKeyDatumTrait
 *
 * @author j
 *        
 */
trait ForeignKeyConstraintTrait{

	protected $onDeleteReferenceOption;

	protected $onUpdateReferenceOption;
	
	public function setOnDelete($ondelete){
		$f = __METHOD__;
		if($this->hasOnDelete()){
			$this->release($this->onDeleteReferenceOption);
		}
		if(!is_string($ondelete)){
			Debug::error("{$f} reference option must be a string");
		}elseif($this instanceof AbstractDatum){ // setting a reference option implies a constraint
			$this->constrain();
			if($ondelete === REFERENCE_OPTION_SET_NULL){
				$this->setNullable(true);
			}
		}
		
		return $this->onDeleteReferenceOption = $this->claim($ondelete);
	}

	public function hasOnDelete():bool{
		return isset($this->onDeleteReferenceOption);
	}

	public function getOnDelete(){
		$f = __METHOD__;
		if(!$this->hasOnDelete()){
			Debug::error("{$f} on delete is undefined");
		}
		return $this->onDeleteReferenceOption;
	}

	public function onDelete($ondelete){
		$this->setOnDelete($ondelete);
		return $this;
	}

	public function setOnUpdate($onupdate){
		$f = __METHOD__;
		if($this->hasOnUpdate()){
			$this->release($this->onUpdateReferenceOption);
		}
		if(!is_string($onupdate)){
			Debug::error("{$f} reference option must be a string");
		}elseif($this instanceof AbstractDatum){ // setting a reference option implies a constraint
			$this->constrain();
			if($onupdate === REFERENCE_OPTION_SET_NULL){
				$this->setNullable(true);
			}
		}
		
		return $this->onUpdateReferenceOption = $this->claim($onupdate);
	}

	public function hasOnUpdate():bool{
		return isset($this->onUpdateReferenceOption);
	}

	public function getOnUpdate(){
		$f = __METHOD__;
		if(!$this->hasOnUpdate()){
			Debug::error("{$f} on update is undefined");
		}
		return $this->onUpdateReferenceOption;
	}

	public function onUpdate($onupdate){
		$this->setOnUpdate($onupdate);
		return $this;
	}
}
