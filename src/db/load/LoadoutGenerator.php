<?php

namespace JulianSeymour\PHPWebApplicationFramework\db\load;

use function JulianSeymour\PHPWebApplicationFramework\use_case;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\paginate\Paginator;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

abstract class LoadoutGenerator extends Basic{

	use ArrayPropertyTrait;
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}
	
	public function getNonRootNodeTreeSelectStatements(DataStructure $ds, ?UseCase $use_case = null):?array{
		return null;
	}

	public function getRootNodeTreeSelectStatements(?PlayableUser $ds = null, ?UseCase $use_case = null):?array{
		return null;
	}

	public function generateNonRootLoadout(DataStructure $ds, ?UseCase $use_case = null): ?Loadout{
		$f = __METHOD__;
		$print = false;
		$dependencies = $this->getNonRootNodeTreeSelectStatements($ds, $use_case);
		if($dependencies){
			if($print){
				Debug::print("{$f} generating a loadout from ".count($dependencies)." relationships");
			}
			return Loadout::generate($dependencies);
		}elseif($print){
			Debug::print("{$f} there are no relationships to load");
		}
		return null;
	}

	public function generateRootLoadout(?PlayableUser $ds = null, ?UseCase $use_case = null): ?Loadout{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered for this ".$this->getDebugString());
		}
		$dependencies = $this->getRootNodeTreeSelectStatements($ds, $use_case);
		if($dependencies){
			if($print){
				Debug::print("{$f} generating a loadout from ".count($dependencies)." relationships");
			}
			return Loadout::generate($dependencies);
		}elseif($print){
			Debug::print("{$f} there are no relationships to load");
		}
		return null;
	}
	
	public function getPaginator():?Paginator{
		return use_case()->getPaginator();
	}
}
