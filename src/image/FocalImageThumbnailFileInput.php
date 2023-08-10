<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use Exception;

class FocalImageThumbnailFileInput extends ImageThumbnailFileInput
{

	public static function getThumbnailElement($context)
	{
		$f = __METHOD__;
		try {
			$print = false;
			$span = parent::getThumbnailElement($context);
			// $context = $this->getContext();
			if ($context->getOrientation() !== ORIENTATION_SQUARE) {
				$reticule = new SpanElement();
				$reticule_id = "profile_image_reticule";
				$reticule->setIdAttribute($reticule_id);
				$reticule->addClassAttribute("reticule");
				if ($context->isTall()) {
					$dimension1 = $context->getThumbnailWidth();
					$dimension2 = $context->getThumbnailHeight();
					$position1 = "left";
					$position2 = "top";
				} else {
					$dimension1 = $context->getThumbnailHeight();
					$dimension2 = $context->getThumbnailWidth();
					$position1 = "top";
					$position2 = "left";
				}
				$ratio = ($dimension2 - $dimension1) / $dimension2;
				// Debug::print("{$f} ratio is {$ratio}");
				$min = 0.5 - $ratio / 2;
				$max = 0.5 + $ratio / 2;
				$translate = 100 * ($context->getFocalLineRatio() - 0.5 + ($max - $min) / 2);
				$reticule->setStyleProperties([
					"width" => "{$dimension1}px",
					"height" => "{$dimension1}px",
					$position1 => 0,
					$position2 => "{$translate}px"
				]);
				$reticule->setAllowEmptyInnerHTML(true);
				$span->appendChild($reticule);
			}elseif($print){
				Debug::print("{$f} image is square");
			}
			$div = new DivElement();
			$div->appendChild($span);
			return $div;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
