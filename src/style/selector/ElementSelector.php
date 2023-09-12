<?php
namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

use JulianSeymour\PHPWebApplicationFramework\element\ElementTagTrait;

class ElementSelector extends Selector
{

	use ElementTagTrait;

	private $coselectors;

	public function __construct($tag = null)
	{
		parent::__construct();
		if(isset($tag)) {
			$this->setElementTag($tag);
		}
	}

	public function pushCoselector(...$selectors)
	{
		if(!is_array($this->coselectors)) {
			$this->coselectors = [];
		}
		$i = 0;
		foreach($selectors as $selector) {
			$i += array_push($this->coselectors, $selector);
		}
		return $i;
	}

	/*
	 * public function setElementTag($tag){
	 * return $this->tag = $tag;
	 * }
	 *
	 * public function getElementTag(){
	 * return $this->tag;
	 * }
	 *
	 * public function hasElementTag(){
	 * return isset($this->tag);
	 * }
	 */
	public function echo(bool $destroy = false): void
	{
		if($this->hasElementTag()) {
			echo $this->getElementTag();
		}
		if($this->hasClassAttribute()) {
			foreach($this->classList as $class) {
				echo ".{$class}";
			}
		}
		if($this->hasIdAttribute()) {
			echo "#" . $this->getIdAttribute();
		}
		if(!empty($this->coselectors)) {
			foreach($this->coselectors as $selector) {
				$selector->echo();
			}
		}
		if($destroy) {
			$this->dispose();
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->coselectors);
		unset($this->tag);
	}

	/*
	 * public function pushCoselector(...$selectors){
	 * foreach($selectors as $selector){
	 * $this->pushCoselector($selector);
	 * }
	 * }
	 */
	public function attribute($key, $value = null): ElementSelector
	{
		$this->pushCoselector(new AttributeSelector($key, $value));
		return $this;
	}

	public function attributes(array $keyvalues): ElementSelector
	{
		foreach($keyvalues as $key => $value) {
			$this->pushCoselector(new AttributeSelector($key, $value));
		}
		return $this;
	}

	public function checked(): ElementSelector
	{
		return $this->pseudoclass("checked");
	}

	public function child($chile): ChildSelector
	{
		return new ChildSelector($this, $chile);
	}

	public function descendant($descendant): DescendantSelector
	{
		return new DescendantSelector($this, $descendant);
	}

	public function sibling($sib): SiblingSelector
	{
		return new SiblingSelector($this, $sib);
	}

	public function attributeContains($name, $value): ElementSelector
	{
		$this->pushCoselector(new AttributeContainsSelector($name, $value));
		return $this;
	}

	public function attributesContain(array $keyvalues): ElementSelector
	{
		foreach($keyvalues as $key => $value) {
			$this->pushCoselector(new AttributeContainsSelector($key, $value));
		}
		return $this;
	}

	public function attributeEndsWith($name, $value): ElementSelector
	{
		$this->pushCoselector(new AttributeEndsWithSelector($name, $value));
		return $this;
	}

	public function attributesEndWith(array $keyvalues): ElementSelector
	{
		foreach($keyvalues as $key => $value) {
			$this->pushCoselector(new AttributeEndsWithSelector($key, $value));
		}
		return $this;
	}

	public function attributeStartsWith($name, $value): ElementSelector
	{
		$this->pushCoselector(new AttributeStartsWithSelector($name, $value));
		return $this;
	}

	public function attributesStartWith(array $keyvalues): ElementSelector
	{
		foreach($keyvalues as $key => $value) {
			$this->pushCoselector(new AttributeStartsWithSelector($key, $value));
		}
		return $this;
	}

	public function nextSibling($sib): NextSiblingSelector
	{
		return new NextSiblingSelector($this, $sib);
	}

	public function pseudoclass(...$ps): ElementSelector
	{
		foreach($ps as $p) {
			$this->pushCoselector(new PseudoclassSelector($p));
		}
		return $this;
	}

	public function pseudoelement(...$ps): ElementSelector
	{
		foreach($ps as $p) {
			$this->pushCoselector(new PseudoelementSelector($p));
		}
		return $this;
	}

	public static function element(?string $tag = null): ElementSelector
	{
		return new ElementSelector($tag);
	}

	public static function id($id): ElementSelector
	{
		$s = new ElementSelector();
		$s->setIdAttribute($id);
		return $s;
	}

	public static function elementClass(...$classes): ElementSelector
	{
		$s = new ElementSelector();
		$s->addClassAttribute(...$classes);
		return $s;
	}

	public function not($selector)
	{
		$this->pushCoselector(new NegationSelector($selector));
		return $this;
	}
}
