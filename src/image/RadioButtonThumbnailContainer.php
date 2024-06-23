<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::deprecated(__FILE__);

class RadioButtonThumbnailContainer extends RadioButtonInput{

	protected function getSelfGeneratedSuccessors(): array{
		$f = __METHOD__;
		$value = $this->getValueAttribute();
		if(empty($value)){
			$span = new SpanElement();
			$span->setAllowEmptyInnerHTML(true);
			$span->setInnerHTML("Nothing");
			return [
				$span
			];
		}
		$ds = $this->getMutex()->getContext()->getDataStructure();
		$tree_name = ImageData::getPhylumName();
		$images = $ds->hasForeignDataStructureList($tree_name) ? $ds->getForeignDataStructureList($tree_name) : [];
		foreach($images as $image){
			if($image->getIdentifierValue() === $this->getValueAttribute()){
				Debug::print("{$f} value match -- about to print thumbnail");
				$img = new ImageElement();
				$img->setSourceAttribute($image->getWebThumbnailPath());
				$img->setStyleProperty("max-width", THUMBNAIL_MAX_DIMENSION . "px");
				$img->setStyleProperty("max-height", THUMBNAIL_MAX_DIMENSION . "px");
				return [
					$img
				];
			}
		}
		Debug::error("{$f} did not find the image we were looking for");
	}
}
