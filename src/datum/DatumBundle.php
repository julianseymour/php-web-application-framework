<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

/**
 * An object that generates multiple datums with the function geneerateComponents
 *
 * @author j
 *        
 */
abstract class DatumBundle extends AbstractDatum{

	use NamedTrait;

	public abstract function generateComponents(?DataStructure $ds = null): array;

	public function __construct(?string $name = null, ?DataStructure $ds = null){
		parent::__construct();
		if(!empty($name)){
			$this->setName($name);
		}
	}

	public function getArrayKey($i):string{
		return $this->getName();
	}

	public function setComponents(?array $components): ?array{
		return $this->setArrayProperty("components", $components);
	}

	public function hasComponents(): bool{
		return $this->hasArrayProperty("components");
	}

	public function getComponent($name): Datum{
		return $this->getArrayPropertyValue("components", $name);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
	}
}
