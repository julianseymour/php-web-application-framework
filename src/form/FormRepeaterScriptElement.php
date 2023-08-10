<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\ScriptElement;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

class FormRepeaterScriptElement extends ScriptElement
{

	use ColumnNameTrait;

	protected $superiorFormClass;

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null, ?string $super_form_class = null, ?string $column_name = null)
	{
		if ($super_form_class != null) {
			$this->setSuperiorFormClass($super_form_class);
		}
		if ($column_name != null) {
			$this->setColumnName($column_name);
		}
		parent::__construct($mode, $context);
	}

	public function setSuperiorFormClass(?string $class): ?string
	{
		$f = __METHOD__; //FormRepeaterScriptElement::getShortClass()."(".static::getShortClass().")->setSuperiorFormClass()";
		if ($class == null) {
			unset($this->superiorFormClass);
			return null;
		} elseif (! is_a($class, AjaxForm::class, true)) {
			Debug::error("{$f} superior form class \"{$class}\" is not an AjaxForm");
		}
		return $this->superiorFormClass = $class;
	}

	public function hasSuperiorFormClass(): bool
	{
		return isset($this->superiorFormClass);
	}

	public function getSuperiorFormClass(): string
	{
		$f = __METHOD__; //FormRepeaterScriptElement::getShortClass()."(".static::getShortClass().")->getSuperiorFormClass()";
		if (! $this->hasSuperiorFormClass()) {
			Debug::error("{$f} superior form class is undefined");
		}
		return $this->superiorFormClass;
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //FormRepeaterScriptElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		$print = false;
		$context = $this->getContext();
		$temp_map = [];
		$column_name = $this->getColumnName();
		$datum = $context->getColumn($column_name);
		$fdsc = $datum->getForeignDataStructureClass();
		$temp_struct = new $fdsc();
		$iterator = '${iterator+1}';
		$temp_struct->setIterator($iterator);
		$temp_structs = [
			$iterator => $temp_struct
		];
		$temp_div = new DivElement(ALLOCATION_MODE_TEMPLATE);
		$sfc = $this->getSuperiorFormClass();
		$form = new $sfc(ALLOCATION_MODE_TEMPLATE, $context);
		$temp_map = $form->subindexNestedInputs($context->getColumn($column_name), $temp_structs);
		if ($print) {
			Debug::print("{$f} subindexNestedInputs returned the following items:");
			foreach ($temp_map as $name => $input) {
				Debug::print("{$f} {$name} : " . $input->getClass());
			}
		}
		$temp_div->appendChild(...$form->getInternalFormElements($temp_map));
		if ($temp_div->getChildNodeCount() > 1) {
			Debug::error("{$f} this should only return a single child node");
		}
		$form_class = $form->getInputClass($column_name);
		$generator = new FormRepeaterFunctionGenerator($form_class);
		$repeater = $generator->generate($temp_div->getChildNodes()[0]);
		$this->appendChild($repeater);
		return $this->getChildNodes();
	}
}
