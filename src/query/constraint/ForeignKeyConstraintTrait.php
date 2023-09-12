<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\AbstractDatum;

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
		if($ondelete == null) {
			unset($this->onDeleteReferenceOption);
			return null;
		}elseif(!is_string($ondelete)) {
			Debug::error("{$f} reference option must be a string");
		}elseif($this instanceof AbstractDatum) { // setting a reference option implies a constraint
			$this->constrain();
			if($ondelete === REFERENCE_OPTION_SET_NULL) {
				$this->setNullable(true);
			}
		}
		return $this->onDeleteReferenceOption = $ondelete;
	}

	public function hasOnDelete(){
		return isset($this->onDeleteReferenceOption);
	}

	public function getOnDelete(){
		$f = __METHOD__;
		if(!$this->hasOnDelete()) {
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
		if($onupdate == null) {
			unset($this->onUpdateReferenceOption);
			return null;
		}elseif(!is_string($onupdate)) {
			Debug::error("{$f} reference option must be a string");
		}elseif($this instanceof AbstractDatum) { // setting a reference option implies a constraint
			$this->constrain();
			if($onupdate === REFERENCE_OPTION_SET_NULL) {
				$this->setNullable(true);
			}
		}
		return $this->onUpdateReferenceOption = $onupdate;
	}

	public function hasOnUpdate(){
		return isset($this->onUpdateReferenceOption);
	}

	public function getOnUpdate(){
		$f = __METHOD__;
		if(!$this->hasOnUpdate()) {
			Debug::error("{$f} on update is undefined");
		}
		return $this->onUpdateReferenceOption;
	}

	public function onUpdate($onupdate){
		$this->setOnUpdate($onupdate);
		return $this;
	}
}
