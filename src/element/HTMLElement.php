<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

class HTMLElement extends IntangibleElement
{

	public static function getElementTagStatic(): string
	{
		return "html";
	}

	protected function generatePredecessors(): ?array
	{
		return [
			"<!DOCTYPE html>"
		];
	}
}
