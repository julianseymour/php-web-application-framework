<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;

/**
 * this use case must be executed as the successor to another use case
 * You must set $this->transitionValidated inside validateTransition()
 *
 * @author j
 */
abstract class SubsequentUseCase extends UseCase{

	protected $transitionValidated;

	public function __construct($predecessor = null, $segments = null){
		$this->transitionValidated = false;
		parent::__construct($predecessor, $segments);
	}

	public function getActionAttribute(): ?string{
		return $this->getPredecessor()->getActionAttribute();
	}

	public function getProcessedDataType():?string{
		return $this->getPredecessor()->getProcessedDataType();
	}
	
	public function getDataOperandClass():?string{
		return $this->getPredecessor()->getDataOperandClass();
	}
	
	public function getExecutePermission(){
		if($this->hasPredecessor()){
			return $this->getPredecessor()->getExecutePermission();
		}
		return parent::getExecutePermission();
	}

	protected function getExecutePermissionClass(){
		if($this->hasPredecessor()){
			return $this->getPredecessor()->getExecutePermissionClass();
		}
		return parent::getExecutePermissionClass();
	}

	public function isPageUpdatedAfterLogin(): bool{
		return $this->getPredecessor()->isPageUpdatedAfterLogin();
	}

	public function getLoadoutGeneratorClass(?DataStructure $object = null): ?string{
		if($this->hasPredecessor()){
			return $this->getPredecessor()->getLoadoutGeneratorClass($object);
		}
		return parent::getLoadoutGeneratorClass($object);
	}
	
	public function getLoadoutGenerator(?PlayableUser $user=null):?LoadoutGenerator{
		if($this->hasPredecessor()){
			return $this->getPredecessor()->getLoadoutGenerator($user);
		}
		return parent::getLoadoutGenerator($user);
	}
	
	public function getUriSegmentParameterMap(): ?array{
		return $this->getPredecessor()->getUriSegmentParameterMap();
	}
	
	public function hasImplicitParameter(string $name): bool{
		/*if(!$this->hasPredecessor()){
			return false;
		}*/
		return $this->getPredecessor()->hasImplicitParameter($name);
	}
	
	public function getImplicitParameter(string $name){
		return $this->getPredecessor()->getImplicitParameter($name);
	}
	
	public function URISegmentParameterExists(string $name):bool{
		/*if(!$this->hasPredecessor()){
			return false;
		}*/
		return $this->getPredecessor()->URISegmentParameterExists($name);
	}
}
