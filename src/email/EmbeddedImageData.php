<?php
namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\array_remove_key;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\image\ImageData;
use finfo;

/**
 * locally-sourced image for embedding into HTML emails
 *
 * @author j
 */
class EmbeddedImageData extends ImageData
{

	protected $src;

	protected $webFileDirectory;

	public function __construct($src = null)
	{
		parent::__construct();
		if ($src !== null) {
			$this->setSourceAttribute($src);
		}
	}

	public function setSourceAttribute($src)
	{
		$f = __METHOD__; //EmbeddedImageData::getShortClass()."(".static::getShortClass().")->setSourceAttribute()";
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime_type = $finfo->file("/var/www/html{$src}");
		switch ($mime_type) {
			case MIME_TYPE_JPEG:
			case MIME_TYPE_PNG:
			case MIME_TYPE_GIF:
				$this->setMimeType($mime_type);
				break;
			default:
				Debug::error("{$f} invalid MIME type \"{$mime_type}\"");
				return null;
		}
		$splat = explode('/', $src);
		$this->setFilename($splat[count($splat) - 1]);
		$sliced = array_remove_key($splat, count($splat) - 1);
		$this->setWebFileDirectory(implode('/', $sliced));
		return $this->src = $src;
	}

	public function hasSourceAttribute()
	{
		return isset($this->src);
	}

	public function getSourceAttribute()
	{
		$f = __METHOD__; //EmbeddedImageData::getShortClass()."(".static::getShortClass().")->getSourceAttribute()";
		if (! $this->hasSourceAttribute()) {
			Debug::error("{$f} src attribute is undefined");
		}
		return $this->src;
	}

	public function hasWebFileDirectory()
	{
		return isset($this->webFileDirectory);
	}

	public function getWebFileDirectory()
	{
		$f = __METHOD__; //EmbeddedImageData::getShortClass()."(".static::getShortClass().")->getWebFileDirectory()";
		if (! $this->hasWebFileDirectory()) {
			Debug::error("{$f} web file directory is undefined");
		}
		return $this->webFileDirectory;
	}

	public function setWebFileDirectory($dir)
	{
		return $this->webFileDirectory = $dir;
	}

	public static function getImageTypeStatic()
	{
		return IMAGE_TYPE_EMBEDDED;
	}

	/**
	 * Returns a string
	 *
	 * @return string
	 */
	public function embed()
	{
		$dir = $this->getFullFileDirectory();
		$filename = $this->getFilename();
		$mime_type = $this->getMimeType();
		$chunks = chunk_split(base64_encode(file_get_contents("{$dir}/{$filename}")));
		$eol = "\r\n";
		$cid = $this->getIdentifierValue();
		$embed = "Content-Type:{$mime_type};name=\"{$cid}\"{$eol}";
		$embed .= "Content-Transfer-Encoding:base64{$eol}";
		$embed .= "Content-ID:<{$cid}>{$eol}{$eol}";
		$embed .= "{$chunks}{$eol}";
		return $embed;
	}
}
