<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

interface AllocationModeInterface{

	function hasAllocationMode(): bool;

	function setAllocationMode(?int $mode): ?int;

	function getAllocationMode(): int;
}
