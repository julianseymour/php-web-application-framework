<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\TextareaInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;

class PersonalNoteForm extends AjaxForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$this->setGenerateFormButtonsCommandClass(GenerateStaticFormButtonsCommand::class);
		parent::__construct($mode, $context);
		$this->addClassAttribute("note_area");
	}

	public function getFormInputManifest(): ?array{
		return $this->getFormDataIndices();
	}

	public function getFormDataIndices(): ?array{
		return [
			"note" => TextareaInput::class,
			$this->getContext()->getIdentifierName() => HiddenInput::class
		];
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	public function bindContext($context){
		$context = parent::bindContext($context);
		$idn = $context->getIdentifierName();
		$this->setAttribute($idn, new GetColumnValueCommand($context, $idn));
		return $context;
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "personal_note";
	}

	public function reconfigureInput($input): int{
		$vn = $input->getColumnName();
		switch($vn){
			case "note":
				$context = $this->getContext();
				$input->setIdAttribute(GetColumnValueCommand::concatIndex("note_", $context, $context->getIdentifierName()));
				$personal = _("Personal note");
				$optional = _("Optional");
				$placeholder = new ConcatenateCommand($personal, " (", $optional, ")");
				$input->setPlaceholderAttribute($placeholder);
				break;
			default:
		}
		return parent::reconfigureInput($input);
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($name);
				$button->setInnerHTML(_("Save note"));
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}
