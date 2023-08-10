<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

abstract class ChronometricInput extends NumericInput
{

	use ReadOnlyAttributeTrait;
	use RequiredAttributeTrait;
}
