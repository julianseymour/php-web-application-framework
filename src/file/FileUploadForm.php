<?php

namespace JulianSeymour\PHPWebApplicationFramework\file;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\FileInput;

abstract class FileUploadForm extends AjaxForm{

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getDirectives(): ?array{
		return $this->getContext()->isUninitialized() ? [
			DIRECTIVE_UPLOAD
		] : [
			DIRECTIVE_DELETE
		];
	}

	public function getFormDataIndices(): ?array{
		return [
			"originalFilename" => FileInput::class
		];
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		$button = $this->generateGenericButton($name);
		switch($name){
			case DIRECTIVE_UPLOAD:
				break;
			case DIRECTIVE_DELETE:
				$button->setInnerHTML(_("Delete"));
				break;
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
		return [
			$button
		];
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		$vn = $input->getColumnName();
		switch($vn){
			case "originalFilename":
				if($this->getUploadMultipleFlag()){
					$input->setMultipleAttribute(null);
				}
			default:
				return parent::reconfigureInput($input);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"uploadMultiple"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"uploadMultiple"
		]);
	}
	
	public function setUploadMultipleFlag(bool $value = true): bool{
		return $this->setFlag("uploadMultiple", $value);
	}

	public function getUploadMultipleFlag(): bool{
		return $this->getFlag("uploadMultiple");
	}
}
