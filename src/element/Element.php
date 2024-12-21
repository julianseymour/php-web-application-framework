<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\double_quote;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mutual_reference;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\resolve_template_command;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\TryCatchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\ColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AddClassCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\BindElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\DeleteElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\ScrollIntoViewCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetStylePropertiesCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnBlurCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnClickCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnFocusCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnKeyDownCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnKeyUpCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\IndirectParentScopeTrait;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\VariableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ContextualTrait;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\email\SpamEmail;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterRenderEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeRenderEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseChildNodeEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseDocumentFragmentEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseSubcommandCollectorEvent;
use JulianSeymour\PHPWebApplicationFramework\form\RepeatingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\input\InputlikeInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\Attribute;
use JulianSeymour\PHPWebApplicationFramework\script\DocumentFragment;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptClass;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementFunctionGenerator;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateFlagTrait;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use function JulianSeymour\PHPWebApplicationFramework\get_file_line;

/**
 * Approximation of an HTML DOM Element.
 * There are three methods of rendering an Element which should all result in the same end product:
 * 1. echoElement, which prints it as a string to the output buffer. This is always used when initially rendering the page.
 * 2. echoJson, which outputs a serialized array that can be converted into an element with the JS hydrate function.
 * 3. generateTemplateFunction, which generates a function that creates the element in JavaScript. This one requires a significant amount of work to use, including generating all context-sensitive attributes and inner HTML using template function commands, and overriding the isTemplateworthy function to return true.
 *
 * @author j
 *        
 */
class Element extends Basic 
implements 
AllocationModeInterface, 
ArrayKeyProviderInterface, 
CacheableInterface, 
DisposableInterface, 
EchoJsonInterface, 
IncrementVariableNameInterface, 
ParentNodeInterface,
ReplicableInterface,
ScopedCommandInterface{

	use AllocationModeTrait;
	use ArrayPropertyTrait;
	use CacheableTrait;
	use ContextualTrait;
	use ElementTagTrait;
	use EchoJsonTrait;
	use IndirectParentScopeTrait;
	use InnerHTMLTrait;
	use ParentNodeTrait;
	use ReplicableTrait;
	use TemplateFlagTrait;
	use UriTrait;
	use VariableNameTrait;

	protected $attributes;

	/**
	 * This is used in insertChild to know when to echo commas for spontaneously generating JSON in ultra-lazy rendering mode
	 * @var unknown
	 */
	private $childElementCount;

	protected $childNodes;

	protected $classList;

	private $documentFragment;

	protected $embeddedImageCollector;

	protected $replacementId;

	protected $responsiveStyleProperties;

	protected $savedChildren;

	protected $style;

	protected $subcommandCollector;

	protected $wrapperElement;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		$print = false;
		if($print){
			$mem = memory_get_usage();
			Debug::print("{$f} memory at construction: {$mem}; rendering mode \"{$mode}\"");
		}
		if($this->hasClassList()){
			Debug::error("{$f} classList has already been initialized");
		}
		$this->setAllowEmptyInnerHTML(false);
		Debug::incrementElementConstructorCount();
		parent::__construct($mode, $context);
		$this->setAllocationMode($mode);
		if(isset($context)){
			$this->bindContext($context);
		}
		$status = $this->afterConstructorHook($mode, $context);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} afterConstructorHook returned error status \"{$err}\"");
			$this->setObjectStatus($status);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"allowEmptyInnerHTML",
			"catchReportedSubcommands",
			"contentsGenerated",
			"deletePredecessors",
			"deleteSuccessors",
			"deleted",
			"disableRendering",
			"dispatched",
			"disposeContext",
			// "fileCache",
			"htmlCacheable", // some elements can be cached at JSON but not HTML
			"noUpdate",
			"predecessorsGenerated",
			"preservePredecessors",
			"preserveSuccessors",
			"requireClassAttribute",
			"template",
			"templateLoop"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"allowEmptyInnerHTML",
			//"catchReportedSubcommands",
			"deletePredecessors",
			"deleteSuccessors",
			"disableRendering",
			"htmlCacheable", //some elements can be cached at JSON but not HTML
			"noUpdate",
			"preservePredecessors",
			"preserveSuccessors",
			"template"
		]);
	}
	
	protected static function getExcludedConstructorFunctionNames():?array{
		return array_merge(parent::getExcludedConstructorFunctionNames(), ["createElement"]);
	}
	
	public function getIdOverride(){
		return $this->getVariableName();
	}
	
	public function setIdOverride($id){
		return $this->setVariableName($id);
	}
	
	public function hasIdOverride():bool{
		return $this->hasVariableName();
	}
	
	public static function isUltraLazyRenderingCompatible(): bool{
		return true;
	}

	/**
	 * this is a workaround for ultra lazy rendering, do not delete
	 *
	 * @param Element[] ...$children
	 */
	public function saveChild(...$children): void{
		$mode = $this->getAllocationMode();
		if($mode !== ALLOCATION_MODE_ULTRA_LAZY){
			$this->appendChild(...$children);
			return;
		}elseif(!isset($this->savedChildren) || !is_array($this->savedChildren)){
			$this->savedChildren = [];
		}
		$this->claim($children);
		array_push($this->savedChildren, ...$children);
	}

	public function hasSavedChildren(): bool{
		return isset($this->savedChildren) && is_array($this->savedChildren) && !empty($this->savedChildren);
	}

	public function appendSavedChildren(bool $deallocate = true){
		if($this->hasSavedChildren()){
			$this->appendChild(...$this->savedChildren);
			if($deallocate){
				$this->release($this->savedChildren, $deallocate);
			}
		}
	}

	public function getSavedChildren(){
		$f = __METHOD__;
		if(!$this->hasSavedChildren()){
			Debug::error("{$f} saved children are undefined");
		}
		return $this->savedChildren;
	}

	protected function afterConstructorHook(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null): int{
		$params = [];
		if($mode !== null){
			$params['allocationMode'] = $mode;
		}
		if($context !== null){
			$params['context'] = $context;
		}
		return SUCCESS;
	}

	public function setEnterKeyHintAttribute($value){
		return $this->setAttribute('enterkeyhint', $value);
	}

	public function hasEnterKeyHintAttribute(): bool{
		return $this->hasAttribute('enterkeyhint');
	}

	public function getEnterKeyHintAttribute(){
		$f = __METHOD__;
		if(!$this->hasEnterKeyHintAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('enterkeyhint');
	}

	public function setDraggableAttribute($value = "true"){
		return $this->setAttribute('draggable', $value);
	}

	public function hasDraggableAttribute(): bool{
		return $this->hasAttribute('draggable');
	}

	public function draggable($value = "true"): Element{
		$this->setDraggableAttribute($value);
		return $this;
	}

	public function getDraggableAttribute(){
		$f = __METHOD__;
		if(!$this->hasDraggableAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('draggable');
	}

	public function setOnScrollAttribute($value){
		return $this->setAttribute("onscroll", $value);
	}

	public function hasOnScrollAttribute(): bool{
		return $this->hasAttribute("onscroll");
	}

	public function getOnScrollAttribute(){
		return $this->getAttribute("onscroll");
	}

	public function onScroll($value): Element{
		$this->setOnScrollAttribute($value);
		return $this;
	}

	public function setDirectionalityAttribute($value){
		return $this->setAttribute('dir', $value);
	}

	public function hasDirectionalityAttribute(): bool{
		return $this->hasAttribute('dir');
	}

	public function direction($value): element{
		$this->setDirectionalityAttribute($value);
		return $this;
	}

	public function getDirectionalityAttribute(){
		$f = __METHOD__;
		if(!$this->hasDirectionalityAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('dir');
	}

	public function setContentEditableAttribute($value = "true"){
		return $this->setAttribute("contenteditable", $value);
	}

	public function hasContentEditableAttribute(): bool{
		return $this->hasAttribute("contenteditable");
	}

	public function getContentEditableAttribute(){
		$f = __METHOD__;
		if(!$this->hasContentEditableAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('contenteditable');
	}

	public function setAutocapitalizeAttribute($value){
		return $this->setAttribute("autocapitalize", $value);
	}

	public function hasAutocapitalizeAttribute(): bool{
		return $this->getAttribute("autocapitalize");
	}

	public function getAutocapitalizeAttribute(){
		$f = __METHOD__;
		if(!$this->hasAutocapitalizeAttribute()){
			Debug::error("{$f} autocapitalize attribute is undefined");
		}
		return $this->getAttribute("autocapitalize");
	}

	public function setAccessKeyAttribute($value){
		return $this->setAttribute("accesskey", $value);
	}

	public function hasAccessKeyAttribute(): bool{
		return $this->hasAttribute("accesskey");
	}

	public function getAccessKeyAttriubute(){
		$f = __METHOD__;
		if(!$this->hasAccessKeyAttribute()){
			Debug::error("{$f} access key attribute is undefined");
		}
		return $this->getAttribute("accesskey");
	}

	public function setItemScopeAttribute($value){
		return $this->setAttribute('itemscope', $value);
	}

	public function hasItemScopeAttribute(): bool{
		return $this->hasAttribute('itemscope');
	}

	public function getItemScopeAttribute(){
		$f = __METHOD__;
		if(!$this->hasItemScopeAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('itemscope');
	}

	public function setItemReferenceAttribute($value){
		return $this->setAttribute('itemref', $value);
	}

	public function hasItemReferenceAttribute(): bool{
		return $this->hasAttribute('itemref');
	}

	public function getItemReferenceAttribute(){
		$f = __METHOD__;
		if(!$this->hasItemReferenceAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('itemref');
	}

	public function setItemPropertyAttribute($value){
		return $this->setAttribute('itemprop', $value);
	}

	public function hasItemPropertyAttribute(): bool{
		return $this->hasAttribute('itemprop');
	}

	public function getItemPropertyAttribute(){
		$f = __METHOD__;
		if(!$this->hasItemPropertyAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('itemprop');
	}

	public function setItemIdAttribute($value){
		return $this->setAttribute('itemid', $value);
	}

	public function hasItemIdAttribute(): bool{
		return $this->hasAttribute('itemid');
	}

	public function getItemIdAttribute(){
		$f = __METHOD__;
		if(!$this->hasItemIdAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('itemid');
	}

	public function setIsAttribute($value){
		return $this->setAttribute('is', $value);
	}

	public function hasIsAttribute(): bool{
		return $this->hasAttribute('is');
	}

	public function getIsAttribute(){
		$f = __METHOD__;
		if(!$this->hasIsAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('is');
	}

	public function hasInputModeAttribute(): bool{
		return $this->hasAttribute("inputmode");
	}

	public function setInputModeAttribute($value){
		return $this->setAttribute("inputmode", $value);
	}

	public function getInputModeAttribute(){
		$f = __METHOD__;
		if(!$this->hasInputModeAttribute()){
			Debug::error("{$f} input mode attribute is undefined");
		}
		return $this->getAttribute("inputmode");
	}

	public function setHiddenAttribute($value = null){
		return $this->setAttribute('hidden', $value);
	}

	public function hasHiddenAttribute(): bool{
		return $this->hasAttribute('hidden');
	}

	public function getHiddenAttribute(){
		$f = __METHOD__;
		if(!$this->hasHiddenAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('hidden');
	}

	public function setTitleAttribute($value){
		return $this->setAttribute('title', $value);
	}

	public function hasTitleAttribute(): bool{
		return $this->hasAttribute('title');
	}

	public function getTitleAttribute(){
		$f = __METHOD__;
		if(!$this->hasTitleAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('title');
	}

	public function setTabIndexAttribute($value){
		return $this->setAttribute('tabindex', $value);
	}

	public function hasTabIndexAttribute(): bool{
		return $this->hasAttribute('tabindex');
	}

	public function getTabIndexAttribute()
	{
		$f = __METHOD__;
		if(!$this->hasTabIndexAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('tabindex');
	}

	public function setSlotAttribute($value){
		return $this->setAttribute('slot', $value);
	}

	public function hasSlotAttribute(): bool{
		return $this->hasAttribute('slot');
	}

	public function getSlotAttribute(){
		$f = __METHOD__;
		if(!$this->hasSlotAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('slot');
	}

	public function setPartAttribute($value){
		return $this->setAttribute('part', $value);
	}

	public function hasPartAttribute(): bool{
		return $this->hasAttribute('part');
	}

	public function getPartAttribute(){
		$f = __METHOD__;
		if(!$this->hasPartAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('part');
	}

	public function setNonceAttribute($value){
		return $this->setAttribute('nonce', $value);
	}

	public function hasNonceAttribute(): bool{
		return $this->hasAttribute('nonce');
	}

	public function getNonceAttribute(){
		$f = __METHOD__;
		if(!$this->hasNonceAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('nonce');
	}

	public function setLanguageAttribute($value){
		return $this->setAttribute('lang', $value);
	}

	public function hasLanguageAttribute(): bool{
		return $this->hasAttribute('lang');
	}

	public function getLanguageAttribute(){
		$f = __METHOD__;
		if(!$this->hasLanguageAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('lang');
	}

	public function language($value): Element{
		$this->setLanguageAttribute($value);
		return $this;
	}

	public function setItemTypeAttribute($value){
		return $this->setAttribute('itemtype', $value);
	}

	public function hasItemTypeAttribute(): bool{
		return $this->hasAttribute('itemtype');
	}

	public function getItemTypeAttribute(){
		$f = __METHOD__;
		if(!$this->hasItemTypeAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute('itemtype');
	}

	public static function isTemplateworthy(): bool{
		return false;
	}

	public static function isEmptyElement(): bool{
		return false;
	}

	public function setHTMLCacheableFlag(bool $value = true): bool{
		return $this->setFlag("htmlCacheable", $value);
	}

	public function getHTMLCacheableFlag(): bool{
		return $this->getFlag("htmlCacheable");
	}

	/**
	 * set this to true to exclude an element from getting updated in UpdateElementCommand
	 *
	 * @param boolean $value
	 * @return boolean
	 */
	public function setNoUpdateFlag(bool $value = true): bool{
		return $this->setFlag("noUpdate", $value);
	}

	public function getNoUpdateFlag(): bool{
		return $this->getFlag("noUpdate");
	}

	public function update(): UpdateElementCommand{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::printStackTraceNoExit("{$f} entered");
		}
		if($this->hasReplacementId()){
			return new UpdateElementCommand([
				$this->getReplacementId() => $this
			]);
		}
		return new UpdateElementCommand($this);
	}

	public function updateInnerHTML(): UpdateElementCommand{
		return $this->update()->inner();
	}

	public function delete(): DeleteElementCommand{
		return new DeleteElementCommand($this);
	}

	public function appendChildCommand(...$children):AppendChildCommand{
		return new AppendChildCommand($this, ...$children);
	}

	public function getDisableRenderingFlag(): bool{
		return $this->getFlag("disableRendering");
	}

	public function setDisableRenderingFlag(bool $value = true): bool{
		return $this->setFlag("disableRendering", $value);
	}

	public function disableRendering(bool $value = true): Element{
		$this->setDisableRenderingFlag($value);
		return $this;
	}

	public function setSpellcheckAttribute($value = "true"){
		return $this->setAttribute("spellcheck", $value);
	}

	public function hasSpellcheckAttribute():bool{
		return $this->hasAttribute("spellcheck");
	}

	public function getSpellcheckAttribute(){
		$f = __METHOD__;
		if(!$this->hasSpellcheckAttribute()){
			Debug::error("{$f} spellcheck attribute is undefined");
		}
		return $this->getAttribute("spellcheck");
	}

	public function releaseWrapperElement(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasWrapperElement()){
			Debug::error("{$f} wrapper element is undefined for this ".$this->getDebugString());
		}
		$wrapper = $this->wrapperElement;
		unset($this->wrapperElement);
		$this->release($wrapper, $deallocate);
	}
	
	public function setWrapperElement(?Element $wrapper):?Element{
		$f = __METHOD__;
		if($this->hasWrapperElement()){
			$this->releaseWrapperElement();
		}
		return $this->wrapperElement = $this->claim($wrapper);
	}

	public function hasWrapperElement(): bool{
		return isset($this->wrapperElement);
	}

	public function getWrapperElement(){
		$f = __METHOD__;
		if(!$this->hasWrapperElement()){
			Debug::error("{$f} wrapper element is undefined");
		}
		return $this->wrapperElement;
	}

	public function setAllocationMode(?int $mode): ?int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if(!is_int($mode)){
				Debug::error("{$f} whoops, rendering mode must be an integer");
			}elseif($this->hasAllocationMode()){
				$this->release($this->allocationMode);
			}
			switch($mode){
				case ALLOCATION_MODE_FORM_TEMPLATE:
				case ALLOCATION_MODE_TEMPLATE:
					if($print){
						Debug::print("{$f} template or form template generation mode");
					}
					$this->setTemplateFlag(true);
					if(!$this->getTemplateFlag()){
						Debug::error("{$f} template flag is undefined");
					}
					break;
				case ALLOCATION_MODE_NEVER:
					if($print){
						Debug::print("{$f} no children");
					}
					$this->setAllowEmptyInnerHTML(true);
					break;
				case ALLOCATION_MODE_ULTRA_LAZY:
					if(!$this->isUltraLazyRenderingCompatible()){
						if($print){
							Debug::print("{$f} this element is incompatible with ultra lazy rendering mode");
						}
						$mode = ALLOCATION_MODE_LAZY;
					}elseif($print){
						Debug::print("{$f} ultra lazy rendering mode");
					}
					break;
				default:
			}
			if($print){
				$sc = $this->getShortClass();
				$did = $this->getDebugId();
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} setting allocation mode to \"".$mode."\" for {$sc} declared {$decl} with debug ID {$did}.");
			}
			return $this->allocationMode = $this->claim($mode);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function wrap(...$children): Element{
		$element = new static(ALLOCATION_MODE_UNDEFINED);
		$element->appendChild(...$children);
		return $element;
	}

	public function hasResizeAttribute(): bool{
		return $this->hasAttribute("resize");
	}

	public function hasAttribute($key, $value = null): bool{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasAttributes()){
			return false;
		}elseif(!array_key_exists($key, $this->attributes)){
			return false;
		}elseif($value === null){
			if($print){
				Debug::print("{$f} attribute exists but value is null");
			}
			return true;
		}
		return $this->attributes[$key] === $value;
	}

	public function hasAttributes(): bool{
		return isset($this->attributes) && is_array($this->attributes) && !empty($this->attributes);
	}

	public function setResponsiveStyleProperty($attr, $command){
		if(!is_array($this->responsiveStyleProperties)){
			$this->responsiveStyleProperties = [];
		}elseif($this->hasResponsiveStyleProperty($attr)){
			$this->release($this->responsiveStyleProperties[$attr]);
		}
		return $this->responsiveStyleProperties[$attr] = $this->claim($command);
	}

	public function hasResponsiveStyleProperty(string $key): bool{
		return array_key_exists($key, $this->responsiveStyleProperties);
	}

	public function getResponsiveStyleProperty($key){
		$f = __METHOD__;
		if(!$this->hasResponsiveStyleProperty($key)){
			Debug::error("{$f} responsive style property with key \"{$key}\" does not exist");
		}
		return $this->responsiveStyleProperties[$key];
	}

	public static function getTemplateFunctionName(): string{
		$class = get_short_class(static::class);
		return "bind{$class}";
	}

	public function getContentsGeneratedFlag(): bool{
		return $this->getFlag("contentsGenerated");
	}

	public function setContentsGeneratedFlag(bool $value = true): bool{
		return $this->setFlag("contentsGenerated", $value);
	}

	public function reportScriptForDomInsertion($script){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasSubcommandCollector()){
			if($print){
				Debug::print("{$f} subcommand collector is undefined");
			}
			if($this->hasParentNode()){
				if($print){
					Debug::print("{$f} reporting script to parent node");
				}
				return $this->getParentNode()->reportScriptForDomInsertion($script);
			}
			Debug::error("{$f} this object lacks a subcommand collector or parent node");
		}elseif($print){
			Debug::print("{$f} subcommand collector is defined; pushing append child command");
		}
		$this->getSubcommandCollector()->pushSubcommand(new AppendChildCommand("head", $script));
		return $script;
	}

	/**
	 * This is just a convenience function to easily attach attributes to the element
	 */
	protected function generateAttributes():int{
		$f = __METHOD__;
		$print = false;
		if($this->hasDeclarationLine()){
			$decl = $this->getDeclarationLine();
			$this->setAttribute("declared", $decl);
			if($print){
				Debug::print("{$f} declared \"{$decl}\"");
			}
		}
		if($this->hasDebugId()){
			//$this->setAttribute("debugid", $this->getDebugId());
		}
		$this->setAttribute("php_class", get_short_class(static::class));
		return SUCCESS;
	}
	
	protected function beforeRenderHook(): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_RENDER)){
			$this->dispatchEvent(new BeforeRenderEvent());
		}
		$this->generateAttributes();
		return SUCCESS;
	}
	
	protected function afterRenderHook(){
		if($this->hasAnyEventListener(EVENT_AFTER_RENDER)){
			$this->dispatchEvent(new AfterRenderEvent());
		}
		return SUCCESS;
	}
	
	/**
	 * generates the child nodes/innerHTML for this element, if any, as well as predecessors and successors (depending on the rendering mode)
	 *
	 * @return string
	 */
	public final function generateContents(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				if($this->hasContext()){
					$context = $this->getContext();
					if(is_object($context)){
						$cc = $context->getClass();
						if($context instanceof Datum){
							$cn = $context->getName();
							Debug::print("{$f} context is a {$cc} named \"{$cn}\"");
						}else{
							Debug::print("{$f} context is an object of class \"{$cc}\"");
						}
					}else{
						$gottype = gettype($context);
						Debug::print("{$f} context is a {$gottype}");
					}
				}else{
					Debug::print("{$f} context is undefined");
				}
			}
			if(!$this->getAllocatedFlag()){
				Debug::error("{$f} allocated flag is not set for this ".$this->getDebugString());
			}elseif($this->getContentsGeneratedFlag()){
				if($print){
					Debug::warning("{$f} contents already generated");
				}
				return SUCCESS;
			}
			$status = $this->beforeRenderHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeRenderHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$mode = $this->getAllocationMode();
			if($mode !== ALLOCATION_MODE_ULTRA_LAZY){
				$this->generatePredecessors();
			}elseif($print){
				Debug::print("{$f} ultra lazy rendering mode, not generating predecessors");
			}
			if($mode === ALLOCATION_MODE_LAZY){
				$this->generateChildNodes();
			}elseif($print){
				Debug::print("{$f} child node generation only happens in generateContents if the rendering mode is lazy. This object's rendering mode is {$mode}");
			}
			if($mode !== ALLOCATION_MODE_ULTRA_LAZY){
				$this->generateSuccessors();
			}elseif($print){
				Debug::print("{$f} ultra lazy rendering mode, not generating successors");
			}
			$this->setContentsGeneratedFlag(true);
			$status = $this->afterRenderHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterRenderHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function releasePredecessors(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasPredecessors()){
			Debug::error("{$f} no predecessors to release for this ".$this->getDebugString());
		}
		foreach(array_keys($this->properties['predecessors']) as $key){
			$this->releaseArrayPropertyKey('predecessors', $key, $deallocate);
		}
	}
	
	public function releaseSuccessors(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasSuccessors()){
			Debug::error("{$f} no successors to release for this ".$this->getDebugString());
		}
		foreach(array_keys($this->properties['successors']) as $key){
			$this->releaseArrayPropertyKey('successors', $key, $deallocate);
		}
	}
	
	/**
	 * echo this element directly to the user's browser as JSON.
	 * The deserialized JSON can be converted into an element in JS with hydrate().
	 * Saves memory compared to json_encode.
	 * This function has to be gigantic because breaking it up would exceed maximum execution depth
	 *
	 * @param boolean $deallocate
	 * @return void
	 */
	public function echoJson(bool $deallocate = false): void{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($this->getTemplateFlag()){
				Debug::error("{$f} should not be echoing templated object ".$this->getDebugString());
			}elseif($this->hasWrapperElement()){
				$wrapper = $this->getWrapperElement();
				$this->releaseWrapperElement();
				$wrapper->appendChild($this);
				$wrapper->echoJson($deallocate);
				if(!$deallocate){
					ErrorMessage::unimplemented($f); //XXX TODO need to 
					$wrapper->removeChild($this);
					$this->setWrapperElement($wrapper);
				}else{
					unset($this->properties['successors']);
					if($wrapper instanceof HitPointsInterface && $wrapper->getAllocatedFlag()){ //necessary because the wrapper may have been already deallocated if it had its own wrapper
						deallocate($wrapper);
					}else{
						unset($wrapper);
					}
				}
				return;
			}elseif($print){
				if($this->getFlag("echoed")){
					Debug::error("{$f} this ".$this->getDebugString()." has already been echoed");
				}
				$this->setFlag("echoed");
				Debug::printStackTraceNoExit("{$f} echoing ".$this->getDebugString());
			}
			$cache = false;
			if($this->isCacheable() && JSON_CACHE_ENABLED){
				if(cache()->has($this->getCacheKey() . ".json")){
					if($print){
						Debug::print("{$f} this object's JSON has already been cached");
					}
					echo cache()->get($this->getCacheKey() . ".json");
					return;
				}else{
					$cache = true;
					ob_start();
				}
			}elseif($print){
				Debug::print("{$f} cache this object is not cacheable");
			}
			$this->generateContents();
			if($this->hasParentNode()){
				//$this->generateContents();
				if($this->getAllocationMode() === ALLOCATION_MODE_ULTRA_LAZY){
					if($print){
						Debug::print("{$f} ultra lazy rendering mode -- generating predecessors now");
					}
					$this->generatePredecessors();
				}
			}else{
				if($print){
					Debug::print("{$f} parent node is undefined");
				}
				//$this->generateContents();
				if($this->getAllocationMode() === ALLOCATION_MODE_ULTRA_LAZY){
					if($print){
						Debug::print("{$f} ultra lazy rendering mode -- generating predecessors/successors now");
					}
					$this->generatePredecessors();
					$this->generateSuccessors();
				}
				if($this->hasPredecessors() || $this->hasSuccessors()){
					$fragment = new DocumentFragment();
					if($this->hasPredecessors()){
						$fragment->appendChild(...array_values($this->getPredecessors()));
						$this->releasePredecessors();
					}
					$fragment->appendChild($this);
					if($this->hasSuccessors()){
						$fragment->appendChild(...array_values($this->getSuccessors()));
						$this->releaseSuccessors();
					}
					$this->setParentNode($fragment);
					$fragment->echoJson($deallocate);
					deallocate($fragment);
					return;
				}elseif($print){
					Debug::print("{$f} there are no predecessors or successor nodes");
				}
			}
			// all of this was copied from the now-defunct echoJsonHelper to reduce execution depth
			// predecessors
			if($this->hasPredecessors()){
				$i = 0;
				foreach($this->getPredecessors() as $key => $p){
					if($p instanceof EchoJsonInterface && $p->skipJson()){
						continue;
					}
					if($i++ > 0){
						echo ",";
					}
					if($p instanceof Element && $this->hasParentNode()){
						$p->setParentNode($this->getParentNode());
					}
					Json::echo($p, $deallocate, false);
					if($p instanceof Element && $p->hasParentNode()){
						$p->releaseParentNode();
					}
					if($deallocate){
						if($this->hasArrayPropertyKey('predecessors', $key)){
							if($print){
								Debug::print("{$f} about to release predecessor {$key} of this ".$this->getDebugString());
							}
							$this->releaseArrayPropertyKey('predecessors', $key, $deallocate); //release($p);
						}else{
							if($print){
								Debug::print("{$f} this ".$this->getDebugString()." does not have a predecessor {$key}");
							}
							deallocate($p);
						}
					}elseif($print){
						Debug::print("{$f} we are not interested in deallocating predecessors for this ".$this->getDebugString());
					}
				}
				echo ",";
			}elseif($print){
				Debug::print("{$f} no predecessors");
			}
			// this
			echo "{";
			// all attributes, including class and inline style
			if($this->hasAttributes() || $this->hasClassList() || $this->hasInlineStyleAttribute()){
				if($print){
					Debug::print("{$f} this element has attributes, a class list, or inline style properties");
				}
				// generate class attribute
				if($this->hasClassAttribute()){
					if($print){
						Debug::print("{$f} assigning class attribute");
					}
					$this->setAttribute('class', $this->getClassAttribute());
					if($deallocate){
						$this->release($this->classList, $deallocate);
					}
				}elseif($this->getFlag("requireClassAttribute")){
					Debug::error("{$f} class attribute is required");
				}elseif($print){
					Debug::print("{$f} class attribute is undefined, but that's OK");
				}
				// add inline style properties to attributes
				if($this->hasInlineStyleAttribute()){
					$this->setAttribute('style', $this->getInlineStyleAttribute());
					if($deallocate){
						$this->release($this->style, $deallocate);
					}
				}elseif($print){
					Debug::print("{$f} inline style attribute is undefined");
				}
				// echo attributes
				if($this->hasAttributes()){
					foreach($this->attributes as $attr_key => $attr){
						if($attr instanceof Command){
							if(!$attr->getAllocatedFlag()){
								Debug::warning("{$f} attribute at index \"{$attr_key}\" was deallocated");
								$this->attributes[$attr_key] = "[deleted]";
								$this->debugPrintRootElement();
							}
						}
					}
					Json::echoKeyValuePair("attributes", $this->attributes, $deallocate);
					// InitializeFormCommand needs ID until commands are dispatched
				}elseif($print){
					Debug::print("{$f} no other attributes defined");
				}
			}elseif($print){
				Debug::print("{$f} this element has no attributes, except possibly responsive style properties");
			}
			// responsive style properties that require client side calculation
			if($this->hasResponsiveStyleProperties()){
				Json::echoKeyValuePair('responsiveStyleProperties', $this->responsiveStyleProperties, $deallocate, false);
				if($deallocate){
					$this->release($this->responsiveStyleProperties, $deallocate);
				}
			}elseif($print){
				Debug::print("{$f} this element has no responsive style properties");
			}
			// echo innerHTML or childNodes
			$mode = $this->getAllocationMode();
			if($this instanceof EmptyElement){
				if($print){
					Debug::print("{$f} this is an empty element with no innerHTML");
				}
			}elseif($this->hasInnerHTML()){
				if($print){
					Debug::print("{$f} innerHTML is defined as \"{$this->innerHTML}\"");
				}
				Json::echoKeyValuePair("innerHTML", $this->innerHTML, $deallocate);
				if($deallocate){
					$this->release($this->innerHTML, $deallocate);
				}
			}else{
				if($print){
					Debug::print("{$f} this is not an empty element, and it does not have an explicit innerHTML");
				}
				if($this->hasChildNodes()){
					if($print){
						Debug::print("{$f} about to echo child nodes of this ".$this->getDebugString());
					}
					// copied from Json::echoKeyValuePair to reduce execution depth
					echo "\"childNodes\":";
					Json::echo($this->childNodes, $deallocate, false);
					echo ",";
					// dispose childNodes as soon as possible
					if($deallocate){
						$this->releaseChildNodes($deallocate);
					}
				}elseif($mode === ALLOCATION_MODE_ULTRA_LAZY){
					if($print){
						Debug::print("{$f} ultra-lazy allocation mode");
					}
					echo "\"childNodes\":[";
					$this->childElementCount = 0;
					$this->generateChildNodes();
					echo "],";
				}elseif(!$this->getAllowEmptyInnerHTML()){
					Debug::error("{$f} child nodes array is empty for this ".$this->getDebugString().". Allocation mode is \"{$mode}\".");
				}elseif($print){
					Debug::print("{$f} empty innerHTML");
				}
			}
			// element tag always comes last
			Json::echoKeyValuePair("tag", $this->getElementTag(), $deallocate, false);
			if(
				Request::isAjaxRequest() && 
				!$this->hasDispatchedCommands() && 
				!$this->getRefuseCommandsFlag()
			){
				if($print){
					Debug::print("{$f} dispatching commands now");
				}
				$this->dispatchCommands();
			}elseif($print){
				Debug::print("{$f} this is not an XHR or fetch event, or commands were already dispatched");
			}
			echo "}";
			// successors
			if($this->hasParentNode() && $mode === ALLOCATION_MODE_ULTRA_LAZY){
				$this->generateSuccessors();
			}
			if($this->hasSuccessors()){
				foreach($this->getSuccessors() as $key => $s){
					echo ",";
					if($s instanceof Element && $this->hasParentNode()){
						$s->setParentNode($this->getParentNode());
					}
					Json::echo($s, $deallocate, false);
					if($s instanceof Element && $s->hasParentNode()){
						$s->releaseParentNode();
					}
					if($deallocate){
						if($this->hasArrayPropertyKey('successors', $key)){
							$this->releaseArrayPropertyKey('successors', $key, $deallocate);
						}else{
							deallocate($s);
						}
					}
				}
			}elseif($print){
				Debug::print("{$f} no successors");
			}
			// end of transplant from echoJsonHelper. Deal with cache insert here
			if($cache){
				if($print){
					Debug::print("{$f} about to cache JSON");
				}
				$json = ob_get_clean();
				cache()->set($this->getCacheKey() . ".json", $json, time() + 30 * 60);
				echo $json;
				unset($json);
			}elseif($print){
				Debug::print("{$f} nothing to cache");
			}
			if($print){
				Debug::print("{$f} returning normally for this ".$this->getDebugString());
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function releaseAttributes(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasAttributes()){
			Debug::error("{$f} no attributes to release");
		}
		foreach(array_keys($this->getAttributes()) as $key){
			$this->removeAttribute($key, $deallocate);
		}
	}
	
	public function removeAttribute(string $key, bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasAttribute($key)){
			Debug::error("{$f} attribute \"{$key}\" is undefined");
		}
		$value = $this->attributes[$key];
		unset($this->attributes[$key]);
		$this->release($value, $deallocate);
		return $value;
	}
	
	public function echoInnerJson(bool $deallocate = false): void{
		$f = __METHOD__;
		Debug::error("{$f} disabled as a flimsy attempt to reduce execution depth");
	}

	public function echoAttributeString(bool $deallocate = false): void{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasClassAttribute()){
				echo " class=\"";
				$this->echoClassAttribute($deallocate);
				echo "\"";
			}
			if($this->hasAttributes()){
				foreach($this->getAttributes() as $key => $value){
					if($key === "class"){
						continue;
					}
					echo " {$key}";
					if($value === null){
						if($print){
							Debug::print("{$f} attribute \"{$key}\" is null");
						}
						continue;
					}elseif($key === "value" || $value !== null && $value !== ""){
						if($value instanceof ValueReturningCommandInterface){
							if(!$this->getTemplateFlag()){
								Debug::error("{$f} it should not be possible to have a command for an attribute value without the template flag set");
							}elseif($print){
								Debug::print("{$f} value is a ".$value->getDebugString());
							}
							$value = $value->toJavaScript();
						}
						echo "=".double_quote(htmlspecialchars($value));
					}
				}
			}
			if($this->hasInlineStyleAttribute()){
				echo " style=\"";
				$this->echoInlineStyleAttribute($deallocate);
				echo "\"";
			}elseif($print){
				Debug::print("{$f} inline style attribute is undefined");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getAttribute($key){
		$f = __METHOD__;
		$print = false;
		if($print){
			$sc = $this->getShortClass();
			$decl = $this->getDeclarationLine();
			$did = $this->getDebugId();
			Debug::print("{$f} getting attribute \"{$key}\" from a {$sc} declared {$decl} with debug ID {$did}");
		}
		if(!$this->hasAttribute($key)){
			return null;
		}
		return $this->attributes[$key];
	}

	public function debugPrintAttributes(): void{
		if(empty($this->attributes)){
			Debug::print("No attributes");
		} else
			foreach($this->attributes as $name => $value){
				Debug::print("{$name} : {$value}");
			}
		Debug::printStackTraceNoExit();
	}

	public function debugPrintStyleProperties(): void{
		$f = __METHOD__;
		if(empty($this->style)){
			Debug::print("{$f} No style properties");
		} else
			foreach($this->style as $name => $value){
				if($value instanceof JavaScriptInterface && ! $value instanceof StringifiableInterface){
					$value = $value->toJavaScript();
				}
				Debug::print("{$name} : {$value}");
			}
		Debug::printStackTraceNoExit();
	}

	public function setReportedSubcommands($values){
		return $this->setArrayProperty("reportedSubcommands", $values);
	}

	public function pushReportedSubcommands(...$values): int{
		return $this->pushArrayProperty("reportedSubcommands", ...$values);
	}

	public function hasReportedSubcommands(): bool{
		return $this->hasArrayProperty("reportedSubcommands");
	}

	public function getReportedSubcommands(){
		return $this->getProperty("reportedSubcommands");
	}

	public function hasDispatchedCommands(): bool{
		return $this->getFlag("dispatched");
	}

	public function setDispatchedCommandsFlag(bool $flag = true): bool{
		return $this->setFlag("dispatched", $flag);
	}

	public function setAllowEmptyInnerHTML(bool $allow = true): bool{
		return $this->setFlag("allowEmptyInnerHTML", $allow);
	}

	public function getAllowEmptyInnerHTML():bool{
		return $this->getFlag("allowEmptyInnerHTML");
	}

	public function setParentNode($node){
		if($node instanceof Element && $node->getDisableRenderingFlag()){
			$this->disableRendering();
		}
		if($this->hasParentNode()){
			$this->releaseParentNode();
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->parentNode = $node;
		}
		return $this->parentNode = $this->claim($node);
	}

	/**
	 *
	 * @return Element[]|NULL
	 */
	public function getChildNodes():?array{
		$f = __METHOD__;
		if(!$this->hasChildNodes()){
			$mode = $this->getAllocationMode();
			if($mode === ALLOCATION_MODE_ULTRA_LAZY){
				//nodes were most likely echoed directly and not appended
				return null;
			}
			Debug::error("{$f} child nodes are undefined. ALlocation mode {$mode}");
		}
		return $this->childNodes;
	}

	public function getChildNode($key){
		$f = __METHOD__;
		if(!$this->hasChildNode($key)){
			Debug::error("{$f} there is no child node at index {$key}");
		}
		return $this->childNodes[$key];
	}

	public function setTemplateLoopFlag(bool $value = true): bool{
		return $this->setFlag("templateLoop", $value);
	}

	/**
	 * check this flag to see if the element is part of a template loop, and a new copy of it must be constructed
	 * e.g.
	 * in BindElementCommand->evaluate()
	 */
	public function getTemplateLoopFlag(): bool{
		return $this->getFlag("templateLoop");
	}

	public function appendChild(...$children){
		if(empty($children)){
			return null;
		}
		foreach($children as $child){
			$this->insertChild($child, CONST_AFTER);
		}
		return $children;
	}

	/**
	 * insert a child node
	 *
	 * @param Element $child
	 * @param string $where
	 *        	: before or after
	 * @return Command|DeclareVariableCommand|NodeBearingCommandInterface|SwitchCommand|ServerExecutableCommandInterface|Command:NULL
	 */
	public function insertChild($child, $where){
		$f = __METHOD__;
		try{
			$print = false && $child->getDebugFlag();
			if(!isset($this->childNodes) || !is_array($this->childNodes)){
				$this->childNodes = [];
				if($print){
					Debug::print("{$f} initialized child nodes array");
				}
			}elseif($print){
				Debug::print("{$f} child nodes array has already been initialized");
			}
			$parent_set = false;
			if(is_object($child)){
				if($print){
					Debug::print("{$f} child is an object");
				}
				$cc = $child->getClass();
				if($child instanceof Element || $child instanceof ElementGenerator){
					if($print){
						Debug::print("{$f} inserting ".$child->getDebugString()." into this ".$this->getDebugString());
					}
					if($child instanceof ParentNodeInterface && !$this instanceof DocumentFragment){
						$parent_set = true;
						$child->setParentNode($this);
					}
				}elseif($child instanceof Command){
					$cc = $child->getClass();
					if($print){
						Debug::print("{$f} child is a media command of class \"{$cc}\"");
					}
					if($this instanceof JavaScriptClass || $this instanceof JavaScriptFunction || $this instanceof DocumentFragment || $this instanceof ScriptElement){
						if($print){
							Debug::print("{$f} this is a javascript function");
						}
					}elseif($this->getTemplateFlag()){
						if($print){
							Debug::print("{$f} template flag is set");
						}
					}elseif($child instanceof NodeBearingCommandInterface){
						if($print){
							Debug::print("{$f} child is a node-bearing command interface of class \"{$cc}\"");
						}
						return $this->resolveTemplateCommand($child);
					}elseif($child instanceof ValueReturningCommandInterface){
						if($print){
							Debug::print("{$f} child is a value-returning media command; about to insert its evaluation");
						}
						while($child instanceof ValueReturningCommandInterface){
							$child = $child->evaluate();
						}
						if(/*$mutual &&*/ $child instanceof Element && !$this instanceof DocumentFragment){
							$child->setParentNode($this);
							$parent_set = true;
						}
					}else{
						Debug::error("{$f} something went wrong; child class is \"{$cc}\"");
					}
				}elseif($child instanceof JavaScriptClass || $child instanceof JavaScriptFunction){
					//ok?
				}else{
					Debug::error("{$f} child is an object of class \"{$cc}\"");
				}
			}elseif(is_array($child)){
				Debug::error("{$f} child is an array");
			}elseif(is_string($child)){
				if($print){
					Debug::print("{$f} child is the string \"{$child}\"");
				}
			}elseif(is_int($child)){
				if($print){
					Debug::print("{$f} child is the integer {$child}");
				}
			}elseif(is_float($child)){
				if($print){
					Debug::print("{$f} child is the lfoating point value {$child}");
				}
			}else{
				$gottype = gettype($child);
				Debug::error("{$f} child is something else of type \"{$gottype}\"");
			}
			if($child instanceof Command && !$this->getTemplateFlag() && !$this instanceof JavaScriptClass && !$this instanceof JavaScriptFunction && !$this instanceof ScriptElement){
				$class = $child->getClass();
				Debug::error("{$f} child object is an instance of \"{$class}\"");
			}
			$mode = $this->getAllocationMode();
			if(
				$mode === ALLOCATION_MODE_ULTRA_LAZY 
				&& $this->getContentsGeneratedFlag() 
				&& $this->getFlag("predecessorsGenerated")
			){
				if($print){
					Debug::print("{$f} ultra lazy rendering mode");
				}
				if(!$this->getFlag("predecessorsGenerated")){
					Debug::warning("{$f} predecessors have not been generated --- you cannot insert children at this time");
					$this->announceYourself();
					Debug::printStackTrace();
				}
				if($where === "prepend"){
					Debug::error("{$f} you cannot unshift child nodes in ultra lazy allocation mode");
				}
				if(is_string($child)){
					echo $child;
					unset($child);
					return null;
				}elseif(Request::isXHREvent() || Request::isFetchEvent()){
					if($this->childElementCount++ > 0){
						echo ",";
					}
					$child->disableDeallocation();
					$child->echoJson(true);
					$child->enableDeallocation();
					deallocate($child);
				}else{
					$child->disableDeallocation();
					$child->echo(true);
					$child->enableDeallocation();
					deallocate($child);
				}
				return null;
			}elseif($print){
				if(!$this->getContentsGeneratedFlag()){
					Debug::print("{$f} contents generated flag is not set");
				}
				if($mode !== ALLOCATION_MODE_ULTRA_LAZY){
					Debug::print("{$f} not ultra lazy");
				}
				if(!$this->getFlag("predecessorsGenerated")){
					Debug::print("{$f} predecessors generated flag is not set");
				}
			}
			switch($where){
				case CONST_AFTER:
					if($print){
						Debug::print("{$f} appending child node now");
					}
					$key = $this->getChildNodeCount();
					array_push($this->childNodes, $this->claim($child));
					break;
				case CONST_BEFORE:
					if($print){
						Debug::print("{$f} prepending child node now");
					}
					array_unshift($this->childNodes, $this->claim($child));
					$key = 0;
					break;
				default:
					Debug::error("{$f} invalid insert where value \"{$where}\"");
			}
			if(
				$child instanceof HitPointsInterface && 
				$parent_set && 
				BACKWARDS_REFERENCES_ENABLED
			){
				if($print){
					Debug::print("{$f} child node is a HitPointsInterface, and the parent node was set");
				}
				$parent = $this;
				$closure1 = function(Element $parent, bool $deallocate=false)
				use ($key, $f, $print){
					if($parent->hasChildNode($key)){
						$parent->releaseChildNode($key, $deallocate);
					}
				};
				$closure2 = function(HitPointsInterface $child, bool $deallocate=false)
				use ($f, $print){
					if($child->hasParentNode()){
						$child->releaseParentNode(false);
					}
				};
				mutual_reference($parent, $child, $closure1, $closure2, EVENT_RELEASE_CHILD, EVENT_RELEASE_PARENT, [
					'key' => $key
				]);
			}
			return $child;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getChildNodeNumber(int $num){
		$f = __METHOD__;
		if($this->getChildNodeCount() <= $num){
			Debug::error("{$f} node number {$num} exceeds child node count");
		}
		return $this->getChildNode(array_keys($this->childNodes)[$num]);
	}
	
	public function hasChildNode($key):bool{
		return $this->hasChildNodes() && array_key_exists($key, $this->childNodes) && $this->childNodes[$key] !== null;
	}
	
	public function releaseChildNode($key, bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasChildNode($key)){
			Debug::warning("{$f} there is not child node at index {$key}. The following is a list of valid indices:");
			Debug::printArray($this->childNodes);
			Debug::printStackTrace();
		}
		$child = $this->childNodes[$key];
		$print = $child instanceof Basic && $child->getDebugFlag();
		unset($this->childNodes[$key]);
		if(empty($this->childNodes)){
			unset($this->childNodes);
		}
		if($this->hasAnyEventListener(EVENT_RELEASE_CHILD)){
			$this->dispatchEvent(new ReleaseChildNodeEvent($key, $child, $deallocate));
		}
		if($print){
			Debug::print("{$f} about to release child ".$child->getDebugString()." of this ".$this->getDebugString());
		}
		$this->release($child, $deallocate);
	}
	
	public function prepend(...$children){
		if(empty($children)){
			return null;
		}
		foreach($children as $child){
			$this->insertChild($child, CONST_BEFORE);
		}
		return $children;
	}

	public function hasChildNodes(): bool{
		return isset($this->childNodes) && is_array($this->childNodes) && !empty($this->childNodes);
	}

	public function getChildNodeCount(): int{
		if(!$this->hasChildNodes()){
			return 0;
		}
		return count($this->childNodes);
	}

	public function debugPrintRootElement(): void{
		$f = __METHOD__;
		$this->announceYourself();
		if($this->hasParentNode()){
			$this->parentNode->debugPrintRootElement();
		}
		Debug::error("{$f} end of the line");
	}

	/**
	 * this is used by only a handful of derived classes
	 *
	 * @return string
	 */
	public final function getInnerHTML(){
		$f = __METHOD__;
		if(isset($this->innerHTML)){
			return $this->innerHTML;
		}
		ob_start();
		$this->echoInnerHTML();
		return ob_get_clean();
	}

	public static final function createElement($mode = ALLOCATION_MODE_UNDEFINED, $context = null): Element{
		$class = static::class;
		return new $class($mode, $context);
	}

	public function withChild(...$children): Element{
		$this->appendChild(...$children);
		return $this;
	}

	public function withAttribute($key, $value = null): Element{
		$this->setAttribute($key, $value);
		return $this;
	}

	public function withAttributes($keyvalues): Element{
		$this->setAttributes($keyvalues);
		return $this;
	}

	public function announceYourself(): void{
		$f = __METHOD__;
		$sc = $this->getShortClass();
		Debug::print("{$f} class is {$sc}, declared " . $this->getDeclarationLine());
		if(isset($this->debugId)){
			Debug::print("{$f} debug ID is \"{$this->debugId}\"");
		}else{
			Debug::print("{$f} debug ID is undefined");
		}
		if($this->hasClassAttribute()){
			$class = $this->getClassAttribute();
			Debug::print("{$f} my class attribute is \"{$class}\"");
			$class = null;
		}else{
			Debug::warning("{$f} element has no class attribute");
		}
		if(!$this->hasAttributes()){
			Debug::print("{$f} attributes array is empty");
			return;
		}
		if($this->hasIdAttribute()){
			$id = $this->getIdAttribute();
			Debug::print("{$f} my ID attribute is \"{$id}\"");
			$id = null;
		}else{
			Debug::warning("{$f} element has no ID attribute");
		}
		if($this->hasContext()){
			$context = $this->getContext();
			$class = $context->getClass();
			// Debug::print("{$f} context is an object of class \"{$class}\"");
			if($context instanceof DataStructure){
				if($context->hasIdentifierValue()){
					$key = $context->getIdentifierValue();
					Debug::print("{$f} context {$class} has key \"{$key}\"");
				}else{
					Debug::print("{$f} context {$class} does not have a key");
				}
			}
		}else{
			Debug::print("{$f} this element does not have a context");
		}
		if($this->hasAttribute("value")){
			$value = $this->getValueAttribute();
			Debug::print("{$f} my value is \"{$value}\"");
			$value = null;
		}else{
			Debug::warning("{$f} element has no value attribute");
		}
	}

	/**
	 * echos everything inside this element's html tags to the user's browser
	 * <div>This is innerHTML</div>
	 *
	 * @param boolean $deallocate
	 *        	: deallocate this object once we're finished
	 * @return string
	 */
	public function echoInnerHTML(bool $deallocate = false): void{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				$sc = $this->getShortClass();
				$did = $this->getDebugId();
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} echoing the inner HTML of {$sc} with debug ID {$did}, declared {$decl}");
				if($deallocate){
					Debug::print("{$f} inner HTML will be destroyed after echoing");
				}else{
					Debug::print("{$f} we will not destroy innerHTML when we're done echoing");
				}
			}
			if($this->hasInnerHTML()){
				$html = $this->innerHTML;
				$gottype = is_object($html) ? $html->getClass() : gettype($html);
				if($print){
					Debug::print("{$f} innerHTML is a {$gottype}");
				}
				while($html instanceof ValueReturningCommandInterface){
					if($print){
						Debug::print("{$f} innerHTML is a " . $html->getClass());
					}
					$html = $html->evaluate();
				}
				if($print){
					Debug::print("{$f} innerHTML is defined as \"{$html}\"");
				}
				echo $html;
				if($deallocate){
					$this->release($this->innerHTML, $deallocate);
				}
				return;
			}
			$mode = $this->getAllocationMode();
			if($mode === ALLOCATION_MODE_ULTRA_LAZY){
				if($print){
					Debug::print("{$f} ultra lazy rendering mode");
				}
				if(!$this->hasChildNodes()){
					$this->generateChildNodes();
					return;
				}elseif($print){
					Debug::print("{$f} child nodes are already defined; most likely this is an intermediate node that is child to a complex parent");
				}
			}elseif($print){
				Debug::print("{$f} some other rendering mode besides ultra lazy");
			}
			if(!$this->hasChildNodes()){
				if($mode === ALLOCATION_MODE_LAZY){
					if($print){
						Debug::print("{$f} child generation mode is lazy");
					}
					$children = $this->generateChildNodes();
					if(empty($children) && ! $this->getAllowEmptyInnerHTML()){
						Debug::warning("{$f} childNodes array is still empty");
						echo "ERROR: debug ID {$this->debugId}";
						$this->debugPrintRootElement();
					}
				}elseif(!$this->getAllowEmptyInnerHTML()){
					$decl = $this->hasDeclarationLine() ? $this->getDeclarationLine() : "unknown";
					Debug::warning("{$f} child nodes array is empty; generation mode is \"{$mode}\"; created \"{$decl}\"");
					$this->debugPrintRootElement();
					echo "Error: Undefined innerHTML";
					return; // $this->debugPrintRootElement();
				}
			}elseif($print){
				Debug::print("{$f} child nodes already assigned or generated");
			}
			if($this->hasChildNodes()){
				foreach($this->getChildNodes() as $key => $child){
					if(is_object($child)){
						if(!$child->getAllocatedFlag()){
							Debug::warning("{$f} child object ".$child->getDebugString()." was already deleted");
							$this->debugPrintRootElement();
						}elseif($child instanceof Command || $child instanceof JavaScriptFunction || $child instanceof JavaScriptClass){
							if(!$this instanceof ScriptElement){
								Debug::error("{$f} only script element can echo a JScommand/function as its innerHTML");
							}elseif($print){
								Debug::print("{$f} about to echo ".$child->getDebugString());
							}
							echo $child->toJavaScript().";\n";
							if($deallocate){
								$this->releaseChildNode($key, $deallocate);
							}
							continue;
						}elseif(!$child instanceof Element && !$child instanceof ElementGenerator){
							$class = $child->getClass();
							Debug::warning("{$f} child object is an instance of \"{$class}\"");
							$this->debugPrintRootElement();
						}elseif($print){
							Debug::print("{$f} inducing child ".$child->getDebugString()." to echo itself");
						}
						$child->echo($deallocate);
						if($deallocate){
							$this->releaseChildNode($key, $deallocate);
						}
					}elseif(is_string($child) || is_numeric($child)){
						if($print){
							Debug::print("{$f} echoing a child string");
						}
						echo $child;
					}
				}
			}
			if($deallocate){
				if($print){
					$sc = $this->getShortClass();
					$did = $this->getDebugId();
					$decl = $this->getDeclarationLine();
					Debug::print("{$f} about to deallocate child nodes of a {$sc} with debug ID {$did}, declared {$decl}");
				}
				$this->childNodes = null;
				unset($this->childNodes);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setAttributes($keyvalues): array{
		foreach($keyvalues as $key => $value){
			$this->setAttribute($key, $value);
		}
		return $keyvalues;
	}

	public function setAttributeCommand($attr): SetAttributeCommand{
		return new SetAttributeCommand($this, $attr);
	}

	public function bindElementCommand($context): BindElementCommand{
		return new BindElementCommand($this, $context);
	}

	public function setInnerHTML($innerHTML){
		$f = __METHOD__;
		$print = false;
		if($this->hasInnerHTML()){
			$this->release($this->innerHTML);
		}
		if(!$this->getTemplateFlag()){
			if($print){
				Debug::print("{$f} template flag is NOT set");
			}
			while($innerHTML instanceof ValueReturningCommandInterface){
				if($print){
					Debug::print("{$f} innerHTML is a " . $innerHTML->getClass());
					if($innerHTML instanceof ColumnValueCommand){
						Debug::print("{$f} innerHTML column name is " . $innerHTML->getColumnName());
					}
				}
				$innerHTML = $innerHTML->evaluate();
				if($print){
					if(is_string($innerHTML)){
						Debug::print("{$f} innerHTML is the string \"{$innerHTML}\"");
					}else{
						$gottype = is_object($innerHTML) ? $innerHTML->getClass() : gettype($innerHTML);
						Debug::print("{$f} after evaluation, innerHTML is a {$gottype}");
					}
				}
			}
		}elseif($print){
			Debug::print("{$f} template flag is set -- skipping media command evaluation");
		}
		if($this->hasChildNodes()){
			Debug::error("{$f} don't call this function if there are already child nodes assigned");
			$this->setChildNodes([]);
		}elseif($print){
			Debug::printStackTraceNoExit("{$f} no child nodes to overwrite");
		}
		return $this->innerHTML = $this->claim($innerHTML);
	}

	public function initializeAttributesArray(): void{
		if(!isset($this->attributes) || !is_array($this->attributes)){
			$this->attributes = [];
		}
	}

	// XXX TODO event handler attributes is giving some trouble when used with media commands
	public function setOnClickAttribute($onclick){
		return $this->setAttribute("onclick", $onclick);
	}

	public function getOnClickAttribute(){
		$f = __METHOD__;
		if(!$this->hasOnClickAttribute()){
			Debug::error("{$f} onclick attribute is undefined");
		}
		return $this->getAttribute("onclick");
	}

	/**
	 * onclick is a valid attribute for every element except <base>, <bdo>, <br>, <head>, <html>, <iframe>, <meta>, <param>, <script>, <style> and <title>
	 *
	 * @return string
	 */
	public function hasOnClickAttribute(): bool{
		return $this->hasAttribute("onclick");
	}

	public function withOnClickAttribute($onclick): Element{
		$this->setOnClickAttribute($onclick);
		return $this;
	}

	public function withStyleProperties(?array $keyvalues): Element{
		$this->setStyleProperties($keyvalues);
		return $this;
	}

	public function withStyleProperty($key, $value): Element{
		$this->setStyleProperty($key, $value);
		return $this;
	}

	public function getSuccessorCount():int{
		return $this->getArrayPropertyCount("successors");
	}

	public function ejectSuccessors():?array{
		return $this->ejectProperty("successors");
	}

	public function pushSuccessor(...$successors){
		$f = __METHOD__;
		$print = false;
		if($print){
			foreach($successors as $s){
				Debug::print("{$f} pushing successor \"{$s}\"");
			}
		}
		return $this->pushArrayProperty("successors", ...$successors);
	}

	public function ejectPredecessors(): ?array{
		return $this->ejectProperty("predecessors");
	}

	public function getPredecessorCount(): int{
		return $this->getArrayPropertyCount("predecessors");
	}

	public function pushPredecessor(...$predecessors): int{
		return $this->pushArrayProperty("predecessors", ...$predecessors);
	}

	public function getRefuseCommandsFlag():bool{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$node = $this;
		while(true){
			if($node->hasSubcommandCollector()){
				$collector = $node->getSubcommandCollector();
				if($collector instanceof Command){
					if($print){
						Debug::print("{$f} collector is a command");
					}
					return false; //app()->getResponse(app()->getUseCase())->getRefuseCommandsFlag();
				}elseif($print){
					$decl = $collector->getDeclarationLine();
					Debug::print("{$f} asking subcommand collector of class ".get_short_class($collector)." declared {$decl}");
				}
				return $collector->getRefuseCommandsFlag();
			}elseif($node->getCatchReportedSubcommandsFlag()){
				if($print){
					Debug::print("{$f} catch reported subcommands flag is set");
				}
				return false;
			}elseif(!$node->hasParentNode()){
				if($print){
					Debug::print("{$f} parent node is undefined for this \"".$this->getShortClass()."\"");
				}
				return app()->getResponse(app()->getUseCase())->getRefuseCommandsFlag();
			}
			$node = $node->getParentNode();
		}
		Debug::error("{$f} reached the highest node without an answer");
	}
	
	public function dispatchCommands():int{
		$f = __METHOD__;
		$print = false;
		if($this->hasDispatchedCommands()){
			if($print){
				Debug::print("{$f} commands were already dispatched");
			}
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} setting dispatched commands flag now");
		}
		$this->setDispatchedCommandsFlag(true);
		return SUCCESS;
	}

	public function hasSubcommandCollector(): bool{
		return isset($this->subcommandCollector);
	}

	public function getSubcommandCollector(){
		$f = __METHOD__;
		if(!$this->hasSubcommandCollector()){
			Debug::error("{$f} subcommand collector is undefined");
		}
		return $this->subcommandCollector;
	}

	public function setSubcommandCollector($collector){
		if($this->hasSubcommandCollector()){
			$this->releaseSubcommandCollector();
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->subcommandCollector = $collector;
		}
		return $this->subcommandCollector = $this->claim($collector);
	}

	/**
	 * Send a command needed to generate this element to an object designated for handling it
	 *
	 * @param Command $command
	 * @return Command
	 */
	public function reportSubcommand($command){
		$f = __METHOD__;
		try{
			$print = false;
			$push_to_use_case = false;
			if($this->hasSubcommandCollector()){
				$collector = $this->getSubcommandCollector();
				if($print){
					$sccc = $collector->getClass();
					Debug::print("{$f} subcommand collector is a {$sccc}");
				}
				return $collector->reportSubcommand($command);
			}elseif($this->getCatchReportedSubcommandsFlag()){
				if($print){
					Debug::print("{$f} catch reported subcommands flag is set");
				}
				$this->pushReportedSubcommands($command);
			}elseif($this->hasParentNode()){
				if($print){
					Debug::print("{$f} parent node is set");
				}
				$parent_node = $this->getParentNode();
				while(true){
					if($parent_node->hasSubcommandCollector()){
						$collector = $parent_node->getSubcommandCollector();
						if($print){
							$sccc = $collector->getClass();
							Debug::print("{$f} parent node's subcommand collector is a {$sccc}");
						}
						return $collector->reportSubcommand($command);
					}elseif($parent_node->getCatchReportedSubcommandsFlag()){
						if($print){
							Debug::print("{$f} parent node's catch reported subcommands flag is set");
						}
						$parent_node->pushReportedSubcommands($command);
						return $command;
					}elseif($parent_node->hasParentNode()){
						$parent_node = $parent_node->getParentNode();
					}else{
						$push_to_use_case = true;
						break;
					}
				}
			}else{
				$push_to_use_case = true;
			}
			if($push_to_use_case){
				$uc = app()->getUseCase();
				if($print){
					$did = $uc->getDebugId();
					Debug::print("{$f} parent node is undefined; pushing subcommand directly to use case with debug ID \"{$did}\"");
				}
				if(app()->getResponse(app()->getUseCase())->getRefuseCommandsFlag()){
					Debug::warning("{$f} response is refusing media commands");
					$this->debugPrintRootElement();
				}
				$uc->pushCommand($command);
			}
			return $command;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getCatchReportedSubcommandsFlag(): bool{
		return $this->getFlag("catchReportedSubcommands");
	}

	public function setCatchReportedSubcommandsFlag(bool $flag = true): bool{
		return $this->setFlag("catchReportedSubcommands", $flag);
	}

	public /*final*/ function __toString():string{
		$f = __METHOD__;
		try{
			ob_start();
			$this->echo(false);
			$ret = ob_get_clean();
			if(!is_string($ret)){
				$sc = $this->getShortClass();
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} return value is not a string ofr this {$sc} declared {$decl}");
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function renderUri(string $filename): int{
		$that = $this;
		include $filename;
		$this->dispatchCommands();
		return SUCCESS;
	}

	public function hasSuccessors(): bool{
		return $this->hasArrayProperty("successors");
	}

	public function setSuccessors($nodes){
		$f = __METHOD__;
		$print = false;
		if($print){
			if(!is_array($nodes)){
				Debug::print("{$f} empty!");
			}else{
				Debug::print("{$f} setting successors to the following array:");
				Debug::printArray($nodes);
			}
		}
		return $this->setArrayProperty("successors", $nodes);
	}

	protected function getSelfGeneratedPredecessors():?array{
		return null;
	}
	
	protected function getSelfGeneratedSuccessors():?array{
		return null;
	}

	public final function getSuccessors(){
		return $this->getProperty("successors");
	}

	public function unshiftSuccessor(...$values): int{
		$f = __METHOD__;
		$print = false;
		if($print){
			if($this->hasSuccessors()){
				Debug::print("{$f} successors already exist");
			}else{
				Debug::print("{$f} no successors");
			}
			foreach($values as $value){
				Debug::print("{$f} unshifting \"{$value}\"");
			}
		}
		return $this->unshiftArrayProperty("successors", ...$values);
	}

	public function hasPredecessors(): int{
		return $this->hasArrayProperty("predecessors");
	}

	public function setPredecessors($nodes){
		return $this->setArrayProperty("predecessors", $nodes);
	}

	public function unshiftPredecessor(...$values): int{
		$f = __METHOD__;
		$print = false;
		if($print){
			if($this->hasPredecessors()){
				Debug::print("{$f} predecessors already exist");
			}else{
				Debug::print("{$f} no predecessors");
			}
			foreach($values as $value){
				Debug::print("{$f} unshifting \"{$value}\"");
			}
		}
		return $this->unshiftArrayProperty("predecessors", ...$values);
	}

	public function getPredecessorsGeneratedFlag():bool{
		return $this->getFlag("predecessorsGenerated");
	}
	
	public function setPredecessorsGeneratedFlag(bool $value=true):bool{
		return $this->setFlag("predecessorsGenerated", $value);
	}
	
	public function getSuccessorsGeneratedFlag():bool{
		return $this->getFlag("successorsGenerated");
	}
	
	public function setSuccessorsGeneratedFlag(bool $value=true):bool{
		$f = __METHOD__;
		if($this instanceof RadioButtonInput && $this->getDebugFlag()){
			Debug::printStackTraceNoExit("{$f} successors have been generated for this ".$this->getDebugString());
		}
		return $this->setFlag("successorsGenerated", $value);
	}
	
	public function generatePredecessors(bool $unshift=false):void{
		$f = __METHOD__;
		if($this->getPredecessorsGeneratedFlag()){
			Debug::error("{$f} predecessors were already generated");
			return;
		}
		$this->setPredecessorsGeneratedFlag();
		$nodes = $this->getSelfGeneratedPredecessors();
		if(empty($nodes)){
			return;
		}elseif($unshift){
			$this->unshiftPredecessor(...array_values($nodes));
		}else{
			$this->pushPredecessor(...array_values($nodes));
		}
	}

	public function generateSuccessors(bool $unshift=false):void{
		$f = __METHOD__;
		if($this->getSuccessorsGeneratedFlag()){
			Debug::error("{$f} successors were already generated for this ".$this->getDebugString());
			return;
		}
		$this->setSuccessorsGeneratedFlag();
		$nodes = $this->getSelfGeneratedSuccessors();
		if(empty($nodes)){
			return;
		}elseif($unshift){
			$this->unshiftSuccessor(...array_values($nodes));
		}else{
			$this->pushSuccessor(...array_values($nodes));
		}
	}
	
	/**
	 * set this flag to auto delete predecessor nodes when executing a client side DeleteElementCommand
	 *
	 * @param bool $value
	 */
	public function setDeletePredecessorsFlag(bool $value = true): bool{
		return $this->setFlag("deletePredecessors", $value);
	}

	public function getDeletePredecessorsFlag(): bool{
		return $this->getFlag("deletePredecessors");
	}

	public function setDeleteSuccessorsFlag(bool $value = true): bool{
		return $this->setFlag("deleteSuccessors", $value);
	}

	public function getDeleteSuccessorsFlag(): bool{
		return $this->getFlag("deleteSuccessors");
	}

	public function getPreservePredecessorsFlag(): bool{
		return $this->getFlag("preservePredecessors");
	}

	public function getPreserveSuccessorsFlag(): bool{
		return $this->getFlag("preserveSuccessors");
	}

	public function setPreservePredecessorsFlag(bool $value = true): bool{
		return $this->setFlag("preservePredecessors", $value);
	}

	public function setPreserveSuccessorsFlag(bool $value = true): bool{
		return $this->setFlag("preserveSuccessorsFlag", $value);
	}

	public final function getPredecessors(){
		return $this->getProperty("predecessors");
	}

	public function getDeletedFlag(): bool{
		return $this->getFlag("deleted");
	}

	public function setDeletedFlag(bool $value = true){
		return $this->setFlag("deleted", $value);
	}

	private function echoPredecessors(bool $deallocate = false):void{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered for this ".$this->getDebugString());
			}
			$mode = $this->getAllocationMode();
			if($mode === ALLOCATION_MODE_ULTRA_LAZY){
				if($print){
					Debug::print("{$f} generating ultra lazy predecessors");
				}
				$this->generatePredecessors();
			}
			if($this->hasPredecessors()){
				foreach($this->getPredecessors() as $i => $predecessor){
					if(is_object($predecessor)){
						if($predecessor instanceof Element){
							if(!$predecessor->getAllocatedFlag()){
								Debug::error("{$f} predecessor was already deallocated");
							}
							$predecessor->echo($deallocate);
						}elseif($predecessor instanceof ValueReturningCommandInterface){
							if($print){
								Debug::print("{$f} predecessor is a value-returning media command");
							}
							echo $predecessor->evaluate();
						}else{
							$pc = $predecessor->getClass();
							Debug::error("{$f} predecessor is an object of class \"{$pc}\"");
						}
						
					}elseif(is_array($predecessor)){
						Debug::error("{$f} predecessor is an array");
					}else{
						echo $predecessor;
					}
					if($deallocate){
						$this->releaseArrayPropertyKey('predecessors', $i, true);
					}
				}
			}elseif($print){
				Debug::print("{$f} no predecessor nodes");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getElementTag(): string{
		$f = __METHOD__;
		try{
			if($this->hasElementTag()){
				return $this->tag;
			}elseif(method_exists($this, "getElementTagStatic")){
				return static::getElementTagStatic();
			}
			Debug::warning("{$f} undefined element tag for this ".$this->getDebugString());
			Debug::printStackTraceNoExit();
			return "e";
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function removeClassAttribute(string $remove_me):bool{
		if(!isset($this->classList)){
			return false;
		}
		$unset_us = [];
		foreach($this->classList as $index => $classname){
			if($remove_me === $classname){
				array_unshift($unset_us, $index);
			}
		}
		if(empty($unset_us)){
			return false;
		}
		foreach($unset_us as $index){
			$this->release($this->classList[$index]);
		}
		return true;
	}
	
	/**
	 * output this element to the user's browser as HTML
	 *
	 * @param boolean $deallocate
	 * @return int
	 */
	public function echo(bool $deallocate = false): void{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if(!$this->getAllocatedFlag()){
				Debug::warning("{$f} this object was already deleted");
				$this->debugPrintRootElement();
			}elseif($this->hasWrapperElement()){
				$wrapper0 = $this->getWrapperElement();
				$wrapper1 = $this->getWrapperElement();
				if($print){
					Debug::print("{$f} this element has a wrapper -- echoing it now");
					if($wrapper0->hasStyleProperties()){
						Debug::print("{$f} wrapper has the following inline style properties:");
						Debug::printArray($wrapper0->getStyleProperties());
					}else{
						Debug::print("{$f} wrapper does not have style properties");
					}
				}
				unset($this->wrapperElement);
				$this->release($wrapper0);
				if($this->hasParentNode()){
					$parent = $this->getParentNode();
				}else{
					$parent = null;
				}
				$this->disableDeallocation();
				$wrapper1->appendChild($this);
				$wrapper1->echo($deallocate);
				if($deallocate){
					deallocate($wrapper1);
					$this->enableDeallocation();
				}else{
					ErrorMessage::unimplemented($f);
					$this->setWrapperElement($wrapper1);
					if($parent !== null){
						$this->setParentNode($parent);
					}
				}
				return;
			}
			if($this->getHTMLCacheableFlag() && $this->isCacheable() && HTML_CACHE_ENABLED){
				$cache_key = $this->getCacheKey() . ".html";
				if(cache()->has($cache_key)){
					if($print){
						Debug::print("{$f} cached HTML is defined for key \"{$cache_key}\"");
					}
					echo cache()->getFile($cache_key);
					return;
				}else{
					if($print){
						Debug::print("{$f} HTML is not yet cached");
					}
					$cache = true;
					ob_start();
				}
			}else{
				if($print){
					Debug::print("{$f} this object is not cacheable");
				}
				$cache = false;
			}
			$this->generateContents();
			if(!$this->getContentsGeneratedFlag()){
				Debug::error("{$f} contents generated flag should be set by now");
			}
			$this->echoPredecessors($deallocate);
			if(!$this->getPredecessorsGeneratedFlag()){
				Debug::error("{$f} predecessorsGenerated flag should be set by now");
			}
			$tag = $this->getElementTag();
			if($print){
				Debug::print("{$f} tag is \"{$tag}\" for this ".$this->getDebugString());
			}
			echo "<{$tag}";
			if($this->hasAttributes() || $this->hasClassList()){
				if($tag === "fragment"){
					Debug::error("{$f} fragments cannot have attributes");
				}elseif($print){
					Debug::print("{$f} about to echo attribute string");
				}
				$this->echoAttributeString($deallocate);
					if($print){
					Debug::print("{$f} echoed attribute string");
				}
			}
			if($this->isEmptyElement()){
				echo "/";
			}
			echo ">";
			if(!$this->isEmptyElement()){
				$this->echoInnerHTML($deallocate);
				if($print){
					Debug::print("{$f} echoed inner HTML");
				}
				echo "</{$tag}>\n";
			}elseif($print){
				Debug::print("{$f} this is an empty element");
			}
			if($deallocate && $this->hasAttributes()){
				$this->releaseAttributes($deallocate);
			}
			$this->echoSuccessors($deallocate);
			if($print){
				Debug::print("{$f} echoed successors");
			}
			if($cache){
				$cache_key = $this->getCacheKey() . ".html";
				if($print){
					Debug::print("{$f} about to update cache for key \"{$cache_key}\"");
				}
				$html = ob_get_clean();
				cache()->set($cache_key, $html, time() + 30 * 60);
				echo $html;
				unset($html);
			}elseif($print){
				Debug::print("{$f} nothing to cache");
			}
			// flush();
			if(Request::isAjaxRequest()){
				$this->dispatchCommands();
			}
			return;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function scrollIntoView(bool $alignToTop): ScrollIntoViewCommand{
		return new ScrollIntoViewCommand($this, $alignToTop);
	}

	private function echoSuccessors(bool $deallocate = false): void{
		$f = __METHOD__;
		try{
			$print = false;
			$mode = $this->getAllocationMode();
			if($mode === ALLOCATION_MODE_ULTRA_LAZY){
				if($print){
					Debug::print("{$f} generating ultra lazy successors");
				}
				$this->generateSuccessors();
			}
			if($this->hasSuccessors()){
				$count = $this->getSuccessorCount();
				if($print){
					Debug::print("{$f} {$count} successors");
					foreach($this->getSuccessors() as $s){
						Debug::print("{$f} {$s}");
					}
				}
				foreach($this->getSuccessors() as $key => $successor){
					$print = $print || $successor instanceof Basic && $successor->getDebugFlag();
					if(is_object($successor)){
						if($successor instanceof Element){
							$class = $successor->getClass();
							if(!$successor->getAllocatedFlag()){
								$decl = $successor->hasDeclarationLine() ? $successor->getDeclarationLine() : "unknown";
								Debug::error("{$f} successor of class \"{$class}\" was deallocated; it was declared at {$decl}");
							}elseif($print){
								Debug::print("{$f} successor of class \"{$class}\" was NOT deallocated");
							}
							$successor->echo($deallocate);
						}elseif($successor instanceof ValueReturningCommandInterface){
							if($print){
								Debug::print("{$f} successor is a value-returning media command");
							}
							echo $successor->evaluate();
						}else{
							$sc = $successor->getClass();
							Debug::error("{$f} successor is an object of class \"{$sc}\"");
						}
					}elseif(is_array($successor)){
						Debug::error("{$f} successor is an array");
					}else{
						echo $successor;
					}
					if($deallocate){
						$this->releaseArrayPropertyKey('successors', $key, $deallocate);
					}
				}
			}elseif($print){
				Debug::print("{$f} no successor nodes");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function generateChildNodes(): ?array{
		if($this->hasChildNodes()){
			return $this->getChildNodes();
		}
		return null;
	}

	protected static function allowUseCaseAsContext(): bool{
		return false;
	}

	/**
	 * Irreversibly associates an element with an object that provides values for all its context-sentivie variables, possibly also triggering child node generation
	 *
	 * @param DataStructure|UseCase|SpamEmail $context
	 * @return DataStructure|UseCase|SpamEmail
	 */
	public function bindContext($context){
		$f = __METHOD__;
		try{
			$print = false;
			if(!isset($context)){
				Debug::error("{$f} context is undefined");
			}elseif($context instanceof UseCase && !$this->allowUseCaseAsContext()){
				Debug::error("{$f} context is a use case");
			}elseif($this->hasContext()){
				$context = $this->getContext();
				if(is_object($context)){
					$cc = $context->getClass();
					if($context instanceof Datum){
						$cn = $context->getName();
						Debug::error("{$f} context is a {$cc} named \"{$cn}\"");
					}
					Debug::error("{$f} context is an object of class \"{$cc}\"");
				}else{
					$gottype = gettype($context);
					Debug::error("{$f} context is a {$gottype}");
				}
			}
			if($context instanceof ValueReturningCommandInterface){
				while($context instanceof ValueReturningCommandInterface){
					$context = $context->evaluate();
				}
			}
			if(false && app()->getFlag("debug")){
				if($context instanceof Datum){
					$this->setAttribute("context_debug", $context->getDebugString());
				}
				$bound = get_file_line(["bindContext", "__construct"], 16);
				$this->setAttribute("bound", $bound);
			}
			$this->setContext($context);
			$mode = $this->getAllocationMode();
			switch($mode){
				case ALLOCATION_MODE_EMAIL:
					if(!$this->hasEmbeddedImageCollector() && $context instanceof SpamEmail){
						$this->setEmbeddedImageCollector($context);
					}
					$this->generateChildNodes();
					break;
				case ALLOCATION_MODE_FORM:
				case ALLOCATION_MODE_FORM_TEMPLATE:
					if($print){
						Debug::print("{$f} form rendering mode");
					}
					if($this instanceof InputlikeInterface && $this->hasForm() && $this->getForm() instanceof RepeatingFormInterface){
						$this->generateChildNodes();
					}
					break;
				case ALLOCATION_MODE_DOMPDF_COMPATIBLE:
				case ALLOCATION_MODE_EAGER:
				// case ALLOCATION_MODE_FORM:
				case ALLOCATION_MODE_TEMPLATE:
					$this->generateChildNodes(); // XXX why is this not generate contents?
					break;
				default:
					if($print){
						Debug::print("{$f} rendering mode \"{$mode}\", skipping child node generation");
					}
					break;
			}
			return $context;
		}catch(Exception $x){}
	}

	public function setResizeAttribute($resize){
		return $this->setAttribute("resize", $resize);
	}

	public static function getUriStatic(): ?string{
		$f = __METHOD__;
		if(!isset(static::$uriStatic)){
			Debug::error("{$f} input form URI is undefined");
		}
		return static::$uriStatic;
	}

	public function hasEmbeddedImageCollector(): bool{
		return isset($this->embeddedImageCollector);
	}

	public function getEmbeddedImageCollector(){
		$f = __METHOD__;
		if(!$this->hasEmbeddedImageCollector()){
			Debug::error("{$f} embedded image collector is undefined");
		}
		return $this->embeddedImageCollector;
	}

	public function setEmbeddedImageCollector($collector){
		if($this->hasEmbeddedImageCollector()){
			$this->release($this->embeddedImageCollector);
		}
		return $this->embeddedImageCollector = $this->claim($collector);
	}

	public function reportEmbeddedImage($data){
		$f = __METHOD__;
		$print = false;
		if($this->hasEmbeddedImageCollector()){
			if($print){
				Debug::print("{$f} this element has a designated embedded image collector");
			}
			return $this->getEmbeddedImageCollector()->reportEmbeddedImage($data);
		}elseif($this->hasParentNode()){
			if($print){
				Debug::print("{$f} going to ask parent node");
			}
			return $this->getParentNode()->reportEmbeddedImage($data);
		}
		Debug::error("{$f} embedded image collector and parent node are both undefined");
	}

	public function position($value): Element{
		$this->setStyleProperty("position", $value);
		return $this;
	}

	public function display($value): Element{
		$this->setStyleProperty("display", $value);
		return $this;
	}

	public function echoInlineStyleAttribute(bool $deallocate = false): void{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasInlineStyleAttribute()){
			Debug::error("{$f} inline style attribute is undefined");
		}
		foreach($this->style as $key => $value){
			if($print){
				Debug::print("{$f} echoing attribute \"{$key}\" value \"{$value}\"");
				if($key === "debug"){
					$decl = $this->getDeclarationLine();
					Debug::print("{$f} declared line \"{$decl}\"");
				}
			}
			if($value instanceof Command){
				$decl = $value->getDeclarationLine();
				Debug::error("{$f} value is a command, instantiated {$decl}");
			}
			echo "{$key}:{$value};";
		}
		if($deallocate){
			$this->release($this->style, $deallocate);
		}
	}

	public function hasStyleProperties(...$keys): bool{
		$f = __METHOD__;
		$print = false;
		if(!isset($this->style) || !is_array($this->style) || empty($this->style)){
			if($print){
				Debug::print("{$f} style property is unset, not an array or empty");
			}
			return false;
		}elseif(!isset($keys) && count($keys) === 0){
			if($print){
				Debug::print("{$f} did not receive any keys parameter");
			}
			return true;
		}
		foreach($keys as $key){
			if(!$this->hasStyleProperty($key)){
				if($print){
					Debug::print("{$f} do not have a \"{$key}\" parameter, returning false");
				}
				return false;
			}
		}
		if($print){
			$style = $this->style;
			if(empty($style)){
				Debug::error("{$f} despite everything, getStyleProperties returns an empty array");
			}
			Debug::print("{$f} returning true");
		}
		return true;
	}

	public function hasStyleProperty(string $key):bool{
		if(!isset($this->style) || !is_array($this->style) || empty($this->style)){
			return false;
		}
		return array_key_exists($key, $this->style);
	}
	
	public function hasInlineStyleAttribute(): bool{
		return $this->hasStyleProperties() || $this->hasAttribute("style");
	}

	public function getStyleProperties(...$keys): ?array{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasStyleProperties(...$keys)){
			Debug::error("{$f} inline style properties are undefined for the keys provided");
		}elseif(!isset($keys) || count($keys) === 0){
			if($print){
				Debug::print("{$f} did not receive a keys parameter. Returning the whole array");
			}
			return $this->style;
		}
		$ret = [];
		foreach($keys as $key){
			$ret[$key] = $this->getStyleProperty($key);
		}
		if($print){
			Debug::print("{$f} returning a selection of properties");
			$crap = [];
			$this->temp(...$crap);
		}
		return $ret;
	}

	public function temp(...$keys){
		$f = __METHOD__;
		if(isset($keys)){
			Debug::error("{$f} crap!");
		}
		Debug::print("{$f} OK");
	}
	
	public function getStyleProperty(string $key){
		$f = __METHOD__;
		if(!$this->hasStyleProperty($key)){
			Debug::error("{$f} style property \"{$key}\" is undefined");
		}
		return $this->style[$key];
	}
	
	public function ejectStyleProperties():?array{
		$style = $this->style;
		$this->release($this->style);
		return $style;
	}
	
	public function getInlineStyleAttribute(): ?string{
		$f = __METHOD__;
		if(!$this->hasInlineStyleAttribute()){
			Debug::error("{$f} inline style attribute is undefined");
		}elseif($this->hasAttribute("style")){
			return $this->getAttribute("style");
		}
		$attr = "";
		foreach($this->style as $key => $value){
			$attr .= "{$key}:{$value};";
		}
		return $attr;
	}

	public function setAttribute(string $key, $value = null){
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasAttribute($key)){
				$this->removeAttribute($key);
			}
			if($value instanceof Attribute){
				if(!$this->getTemplateFlag()){
					$key = $value->getName();
					while($key instanceof ValueReturningCommandInterface){
						$key = $key->evaluate();
					}
					$value = $value->getValue();
					while($value instanceof ValueReturningCommandInterface){
						$value = $value->evaluate();
					}
				}
			}elseif(!preg_match('([A-Za-z0-9_]+)', $key)){
				Debug::error("{$f} key \"{$key}\" is not alphanumeric");
			}elseif(strlen($key) < 1){
				Debug::error("{$f} key \"{$key}\" is zero length");
			}elseif($value instanceof ValueReturningCommandInterface && !$this->getTemplateFlag()){
				$class = get_class($value);
				if($print){
					Debug::print("{$f} value is a value returning command of class \"{$class}\", and the template flag is not set");
				}
				$value = $value->evaluate();
				if($value instanceof ValueReturningCommandInterface){
					$class = $value->getClass();
					Debug::error("{$f} evalutation returned a command of class \"{$class}\"");
				}
			}
			if($print){
				if($value instanceof Command){
					$err = "{$f} value for attribute \"{$key}\" is a command";
				}elseif($value instanceof Attribute){
					$err = "{$f} valus for attribute \'{$key}\" is an Attribute";
				}else{
					$err = "{$f} setting attribute \"{$key}\" to \"{$value}\"";
				}
				if($this instanceof InputlikeInterface){
					if($this->hasNameAttribute()){
						$name = $this->getNameAttribute();
						$err .= " for input with name \"{$name}\"";
					}else{
						$err .= " for unnamed input";
					}
				}
				Debug::print($err);
			}
			if(!isset($this->attributes) || !is_array($this->attributes)){
				$this->attributes = [];
			}
			if($value instanceof Command && !$this->getTemplateFlag()){
				Debug::error("{$f} command should have been evaluated by now");
			}
			return $this->attributes[$key] = $this->claim($value);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setStyleProperty(string $key, $value){
		$f = __METHOD__;
		try{
			$print = false;
			$f = __METHOD__;
			if(!$this->getTemplateFlag()){
				while($key instanceof ValueReturningCommandInterface){
					if($print){
						Debug::print("{$f} template flag is not set, key is a value returning command");
					}
					$key = $key->evaluate();
				}
				while($value instanceof ValueReturningCommandInterface){
					if($print){
						Debug::print("{$f} template flag is not set, value is a value returning command");
					}
					$value = $value->evaluate();
				}
			}
			if(!$this->hasStyleProperties()){
				$this->style = [];
			}elseif($value === null){
				if(!$this->hasStyleProperty($key)){
					Debug::error("{$f} stlye property \"{$key}\" is undefined for ".$this->getDebugString());
				}
				$this->ejectProperty($key);
				return null;
			}elseif($this->hasStyleProperty($key)){
				$this->release($this->style[$key]);
			}
			return $this->style[$key] = $this->claim($value);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setStyleProperties(?array $keyvalues):?array{
		$f = __METHOD__;
		if($keyvalues === null){
			Debug::printStackTraceNoExit("{$f} key values array is null");
			$this->release($this->style);
			return null;
		}
		foreach($keyvalues as $key => $value){
			$this->setStyleProperty($key, $value);
		}
		return $keyvalues;
	}

	public function setStylePropertiesCommand(?array $properties): SetStylePropertiesCommand{
		return new SetStylePropertiesCommand($this, $properties);
	}

	public function getAttributes(...$keys): ?array{
		$f = __METHOD__;
		if(!is_array($this->attributes)){
			Debug::error("{$f} attributes array was not defined");
		}elseif(isset($keys) && count($keys) > 0){
			$ret = [];
			foreach($keys as $key){
				if(!$this->hasAttribute($key)){
					Debug::warning("{$f} attribute \"{$key}\" is undefined");
					continue;
				}
				$ret[$key] = $this->getAttribute($key);
			}
			return $ret;
		}
		return $this->attributes;
	}

	public function hasIdAttribute(): bool{
		return $this->hasAttribute("id");
	}

	public function setIdAttribute($id){
		return $this->setAttribute("id", $id);
	}

	public function getIdAttribute(){
		$f = __METHOD__;
		if(!$this->hasIdAttribute()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} ID attribute is undefined; declared {$decl}");
		}
		return $this->getAttribute("id");
	}

	public function withIdAttribute(string $id): Element{
		$this->setAttribute("id", $id);
		return $this;
	}

	public function hasClassAttribute(): bool{
		if($this->hasClassList()){
			return true;
		}
		return $this->hasAttribute("class");
	}

	public function hasClassList(): bool{
		return isset($this->classList);
	}

	public function setClassList(?array $list):?array{
		if($this->hasClassList()){
			$this->releaseClassList();
		}
		return $this->classList = $this->claim($list);
	}
	
	public function echoClassAttribute(bool $deallocate = false): void{
		$f = __METHOD__; 
		if($this->hasAttribute("class")){
			echo $this->getAttribute("class");
		}
		if(!$this->hasClassList()){
			return;
		}
		$i = 0;
		foreach($this->classList as $class){
			if($i > 0){
				echo " ";
			}
			echo $class;
			$i ++;
		}
		if($deallocate){
			$this->release($this->classList, $deallocate);
		}
	}

	public function hide(): Element{
		$this->setHiddenAttribute();
		return $this;
	}

	public function getClassAttribute(){
		$f = __METHOD__;
		$print = false;
		if($this->hasAttribute("class")){
			if($print){
				$ret = $this->getAttribute("class");
				Debug::print("{$f} class is specifically defined as \"{$ret}\"");
			}
			return $this->getAttribute("class");
		}elseif(!$this->hasClassList()){
			Debug::error("{$f} class list is undefined");
		}elseif($print){
			Debug::print("{$f} imploding class list");
		}
		return implode(' ', $this->classList);
	}

	public function getClassList(): ?array{
		$f = __METHOD__;
		if(!$this->hasClassList()){
			Debug::error("{$f} classList is undefined for this ".$this->getDebugString());
		}
		return $this->classList;
	}

	public function releaseClassList(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasClassList()){
			Debug::error("{$f} classList is undefined");
		}
		$cl = $this->classList;
		unset($this->classList);
		$this->release($cl, $deallocate);
	}
	
	public function addClassCommand(...$classes): AddClassCommand{
		return new AddClassCommand($this, ...$classes);
	}

	public function addClassAttribute(...$classes): void{
		if(!is_array($this->classList)){
			$this->classList = [];
		}
		foreach($classes as $class){
			if($class instanceof HitPointsInterface){
				$this->claim($class);
			}
			if(false === array_search($class, $this->classList)){
				array_push($this->classList, $class);
			}
		}
	}

	public function setClassAttribute(?string $class){
		if($this->hasClassList()){
			$this->release($this->classList);
		}
		return $this->setAttribute("class", $class);
	}

	public function withClassAttribute(...$class): Element{
		$this->addClassAttribute(...$class);
		return $this;
	}

	public function getDocumentFragment(): DocumentFragment{
		$f = __METHOD__;
		if(!$this->hasDocumentFragment()){
			Debug::error("{$f} document fragment is undefined");
		}
		return $this->documentFragment;
	}

	public function hasDocumentFragment():bool{
		return isset($this->documentFragment) && $this->documentFragment instanceof DocumentFragment;
	}

	public function setDocumentFragment(?DocumentFragment $df):?DocumentFragment{
		if($this->hasDocumentFragment()){
			$this->releaseDocumentFragment();
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->documentFragment = $df;
		}
		return $this->documentFragment = $this->claim($df);
	}

	/**
	 * Generates a JavaScriptFunction that when executed clientside, returns a JS element equivalent to this object as if it was rendered on the server (when bound to the same context, if necessary)
	 *
	 * @return JavaScriptFunction
	 */
	public final function generateTemplateFunction():?JavaScriptFunction{
		$f = __METHOD__;
		try{
			$generator = new TemplateElementFunctionGenerator();
			$ret = $generator->generate($this);
			deallocate($generator);
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasResponsiveStyleProperties(): bool{
		return is_array($this->responsiveStyleProperties) && !empty($this->responsiveStyleProperties);
	}

	public function getResponsiveStyleProperties(): array{
		$f = __METHOD__;
		if(!$this->hasResponsiveStyleProperties()){
			Debug::error("{$f} responsive style properties are undefined");
		}
		return $this->responsiveStyleProperties;
	}
	
	/**
	 * if this element does not have a defined variable name, define it, and increment the counter
	 *
	 * @param int $counter
	 * @return string
	 */
	public function incrementVariableName(int &$counter): ?string{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($print){
			$ds = $this->getDebugString();
			Debug::print("{$f} entered for this {$ds}");
		}
		if($this->hasIdOverride()){
			if($print){
				$vn = $this->getIdOverride();
				Debug::print("{$f} variable name is already defined as {$vn}");
			}
			return $this->getIdOverride();
		}elseif($print){
			Debug::print("{$f} ID override is not set, about to set it now");
		}
		$vname = $this->setIdOverride("e{$counter}");
		$counter++;
		return $vname;
	}

	public function setLocalDeclarations(?array $values): ?array{
		$f = __METHOD__;
		if($values !== null && !is_array($values)){
			Debug::error("{$f} local declarations must be an associative array");
		}
		return $this->setArrayProperty("localDeclarations", $values);
	}

	public function hasLocalDeclarations(): bool{
		return $this->hasArrayProperty("localDeclarations");
	}

	public function getLocalDeclarations(): ?array{
		return $this->getProperty("localDeclarations");
	}

	public function getLocalDeclaration($i){
		return $this->getArrayPropertyValue("localDeclarations", $i);
	}
	
	public function setLocalDeclaration($key, $value){
		return $this->setArrayPropertyValue("localDeclarations", $key, $value);
	}

	public function pushLocalDeclaration(...$values):int{
		return $this->pushArrayProperty("localDeclarations", ...$values);
	}
	
	public function mergeLocalDeclarations(?array $values): ?array{
		return $this->mergeArrayProperty("localDeclarations", $values);
	}

	public function getLocalDeclarationCount():int{
		return $this->getArrayPropertyCount("localDeclarations");
	}

	public static function getValidOnEventCommands(): ?array{
		return [
			"onblur" => SetOnBlurCommand::class,
			"onclick" => SetOnClickCommand::class,
			"onfocus" => SetOnFocusCommand::class,
			"onkeydown" => SetOnKeyDownCommand::class,
			"onkeyup" => SetOnKeyUpCommand::class
		];
	}

	public function onClick($onclick): Element{
		$this->setOnClickAttribute($onclick);
		return $this;
	}

	/**
	 * returns a GetDeclaredVariableCommand that contains either the key of this element's context
	 * or the string 'new'.
	 * This is a templatable shortcut for $key = $context->isUninitialized() ? "new" : $context->getIdentifierValue()
	 *
	 * @param Element $element
	 * @return GetDeclaredVariableCommand
	 */
	public function getResolvedKey($context = null, ?string $keyname=null){
		$f = __METHOD__;
		try{
			$print = false;
			if($context == null){
				$context = $this->getContext();
			}
			$scope = $this->getResolvedScope();
			if($keyname === null){
				$keyname = $context->getIdentifierName();
			}
			if($scope->hasLocalValue($keyname)){
				return $scope->getLocalValue($keyname);
			}
			$gcvc = new GetColumnValueCommand();
			$gcvc->setDataStructure($context);
			$gcvc->setColumnName($keyname);
			$try = new TryCatchCommand($keyname);
			$try->catch($scope->let($keyname)->withFlag("null", true))->resolveCatch();
			$has = new HasColumnValueCommand($context, $keyname);
			$mode = $this->hasAllocationMode() ? $this->getAllocationMode() : ALLOCATION_MODE_UNDEFINED;
			if($this->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE){
				$get_context = new GetDeclaredVariableCommand();
				$get_context->setVariableName("context");
				$predicate = new AndCommand($get_context, $has);
			}else{
				$predicate = $has;
			}
			$redeclare1 = $scope->redeclare($keyname, $gcvc);
			$redeclare2 = $scope->redeclare($keyname, "new");
			$if = IfCommand::if($predicate)->then($redeclare1)->else($redeclare2);
			$this->resolveTemplateCommand($try, $if);
			if(!$this->getTemplateFlag()){
				$this->disableDeallocation();
				$context->disableDeallocation();
				$scope->disableDeallocation();
				deallocate($try);
				deallocate($if);
				$this->enableDeallocation();
				$context->enableDeallocation();
				$scope->enableDeallocation();
			}
			$gdvc = $scope->getDeclaredVariableCommand($keyname);
			//$this->announce($gdvc);
			return $gdvc;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasLocalDeclaration($key):bool{
		return $this->hasArrayPropertyKey('localDeclarations', $key);
	}
	
	public function releaseLocalDeclaration($key, bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasLocalDeclaration($key)){
			Debug::error("{$f} ".$this->getDebugString()." does not have a local declaration \"{$key}\"");
		}
		$this->releaseArrayPropertyKey('localDeclarations', $key, $deallocate);
	}
	
	public function getResolvedColumnValue($context, $index){
		ErrorMessage::unimplemented(__METHOD__);
	}

	/**
	 * Processes a Command object by either executing server-side (if this element is nontemplated)
	 * or appending the command as a child node (if this element is templated).
	 *
	 * @param Command $command
	 * @return Command|DeclareVariableCommand|NodeBearingCommandInterface|SwitchCommand|ServerExecutableCommandInterface
	 */
	public function resolveTemplateCommand(...$commands): void{
		resolve_template_command($this, ...$commands);
	}

	public function releaseChildNodes(bool $deallocate, ...$keys){
		$f = __METHOD__;
		if(!$this->hasChildNodes()){
			Debug::error("{$f} no child nodes to release");
		}
		if(!isset($keys) || count($keys) === 0){
			$keys = array_keys($this->getChildNodes());
		}
		foreach($keys as $key){
			$this->releaseChildNode($key, $deallocate);
		}
	}
	
	public function setChildNodes(?array $nodes): ?array{
		if($this->hasChildNodes()){
			$this->releaseChildNodes(false);
		}
		$this->childNodes = [];
		$this->appendChild(...$nodes);
		return $nodes;
	}

	public function setReplacementId(?string $id): ?string{
		if($this->hasReplacementId()){
			$this->release($this->replacementId);
		}
		return $this->replacementId = $this->claim($id);
	}

	public function hasReplacementId(): bool{
		return isset($this->replacementId);
	}

	public function getReplacementId(): string{
		$f = __METHOD__;
		if(!$this->hasReplacementId()){
			Debug::error("{$f} replacement ID is undefined");
		}
		return $this->replacementId;
	}

	public function getArrayKey(int $count){
		if($this->hasReplacementId()){
			return $this->getReplacementId();
		}elseif($this->hasIdAttribute()){
			$id = $this->getIdAttribute();
			if(is_string($id)){
				return $id;
			}elseif($id instanceof ValueReturningCommandInterface && ! $this->getTemplateFlag()){
				while($id instanceof ValueReturningCommandInterface){
					$id = $id->evaluate();
				}
				return $id;
			}
		}
		return $count;
	}

	/**
	 * returns an array of Commands for templateworthy elements that declare all necessary variables
	 * needed to generate the element, so resolveTemplateCommand doesn't need to be called
	 * (use getResolvedScope instead)
	 *
	 * @param DataStructure|array $context
	 * @param Scope $scope
	 * @return array
	 */
	protected function getScopeResolutionCommands($context, Scope $scope):?array{
		return [];
	}

	protected final function getResolvedScope():Scope{
		$f = __METHOD__;
		$print = false;
		if($this->hasScope()){
			return $this->getScope();
		}
		$context = $this->hasContext() ? $this->getContext() : null;
		$scope = new Scope();
		$commands = $this->getScopeResolutionCommands($context, $scope);
		if(!is_array($commands) || empty($commands)){
			if($print){
				Debug::warning("{$f} don't call this function unless there are actual commands to resolve");
			}
		}else{
			$this->resolveTemplateCommand(...$commands);
		}
		if($print){
			Debug::print("{$f} about to assign and claim scope for this ".$this->getDebugString());
		}
		return $this->setScope($scope);
	}

	public function setDisposeContextFlag(bool $value=true):bool{
		return $this->setFlag("disposeContext", $value);
	}

	public function disposeContext(bool $value=true):Element{
		$this->setDisposeContextFlag($value);
		return $this;
	}
	
	public function getDisposeContextFlag():bool{
		return $this->getFlag("disposeContext");
	}

	public function copy($that):int{
		$f = __METHOD__;
		$ret = parent::copy($that);
		$mode = $that->hasAllocationMode() ? $that->getAllocationMode() : ALLOCATION_MODE_UNDEFINED;
		$this->setAllocationMode($mode);
		if($that->getTemplateFlag()){
			$this->setTemplateFlag(true);
		}
		if($that->getTemplateFlag() && !$this->getTemplateFlag()){
			Debug::error("{$f} template flag did not get set correctly by replica. Allocation mode is \"{$mode}\"");
		}
		//use CacheableTrait;
		if($that->hasCacheKey()){
			$this->setCacheKey(replicate($that->getCacheKey()));
		}
		if($that->hasTimeToLive()){
			$this->setTimeToLive(replicate($that->getTimeToLive()));
		}
		//use ElementTagTrait;
		if($that->hasElementTag()){
			$this->setElementTag(replicate($that->getElementTag()));
		}
		//use UriTrait;
		if($that->hasURI()){
			$this->setUri(replicate($that->getUri()));
		}
		//protected $attributes;
		if($that->hasAttributes()){
			foreach($that->getAttributes() as $key => $attr){
				if(in_array($key, ['declared', 'php_class', 'debugid'])){
					continue;
				}
				$this->setAttribute($key, replicate($attr));
			}
		}
		//protected $classList;
		if($that->hasClassList()){
			$this->setClassList(replicate($that->getClassList()));
		}
		//protected $context;
		if($that->hasContext()){
			$this->setContext($that->getContext());
		}
		//protected $embeddedImageCollector;
		/*if($that->hasEmbeddedImageCollector()){
			$this->setEmbeddedImageCollector($that->getEmbeddedImageCollector());
		}*/
		if($that->hasIdOverride()){
			$this->setIdOverride(replicate($that->getIdOverride()));
		}
		//private $innerHTML;
		if($that->hasInnerHTML()){
			$this->setInnerHTML(replicate($that->getInnerHTML()));
		}
		//protected $scope;
		if($that->hasScope()){
			$this->setScope($that->getScope());
		}
		//protected $replacementId;
		if($that->hasReplacementId()){
			$this->setReplacementId(replicate($that->getReplacementId()));
		}
		//protected $responsiveStyleProperties;
		if($that->hasResponsiveStyleProperties()){
			$this->setResponsiveStyleProperties(replicate($that->getResponsiveStyleProperties()));
		}
		//protected $savedChildren;
		if($that->hasSavedChildren()){
			$this->setSavedChildren(replicate($that->getSavedChildren()));
		}
		//protected $style;
		if($that->hasStyleProperties()){
			$this->setStyleProperties(replicate($that->getStyleProperties()));
		}
		//protected $subcommandCollector;
		/*if($that->hasSubcommandCollector()){
			$this->setSubcommandCollector($that->getSubcommandCollector());
		}*/
		//protected $wrapperElement;
		if($that->hasWrapperElement()){
			$this->setWrapperElement(replicate($that->getWrapperElement()));
		}
		//private $childNodes;
		if($that->hasChildNodes()){
			$cn = replicate($that->getChildNodes());
			foreach($cn as $n){
				if(!$n instanceof ParentNodeInterface){
					continue;
				}
				$n->setParentNode($this);
			}
			$this->setChildNodes($cn);
		}
		return $ret;
	}
	
	public function releaseSubcommandCollector(bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasSubcommandCollector()){
			Debug::error("{$f} subcommand collector is undefined");
		}elseif($print){
			$ds = $this->getDebugString();
			Debug::print("{$f} releasing subcommand collector for {$ds}");
		}
		$scc = $this->subcommandCollector;
		unset($this->subcommandCollector);
		if($this->hasAnyEventListener(EVENT_RELEASE_SUBCOMMAND_COLLECTOR)){
			$this->dispatchEvent(new ReleaseSubcommandCollectorEvent($scc, $deallocate));
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			unset($scc);
			return;
		}
		$this->release($scc, $deallocate);
	}
	
	public function releaseDocumentFragment(bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasDocumentFragment()){
			Debug::error("{$f} document fragment is undefined");
		}elseif($print){
			$ds = $this->getDebugString();
			Debug::print("{$f} releasing document fragment for {$ds}");
		}
		$df = $this->documentFragment;
		unset($this->documentFragment);
		if($this->hasAnyEventListener(EVENT_RELEASE_DOCUMENT_FRAGMENT)){
			$this->dispatchEvent(new ReleaseDocumentFragmentEvent($df, $deallocate));
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			unset($df);
			return;
		}
		$this->release($df, $deallocate);
	}
	
	public function dispose(bool $deallocate=false): void{
		$f = __METHOD__;
		$print = false;
		if($print){
			$ds = $this->getDebugString();
			if($deallocate){
				Debug::print("{$f} entered for this {$ds}. Hard deallocation");
			}else{
				Debug::print("{$f} entered for this {$ds}. Regular disposal");
			}
		}
		if($this->hasAttributes()){
			$this->releaseAttributes($deallocate);
		}
		if($this->hasChildNodes()){
			if($print){
				Debug::print("{$f} about to release child nodes for this {$ds}");
			}
			$this->releaseChildNodes($deallocate);
		}elseif($print){
			Debug::print("{$f} child nodes are undefined for this {$ds}");
		}
		if($this->hasContext()){
			$this->releaseContext($deallocate);
		}
		if($this->hasDocumentFragment()){
			$this->releaseDocumentFragment($deallocate);
		}
		if($this->hasParentNode()){
			if($print){
				Debug::print("{$f} releasing parent node ".$this->parentNode->getDebugString());
			}
			$this->releaseParentNode($deallocate);
		}elseif($print){
			Debug::print("{$f} parent node is undefined");
		}
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		if($this->hasScope()){
			$this->releaseScope($deallocate);
		}
		if($this->hasSubcommandCollector()){
			$this->releaseSubcommandCollector($deallocate);
		}elseif($print){
			Debug::print("{$f} no subcommand collector");
		}
		if($this->hasWrapperElement()){
			$this->releaseWrapperElement($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasAllocationMode()){
			$this->release($this->allocationMode, $deallocate);
		}
		if($this->hasCacheKey()){
			$this->release($this->cacheKey, $deallocate);
		}
		unset($this->childElementCount);
		if($this->hasClassList()){
			$this->release($this->classList, $deallocate);
		}
		if($this->hasEmbeddedImageCollector()){
			$this->release($this->embeddedImageCollector, $deallocate);
		}
		if($this->hasInnerHTML()){
			$this->release($this->innerHTML, $deallocate);
		}
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
		$this->release($this->replacementId, $deallocate);
		$this->release($this->responsiveStyleProperties, $deallocate);
		$this->release($this->savedChildren, $deallocate);
		$this->release($this->style, $deallocate);
		$this->release($this->tag, $deallocate);
		if($this->hasTImeToLive()){
			$this->release($this->timeToLive, $deallocate);
		}
		$this->release($this->uri, $deallocate);
		if($this->hasVariableName()){
			$this->release($this->variableName, $deallocate);
		}
	}
}
