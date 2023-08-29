<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\ui\LabelGeneratorTrait;

class Choice extends Basic{

	use AllFlagTrait;
	use ArrayPropertyTrait;
	use LabelGeneratorTrait;
	use ValuedTrait;

	protected $labelString;

	protected $wrapperClass;

	public function __construct($value = null, $labelString = null, $select = false){
		$f = __METHOD__;
		$print = false;
		parent::__construct();
		if ($value !== null) {
			$this->setValue($value);
		}
		if ($labelString !== null) {
			$this->setLabelString($labelString);
		}
		if ($select !== false) {
			if (is_bool($select) && $select) {
				$this->select();
			} elseif (gettype($select) === gettype($value) && $select === $value) {
				$this->select();
			}
		}
		if ($print) {
			Debug::print("{$f} new choice {$value} => {$labelString}");
		}
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"all",
			"selected"
		]);
	}

	public function setSelectedFlag(bool $value = true): bool{
		return $this->setFlag("selected", $value);
	}

	public function getSelectedFlag(): bool{
		return $this->getFlag("selected");
	}

	public function select(bool $value = true): Choice{
		$this->setSelectedFlag($value);
		return $this;
	}

	public function deselect(): Choice{
		$this->setSelectedFlag(false);
		return $this;
	}

	public function hasWrapperClass(){
		return ! empty($this->wrapperClass);
	}

	public function getWrapperClass(){
		$f = __METHOD__;
		if (! $this->hasWrapperClass()) {
			Debug::error("{$f} wrapper class is undefined");
		}
		return $this->wrapperClass;
	}

	public function setWrapperClass($class){
		return $this->wrapperClass = $class;
	}

	public function setLabelString($ls){
		$f = __METHOD__;
		if ($ls === null) {
			unset($this->labelString);
			return null;
		}
		return $this->labelString = $ls;
	}

	public function hasLabelString(): bool{
		return isset($this->labelString);
	}

	public function getLabelString(){
		$f = __METHOD__;
		if (! $this->hasLabelString()) {
			Debug::error("{$f} label string is undefined");
		}
		return $this->labelString;
	}
}
