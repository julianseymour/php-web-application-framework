<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\file\RetrospectiveEncryptedFile;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use Exception;

class EncryptedImageElement extends DivElement implements TemplateElementInterface{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("image_container");
	}

	public static function isTemplateworthy(): bool{
		return true;
	}

	public function bindContext($context){
		$f = __METHOD__;
		$ret = parent::bindContext($context);
		$aspect_ratio = new BinaryExpressionCommand(
			new GetColumnValueCommand($context, "height"),
			OPERATOR_DIVISION, 
			new GetColumnValueCommand($context, "width")
		);
		$padding = new BinaryExpressionCommand($aspect_ratio, OPERATOR_MULT, 100);
		$this->setStyleProperty("padding-top", new ConcatenateCommand("", $padding, "%"));
		return $ret;
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$context = $this->getContext();
			$mode = $this->getAllocationMode();
			$img = new ImageElement($mode);
			$img->setSourceAttribute(new GetColumnValueCommand($context, "webFilePath"));
			$this->appendChild($img);
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function getTemplateContextClass(): string
	{
		return RetrospectiveEncryptedFile::class;
	}
}
