<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;

class AudioElement extends Element
{

	use SourceAttributeTrait;

	// XXX autoplay, controls, crossorigin, currentTime, disableRemotePlayback (experimental), duration,
	// XXX loop, muted, preload
	public static function getElementTagStatic(): string
	{
		return "audio";
	}
}
