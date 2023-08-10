<?php
namespace JulianSeymour\PHPWebApplicationFramework\style;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use Exception;

class CssRule extends Element
{

	// protected $StyleProperties;

	// protected $selectors;
	// protected $properties;

	/*
	 * public function pushProperty($property){
	 * if(!is_array($this->properties)){
	 * $this->properties = [];
	 * }
	 * array_push($this->properties, $property);
	 * return $property;
	 * }
	 */

	/*
	 * public function pushProperties(...$properties){
	 * $f = __METHOD__; //CssRule::getShortClass()."(".static::getShortClass().")->pushProperties()";
	 * ErrorMessage::deprecated($f);
	 * return $this->pushArrayProperty("properties", ...$properties);
	 * foreach($properties as $p){
	 * $this->pushProperty($p);
	 * }
	 * return SUCCESS;
	 * }
	 */
	public static function rule(): CssRule
	{
		return new CssRule();
	}

	public function pushSelector(...$selectors)
	{
		return $this->pushArrayProperty("selectors", ...$selectors);
		/*
		 * foreach($selectors as $s){
		 * $this->pushSelector($s);
		 * }
		 * return SUCCESS;
		 */
	}

	/*
	 * public function pushSelector($selector){
	 * if(!is_array($this->selectors)){
	 * $this->selectors = [];
	 * }
	 * array_push($this->selectors, $selector);
	 * return $selector;
	 * }
	 */
	public function echoJson(bool $destroy = false): void
	{
		echo json_encode($this->__toString());
		if ($destroy) {
			$this->dispose();
		}
	}

	/*
	 * public function dispose():void{
	 * parent::dispose();
	 * unset($this->properties);
	 * unset($this->selectors);
	 * }
	 */

	/*
	 * public function setStyleProperties(?array $keyvalues):?array{
	 * if($keyvalues == null){
	 * unset($this->StyleProperties);
	 * return null;
	 * }elseif(!$this->hasStyleProperties()){
	 * return $this->StyleProperties = $keyvalues;
	 * }
	 * $this->StyleProperties = [];
	 * foreach($keyvalues as $key => $value){
	 * $this->StyleProperties[$key] = $value;
	 * }
	 * return $keyvalues;
	 * //return $this->StyleProperties = $values; //setArrayProperty("properties", $values);
	 * }
	 *
	 * public function setStyleProperty($key, $value){
	 * if(!$this->hasStyleProperties()){
	 * $this->style = [];
	 * }
	 * if($value === null){
	 * $this->style = array_remove_key($this->style, $key);
	 * return null;
	 * }
	 * $this->style[$key] = $value;
	 * return $value;
	 * }
	 *
	 * public function getStyleProperties(){
	 * $f = __METHOD__; //CssRule::getShortClass()."(".static::getShortClass().")->getStyleProperties()";
	 * if(!$this->hasStyleProperties()){
	 * Debug::error("{$f} CSS properties are undefined");
	 * }
	 * return $this->StyleProperties; //getArrayProperty("properties");
	 * }
	 *
	 * public function hasStyleProperties(){
	 * return isset($this->StyleProperties) && is_array($this->StyleProperties); //$this->hasArrayProperty("properties");
	 * }
	 */
	public function setSelectors(...$selectors)
	{
		if (count($selectors) == 1 && is_array($selectors[0])) {
			$selectors = $selectors[0];
		} /*
		   * elseif(count($selectors) > 1){
		   * $selectors = [...$selectors];
		   * }
		   */
		return $this->setArrayProperty("selectors", $selectors);
	}

	public function withSelectors(...$selectors): CssRule
	{
		$this->setSelectors($selectors);
		return $this;
	}

	public function getSelectors()
	{
		return $this->getProperty("selectors");
	}

	public function hasSelectors()
	{
		return $this->hasArrayProperty("selectors");
	}

	/*
	 * public function withStyleProperties($keyvalues){
	 * $this->setStyleProperties($keyvalues);
	 * return $this;
	 * }
	 */
	public function echo(bool $destroy = false): void
	{
		$f = __METHOD__; //CssRule::getShortClass()."(".static::getShortClass().")->echo()";
		try {
			$print = false;
			$i = 0;
			if (! $this->hasSelectors()) {
				$dl = $this->getDeclarationLine();
				if ($this->hasStyleProperties()) {
					$properties = $this->getStyleProperties();
					if (! is_array($properties)) {
						$decl = $this->getDeclarationLine();
						Debug::error("{$f} properties is not an array. Declared {$decl}");
					} elseif ($print) {
						Debug::print("{$f} printing properties");
						Debug::printArray($properties);
					}
				} else {
					Debug::error("{$f} selectors and properties are both undefined; declared at {$dl}");
				}
				Debug::error("{$f} selectors are undefined; declared at {$dl}");
			}
			foreach ($this->getSelectors() as $selector) {
				if ($i > 0) {
					echo ",";
				}
				if (is_string($selector)) {
					echo $selector;
				} elseif (is_object($selector)) {
					$selector->echo();
				} else {
					Debug::error("{$f} selector is neither string nor object");
				}
				$i ++;
			}
			if ($destroy) {
				// unset($this->selectors);
				$this->setSelectors(null);
			}
			echo "{\n";
			foreach ($this->getStyleProperties() as $property_name => $property) {
				if ($property instanceof CssProperty) {
					$property->echo();
				} else {
					echo "\t{$property_name}:{$property};\n";
				}
			}
			if ($destroy) {
				// unset($this->properties);
				$this->setStyleProperties(null);
			}
			echo "}\n";
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/*
	 * public function dispose():void{
	 * parent::dispose();
	 * unset($this->StyleProperties);
	 * }
	 */
}