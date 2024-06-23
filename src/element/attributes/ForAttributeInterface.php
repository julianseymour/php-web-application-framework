<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\command\element\SetLabelHTMLForCommand;

interface ForAttributeInterface{

	public function setForAttribute($for);

	public function hasForAttribute(): bool;

	public function getForAttribute();

	public function setForAttributeCommand($for): SetLabelHTMLForCommand;
}
