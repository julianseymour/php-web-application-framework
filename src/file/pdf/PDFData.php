<?php

namespace JulianSeymour\PHPWebApplicationFramework\file\pdf;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\CleartextFileData;

class PDFData extends CleartextFileData{

	protected $element;

	protected $context;

	protected $fileGenerationMode;

	public function getWebFileDirectory():string{
		$dir = '/pdf/';
		if($this->getFileGenerationMode() === FILE_GENERATION_MODE_DOMPDF) {
			$context = $this->getContext();
			$key = $context->getIdentifierValue();
			$datatype = $context->getDataType();
			$dir .= $datatype;
		}else{
			$dir .= "file";
		}
		$dir .= "/{$key}";
		return $dir;
	}

	public final function getMimeType():string{
		return MIME_TYPE_PDF;
	}

	public static function getPrettyClassName():string{
		return _("PDF");
	}

	public static function getPrettyClassNames():string{
		return _("PDFs");
	}

	public function getFileToWrite(){
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function hasContext():bool{
		return isset($this->context);
	}

	public function setContext($context){
		$this->context = $context;
		$this->setFileGenerationMode(FILE_GENERATION_MODE_DOMPDF);
		return $this->getContext();
	}

	public function getContext()
	{
		$f = __METHOD__; //PDFData::getShortClass()."(".static::getShortClass().")->getContext()";
		if(!$this->hasContext()) {
			Debug::error("{$f} context is undefined");
		}
		return $this->context;
	}

	public function hasElement()
	{
		return isset($this->element) && is_object($this->element) && $this->element instanceof Element;
	}

	public function setElement($element)
	{
		$this->element = $element;
		if($element->hasContext()) {
			$this->setContext($element->getContext());
		}else{
			$this->setFileGenerationMode(FILE_GENERATION_MODE_DOMPDF);
		}
		return $this->getElement();
	}

	public function getElement()
	{
		$f = __METHOD__; //PDFData::getShortClass()."(".static::getShortClass().")->getElement()";
		if(!$this->hasElement()) {
			Debug::error("{$f} element is undefined");
		}
		return $this->element;
	}

	public function getFileGenerationMode()
	{
		return $this->fileGenerationMode;
	}

	public function setFileGenerationMode($mode)
	{
		return $this->fileGenerationMode = $mode;
	}

	/**
	 * returns true if the element can be rendered as a PDF, or false if not
	 *
	 * @param Element $element
	 */
	public static function validateDompdfCompatibility(Element $element)
	{
		// list of incompatible tags:
		$incompatible = [
			"button",
			"input",
			"label",
			"main",
			"option",
			"script",
			"select",
			"tbody",
			"textarea",
			"thead"
		];
		if(in_array($element->getElemenTag(), $incompatible, true)) {
			return false;
		}
		if($element->hasChildNodes()) {
			foreach($element->getChildNodes() as $child) {
				if(! static::validateDompdfCompatibility($child)) {
					return false;
				}
			}
		}
		return true;
	}

	public function outputFileToBrowser():void{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($this->getFileGenerationMode() !== FILE_GENERATION_MODE_DOMPDF) {
			parent::outputFileToBrowser();
			return;
		}elseif($this->hasElement()) {
			$element = $this->getElement();
			if($element->getAllocationMode() !== ALLOCATION_MODE_DOMPDF_COMPATIBLE) {
				Debug::error("{$f} child generation mode must be DOMPDF-compatible");
			}
		}elseif(!$this->hasElementClass()) {
			Debug::error("{$f} element class is undefined");
		}else{
			$ec = $this->getElementClass();
			$element = new $ec(ALLOCATION_MODE_DOMPDF_COMPATIBLE, $this->getContext());
		}
		$dompdf = new \Dompdf\Dompdf();
		$html = $element->__toString();
		if($print){
			Debug::print("{$f} about to load the following HTML:");
			Debug::print($html);
		}
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		echo $dompdf->output();
	}
}
