<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

interface ChoiceGeneratorInterface
{

	function generateChoices($context): ?array;
}
