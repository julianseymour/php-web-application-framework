<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\command\element\SetLabelHTMLForCommand;

interface ForAttributeInterface
{

	public function setIgnoreForAttribute($ignore): bool;

	public function setForAttribute($for);

	public function getIgnoreForAttribute(): bool;

	public function hasForAttribute(): bool;

	public function getForAttribute();

	public function setForAttributeCommand($for): SetLabelHTMLForCommand;
}
