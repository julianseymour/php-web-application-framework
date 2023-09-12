<?php
namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\inline\AnchorElement;
use Exception;

class EmailNotificationElement extends SimpleEmailElement
{
	protected function getBodyElement():Element{
		$f = __METHOD__; //EmailNotificationElement::getShortClass()."(".static::getShortClass().")->getBodyElement()";
		try{
			$mode = $this->getAllocationMode();
			$middle = parent::getBodyElement();
			$context = $this->getContext();
			$prompts = $context->getActionURIPromptMap();
			if(!empty($prompts)) {
				$links = new DivElement($mode);
				$links->setStyleProperties([
					"position" => "relative",
					"display" => "block",
					"width" => "100%",
					"margin-top" => "6px"
				]);
				foreach($prompts as $uri => $prompt) {
					$a = new AnchorElement($mode);
					$a->setHrefAttribute($uri);
					$a->setInnerHTML($prompt);
					$a->setStyleProperties([
						"background-color" => "#0065d1",
						"color" => "#fff",
						"padding" => "6px",
						"font-size" => 14,
						"display" => "inline-block",
						"position" => "relative",
						"border-radius" => "6px"
					]);
					$links->appendChild($a);
				}
				$middle->appendChild($links);
			}
			return $middle;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}

