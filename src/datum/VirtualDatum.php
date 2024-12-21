<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ReturnTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\input\GhostInput;
use Closure;
use Exception;

/**
 * Read-only datum that is not stored in the database and must rely on closures or the data structure for its value
 *
 * @author j
 */
class VirtualDatum extends Datum implements StaticElementClassInterface{

	use ReturnTypeTrait;

	/**
	 * Closure that returns the value indirectly 'contained' by this datum.
	 * This closure has higher priority than getDataStructure->getVirtualColumnValue, if it exists.
	 * Usage not recommended because instantiating closures takes a great deal of memory.
	 * Function signature should expext $this and nothing else.
	 *
	 * @var Closure
	 */
	protected $accessor;

	/**
	 * Similar to the above except the function returns true if the value is defined, false otherwise
	 *
	 * @var Closure
	 */
	protected $existencePredicate;

	/**
	 * Closure that assigns the value
	 *
	 * @var Closure
	 */
	protected $mutator;

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return GhostInput::class;
	}

	public function getHumanWritableValue(){
		return $this->getValue();
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see Datum::getPersistenceMode()
	 */
	public final function getPersistenceMode(): int{
		return PERSISTENCE_MODE_VOLATILE;
	}

	public function parseValueFromSuperglobalArray($value){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getTypeSpecifier(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getHumanReadableValue(){
		return $this->getValue();
	}

	public static function parseString(string $string){
		return $string;
	}

	public static function validateStatic($value): int{
		return SUCCESS;
	}

	public function getUrlEncodedValue(): string{
		return urlencode($this->getValue());
	}

	public function parseValueFromQueryResult($raw){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function hasAccessor(): bool{
		return isset($this->accessor);
	}

	public function setAccessor(?Closure $accessor): ?Closure{
		if($this->hasAccessor()){
			$this->release($this->accessor);
		}
		return $this->accessor = $this->claim($accessor);
	}

	public function getAccessor(): Closure{
		$f = __METHOD__;
		if(!$this->hasAccessor()){
			Debug::error("{$f} accessor is undefined");
		}
		return $this->accessor;
	}

	public function hasExistencePredicate(): bool{
		return isset($this->existencePredicate);
	}

	public function setExistencePredicate(?Closure $existencePredicate): ?Closure{
		if($this->hasExistencePredicate()){
			$this->release($this->existencePredicate);
		}
		return $this->existencePredicate = $this->claim($existencePredicate);
	}

	public function getExistencePredicate(): ?Closure{
		$f = __METHOD__;
		if(!$this->hasExistencePredicate()){
			Debug::error("{$f} existencePredicate is undefined");
		}
		return $this->existencePredicate;
	}

	public function hasMutator(): bool{
		return isset($this->mutator);
	}

	public function setMutator(?Closure $mutator): ?Closure{
		if($this->hasMutator()){
			$this->release($this->mutator);
		}
		return $this->mutator = $this->claim($mutator);
	}

	public function getMutator(): ?Closure{
		$f = __METHOD__;
		if(!$this->hasExistencePredicate()){
			Debug::error("{$f} mutator is undefined");
		}
		return $this->mutator;
	}

	public function getValue(){
		if($this->hasAccessor()){
			$accessor = $this->getAccessor();
			return $accessor($this);
		}
		return $this->getDataStructure()->getVirtualColumnValue($this->getName());
	}

	public function hasValue(): bool{
		if($this->hasExistencePredicate()){
			$existencePredicate = $this->getExistencePredicate();
			return $existencePredicate($this);
		}
		return $this->getDataStructure()->hasVirtualColumnValue($this->getName());
	}

	public function getConstructorParams(): array{
		return [
			$this->getName()
		];
	}

	public function getColumnTypeString(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->accessor, $deallocate);
		$this->release($this->existencePredicate, $deallocate);
		$this->release($this->mutator, $deallocate);
		$this->release($this->returnType, $deallocate);
	}
	
	public function setValueFromQueryResult($raw){
		$f = __METHOD__;
		try{
			$vn = $this->getName();
			$ds = $this->getDataStructure();
			$dsc = $ds->getClass();
			Debug::error("{$f} data structure of class \"{$dsc}\" is storing a virtual datum at index \"{$vn}\"");
			return $this->setValue(null);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
