<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\file\FileUploadForm;

class ImageThumbnailForm extends FileUploadForm
{

	public function getFormDataIndices(): ?array
	{
		return [
			"originalFilename" => ImageThumbnailFileInput::class
		];
	}

	/*
	 * public function getImageThumbnailElement(){
	 * $context = $this->getContext();
	 * $img = new ImageElement();
	 * $img->setSourceAttribute($context->getWebThumbnailPath());
	 * $img->setStyleProperty("max-height", THUMBNAIL_MAX_DIMENSION."px");
	 * $img->setStyleProperty("max-width", THUMBNAIL_MAX_DIMENSION."px");
	 * $span = new SpanElement();
	 * $span->addClassAttribute("thumbnail_container");
	 * $span->appendChild($img);
	 * return $span;
	 * }
	 */

	/*
	 * public function generateFormHeader():void{
	 * $f = __METHOD__; //ImageThumbnailForm::getShortClass()."(".static::getShortClass().")->generateFormHeader()";
	 * try{
	 * //$context
	 * $data = $this->getContext();
	 * //$data = $context->getImageData();
	 * if($data->isUninitialized()){
	 * //Debug::print("{$f} image data is uninitialized");
	 * return;
	 * }
	 * $key = $data->getIdentifierValue(); //context->getIdentifierValue();
	 * //Debug::print("{$f} context is not uninitialized; its key is \"{$key}\"");
	 * $img = $this->getImageThumbnailElement();
	 * $this->appendChild($img);
	 * return $img;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */

	/*
	 * public function reconfigureInput($input):int{
	 * $f = __METHOD__; //ImageThumbnailForm::getShortClass()."(".static::getShortClass().")->reconfigureInput()";
	 * try{
	 * $vn = $input->getColumnName();
	 * switch($vn){
	 * case "originalFilename":
	 * //Debug::print("{$f} reconfiguring original filename input");
	 * $input->setAcceptAttribute("image/png, image/jpeg, image/gif");
	 * $context = $this->getContext();
	 * if(!$context instanceof ImageData){
	 * Debug::error("{$f} you should not be here");
	 * }
	 * if($context->isUninitialized()){
	 * //Debug::print("{$f} image data is uninitialized");
	 * return parent::reconfigureInput($input);
	 * }
	 * $key = $context->getIdentifierValue();
	 * //Debug::print("{$f} context is not uninitialized; its key is \"{$key}\"");
	 * $img = $this->getImageThumbnailElement();
	 * //$input->setOnChangeAttribute("handleAttachedFile");
	 * //$input->setOnClickAttribute("attachFileClicked");
	 * $input->setPredecessors([$img]);
	 * default:
	 * return parent::reconfigureInput($input);
	 * }
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public static function getFormDispatchIdStatic(): ?string
	{
		return "image_thumbnail";
	}

	/*
	 * public function bindContext($context){
	 * $f = __METHOD__; //ImageThumbnailForm::getShortClass()."(".static::getShortClass().")->bindContext()";
	 * try{
	 * if($context->isUninitialized()){
	 * $key = sha1(random_bytes(32));
	 * }else{
	 * $key = $context->getColumnValueCommand($context->getIdentifierName());
	 * }
	 * $dispatch_id = static::getFormDispatchIdStatic();
	 * $id = new ConcatenateCommand("{$dispatch_id}-", $key);
	 * $this->setIdAttribute($id);
	 * return parent::bindContext($context);
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public static function getActionAttributeStatic(): ?string
	{
		$f = __METHOD__; //ImageThumbnailForm::getShortClass()."(".static::getShortClass().")::getActionAttributeStatic()";
		return null; // ErrorMessage::unimplemented($f);
	}

	public static function getNewFormOption(): bool
	{
		return true;
	}
}
