<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait EnumeratedDatumTrait{

	protected $validEnumerationMap;

	protected $valueLabelStringsMap;

	public function setValidEnumerationMap(?array $map): ?array{
		if($map === null) {
			unset($this->validEnumerationMap);
			return null;
		}
		return $this->validEnumerationMap = $map;
	}

	public function hasValidEnumerationMap(): bool{
		$f = __METHOD__;
		$print = false;
		if($print) {
			$cn = $this->getName();
			if(!is_array($this->validEnumerationMap)) {
				$gottype = gettype($this->validEnumerationMap);
				Debug::print("{$f} column {$cn}'s enumeration map is a {$gottype}");
			}elseif(empty($this->validEnumerationMap)) {
				Debug::print("{$f} column {$cn}'s enumeration map is empty");
			}else{
				Debug::print("{$f} returning true");
			}
		}
		return is_array($this->validEnumerationMap) && ! empty($this->validEnumerationMap);
	}

	public function getValidEnumerationMap(): array{
		$f = __METHOD__;
		if(!$this->hasValidEnumerationMap()) {
			$vn = $this->getName();
			Debug::error("{$f} valid enumeration map is undefined for datum \"{$vn}\"");
		}
		return $this->validEnumerationMap;
	}

	protected function validateEnumeration($value): int{
		$f = __METHOD__;
		try{
			$print = false;
			$vn = $this->getName();
			if($this->getAlwaysValidFlag()) {
				if($print) {
					Debug::print("{$f} this datum accepts all values");
				}
				return SUCCESS;
			}elseif($this->hasValidEnumerationMap()) {
				$valid = $this->getValidEnumerationMap();
				if($valid == null) {
					Debug::error("{$f} valid enumeration map is undefined for variable \"{$vn}\"");
				}elseif(in_array($value, $valid, true)) {
					if($print) {
						Debug::print("{$f} value \"{$value}\" is valid");
					}
					$checked = false;
					foreach($valid as $checkme) {
						if($checkme === $value) {
							$checked = true;
						}elseif($print) {
							Debug::print("{$f} \"{$checkme}\" is not the value");
						}
					}
					if(!$checked) {
						Debug::error("{$f} like hell it is");
					}
					return SUCCESS;
				}
				Debug::printStackTrace("{$f} value \"{$value}\" is not in the array");
				return FAILURE;
			}
			$dsc = $this->getDataStructureClass();
			Debug::print($this->validEnumerationMap);
			Debug::error("{$f} datum \"{$vn}\" lacks valid enumerations in class \"{$dsc}\"");
			return FAILURE;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasValueLabelStringsMap(): bool{
		return isset($this->valueLabelStringsMap) && is_array($this->valueLabelStringsMap) && ! empty($this->valueLabelStringsMap);
	}

	public function getValueLabelStringsMap(): array{
		$f = __METHOD__;
		if(!$this->hasValueLabelStringsMap()) {
			Debug::error("{$f} value => label strings map is undefined");
		}
		return $this->valueLabelStringsMap;
	}

	public function setValueLabelStringsMap(?array $map): ?array{
		if($map === null) {
			unset($this->valueLabelStringsMap);
			return null;
		}
		return $this->valueLabelStringsMap = $map;
	}

	public function mapLabelStringToValue($value, $ls){
		if(! isset($this->valueLabelStringsMap) || ! is_array($this->valueLabelStringsMap)) {
			$this->valueLabelStringsMap = [];
		}
		return $this->valueLabelStringsMap[$value] = $ls;
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->validEnumerationMap);
		unset($this->valueLabelStringsMap);
	}
}
