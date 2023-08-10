<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\GhostElementInterface;

class GhostButton extends ButtonInput implements GhostElementInterface
{

	public function echo(bool $destroy = false): void
	{
		return;
	}

	public function echoJson(bool $destroy = false): void
	{
		return;
	}

	public function skipJson(): bool
	{
		return true;
	}

	public function bindContext($context)
	{
		if ($context instanceof Datum && $context->hasColumnName()) {
			$this->setColumnName($context->getColumnName());
		}
		return $this->context = $context;
	}

	/*
	 * public function getTemplateFunctionCommands($parent_name, &$counter){
	 * return null;
	 * }
	 */
}
