<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

interface TypeSpecificInterface
{

	function setTypeSpecifier(?string $typedef): ?string;

	function hasTypeSpecifier();

	function getTypeSpecifier();

	function withTypeSpecifier(?string $typedef);

	function appendTypeSpecifier(string $s);
}
