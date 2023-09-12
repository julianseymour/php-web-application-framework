<?php
namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\image\ImageElement;
use Exception;

class EmailHeaderElement extends DivElement
{

	// XXX need to detect the recipient's theme settings
	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //EmailHeaderElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try{
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$recipient = $context->getRecipient();
			$logo1 = new ImageElement($mode);
			$logo1->setCacheKey(EmailHeaderElement::class . "_attached"); // this is getting cached so the chunk_split function doesn't have to be called repeatedly for every single email that gets sent
			$logo1->setStyleProperties([
				"height" => "50px"
			]);
			// $theme = $recipient->getTheme();
			// $theme_class = mods()->getThemeClass($theme);
			/*
			 * switch($theme){
			 * case THEME_DARK:
			 * if(defined("WEBSITE_LOGO_URI_HORIZONTAL_DARK")){
			 * $uri = WEBSITE_LOGO_URI_HORIZONTAL_DARK;
			 * }else{
			 * $uri = null;
			 * }
			 * break;
			 * case THEME_DEFAULT:
			 * case THEME_LIGHT:
			 * if(defined("WEBSITE_LOGO_URI_HORIZONTAL_LIGHT")){
			 * $uri = WEBSITE_LOGO_URI_HORIZONTAL_LIGHT;
			 * }else{
			 * $uri = null;
			 * }
			 * break;
			 * default:
			 * Debug::error("{$f} invalid theme \"{$theme}\"");
			 * }
			 * $theme_data = new $theme_class();
			 */
			$uri = WEBSITE_LOGO_URI_HORIZONTAL_DARK;
			$this->setStyleProperties([
				"height" => "50px",
				"background-color" => "#000", // $theme_data->getBackgroundColor1(),
				"padding" => "8px"
			]);
			if(is_object($uri)) {
				Debug::error("{$f} URI is an object of class " . get_class($uri) . ", declared " . $uri->getDeclarationLine());
			}elseif(is_string($uri)) {
				if(file_exists($uri)) {
					$logo1->setSourceAttribute($uri);
					$this->appendChild($logo1);
				}else{
					Debug::warning("{$f} file does not exist");
					$this->setAllowEmptyInnerHTML(true);
				}
			}
			return $this->getChildNodes();
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
