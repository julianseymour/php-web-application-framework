<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\CompoundElement;
use JulianSeymour\PHPWebApplicationFramework\element\StyleElement;
use JulianSeymour\PHPWebApplicationFramework\input\choice\Choice;
use JulianSeymour\PHPWebApplicationFramework\input\choice\MultipleRadioButtons;
use JulianSeymour\PHPWebApplicationFramework\style\CssRule;
use JulianSeymour\PHPWebApplicationFramework\style\selector\ElementSelector;
use JulianSeymour\PHPWebApplicationFramework\style\selector\PseudoclassSelector;
use Exception;

class ToolBelt extends CompoundElement{

	public function generateComponents(): ?array{
		$f = __METHOD__; //ToolBelt::getShortClass()."(".static::getShortClass().")->generateComponents()";
		try{
			$print = false;
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$none = new Choice("none", "None", true);
			$widgets = mods()->getWidgetClasses();
			$style = new StyleElement();
			$containers = [];
			$count = 0;
			foreach($widgets as $wc) {
				$name = $wc::getWidgetName();
				$cid = "{$name}_widget_container";
				$c = new WidgetContainer(ALLOCATION_MODE_EAGER); // new DivElement($mode);
				$c->setIterator($count ++);
				$c->setWidgetClass($wc);
				// $c->setParentNode($this->getParentNode());
				$c->bindContext($context);
				$icon_class = $wc::getIconClass($context);
				if($icon_class === null){
					if($print){
						Debug::print("{$f} {$wc} has no icon class");
					}
					$index = 0;
				}else{
					if($print){
						Debug::print("{$f} {$wc}'s icon class is \"{$icon_class}\"");
					}
					$index = 1;
				}
				$widget = $c->getChildNode($index);
				if($print) {
					Debug::print("{$f} widget is a " . get_class($widget) . " that was declared on " . $widget->getDeclarationLine());
				}
				// style properties for container ID
				$negative = $wc::getClosedDisplayProperties();
				if(!empty($negative)) {
					$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->not(new PseudoclassSelector("checked"))
						->sibling(ElementSelector::id($cid))
						->child(ElementSelector::id($widget->getIdAttribute())))
						->withStyleProperties($negative));
				}
				$positive = $wc::getOpenDisplayProperties();
				if(!empty($positive)) {
					$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->checked()
						->sibling(ElementSelector::id($cid))
						->child(ElementSelector::id($widget->getIdAttribute())))
						->withStyleProperties($positive));
				}
				// choices for magically generating elements
				$choice = new Choice($name, $name);
				// array_push($choices, $choice);
				// radio buttons
				$button = new MultipleRadioButtons();
				$button->hide();
				$button->setNameAttribute("widget");
				// widget containers
				$c->pushPredecessor($button->generateInput($choice));
				if($icon_class !== null) {
					$icon = $c->getChildNode(0);
					$iid = $icon->getIdAttribute();
					$negative = $icon_class::getClosedDisplayProperties();
					if(!empty($negative)) {
						$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->not(new PseudoclassSelector("checked"))
							->sibling(ElementSelector::elementClass("widget_container"))
							->child(ElementSelector::id($iid)))
							->withStyleProperties($negative));
					}
					$positive = $icon_class::getOpenDisplayProperties();
					if(!empty($positive)) {
						$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->checked()
							->sibling(ElementSelector::elementClass("widget_container"))
							->child(ElementSelector::id($iid)))
							->withStyleProperties($positive));
					}
				}
				// container comes after the icon
				// $c->appendChild($widget);
				array_push($containers, $c);
			}
			return array(
				$style,
				$button->generateInput($none),
				...array_values($containers)
			);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
