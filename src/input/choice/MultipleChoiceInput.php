<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\use_case;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\CompoundInput;
use JulianSeymour\PHPWebApplicationFramework\input\MultipleAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\input\MultipleInputsTrait;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\ui\LabelGeneratorTrait;
use Exception;

class MultipleChoiceInput extends CompoundInput{

	use AllFlagTrait;
	use ElementBindableTrait;
	use LabelGeneratorTrait;
	use MultipleAttributeTrait;
	use MultipleChoiceInputTrait;
	use MultipleInputsTrait;

	protected $individualWrapperClass;

	protected $labelElementClass;

	protected $uniqueSuffix;

	protected $valueAttributeNameOverride;

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		if ($mode === ALLOCATION_MODE_ULTRA_LAZY) {
			$mode = ALLOCATION_MODE_LAZY;
		}
		parent::__construct($mode, $context);
		// $this->setAutoLabelFlag(true);
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"autoLabel"
		]);
	}

	public function configure(AjaxForm $form): int{
		$f = __METHOD__;
		$print = false;
		if ($this->hasContext()) {
			$context = $this->getContext();
			if ($context instanceof KeyListDatum) {
				if ($print) {
					Debug::print("{$f} context is a KeyListDatum");
				}
			} elseif ($context->hasValue()) {
				$v = $context->getValue();
				if ($this->hasChoice($v)) {
					$this->select($v);
					if ($print) {
						Debug::print("{$f} selected \"{$v}\"");
					}
				} elseif ($print) {
					Debug::print("{$f} no choice \"{$v}\"");
				}
			} elseif ($print) {
				$class = get_class($context);
				Debug::print("{$f} context is a {$class}, and it does not have a value");
			}
		} elseif ($print) {
			Debug::print("{$f} context is undefined");
		}
		return parent::configure($form);
	}

	public function select(string $name, bool $value = true){
		$this->getChoice($name)->select();
	}

	public function setAutoLabelFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		return $this->setFlag("autoLabel", $value);
	}

	public function getAutoLabelFlag(): bool{
		return $this->getFlag("autoLabel");
	}

	public function autoLabel(bool $value = true): MultipleChoiceInput{
		$this->setAutoLabelFlag($value);
		return $this;
	}

	public function hasUniqueSuffix(): bool{
		return isset($this->uniqueSuffix) && is_string($this->uniqueSuffix) && ! empty($this->uniqueSuffix);
	}

	public function setUniqueSuffix(?string $suffix): ?string{
		if ($suffix === null) {
			unset($this->uniqueSuffix);
			return null;
		}
		return $this->uniqueSuffix = $suffix;
	}

	public function getUniqueSuffix(): string{
		$f = __METHOD__;
		if (! $this->hasUniqueSuffix()) {
			Debug::error("{$f} unique suffix is undefined");
		}
		return $this->uniqueSuffix;
	}

	public function bindContext($context){
		$f = __METHOD__;
		$print = false;
		if (! $this->hasUniqueSuffix()) {
			if ($context instanceof DataStructure || $context instanceof Datum) {
				if ($context instanceof DataStructure) {
					$ds = $context;
				} elseif ($context instanceof Datum) {
					$ds = $context->getDataStructure();
				} else {
					Debug::error("{$f} neither of the above");
				}
				if ($ds->hasIdentifierValue()) {
					$this->setUniqueSuffix($ds->getIdentifierValue());
				}
			} elseif ($print) {
				Debug::print("{$f} context is neither datum nor data structure");
			}
		} elseif ($print) {
			Debug::print("{$f} this object already has a unique suffix");
		}
		return parent::bindContext($context);
	}

	public function hasIndividualWrapperClass(): bool{
		return isset($this->individualWrapperClass) && is_a($this->individualWrapperClass, Element::class, true);
	}

	public function getIndividualWrapperClass(): string{
		$f = __METHOD__;
		if (! $this->hasIndividualWrapperClass()) {
			Debug::error("{$f} individual wrapper class does not exist");
		}
		return $this->individualWrapperClass;
	}

	public function setIndividualWrapperClass(?string $class): ?string{
		$f = __METHOD__;
		if ($class === null) {
			unset($this->individualWrapperClass);
			return null;
		} elseif (! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}
		return $this->individualWrapperClass = $class;
	}

	public function wrapIndividually(?string $class): MultipleChoiceInput{
		$this->setIndividualWrapperClass($class);
		return $this;
	}

	public function hasChoices(): bool{
		return $this->hasArrayProperty("choices");
	}

	public function setChoices($values){
		return $this->setArrayProperty("choices", $values);
	}

	public function getChoices(){
		return $this->getProperty("choices");
	}

	public function getChoice($value){
		return $this->getArrayPropertyValue("choices", $value);
	}

	public function hasChoice($name): bool{
		if(is_bool($name)){
			if($name){
				$name = "true";
			}else{
				$name = "false";
			}
		}
		return $this->hasArrayPropertyKey("choices", $name);
	}

	public function setChoice($key, $value){
		return $this->setArrayPropertyValue("choices", $key, $value);
	}

	public function mergeChoices($values){
		return $this->mergeArrayProperty("choices", $values);
	}

	public function getChoiceCount(): int{
		return $this->getArrayPropertyCount("choices");
	}

	public function pushChoice(...$values): int{
		return $this->pushArrayProperty("choices", ...$values);
	}

	public function getChoiceAtOffset($offset){
		return $this->getArrayPropertyValueAtOffset("choices", $offset);
	}

	public function unshiftChoice(...$values): int{
		return $this->unshiftArrayProperty("choices", ...$values);
	}

	public function setLabelElementClass($class){
		$f = __METHOD__;
		if (! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}
		return $this->labelElementClass = $class;
	}

	public function getLabelElementClass(){
		if (! $this->hasLabelElementClass()) {
			return LabelElement::class;
		}
		return $this->labelElementClass;
	}

	public function hasLabelElementClass(){
		return isset($this->labelElementClass);
	}

	public function hasChoiceGenerator(): bool{
		if (is_a($this->getElementClass(), OptionElement::class, true)) {
			return $this->getParentNode()->hasChoiceGenerator();
		}
		return isset($this->choiceGenerator);
	}

	public function getChoiceGenerator(){
		$f = __METHOD__;
		if (! $this->hasChoiceGenerator()) {
			Debug::error("{$f} choice generator is undefined");
		} elseif (is_a($this->getElementClass(), OptionElement::class, true)) {
			return $this->getParentNode()->getChoiceGenerator();
		}
		return $this->choiceGenerator;
	}

	public function setChoiceGenerator($gen){
		if (is_a($this->getElementClass(), OptionElement::class, true)) {
			return $this->getParentNode()->setChoiceGenerator($gen);
		} elseif ($gen == null) {
			unset($this->choiceGenerator);
			return null;
		}
		return $this->choiceGenerator = $gen;
	}

	public function getChoiceGenerationParameters(){
		return $this->getForm()->getChoiceGenerationParameters($this);
	}

	public function generateChoices(): ?array{
		$f = __METHOD__;
		$print = false;
		if ($this->hasChoiceGenerator()) {
			if ($print) {
				Debug::print("{$f} this object has a choice generator");
			}
			$gen = $this->getChoiceGenerator();
			$params = $this->getChoiceGenerationParameters();
			if ($print) {
				if (isset($params)) {
					if (! $this->getTemplateFlag()) {
						Debug::print("{$f} got the following choice generation parameters");
						Debug::printArray($params);
					} else {
						Debug::print("{$f} template flag is set");
					}
				} else {
					Debug::print("{$f} there are no choice generation parameters for this input");
				}
			}
			$choices = $gen->evaluate($this->getContext(), $params);
		} else {
			if ($print) {
				Debug::print("{$f} this object does not have a choice generator");
			}
			if ($this->hasForm()) {
				if ($print) {
					Debug::print("{$f} this input has a form");
				}
				$choices = $this->getForm()->generateChoices($this);
			} else {
				if ($print) {
					Debug::print("{$f} this input lacks a form");
				}
				$choices = use_case()->generateChoices($this->getContext());
			}
		}
		if(is_array($choices)){
			if(!is_associative($choices)){
				$temp = [];
				foreach ($choices as $i => $choice) {
					$temp[$choice->getValue()] = $choice;
				}
				$choices = $temp;
			}
		}else{
			$choices = [];
		}
		if ($this->hasContext()) {
			$context = $this->getContext();
			if ($context instanceof KeyListDatum) {
				if ($print) {
					Debug::print("{$f} context is a KeyListDatum");
				}
			} elseif ($context->hasValue()) {
				$value = $context->getValue();
				if ($print) {
					$cc = $context->getClass();
					$gottype = is_object($value) ? get_class($value) : gettype($value);
					Debug::print("{$f} context of class \"{$cc}\"  has value \"{$value}\" of type \"{$gottype}\"");
					// Debug::printArray($choices);
				}
				if (is_object($value)) {
					Debug::print("{$f} choice value is an object of class \"" . get_class($value) . "\"");
				}
				if(is_bool($value)){
					if($value){
						$value = "true";
					}else{
						$value = "false";
					}
				}
				if (array_key_exists($value, $choices)) {
					$choices[$value]->select();
				} elseif ($print) {
					Debug::print("{$f} did not generate a choice for value \"{$value}\"");
				}
			} elseif ($print) {
				Debug::print("{$f} context has no value");
			}
		} elseif ($print) {
			Debug::print("{$f} context is undefined");
		}
		return $choices;
	}

	public function generateComponents(){
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$mode = $this->getAllocationMode();
			if (($this->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE) && $this->hasChoiceGenerator()) {
				if ($print) {
					Debug::print("{$f} template flag is set");
				}
				$gen = $this->getChoiceGenerator();
				return [
					new GenerateMultipleChoiceInputCommand($this, $gen)
				];
			} elseif ($print) {
				Debug::print("{$f} no template flags here");
			}
			if (! $this->hasChoices()) {
				$choices = $this->generateChoices();
				if (empty($choices)) {
					if ($print) {
						Debug::warning("{$f} choices returned null");
					}
					return null;
				}
				$this->setChoices($choices);
			} elseif ($print) {
				Debug::print("{$f} choices were already generated");
			}
			return $this->getInputs();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateInput(Choice $choice): Element{
		$f = __METHOD__;
		try {
			$print = false;
			$ec = $this->getElementClass();
			if (is_a($ec, OptionElement::class, true)) {
				return $this->generateOptionElement($choice);
			}
			$mode = $this->getAllocationMode();
			$input = new $ec($mode);
			$name = $this->getNameAttribute();
			$value = $choice->getValue();
			if (is_a($ec, CheckboxInput::class, true)) {
				$input->setNameAttribute("{$name}[{$value}]");
			} elseif (is_a($ec, RadioButtonInput::class, true)) {
				$input->setNameAttribute($name);
			} else {
				Debug::error("{$f} invalid element class \"{$ec}\"");
			}
			if ($value === null || $value === "") {
				if ($print) {
					Debug::print("{$f} value is null or empty string");
				}
			} elseif ($this->hasValueAttributeNameOverride()) {
				$input->setAttribute($this->getValueAttributeNameOverride(), $value);
			} else {
				$input->setValueAttribute($value);
			}
			$input->setIdAttribute($this->getInputIdAttribute($choice));
			if ($choice->hasLabelString()) {
				$ls = $choice->getLabelString();
				if ($print) {
					Debug::print("{$f} choice has the label string \"{$ls}\"");
				}
				switch ($ec) {
					case CheckboxInput::class:
					case RadioButtonInput::class:
						if ($print) {
							Debug::print("{$f} \"{$ec}\" is a bare input without decoration");
						}
						if ($this->hasHiddenAttribute()) {
							if ($print) {
								Debug::print("{$f} this input collection is hidden, skipping label");
							}
							// $this->hide();
							break;
						} elseif (! $this->getAutoLabelFlag()) {
							if ($print) {
								Debug::print("{$f} autolabel flag is not set");
							}
							break;
						} elseif ($print) {
							Debug::print("{$f} this input collection is not hidden; about to decorate bare {$ec}");
						}
						$input->pushSuccessor($this->generateLabelElement($choice));
						break;
					default:
						if ($print) {
							Debug::print("{$f} class \"{$ec}\" is not a bare input");
						}
						$input->setLabelString($ls);
						break;
				}
			} elseif ($print) {
				Debug::print("{$f} choice does not have a label string");
			}
			if ($this->hasClassAttribute()) {
				$input->addClassAttribute($this->getClassAttribute());
			}
			if ($this->hasIndividualWrapperClass()) {
				$wc = $this->getIndividualWrapperClass();
				$input->setWrapperElement(new $wc($mode));
			}
			if ($this->hasHiddenAttribute()) {
				if ($print) {
					$id = $input->getIdAttribute();
					Debug::print("{$f} this input collection is hidden. Hiding input with ID \"{$id}\"");
				}
				$input->hide();
			}
			if ($choice->getSelectedFlag()) {
				$input->select();
			}
			return $input;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateLabelElement(Choice $choice, ?string $label_class = null): Element{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} entered for choice " . $choice->getLabelString().", declared {$decl}");
			}
			if (is_string($choice)) {
				$choice = $this->getChoice($choice);
			}
			$mode = $this->hasAllocationMode() ? $this->getAllocationMode() : ALLOCATION_MODE_LAZY;
			if ($label_class === null) {
				$label_class = $this->getLabelElementClass();
			}
			$label = new $label_class($mode);
			$id = $this->getInputIdAttribute($choice);
			if ($label instanceof LabelElement) {
				$label->setForAttribute($id);
			}
			$label_id = "label-{$id}";
			if ($this->hasForm()) {
				$form = $this->getForm();
				while($form->hasSuperiorForm()){
					$form = $form->getSuperiorForm();
				}
				if ($form->hasIdAttribute()) {
					$form_id = $form->getIdAttribute();
					$label_id .= "-{$form_id}";
				} else {
					Debug::error("{$f} form lacks an ID attribute");
				}
			} elseif ($print) {
				Debug::print("{$f} this input collection is not part of a form");
			}
			$label->setIdAttribute($label_id);
			$ls = $choice->getLabelString();
			if($print){
				Debug::print("{$f} label string is \"{$ls}\"");
			}
			$label->setInnerHTML($ls);
			if ($choice->hasLabelStyleProperties()) {
				$label->setStyleProperties($choice->getLabelStyleProperties());
			}
			if ($this->hasLabelStyleProperties()) {
				$label->setStyleProperties($this->getLabelStyleProperties());
			}
			if ($choice->hasLabelClassAttribute()) {
				$label->addClassAttribute(...$choice->getLabelClassAttribute());
			}
			if ($this->hasLabelClassAttribute()) {
				$label->addClassAttribute(...$this->getLabelClassAttribute());
			}
			return $label;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateOptionElement(Choice $choice): OptionElement{
		$f = __METHOD__;
		$print = false;
		$option = new OptionElement($this->getAllocationMode());
		if ($choice->hasValue()) {
			$value = $choice->getValue();
			if ($print) {
				Debug::print("{$f} choice has value \"{$value}\"");
			}
			$option->setValueAttribute($value);
		} elseif ($print) {
			Debug::print("{$f} choice does not have a value");
		}
		if ($choice->getSelectedFlag()) {
			if ($print) {
				Debug::print("{$f} selected flag is set");
			}
			$option->setSelectedAttribute("selected");
		} elseif ($print) {
			Debug::print("{$f} selected flag is not set");
		}
		$ls = $choice->getLabelString();
		if ($print) {
			Debug::print("{$f} setting inner HTML to \"{$ls}\"");
		}
		$option->setInnerHTML($ls);
		return $option;
	}

	protected static function chooseAll(bool $select = true): Choice{
		$all = new Choice(CONST_ALL, _("All"), $select);
		$all->setAllFlag(true);
		return $all;
	}

	public function getInputs(): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->hasInputs()) {
				return $this->inputs;
			}
			$choices = $this->getChoices();
			if (! is_array($choices)) {
				Debug::error("{$f} this class only accepts arrays for this function");
			}
			$inputs = [];
			if ($this->getAllFlag()) {
				array_unshift($choices, $this->chooseAll());
			}
			foreach ($choices as $choice) {
				while ($choice instanceof ValueReturningCommandInterface) {
					$choice = $choice->evaluate();
				}
				if (! $choice->hasValue()) {
					$decl = $choice->getDeclarationLine();
					Debug::error("{$f} option lacks a keyword; created {$decl}");
				}
				$value = $choice->getValue();
				while ($value instanceof ValueReturningCommandInterface) {
					$value = $value->evaluate();
				}
				$input = $this->generateInput($choice);
				if ($print) {
					Debug::print("{$f} about to map option \"{$value}\"");
				}
				$inputs[$value] = $input;
			}
			if ($print) {
				$count = count($inputs);
				Debug::print("{$f} generated {$count} inputs");
			}
			return $this->setInputs($inputs);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasValueAttributeNameOverride(): bool{
		return isset($this->valueAttributeNameOverride) && is_string($this->valueAttributeNameOverride) && ! empty($this->valueAttributeNameOverride);
	}

	public function setValueAttributeNameOverride(?string $name): ?string{
		if ($name === null) {
			unset($this->valueAttributeNameOverride);
			return null;
		}
		return $this->valueAttributeNameOverride = $name;
	}

	public function getValueAttributeNameOverride(): string{
		if (! $this->hasValueAttributeNameOverride()) {
			return "value";
		}
		return $this->valueAttributeNameOverride;
	}

	public function hasForm(): bool{
		$f = __METHOD__;
		$print = false;
		if ($this->getElementClass() === OptionElement::class) {
			if (parent::hasForm()) {
				if ($print) {
					Debug::print("{$f} parent function returned true");
				}
				return true;
			} elseif ($this->hasParentNode() && $this->getParentNode()->hasForm()) {
				if ($print) {
					Debug::print("{$f} parent node has a form");
				}
				return true;
			} elseif ($print) {
				Debug::print("{$f} neither of the above");
			}
			return false;
		} elseif ($print) {
			Debug::print("{$f} returning parent function");
		}
		return parent::hasForm();
	}

	public function getForm(): FormElement{
		$f = __METHOD__;
		$print = false;
		if ($this->getElementClass() === OptionElement::class) {
			if (! isset($this->form)) {
				if ($print) {
					Debug::print("{$f} form is undefined");
				}
				return $this->getParentNode()->getForm();
			}
		}
		//
		if ($print) {
			Debug::print("{$f} returning parent function");
		}
		return parent::getForm();
	}

	protected function getInputIdAttribute(Choice $choice): string{
		$f = __METHOD__;
		try {
			$print = false;
			$name = $this->getNameAttribute();
			$value = $choice->getValue();
			if ($value === null || $value === "") {
				$value = "none";
			}
			$id = "{$name}-{$value}";
			if ($this->hasForm()) {
				$form = $this->getForm();
				while($form->hasSuperiorForm()){
					$form = $form->getSuperiorForm();
				}
				if ($form->hasIdAttribute()) {
					$form_id = $form->getIdAttribute();
					$id .= "-{$form_id}";
				} else {
					Debug::error("{$f} form lacks an ID attribute");
				}
			} elseif ($print) {
				Debug::print("{$f} this input collection is not part of a form");
			}
			if ($this->hasUniqueSuffix()) {
				$id .= "-" . $this->getUniqueSuffix();
			}
			return $id;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getLabels(string $label_class = LabelElement::class, bool $include_empty = false): array{
		$f = __METHOD__;
		$print = false;
		$labels = [];
		foreach ($this->getChoices() as $choice) {
			$value = $choice->getValue();
			if (! $include_empty && ($value === null || $value === "")) {
				if ($print) {
					Debug::print("{$f} choice's value is null or empty string; skipping label generation");
				}
				continue;
			}
			$labels[$value] = $this->generateLabelElement($choice, $label_class);
		}
		return $labels;
	}

	public function dispose(): void{
		$f = __METHOD__;
		parent::dispose();
		unset($this->choiceGenerator);
		unset($this->individualWrapperClass);
		unset($this->labelClassAttribute);
		unset($this->labelElementClass);
		unset($this->uniqueSuffix);
		unset($this->valueAttributeNameOverride);
	}
}
