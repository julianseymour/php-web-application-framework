<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\embed;

use JulianSeymour\PHPWebApplicationFramework\element\EmptyElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\MediaAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;

class SourceElement extends EmptyElement
{

	use MediaAttributeTrait;

	// XXX media, sizes, srcset
	use SourceAttributeTrait;
	use TypeAttributeTrait;
}
