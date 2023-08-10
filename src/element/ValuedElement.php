<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\ValueAttributeInterface;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ValueAttributeTrait;

abstract class ValuedElement extends Element implements ValueAttributeInterface{

	use ValueAttributeTrait;
}
