<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;

interface ValueAttributeInterface
{

	public function getValueAttribute();

	public function setValueAttribute($value);

	public function hasValueAttribute(): bool;

	public function withValueAttribute($value);

	public function setValueAttributeCommand($value): SetInputValueCommand;
}