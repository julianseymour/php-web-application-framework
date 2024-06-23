<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
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
		$f = __METHOD__;
		try{
			$print = false;
			$context = $this->getContext();
			$none = new Choice("none", "None", true);
			$widgets = mods()->getWidgetClasses();
			$style = new StyleElement();
			$containers = [];
			$count = 0;
			foreach($widgets as $wc){
				$name = $wc::getWidgetName();
				$cid = "{$name}_widget_container";
				$c = new WidgetContainer(ALLOCATION_MODE_EAGER); // new DivElement($mode);
				$c->setIterator($count ++);
				$c->setWidgetClass($wc);
				$html_classes = $wc::getContainerClassAttributes();
				if(!empty($html_classes)){
					$c->addClassAttribute(...$html_classes);
				}
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
				$widget = $c->getChildNodeNumber($index);
				if($print){
					Debug::print("{$f} widget is a " . get_class($widget) . " that was declared on " . $widget->getDeclarationLine());
				}
				// style properties for container ID
				$negative = $wc::getClosedDisplayProperties();
				if(!empty($negative)){
					$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->not(new PseudoclassSelector("checked"))->sibling(ElementSelector::id($cid))->child(ElementSelector::id($widget->getIdAttribute())))->withStyleProperties($negative));
				}
				$positive = $wc::getOpenDisplayProperties();
				if(!empty($positive)){
					$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->checked()->sibling(ElementSelector::id($cid))->child(ElementSelector::id($widget->getIdAttribute())))->withStyleProperties($positive));
				}
				// choices for magically generating elements
				$choice = new Choice($name, $name);
				// radio buttons
				$button = new MultipleRadioButtons();
				$button->hide();
				$button->setNameAttribute("widget");
				// widget containers
				$c->pushPredecessor($button->generateInput($choice));
				if(!$this->getTemplateFlag()){
					deallocate($button);
					deallocate($choice);
				}
				if($icon_class !== null){
					$icon = $c->getChildNodeNumber(0);
					$iid = $icon->getIdAttribute();
					$negative = $icon_class::getClosedDisplayProperties();
					if(!empty($negative)){
						$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->not(new PseudoclassSelector("checked"))
							->sibling(ElementSelector::elementClass("widget_container"))
							->child(ElementSelector::id($iid)))
							->withStyleProperties($negative));
					}
					$positive = $icon_class::getOpenDisplayProperties();
					if(!empty($positive)){
						$style->appendChild(CssRule::rule()->withSelectors(ElementSelector::id("widget-{$name}")->checked()->sibling(ElementSelector::elementClass("widget_container"))->child(ElementSelector::id($iid)))->withStyleProperties($positive));
					}
				}
				// container comes after the icon
				// $c->appendChild($widget);
				array_push($containers, $c);
			}
			$button = new MultipleRadioButtons();
			$button->hide();
			$button->setNameAttribute("widget");
			$ret = [
				$style,
				$button->generateInput($none),
				...array_values($containers)
			];
			if(!$this->getTemplateFlag()){
				deallocate($button);
				deallocate($none);
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
