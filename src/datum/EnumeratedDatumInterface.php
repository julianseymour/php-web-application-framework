<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

interface EnumeratedDatumInterface{

	function setValidEnumerationMap(?array $map): ?array;

	function hasValidEnumerationMap(): bool;

	function getValidEnumerationMap(): array;

	function hasValueLabelStringsMap(): bool;

	function getValueLabelStringsMap(): array;

	function setValueLabelStringsMap(?array $map): ?array;

	function mapLabelStringToValue($value, $ls);
}
