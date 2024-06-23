<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

abstract class ChronometricInput extends InputElement{

	use ListAttributeTrait;
	use ReadOnlyAttributeTrait;
	use RequiredAttributeTrait;
}
