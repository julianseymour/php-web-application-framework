<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

interface SoftDeletableInterface
{

	function setSoftDeletionTimestamp(int $value): int;

	function getSoftDeletionTimestamp(): int;

	function ejectSoftDeletionTimestamp(): ?int;

	function hasSoftDeletionTimestamp(): bool;
}