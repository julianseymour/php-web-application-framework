<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\DimensionAttributesTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;

class VideoElement extends Element
{

	use DimensionAttributesTrait;
	use SourceAttributeTrait;

	// XXX shared with audio:
	// XXX autoplay, controls, crossorigin, currentTime, disableRemotePlayback (experimental),
	// XXX duration (readonly), loop, muted, preload,

	// XXX autoPictureInPicture (experimental), buffered, controlslist (experimental),
	// XXX disablePictureInPicture (experimental),
	// XXX playsinline, poster
	public static function getElementTagStatic(): string
	{
		return "video";
	}
}
