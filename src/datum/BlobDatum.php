<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class BlobDatum extends StringDatum
{

	/*
	 * public function getHumanReadableValue(){
	 * if($this->getNeverLeaveServer()){
	 * return null;
	 * }
	 * return htmlspecialchars($this->getValue());
	 * }
	 */
	public function getHumanWritableValue()
	{
		if ($this->getNeverLeaveServer()) {
			return null;
		}
		return $this->getValue();
	}

	public function getUrlEncodedValue()
	{
		return urlencode($this->getValue());
	}

	public function getConstructorParams(): ?array
	{
		return [
			$this->getColumnName()
		];
	}

	public function getColumnTypeString(): string
	{
		return "BLOB";
	}

	/*
	 * public function setDefaultValue($v){
	 * $f = __METHOD__; //BlobDatum::getShortClass()."(".static::getShortClass().")->setDefaultValue()";
	 * $print = false;
	 * if($print){
	 * Debug::print("{$f} ERROR 1101 (42000): BLOB, TEXT, GEOMETRY or JSON column 'languagePreference' can't have a default value");
	 * }
	 * return $v;
	 * }
	 *
	 * public function getDefaultValue(){
	 * return null;
	 * }
	 */
}
