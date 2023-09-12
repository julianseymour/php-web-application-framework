<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\IteratorTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use Exception;

trait RepeatingFormTrait{

	public abstract function getContext();

	public abstract function getInternalFormElementsHelper(array $inputs): ?array;

	public abstract function getAllocationMode(): int;

	public abstract function getSuperiorForm(): AjaxForm;

	public abstract function getSuperiorFormIndex();

	public abstract function getTemplateFlag(): bool;

	use FlagBearingTrait;
	use IteratorTrait;

	public static function getNewFormOption(): bool{
		return true;
	}

	public function getInternalFormElements(array $inputs): ?array{
		$f = __METHOD__;
		$print = false;
		$mode = $this->getTemplateFlag() ? ALLOCATION_MODE_TEMPLATE : $this->getAllocationMode();
		$container = new DivElement($mode);
		$container->setStyleProperty("border", "1px solid");
		$container->setStyleProperty("padding", "1rem");
		if($this->getLastChildFlag()) {
			if($print) {
				Debug::print("{$f} last child flag is set -- about to generate replication button");
			}
			$container->pushSuccessor($this->createRepeaterButton());
		}elseif($print) {
			Debug::print("{$f} last child flag is undefined");
		}
		$iterator = $this->getIterator();
		if($print) {
			Debug::print("{$f} iterator is \"{$iterator}\"");
		}
		if($this->getTemplateFlag()) {
			if($print) {
				Debug::print("{$f} template flag is set");
			}
			$parent_id = new GetDeclaredVariableCommand("button.form.id");
		}else{
			if($print) {
				Debug::print("{$f} no template flag");
			}
			$parent_id = $this->getSuperiorForm()->getIdAttribute();
		}
		$id = new ConcatenateCommand("selfReplicatingSubordinateFormInputContainer-", $parent_id, "-", $iterator);
		$id->setEscapeType(ESCAPE_TYPE_STRING);
		$id->setQuoteStyle(QUOTE_STYLE_BACKTICK);
		$container->setIdAttribute($id);
		$container->appendChild(...$this->getInternalFormElementsHelper($inputs));
		$container->appendChild($this->createDeleteButton($container));
		return [
			$container
		];
	}

	protected function createDeleteButton($parent_node){
		$f = __METHOD__;
		try{
			$print = false;
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$button = new ButtonInput($mode);
			$button->setTypeAttribute(INPUT_TYPE_SUBMIT);
			$button->setNameAttribute("directive"); // DIRECTIVE_DELETE_FOREIGN);
			$sfi = $this->getSuperiorFormIndex();
			$value = $context->getIdentifierValueCommand();
			$directive = DIRECTIVE_DELETE_FOREIGN;
			$name = new ConcatenateCommand("directive[{$directive}][", $sfi, "]");
			$button->setInnerHTML(_("Delete"));
			$fade = null;
			if($parent_node !== null) {
				$fade = "fadeElementById(this.parentNode.id);return false;";
			}elseif($print) {
				Debug::print("{$f} parent node is null");
			}
			if($this->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE) {
				if($print) {
					Debug::print("{$f} template flag is set");
				}
			}else{
				if($print) {
					Debug::print("{$f} no template flag");
				}
			}
			if($context->hasObjectStatus()) {
				$button->resolveTemplateCommand(
					$button->setNameAttributeCommand($name), 
					new SetInputValueCommand($button, $value)
				);
				$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
			}else{
				$button->setOnClickAttribute($fade);
			}
			return $button;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setLastChildFlag($value = true): bool{
		return $this->setFlag("lastChild", $value);
	}

	public function getLastChildFlag(): bool{
		return $this->getFlag("lastChild");
	}

	private function createRepeaterButton(): ButtonInput{
		$mode = $this->getAllocationMode();
		$button = new ButtonInput($mode);
		$button->setInnerHTML('+');
		$button->setAttribute("iterator", $this->getIterator());
		$form_class = get_short_class($this);
		$button->setOnClickAttribute("repeat{$form_class}(event, this)");
		return $button;
	}
}
