<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;


use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\input\RangeInput;
use Exception;

class FocalImageThumbnailForm extends ImageThumbnailForm
{

	// public static function getFormDispatchIdStatic():?string{}
	public static function getActionAttributeStatic(): ?string
	{
		return request()->getRequestURI();
	}

	public function getFormDataIndices(): ?array
	{
		$f = __METHOD__; //FocalImageThumbnailForm::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$print = false;
		if(!$this->hasContext()){
			Debug::error("{$f} context is undefined");
		}
		$context = $this->getContext();
		$indices = parent::getFormDataIndices();
		$mode = $this->getAllocationMode();
		if($mode === ALLOCATION_MODE_FORM || ! $context->isUninitialized()){
			if($print){
				Debug::print("{$f} context is initialized, or we are allocating the form for processing only");
			}
			$indices['originalFilename'] = FocalImageThumbnailFileInput::class;
			if(!$context->isUninitialized() && $context->getOrientation() !== ORIENTATION_SQUARE){
				if($print){
					Debug::print("{$f} context is initialized");
				}
				$indices["focalLineRatio"] = RangeInput::class;
			}elseif($print){
				Debug::print("{$f} context is uninitialized");
			}
		}elseif($print){
			Debug::print("{$f} context is uninitialized and not being allocated for form processing");
		}
		return $indices;
	}

	public static function getNewFormOption(): bool
	{
		return true;
	}

	public function reconfigureInput($input): int
	{
		$f = __METHOD__; //FocalImageThumbnailForm::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try{
			$print = false;
			if(!$input->hasColumnName()){
				return parent::reconfigureInput($input);
			}
			$vn = $input->getColumnName();
			switch($vn){
				case "focalLineRatio":
					$pid = $this->getContext();
					if($pid->isUninitialized()){
						if($print){
							Debug::print("{$f} context is uninitialized");
						}
						return SUCCESS;
					}elseif($pid->getOrientation() === "square"){
						if($print){
							Debug::print("{$f} image has a square aspect ratio");
						}
						return SUCCESS;
					}elseif($print){
						Debug::print("{$f} reconfiguring input");
					}
					// $this = new RangeInput();
					// $this->setIgnoreDatumSensitivity(true);
					// $this->setNameAttribute("focal_line_ratio");
					$input->setStyleProperty("max-width", THUMBNAIL_MAX_DIMENSION . "px");
					$input->setStyleProperty("transform-origin", "left");
					$input->setStepAttribute(0.01);
					$input->setValueAttribute($pid->getFocalLineRatio());
					if($pid->isTall()){
						$input->setStyleProperty("transform", "rotate(90deg) translate(calc(-100% + 1em))");
						$dimension1 = $pid->getThumbnailWidth();
						$dimension2 = $pid->getThumbnailHeight();
						$input->setAttribute("orientation", "portrait");
					}else{
						$input->setStyleProperty("display", "block");
						$dimension1 = $pid->getThumbnailHeight();
						$dimension2 = $pid->getThumbnailWidth();
						$input->setAttribute("orientation", "landscape");
					}
					$reticule = new SpanElement();
					$reticule_id = "profile_image_reticule";
					$reticule->setIdAttribute($reticule_id);
					$reticule->addClassAttribute("reticule");
					$ratio = ($dimension2 - $dimension1) / $dimension2;
					// Debug::print("{$f} ratio is {$ratio}");
					$input->setMinimumAttribute(0.5 - $ratio / 2);
					$input->setMaximumAttribute(0.5 + $ratio / 2);
					$reticule_id = "profile_image_reticule";
					$input->setOnInputAttribute("slideReticule(event, this, '{$reticule_id}')");
					// $span->appendChild($reticule);
					return SUCCESS;
				default:
					return parent::reconfigureInput($input);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
