<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\input\FileInput;

class ImageThumbnailFileInput extends FileInput
{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setAcceptAttribute("image/png, image/jpeg, image/gif");
	}

	protected function generatePredecessors():?array{
		$f = __METHOD__; //ImageThumbnailFileInput::getShortClass()."(".static::getShortClass().")->generatePredecessors()";
		$print = false;
		$context = $this->getContext();
		$ds = $context->getDataStructure();
		if($ds->isUninitialized()) {
			if($print){
				$decl = $ds->getDeclarationLine();
				Debug::print("{$f} context is uninitialized, instantiated {$decl}");
			}
			return parent::generatePredecessors();
		}elseif($print){
			Debug::print("{$f} context is initialized. You should see a thumbnail when the form renders");
		}
		return [
			$this->getThumbnailElement($ds)
		];
	}

	public static function getThumbnailElement($context)
	{
		$img = new ImageElement();
		$img->setSourceAttribute($context->getWebThumbnailPath());
		$img->setStyleProperty("max-height", THUMBNAIL_MAX_DIMENSION . "px");
		$img->setStyleProperty("max-width", THUMBNAIL_MAX_DIMENSION . "px");
		$span = new SpanElement();
		$span->addClassAttribute("thumbnail_container");
		$span->appendChild($img);
		return $span;
	}
}
