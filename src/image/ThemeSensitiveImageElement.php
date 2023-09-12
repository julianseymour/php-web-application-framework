<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * compound element consisting of two images, one for light theme and the other for dark
 *
 * @author j
 */
class ThemeSensitiveImageElement extends CompoundImageElement
{

	protected $darkThemeImageURI;

	protected $lightThemeImageURI;

	public function setDarkThemeImageURI($src)
	{
		return $this->darkThemeImageURI = $src;
	}

	public function hasDarkThemeImageURI()
	{
		return isset($this->darkThemeImageURI);
	}

	public function getDarkThemeImageURI()
	{
		$f = __METHOD__; //ThemeSensitiveImageElement::getShortClass()."(".static::getShortClass().")->getDarkThemeImageURI()";
		if(!$this->hasDarkThemeImageURI()) {
			Debug::error("{$f} dark theme image URI is undefined");
		}
		return $this->darkThemeImageURI;
	}

	public function hasLightThemeImageURI()
	{
		return isset($this->lightThemeImageURI);
	}

	public function getLightThemeImageURI()
	{
		$f = __METHOD__; //ThemeSensitiveImageElement::getShortClass()."(".static::getShortClass().")->getLightThemeImageURI()";
		if(!$this->hasLightThemeImageURI()) {
			Debug::error("{$f} dark theme image URI is undefined");
		}
		return $this->lightThemeImageURI;
	}

	public function setLightThemeImageURI($src)
	{
		return $this->lightThemeImageURI = $src;
	}

	public function generateComponents()
	{
		$f = __METHOD__; //ThemeSensitiveImageElement::getShortClass()."(".static::getShortClass().")->generateComponents()";
		$mode = $this->getAllocationMode();
		$dark = new ImageElement($mode);
		$dark->setSourceAttribute($this->getDarkThemeImageURI());
		$dark->setAttribute("theme", "dark");
		$dark->setStyleProperties([
			"max-height" => "100%",
			"max-width" => "100%"
		]);
		$light = new ImageElement($mode);
		$light->setSourceAttribute($this->getLightThemeImageURI());
		$light->setAttribute("theme", "light");
		$light->setStyleProperties([
			"max-height" => "100%",
			"max-width" => "100%"
		]);
		foreach([
			$dark,
			$light
		] as $img) {
			if($this->hasAlternateTextAttribute()) {
				$img->setAlternateTextAttribute($this->getAlternateTextAttribute());
			}
			if($this->hasHrefAttribute()) {
				$img->setHrefAttribute($this->getHrefAttribute());
			}
			if($this->hasStyleProperties()) {
				foreach($this->getStyleProperties() as $property => $value) {
					$img->setStyleProperty($property, $value);
				}
			}
		}
		return $this->setComponents([
			'dark' => $dark,
			'light' => $light
		]);
	}
}