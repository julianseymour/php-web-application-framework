<?php

namespace JulianSeymour\PHPWebApplicationFramework\db\load;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\paginate\Paginator;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

abstract class LoadoutGenerator extends Basic{

	use ArrayPropertyTrait;
	
	protected $paginator;
	
	public function setPaginator(?Paginator $paginator): ?Paginator{
		if ($paginator == null) {
			unset($this->paginator);
			return null;
		}
		return $this->paginator = $paginator;
	}
	
	public function getPaginator(UseCase $use_case): ?Paginator{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasPaginator()) {
			$paginator = new Paginator();
			if($print){
				Debug::printStackTraceNoExit("{$f} instantiating a plain old paginator");
			}
			return $this->setPaginator($paginator);
			Debug::error("{$f} paginator is undefined");
		}
		return $this->paginator;
	}
	
	public function hasPaginator(): bool{
		return isset($this->paginator) && $this->paginator instanceof Paginator;
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
		if ($dependencies) {
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
		$dependencies = $this->getRootNodeTreeSelectStatements($ds, $use_case);
		if ($dependencies) {
			if($print){
				Debug::print("{$f} generating a loadout from ".count($dependencies)." relationships");
			}
			return Loadout::generate($dependencies);
		}elseif($print){
			Debug::print("{$f} there are no relationships to load");
		}
		return null;
	}
}
