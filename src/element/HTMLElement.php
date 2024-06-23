<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

class HTMLElement extends IntangibleElement{

	public static function getElementTagStatic(): string{
		return "html";
	}

	protected function getSelfGeneratedPredecessors(): ?array{
		return [
			"<!DOCTYPE html>"
		];
	}
}
