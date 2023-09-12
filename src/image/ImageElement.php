<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\EmptyElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\inline\HypertextAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\email\EmbeddedImageData;
use Exception;
use finfo;

class ImageElement extends EmptyElement
{

	use AlternateTextAttributeTrait, HypertextAttributeTrait, SourceAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "img";
	}

	public function hasHrefAttribute()
	{
		if($this->hasSourceAttribute()) {
			return true;
		}
		return $this->hasAttribute("href");
	}

	public function getHrefAttribute()
	{
		if(!$this->hasHrefAttribute() && $this->hasSourceAttribute()) {
			return $this->getSourceAttribute();
		}
		return $this->getAttribute("href");
	}

	public function setSourceAttribute($src)
	{
		$f = __METHOD__; //ImageElement::getShortClass()."(".static::getShortClass().")->setSourceAttribute()";
		try{
			$print = false;
			switch ($this->getAllocationMode()) {
				case ALLOCATION_MODE_DOMPDF_COMPATIBLE: // convert image to base64 URI
					if($print) {
						Debug::print("{$f} generating a PDF");
					}
					$pattern = "(data:image\/(gif|jpeg|png);base64,[\+\/0-9A-Za-z]+={0,2})";
					if(preg_match($pattern, $src)) {
						Debug::print("{$f} src is already a base64 data URI");
						return $this->setAttribute("src", $src);
					}
					$filename = "/var/www/html{$src}";
					// $this->setAttribute("src", $filename);
					// break;
					$finfo = new finfo(FILEINFO_MIME_TYPE);
					$mime_type = $finfo->file($filename);
					switch ($mime_type) {
						case MIME_TYPE_GIF:
						case MIME_TYPE_JPG:
						case MIME_TYPE_PNG:
							break;
						default:
							return Debug::error("{$f} invalid mime type \"{$mime_type}\"");
					}
					if($print) {
						Debug::print("{$f} about to load contents of file \"{$filename}\"");
					}
					$base64 = base64_encode(file_get_contents($filename));
					$this->setAttribute("src", "data:{$mime_type};base64,{$base64}");
					break;
				case ALLOCATION_MODE_EMAIL: // embedded images must be reported to the email that embeds them
					if($print) {
						Debug::print("{$f} reporting an embedded image");
					}
					$cid = $this->reportEmbeddedImage(new EmbeddedImageData($src));
					$this->setAttribute("src", "cid:{$cid}");
					break;
				default:
					$this->setAttribute("src", $src);
					break;
			}
			if(!$this->hasHrefAttribute()) {
				$this->setHrefAttribute($src);
			}
			return $src;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}
}
