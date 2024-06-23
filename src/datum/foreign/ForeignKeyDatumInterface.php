<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum\foreign;

interface ForeignKeyDatumInterface{

	function getForeignDataTypeName(): string;

	function getForeignDataSubtypeName(): ?string;

	function hasForeignDataTypeName(): bool;

	function hasForeignDataSubtypeName(): bool;

	function getForeignDataType(): string;

	function getForeignDataSubtype(): ?string;

	function hasForeignDataType(): bool;

	function hasForeignDataSubtype(): bool;
}
