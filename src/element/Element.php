<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\array_remove_key;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserLanguagePreference;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\ColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AddClassCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\BindElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\DeleteElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\ScrollIntoViewCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand;
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
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReusableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\email\SpamEmail;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterConstructorEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeConstructorEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterRenderEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeRenderEvent;
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;
use JulianSeymour\PHPWebApplicationFramework\form\RepeatingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
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
ArrayKeyProviderInterface, 
CacheableInterface, 
DisposableInterface, 
EchoJsonInterface, 
IncrementVariableNameInterface, 
AllocationModeInterface, 
ScopedCommandInterface{

	use AllocationModeTrait;
	use ArrayPropertyTrait;
	use CacheableTrait;
	use ElementTagTrait;
	use EventListeningTrait;
	use EchoJsonTrait;
	use IndirectParentScopeTrait;
	use ParentNodeTrait;
	use TemplateFlagTrait;
	use UriTrait;

	protected $attributes;

	private $childElementCount;

	private $childNodes;

	protected $classList;

	protected $context;

	private $documentFragment;

	protected $embeddedImageCollector;

	protected $variableName;

	private $innerHTML;

	protected $labelString;

	protected $scope;

	protected $replacementId;

	protected $responsiveStyleProperties;

	protected $savedChildren;

	protected $style;

	protected $subcommandCollector;

	protected $wrapperElement;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		$print = false;
		if ($print) {
			$mem = memory_get_usage();
			Debug::print("{$f} memory at construction: {$mem}; rendering mode \"{$mode}\"");
		}
		// Debug::checkMemoryUsage("In element constructor", 96000000);
		if ($this->hasClassList()) {
			Debug::error("{$f} classList has already been initialized");
		}
		$status = $this->beforeConstructorHook($mode, $context);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} beforeConstructorHook returned error status \"{$err}\"");
			$this->setObjectStatus($status);
		}
		$this->setAllowEmptyInnerHTML(false);
		// $this->innerHTML/ConversionMode = ELEMENT_INNERHTML_/CONVERSION_ASSOC;
		Debug::incrementElementConstructorCount();
		parent::__construct($mode, $context);
		if (! $this instanceof HTMLCommentElement && ! $this instanceof DocumentFragment) {
			if ($this->hasDeclarationLine()) {
				$decl = $this->getDeclarationLine();
				$this->setAttribute("declared", $decl);
				if ($print) {
					Debug::print("{$f} declared \"{$decl}\"");
				}
			}
			if ($this->hasDebugId()) {
				$this->setAttribute("debugid", $this->getDebugId());
			}
			$this->setAttribute("php_class", get_short_class(static::class));
		}
		// Debug::checkMemoryUsage("In element constructor, declared {$decl}", 124000000);
		$this->setAllocationMode($mode);
		if (isset($context)) {
			$this->bindContext($context);
		}
		$status = $this->afterConstructorHook($mode, $context);
		if ($status !== SUCCESS) {
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
		if ($mode !== ALLOCATION_MODE_ULTRA_LAZY) {
			$this->appendChild(...$children);
			return;
		} elseif (! isset($this->savedChildren) || ! is_array($this->savedChildren)) {
			$this->savedChildren = [];
		}
		array_push($this->savedChildren, ...$children);
	}

	public function hasSavedChildren(): bool{
		return isset($this->savedChildren) && is_array($this->savedChildren) && ! empty($this->savedChildren);
	}

	public function appendSavedChildren(bool $destroy = true){
		if ($this->hasSavedChildren()) {
			$this->appendChild(...$this->savedChildren);
			if ($destroy) {
				unset($this->savedChildren);
			}
		}
	}

	public function getSavedChildren(){
		$f = __METHOD__;
		if (! $this->hasSavedChildren()) {
			Debug::error("{$f} saved children are undefined");
		}
		return $this->savedChildren;
	}

	public function clearAttributes(){
		$this->attributes = null;
		$this->responsiveStyleProperties = null;
		$this->classList = null;
		$this->style = null;
	}

	protected function beforeConstructorHook(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null): int{
		$params = [];
		if ($mode !== null) {
			$params['allocationMode'] = $mode;
		}
		if ($context !== null) {
			$params['context'] = $context;
		}
		$this->dispatchEvent(new BeforeConstructorEvent($params));
		return SUCCESS;
	}

	protected function afterConstructorHook(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null): int{
		$params = [];
		if ($mode !== null) {
			$params['allocationMode'] = $mode;
		}
		if ($context !== null) {
			$params['context'] = $context;
		}
		$this->dispatchEvent(new AfterConstructorEvent($params));
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
		if (! $this->hasEnterKeyHintAttribute()) {
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
		if (! $this->hasDraggableAttribute()) {
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
		if (! $this->hasDirectionalityAttribute()) {
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
		if (! $this->hasContentEditableAttribute()) {
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
		if (! $this->hasAutocapitalizeAttribute()) {
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
		if (! $this->hasAccessKeyAttribute()) {
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
		if (! $this->hasItemScopeAttribute()) {
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
		if (! $this->hasItemReferenceAttribute()) {
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
		if (! $this->hasItemPropertyAttribute()) {
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
		if (! $this->hasItemIdAttribute()) {
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
		if (! $this->hasIsAttribute()) {
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
		if (! $this->hasInputModeAttribute()) {
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
		if (! $this->hasHiddenAttribute()) {
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
		if (! $this->hasTitleAttribute()) {
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
		if (! $this->hasTabIndexAttribute()) {
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
		if (! $this->hasSlotAttribute()) {
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
		if (! $this->hasPartAttribute()) {
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
		if (! $this->hasNonceAttribute()) {
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
		if (! $this->hasLanguageAttribute()) {
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
		if (! $this->hasItemTypeAttribute()) {
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
		if ($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		if ($this->hasReplacementId()) {
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

	public function appendChildCommand(...$children): AppendChildCommand{
		return new AppendChildCommand($this, ...$children);
	}

	public function getDisableRenderingFlag(): bool{
		return $this->getFlag("disableRendering");
	}

	public function setDisableRenderingFlag(bool $value = true): bool{
		return $this->setFlag("disableRendering", true);
	}

	public function disableRendering(bool $value = true): Element{
		$this->setDisableRenderingFlag($value);
		return $this;
	}

	public function setSpellcheckAttribute($value = "true"){
		return $this->setAttribute("spellcheck", $value);
	}

	public function hasSpellcheckAttribute(): bool{
		return $this->hasAttribute("spellcheck");
	}

	public function getSpellcheckAttribute(){
		$f = __METHOD__;
		if (! $this->hasSpellcheckAttribute()) {
			Debug::error("{$f} spellcheck attribute is undefined");
		}
		return $this->getAttribute("spellcheck");
	}

	public function setWrapperElement($wrapper): ?Element{
		return $this->wrapperElement = $wrapper;
	}

	public function hasWrapperElement(): bool{
		return isset($this->wrapperElement) && $this->wrapperElement instanceof Element;
	}

	public function getWrapperElement(){
		$f = __METHOD__;
		if (! $this->hasWrapperElement()) {
			Debug::error("{$f} wrapper element is undefined");
		}
		return $this->wrapperElement;
	}

	public function setAllocationMode(?int $mode): ?int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if ($mode === null) {
				if ($print) {
					Debug::print("{$f} unsetting rendering mode");
				}
				unset($this->allocationMode);
				return null;
			} elseif (! is_int($mode)) {
				Debug::error("{$f} whoops, rendering mode must be an integer");
			}
			switch ($mode) {
				case ALLOCATION_MODE_FORM_TEMPLATE:
				case ALLOCATION_MODE_TEMPLATE:
					if ($print) {
						Debug::print("{$f} template or form template generation mode");
					}
					$this->setTemplateFlag(true);
					if (! $this->getTemplateFlag()) {
						Debug::error("{$f} template flag is undefined");
					}
					break;
				case ALLOCATION_MODE_NEVER:
					if ($print) {
						Debug::print("{$f} no children");
					}
					$this->setAllowEmptyInnerHTML(true);
					break;
				case ALLOCATION_MODE_ULTRA_LAZY:
					if (! $this->isUltraLazyRenderingCompatible()) {
						if ($print) {
							Debug::print("{$f} this element is incompatible with ultra lazy rendering mode");
						}
						$mode = ALLOCATION_MODE_LAZY;
					} elseif ($print) {
						Debug::print("{$f} ultra lazy rendering mode");
					}
					break;
				default:
			}
			if ($print) {
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} setting rendering mode to \"{$mode}\". Declared {$decl}");
			}
			return $this->allocationMode = $mode;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function wrap(...$children): Element{
		$element = new static(ALLOCATION_MODE_UNDEFINED);
		$element->appendChild(...$children);
		return $element;
	}

	public function hasResizeAttribute(): bool
	{
		return $this->hasAttribute("resize");
	}

	public function hasAttribute($key, $value = null): bool
	{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasAttributes()) {
			return false;
		} elseif (! array_key_exists($key, $this->attributes)) {
			return false;
		} elseif ($value === null) {
			if ($print) {
				Debug::print("{$f} attribute exists but value is null");
			}
			return true;
		}
		return $this->attributes[$key] === $value;
	}

	public function hasAttributes(): bool{
		return isset($this->attributes) && is_array($this->attributes) && ! empty($this->attributes);
	}

	public function removeChild($child){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function setResponsiveStyleProperty($attr, $command)
	{
		if (! is_array($this->responsiveStyleProperties)) {
			$this->responsiveStyleProperties = [];
		}
		return $this->responsiveStyleProperties[$attr] = $command;
	}

	public function hasResponsiveStyleProperty(string $key): bool{
		return array_key_exists($key, $this->responsiveStyleProperties);
	}

	public function getResponsiveStyleProperty($key){
		$f = __METHOD__;
		if (! $this->hasResponsiveStyleProperty($key)) {
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
		if (! $this->hasSubcommandCollector()) {
			if ($print) {
				Debug::print("{$f} subcommand collector is undefined");
			}
			if ($this->hasParentNode()) {
				if ($print) {
					Debug::print("{$f} reporting script to parent node");
				}
				return $this->getParentNode()->reportScriptForDomInsertion($script);
			}
			Debug::error("{$f} this object lacks a subcommand collector or parent node");
		} elseif ($print) {
			Debug::print("{$f} subcommand collector is defined; pushing append child command");
		}
		$this->getSubcommandCollector()->pushSubcommand(new AppendChildCommand("head", $script));
		return $script;
	}

	public function removeAttribute(string $attr_key){
		$f = __METHOD__;
		if (! $this->hasAttribute($attr_key)) {
			Debug::error("{$f} attribute \"{$attr_key}\" is undefined");
		}
		$value = $this->getAttribute($attr_key);
		$pre_count = count($this->attributes);
		// Debug::print("{$f} attribute count prior to ejection is {$pre_count}");
		$this->attributes = array_remove_key($this->attributes, $attr_key);
		$post_count = count($this->attributes);
		if ($post_count !== $pre_count - 1) {
			$shudbi = $post_count - 1;
			Debug::error("{$f} incorrect number of attributes ({$post_count}, should be {$shudbi})");
		} elseif (! empty($this->attributes)) {
			// Debug::print("{$f} about to print modified attributes");
			// Debug::printArray($this->attributes);
		}
		return $value;
	}

	protected function beforeRenderHook(): int{
		$this->dispatchEvent(new BeforeRenderEvent());
		return SUCCESS;
	}
	
	protected function afterRenderHook(){
		$this->dispatchEvent(new AfterRenderEvent());
		return SUCCESS;
	}
	
	/**
	 * generates the child nodes/innerHTML for this element, if any, as well as predecessors and successors (depending on the rendering mode)
	 *
	 * @return string
	 */
	public final function generateContents(): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				if ($this->hasContext()) {
					$context = $this->getContext();
					if (is_object($context)) {
						$cc = $context->getClass();
						if ($context instanceof Datum) {
							$cn = $context->getName();
							Debug::print("{$f} context is a {$cc} named \"{$cn}\"");
						} else {
							Debug::print("{$f} context is an object of class \"{$cc}\"");
						}
					} else {
						$gottype = gettype($context);
						Debug::print("{$f} context is a {$gottype}");
					}
				} else {
					Debug::print("{$f} context is undefined");
				}
			}
			if (! $this->getAllocatedFlag()) {
				$decl = $this->hasDeclarationLine() ? $this->getDeclarationLine() : "unknown";
				Debug::error("{$f} this object was deallocated; declared \"{$decl}\"");
			} elseif ($this->getContentsGeneratedFlag()) {
				if ($print) {
					Debug::warning("{$f} contents already generated");
				}
				return SUCCESS;
			}
			$status = $this->beforeRenderHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeRenderHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$mode = $this->getAllocationMode();
			if ($mode !== ALLOCATION_MODE_ULTRA_LAZY) {
				$predecessors = $this->generatePredecessors();
				$this->setFlag("predecessorsGenerated", true);
				if (! empty($predecessors)) {
					if (is_associative($predecessors)) {
						if ($print) {
							Debug::print("{$f} generatePredecessors returned an associative array");
						}
						$predecessors = array_values($predecessors);
					}
					$this->pushPredecessor(...$predecessors);
				}
			} elseif ($print) {
				Debug::print("{$f} ultra lazy rendering mode, not generating predecessors");
			}
			if ($mode === ALLOCATION_MODE_LAZY) {
				$this->generateChildNodes();
			} elseif ($print) {
				Debug::print("{$f} child node generation only happens in generateContents if the rendering mode is lazy. This object's rendering mode is {$mode}");
			}
			if ($mode !== ALLOCATION_MODE_ULTRA_LAZY) {
				$successors = $this->generateSuccessors();
				if (! empty($successors)) {
					if (is_associative($successors)) {
						if ($print) {
							Debug::print("{$f} generate successor nodes returned an associative array");
						}
						$successors = array_values($successors);
					}
					$this->pushSuccessor(...$successors);
				} elseif ($print) {
					Debug::print("{$f} no successors were generated");
				}
			} elseif ($print) {
				Debug::print("{$f} ultra lazy rendering mode, not generating successors");
			}
			$this->setContentsGeneratedFlag(true);
			
			
			
			$status = $this->afterRenderHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterRenderHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * echo this element directly to the user's browser as JSON.
	 * The deserialized JSON can be converted into an element in JS with hydrate().
	 * Saves memory compared to json_encode.
	 * This function has to be gigantic because breaking it up would exceed maximum execution depth
	 *
	 * @param boolean $destroy
	 * @return void
	 */
	public function echoJson(bool $destroy = false): void{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if ($this->getTemplateFlag()) {
				Debug::print($this->__toString());
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} should not be echoing a templated object. Declared {$decl}");
			} elseif ($this->hasWrapperElement()) {
				$wrapper = $this->getWrapperElement();
				$this->setWrapperElement(null);
				$wrapper->appendChild($this);
				$wrapper->echoJson($destroy);
				if (! $destroy) {
					$wrapper->removeChild($this);
					$this->setWrapperElement($wrapper);
				} elseif (! $this instanceof ReusableInterface) {
					$this->dispose();
					// unset($this->parentNode);
					$this->setSuccessors(null);
				}
				return;
			}
			$cache = false;
			if ($this->isCacheable() && JSON_CACHE_ENABLED) {
				if (cache()->has($this->getCacheKey() . ".json")) {
					if ($print) {
						Debug::print("{$f} this object's JSON has already been cached");
					}
					echo cache()->get($this->getCacheKey() . ".json");
					return;
				} else {
					$cache = true;
					ob_start();
				}
			} elseif ($print) {
				Debug::print("{$f} cache this object is not cacheable");
			}
			if (! $this->hasParentNode()) {
				// $this->echoOrphan($destroy);
				if ($this->hasParentNode()) {
					Debug::error("{$f} this function should not be called for elements with parent nodes");
				}
				$this->generateContents();
				if ($this->getAllocationMode() === ALLOCATION_MODE_ULTRA_LAZY) {
					if ($print) {
						Debug::print("{$f} ultra lazy rendering mode -- generating predecessors/successors now");
					}
					$predecessors = $this->generatePredecessors();
					$this->setFlag("predecessorsGenerated", true);
					if (empty($predecessors)) {
						$predecessors = [];
					}
					$successors = $this->generateSuccessors();
					if (empty($successors)) {
						$successors = [];
					}
				} else {
					if ($print) {
						Debug::print("{$f} something other than ultra lazy rendering");
					}
					if ($this->hasPredecessors()) {
						$predecessors = $this->getPredecessors();
					} else {
						$predecessors = [];
					}
					if ($this->hasSuccessors()) {
						$successors = $this->getSuccessors();
					} else {
						$successors = [];
					}
				}
				if ($destroy) {
					$this->setPredecessors(null);
					$this->setSuccessors(null);
				}
				if (! empty($predecessors) || ! empty($successors)) {
					$fragment = new DocumentFragment();
					if (! empty($predecessors)) {
						$fragment->appendChild(...array_values($predecessors));
					}
					$fragment->appendChild($this);
					if (! empty($successors)) {
						$fragment->appendChild(...array_values($successors));
					}
					$this->setParentNode($fragment);
					$fragment->echoJson($destroy);
					return;
				}
			} else {
				$this->generateContents();
				if ($this->getAllocationMode() === ALLOCATION_MODE_ULTRA_LAZY) {
					if ($print) {
						Debug::print("{$f} ultra lazy rendering mode -- generating predecessors now");
					}
					$predecessors = $this->generatePredecessors();
					$this->setFlag("predecessorsGenerated", true);
				} else {
					if ($print) {
						Debug::print("{$f} something other than ultra lazy rendering");
					}
					if ($this->hasPredecessors()) {
						$predecessors = $this->getPredecessors();
					} else {
						$predecessors = null;
					}
				}
				if ($destroy) {
					$this->setPredecessors(null);
				}
				$successors = null;
			}
			// all of this was copied from the now-defunct echoJsonHelper to reduce execution depth
			// predecessors
			if (! empty($predecessors)) {
				$i = 0;
				foreach ($predecessors as $p) {
					if ($i ++ > 0) {
						echo ",";
					}
					if ($p instanceof Element && $this->hasParentNode()) {
						$p->setParentNode($this->getParentNode());
					}
					Json::echo($p, $destroy, false);
					if ($destroy) {
						unset($p);
					}
				}
				if ($destroy) {
					unset($predecessors);
				}
				echo ",";
			} elseif ($print) {
				Debug::print("{$f} no predecessors");
			}
			// this
			echo "{";
			// all attributes, including class and inline style
			if ($this->hasAttributes() || $this->hasClassList() || $this->hasInlineStyleAttribute()) {
				// generate class attribute
				if ($this->hasClassAttribute()) {
					if ($print) {
						Debug::print("{$f} assigning class attribute");
					}
					$this->setAttribute('class', $this->getClassAttribute());
					if ($destroy) {
						unset($this->classList);
					}
				} elseif ($this->getFlag("requireClassAttribute")) {
					Debug::error("{$f} class attribute is required");
				} elseif ($print) {
					Debug::print("{$f} class attribute is undefined, but that's OK");
				}
				// add inline style properties to attributes
				if ($this->hasInlineStyleAttribute()) {
					$this->setAttribute('style', $this->getInlineStyleAttribute());
					if ($destroy) {
						unset($this->style);
					}
				} elseif ($print) {
					Debug::print("{$f} inline style attribute is undefined");
				}
				// echo attributes
				if ($this->hasAttributes()) {
					foreach ($this->attributes as $attr_key => $attr) {
						if ($attr instanceof Command) {
							if (! $attr->getAllocatedFlag()) {
								Debug::warning("{$f} attribute at index \"{$attr_key}\" was deallocated");
								$this->attributes[$attr_key] = "[deleted]";
								$this->debugPrintRootElement();
							}
						}
					}
					Json::echoKeyValuePair("attributes", $this->attributes, $destroy);
					/*
					if($destroy){
						unset($this->attributes);
					}
					*/
					// InitializeFormCommand needs ID until commands are dispatched
				} elseif ($print) {
					Debug::print("{$f} no other attributes defined");
				}
			} elseif ($print) {
				Debug::print("{$f} this element has no attributes, except possibly responsive style properties");
			}
			// responsive style properties that require client side calculation
			if ($this->hasResponsiveStyleProperties()) {
				Json::echoKeyValuePair('responsiveStyleProperties', $this->responsiveStyleProperties, $destroy, false);
				if ($destroy) {
					unset($this->responsiveStyleProperties);
				}
			} elseif ($print) {
				Debug::print("{$f} this element has no responsive style properties");
			}
			// echo innerHTML or childNodes
			if ($this instanceof EmptyElement) {
				if ($print) {
					Debug::print("{$f} this is an empty element with no innerHTML");
				}
			} elseif ($this->hasInnerHTML()) {
				if ($print) {
					Debug::print("{$f} innerHTML is defined as \"{$this->innerHTML}\"");
				}
				Json::echoKeyValuePair("innerHTML", $this->innerHTML, $destroy);
				if ($destroy) {
					unset($this->innerHTML);
				}
			} else {
				$mode = $this->getAllocationMode();
				if ($this->isEmptyElement()) {
					if ($print) {
						Debug::print("{$f} element is empty");
					}
				} elseif ($this->hasChildNodes()) {
					// copied from Json::echoKeyValuePair to reduce execution depth
					echo "\"childNodes\":";
					Json::echo($this->childNodes, $destroy, false);
					echo ",";
					// dispose childNodes as soon as possible
					if ($destroy) {
						foreach ($this->childNodes as $key => $c) {
							if (is_object($c) && ! $c instanceof ReusableInterface) {
								$c->dispose();
							}
							// unset($this->childNodes[$key]);
						}
						unset($this->childNodes);
					}
				} elseif ($mode === ALLOCATION_MODE_ULTRA_LAZY) {
					if ($print) {
						Debug::print("{$f} ultra-lazy child generating mode");
					}
					echo "\"childNodes\":[";
					$this->childElementCount = 0;
					$this->generateChildNodes();
					echo "],";
				} elseif (! $this->getAllowEmptyInnerHTML()) {
					$mode = $this->getAllocationMode();
					$decl = $this->getDeclarationLine();
					$class = $this->hasClassAttribute() ? $this->getClassAttribute() : "[undefined]";
					Debug::error("{$f} child nodes array is empty; generation mode is \"{$mode}\"; declared \"{$decl}\"; class \"{$class}\"");
					// $this->debugPrintRootElement();
				} elseif ($print) {
					Debug::print("{$f} empty innerHTML");
				}
			}
			// element tag always comes last
			Json::echoKeyValuePair("tag", $this->getElementTag(), $destroy, false);
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
			if ($successors === null) {
				$mode = $this->getAllocationMode();
				if ($mode === ALLOCATION_MODE_ULTRA_LAZY) {
					if ($print) {
						Debug::print("{$f} ultra lazy rendering mode -- generating successors now");
					}
					$successors = $this->generateSuccessors();
				} else {
					if ($print) {
						Debug::print("{$f} something other than ultra lazy rendering");
					}
					if ($this->hasSuccessors()) {
						$successors = $this->getSuccessors();
					}
				}
				if ($destroy) {
					$this->setSuccessors(null);
				}
			} elseif ($this->hasParentNode()) {
				Debug::error("{$f} you should not be passing non-null values to this function for elements with parent nodes");
			} elseif ($print) {
				Debug::print("{$f} this is an orphaned element");
			}
			if (! empty($successors)) {
				foreach ($successors as $s) {
					echo ",";
					if ($s instanceof Element && $this->hasParentNode()) {
						$s->setParentNode($this->getParentNode());
					}
					Json::echo($s, $destroy, false);
					if ($destroy) {
						unset($s);
					}
				}
				if ($destroy) {
					unset($successors);
				}
			} elseif ($print) {
				Debug::print("{$f} no successors");
			}
			// cleanup
			if ($destroy && ! $this instanceof ReusableInterface) {
				$this->dispose();
				// unset($this->parentNode);
				$this->setSuccessors(null);
			}
			// end of transplant from echoJsonHelper. Deal with cache insert here
			if ($cache) {
				if ($print) {
					Debug::print("{$f} about to cache JSON");
				}
				$json = ob_get_clean();
				cache()->set($this->getCacheKey() . ".json", $json, time() + 30 * 60);
				echo $json;
				unset($json);
			} elseif ($print) {
				Debug::print("{$f} nothing to cache");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Debug::error("{$f} disabled as a flimsy attempt to reduce execution depth");
	}

	public function echoAttributeString(bool $destroy = false): void{
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->hasClassAttribute()) {
				echo " class=\"";
				$this->echoClassAttribute($destroy);
				echo "\"";
			}
			if ($this->hasAttributes()) {
				foreach ($this->attributes as $key => $value) {
					if ($key === "class") {
						continue;
					}
					echo " {$key}";
					if ($value === null) {
						if ($print) {
							Debug::print("{$f} attribute \"{$key}\" is null");
						}
						continue;
					} elseif ($this instanceof InputInterface && $key === "placeholder" && $this->getPlaceholderMode() === INPUT_PLACEHOLDER_MODE_SHRINK) {
						if ($print) {
							Debug::print("{$f} skipping over a placeholder attribute that's getting replaced by its magic shrinking label");
						}
						echo "=\"\"";
					} elseif ($key === "value" || $value !== null && $value !== "") {
						echo "=\"";
						echo htmlspecialchars($value);
						echo "\"";
					}
				}
			}
			if ($this->hasInlineStyleAttribute()) {
				echo " style=\"";
				$this->echoInlineStyleAttribute($destroy);
				echo "\"";
			} elseif ($print) {
				Debug::print("{$f} inline style attribute is undefined");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getAttribute($key){
		if (! $this->hasAttribute($key)) {
			return null;
		}
		return $this->attributes[$key];
	}

	public function debugPrintAttributes(): void{
		if (empty($this->attributes)) {
			Debug::print("No attributes");
		} else
			foreach ($this->attributes as $name => $value) {
				Debug::print("{$name} : {$value}");
			}
		Debug::printStackTraceNoExit();
	}

	public function debugPrintStyleProperties(): void{
		$f = __METHOD__;
		if (empty($this->style)) {
			Debug::print("{$f} No style properties");
		} else
			foreach ($this->style as $name => $value) {
				if ($value instanceof JavaScriptInterface && ! $value instanceof StringifiableInterface) {
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

	public function getAllowEmptyInnerHTML(){
		return $this->getFlag("allowEmptyInnerHTML");
	}

	public function setParentNode(?Element $node): ?Element{
		if ($node === null) {
			unset($this->parentNode);
			return null;
		} elseif ($node->getDisableRenderingFlag()) {
			$this->disableRendering();
		}
		return $this->parentNode = $node;
	}

	/**
	 *
	 * @return Element[]|NULL
	 */
	public function getChildNodes(): ?array{
		return $this->childNodes;
	}

	public function getChildNode($index){
		$f = __METHOD__;
		if(!array_key_exists($index, $this->childNodes)){
			Debug::error("{$f} there is no child node at index {$index}");
		}
		return $this->childNodes[$index];
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
		if (empty($children)) {
			return null;
		}
		foreach ($children as $child) {
			$this->insertChild($child, "append");
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
	protected function insertChild($child, $where){
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::printStackTraceNoExit("{$f} entered");
			}
			if (! isset($this->childNodes) || ! is_array($this->childNodes)) {
				$this->childNodes = [];
			}
			if (is_object($child)) {
				$cc = $child->getClass();
				if ($child instanceof Element) {
					$debug_ids = [];
					foreach ($this->childNodes as $node) {
						if ($node instanceof Element) {
							$debug_ids[$node->getDebugId()] = $node;
						}
					}
					if ($child->hasDebugId()) {
						$did = $child->getDebugId();
						if (array_key_exists($did, $debug_ids)) {
							$decl = $child->getDeclarationLine();
							Debug::error("{$f} child node of class \"{$cc}\" with debug ID \"{$did}\" created {$decl} already exists; inserting {$where}");
						} elseif ($print) {
							Debug::print("{$f} child node of class \"{$cc}\" has not already been inserted");
						}
					}
					if ($print) {
						Debug::print("{$f} inserting a child element of class \"{$cc}\" with debug ID \"{$did}\"");
					}
					if (! $this instanceof DocumentFragment) {
						$child->setParentNode($this);
					}
				} elseif ($child instanceof Command) {
					$cc = $child->getClass();
					if ($print) {
						Debug::print("{$f} child is a media command of class \"{$cc}\"");
					}
					if ($this instanceof JavaScriptClass || $this instanceof JavaScriptFunction || $this instanceof DocumentFragment || $this instanceof ScriptElement) {
						if ($print) {
							Debug::print("{$f} this is a javascript function");
						}
					} elseif ($this->getTemplateFlag()) {
						if ($print) {
							Debug::print("{$f} template flag is set");
						}
					} elseif ($child instanceof NodeBearingCommandInterface) {
						if ($print) {
							Debug::print("{$f} child is a node-bearing command interface of class \"{$cc}\"");
						}
						return $this->resolveTemplateCommand($child);
					} elseif ($child instanceof ValueReturningCommandInterface) {
						if ($print) {
							Debug::print("{$f} child is a value-returning media command; about to insert its evaluation");
						}
						while ($child instanceof ValueReturningCommandInterface) {
							$child = $child->evaluate();
						}
						if ($child instanceof Element && ! $this instanceof DocumentFragment) {
							$child->setParentNode($this);
						}
					} else {
						Debug::error("{$f} something went wrong; child class is \"{$cc}\"");
					}
				} else {
					Debug::error("{$f} child is an object of class \"{$cc}\"");
				}
			} elseif (is_array($child)) {
				Debug::error("{$f} child is an array");
			} elseif (is_string($child)) {
				if ($print) {
					Debug::print("{$f} child is the string \"{$child}\"");
				}
			} elseif (is_numeric($child)) {
				if ($print) {
					Debug::print("{$f} child is the number {$child}");
				}
			} else {
				$gottype = gettype($child);
				Debug::error("{$f} child is something else of type \"{$gottype}\"");
			}
			if ($child instanceof Command && ! $this->getTemplateFlag() && ! $this instanceof JavaScriptClass && ! $this instanceof JavaScriptFunction && ! $this instanceof ScriptElement) {
				$class = $child->getClass();
				Debug::error("{$f} child object is an instance of \"{$class}\"");
			}
			$mode = $this->getAllocationMode();
			if ($this->getContentsGeneratedFlag() && ($mode === ALLOCATION_MODE_ULTRA_LAZY) && $this->getFlag("predecessorsGenerated")) {
				if (! $this->getFlag("predecessorsGenerated")) {
					Debug::warning("{$f} predecessors have not been generated --- you cannot insert children at this time");
					$this->announceYourself();
					Debug::printStackTrace();
				}
				if ($print) {
					Debug::print("{$f} ultra lazy rendering mode");
				}
				if (is_string($child)) {
					echo $child;
					unset($child);
					return null;
				} elseif (Request::isXHREvent() || Request::isFetchEvent()) {
					if ($this->childElementCount ++ > 0) {
						echo ",";
					}
					$child->echoJson(true);
				} else {
					$child->echo(true);
				}
				unset($child);
				return null;
			} else
				switch ($where) {
					case "append":
						array_push($this->childNodes, $child);
						break;
					case "prepend":
						array_unshift($this->childNodes, $child);
						break;
					default:
						Debug::error("{$f} invalid insert where value \"{$where}\"");
				}

			return $child;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function prepend(...$children){
		if (empty($children)) {
			return null;
		}
		foreach ($children as $child) {
			$this->insertChild($child, "prepend");
		}
		return $children;
	}

	public function hasChildNodes(): bool{
		return $this->getChildNodeCount() > 0;
	}

	public function getChildNodeCount(): int{
		if (! is_array($this->childNodes)) {
			return 0;
		}
		return count($this->childNodes);
	}

	public function debugPrintRootElement(): void{
		$f = __METHOD__;
		$this->announceYourself();
		if ($this->hasParentNode()) {
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
		if (isset($this->innerHTML)) {
			return $this->innerHTML;
		}
		ob_start();
		$this->echoInnerHTML();
		return ob_get_clean();
	}

	public function getInnerHTMLCommand(): GetInnerHTMLCommand{
		return new GetInnerHTMLCommand($this);
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

	public function withInnerHTML($innerHTML): Element{
		$this->setInnerHTML($innerHTML);
		return $this;
	}

	public function announceYourself(): void{
		$f = __METHOD__;
		$sc = $this->getShortClass();
		Debug::print("{$f} class is {$sc}, declared " . $this->getDeclarationLine());
		if (isset($this->debugId)) {
			Debug::print("{$f} debug ID is \"{$this->debugId}\"");
		} else {
			Debug::print("{$f} debug ID is undefined");
		}
		if ($this->hasClassAttribute()) {
			$class = $this->getClassAttribute();
			Debug::print("{$f} my class attribute is \"{$class}\"");
			$class = null;
		} else {
			Debug::warning("{$f} element has no class attribute");
		}
		if (! $this->hasAttributes()) {
			Debug::print("{$f} attributes array is empty");
			return;
		}
		if ($this->hasIdAttribute()) {
			$id = $this->getIdAttribute();
			Debug::print("{$f} my ID attribute is \"{$id}\"");
			$id = null;
		} else {
			Debug::warning("{$f} element has no ID attribute");
		}
		if ($this->hasContext()) {
			$context = $this->getContext();
			$class = $context->getClass();
			// Debug::print("{$f} context is an object of class \"{$class}\"");
			if ($context instanceof DataStructure) {
				if ($context->hasIdentifierValue()) {
					$key = $context->getIdentifierValue();
					Debug::print("{$f} context {$class} has key \"{$key}\"");
				} else {
					Debug::print("{$f} context {$class} does not have a key");
				}
			}
		}else{
			Debug::print("{$f} this element does not have a context");
		}
		if ($this->hasAttribute("value")) {
			$value = $this->getValueAttribute();
			Debug::print("{$f} my value is \"{$value}\"");
			$value = null;
		} else {
			Debug::warning("{$f} element has no value attribute");
		}
	}

	/**
	 * echos everything inside this element's html tags to the user's browser
	 * <div>This is innerHTML</div>
	 *
	 * @param boolean $destroy
	 *        	: deallocate this object once we're finished
	 * @return string
	 */
	public function echoInnerHTML(bool $destroy = false): void{
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->hasInnerHTML()) {
				$html = $this->innerHTML;
				$gottype = is_object($html) ? $html->getClass() : gettype($html);
				if ($print) {
					Debug::print("{$f} innerHTML is a {$gottype}");
				}
				while ($html instanceof ValueReturningCommandInterface) {
					if ($print) {
						Debug::print("{$f} innerHTML is a " . $html->getClass());
					}
					$html = $html->evaluate();
				}
				if ($print) {
					Debug::print("{$f} innerHTML is defined as \"{$this->innerHTML}\"");
				}
				echo $html;
				if ($destroy) {
					unset($this->innerHTML);
				}
				return;
			}
			$mode = $this->getAllocationMode();
			if ($mode === ALLOCATION_MODE_ULTRA_LAZY) {
				if ($print) {
					Debug::print("{$f} ultra lazy rendering mode");
				}
				if (! $this->hasChildNodes()) {
					$this->generateChildNodes();
					return;
				} elseif ($print) {
					Debug::print("{$f} child nodes are already defined; most likely this is an intermediate node that is child to a complex parent");
				}
			} elseif ($print) {
				Debug::print("{$f} some other rendering mode besides ultra lazy");
			}
			$children = $this->getChildNodes();
			if (empty($children)) {
				if ($mode === ALLOCATION_MODE_LAZY) {
					if ($print) {
						Debug::print("{$f} child generation mode is lazy");
					}
					$children = $this->generateChildNodes();
					if (empty($children) && ! $this->getAllowEmptyInnerHTML()) {
						Debug::warning("{$f} childNodes array is still empty");
						echo "ERROR: debug ID {$this->debugId}";
						$this->debugPrintRootElement();
					}
				} elseif (! $this->getAllowEmptyInnerHTML()) {
					$decl = $this->hasDeclarationLine() ? $this->getDeclarationLine() : "unknown";
					Debug::warning("{$f} child nodes array is empty; generation mode is \"{$mode}\"; created \"{$decl}\"");
					$this->debugPrintRootElement();
					echo "Error: undefined innerHTML";
					return; // $this->debugPrintRootElement();
				}
			}
			if ($this->hasChildNodes()) {
				$children = $this->getChildNodes();
				foreach ($children as $child) {
					if (is_object($child)) {
						$cc = $child->getClass();
						if ($child instanceof Command) {
							if ($print) {
								Debug::print("{$f} about to echo a child command of class {$cc}");
							}
							if (! $this instanceof ScriptElement) {
								Debug::error("{$f} only script element can echo a command as its innerHTML");
							}
							echo $child->toJavaScript() . ";\n";
							continue;
						} elseif (! $child instanceof Element) {
							$class = $child->getClass();
							Debug::warning("{$f} child object is an instance of \"{$class}\"");
							$this->debugPrintRootElement();
						} elseif (! $child->getAllocatedFlag()) {
							$did = $child->getDebugId();
							$decl = $child->getDeclarationLine();
							Debug::warning("{$f} child object of class \"{$cc}\" with debug ID \"{$did}\" declared {$decl} was already deleted");
							$this->debugPrintRootElement();
							return;
						}
						if (! $child->getAllocatedFlag()) {
							Debug::error("{$f} child of class \"{$cc}\" was already deallocated");
						} elseif ($print) {
							Debug::print("{$f} inducing a child of class \"{$cc}\" to echo itself");
						}
						$child->echo($destroy);
					} elseif (is_string($child) || is_numeric($child)) {
						if ($print) {
							Debug::print("{$f} echoing a child string");
						}
						echo $child;
					}
				}
			}
			if ($destroy) {
				unset($this->childNodes);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setAttributes($keyvalues): array{
		foreach ($keyvalues as $key => $value) {
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
		$print = false && $this->getDebugFlag();
		if (! $this->getTemplateFlag()) {
			while ($innerHTML instanceof ValueReturningCommandInterface) {
				if ($print) {
					Debug::print("{$f} innerHTML is a " . $innerHTML->getClass());
					if ($innerHTML instanceof ColumnValueCommand) {
						Debug::print("{$f} innerHTML column name is " . $innerHTML->getColumnName());
					}
				}
				$innerHTML = $innerHTML->evaluate();
				if ($print) {
					if (is_string($innerHTML)) {
						Debug::print("{$f} innerHTML is the string \"{$innerHTML}\"");
					} else {
						$gottype = is_object($innerHTML) ? $innerHTML->getClass() : gettype($innerHTML);
						Debug::print("{$f} after evaluation, innerHTML is a {$gottype}");
					}
				}
			}
		} elseif ($print) {
			Debug::print("{$f} template flag is set -- skipping media command evaluation");
		}
		if ($this->hasChildNodes()) {
			$this->setChildNodes([]);
		}
		return $this->innerHTML = $innerHTML;
	}

	public function setInnerHTMLCommand($innerHTML): SetInnerHTMLCommand{
		return new SetInnerHTMLCommand($this, $innerHTML);
	}

	public function initializeAttributesArray(): void{
		if (! isset($this->attributes) || ! is_array($this->attributes)) {
			$this->attributes = [];
		}
	}

	// XXX I seem to recall the event handler attributes giving some trouble when used with media commands
	public function setOnClickAttribute($onclick){
		return $this->setAttribute("onclick", $onclick);
	}

	public function getOnClickAttribute(){
		$f = __METHOD__;
		if (! $this->hasOnClickAttribute()) {
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

	public function hasContext(): bool{
		return isset($this->context);
	}

	public function withStyleProperties(?array $keyvalues): Element{
		$this->setStyleProperties($keyvalues);
		return $this;
	}

	public function withStyleProperty($key, $value): Element{
		$this->setStyleProperty($key, $value);
		return $this;
	}

	public function getContext(){
		$f = __METHOD__;
		if (! $this->hasContext()) {
			$decl = $this->hasDeclarationLine() ? $this->getDeclarationLine() : "[undefined]";
			Debug::error("{$f} context is undefined. Declared {$decl}");
		}
		return $this->context;
	}

	public function getSuccessorCount(): int{
		return $this->getArrayPropertyCount("successors");
	}

	public function ejectSuccessors(): ?array{
		return $this->ejectProperty("successors");
	}

	public function pushSuccessor(...$successors){
		$f = __METHOD__;
		$print = false;
		if ($print) {
			foreach ($successors as $s) {
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
		$print = $this->getDebugFlag();
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
	
	public function dispatchCommands(): int{
		$f = __METHOD__;
		$print = false;
		if ($this->hasDispatchedCommands()) {
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
		if (! $this->hasSubcommandCollector()) {
			Debug::error("{$f} subcommand collector is undefined");
		}
		return $this->subcommandCollector;
	}

	public function setSubcommandCollector($collector){
		return $this->subcommandCollector = $collector;
	}

	/**
	 * Send a command needed to generate this element to an object designated for handling it
	 *
	 * @param Command $command
	 * @return Command
	 */
	public function reportSubcommand($command){
		$f = __METHOD__;
		try {
			$print = false;
			$push_to_use_case = false;
			if ($this->hasSubcommandCollector()) {
				$collector = $this->getSubcommandCollector();
				if ($print) {
					$sccc = $collector->getClass();
					Debug::print("{$f} subcommand collector is a {$sccc}");
				}
				return $collector->reportSubcommand($command);
			} elseif ($this->getCatchReportedSubcommandsFlag()) {
				if ($print) {
					Debug::print("{$f} catch reported subcommands flag is set");
				}
				$this->pushReportedSubcommands($command);
			} elseif ($this->hasParentNode()) {
				if ($print) {
					Debug::print("{$f} parent node is set");
				}
				// had to prevent the function from getting called on parent node to limit execution depth //return $this->getParentNode()->reportSubcommand($command);
				$parent_node = $this->getParentNode();
				while (true) {
					if ($parent_node->hasSubcommandCollector()) {
						$collector = $parent_node->getSubcommandCollector();
						if ($print) {
							$sccc = $collector->getClass();
							Debug::print("{$f} parent node's subcommand collector is a {$sccc}");
						}
						return $collector->reportSubcommand($command);
					} elseif ($parent_node->getCatchReportedSubcommandsFlag()) {
						if ($print) {
							Debug::print("{$f} parent node's catch reported subcommands flag is set");
						}
						$parent_node->pushReportedSubcommands($command);
						return $command;
					} elseif ($parent_node->hasParentNode()) {
						$parent_node = $parent_node->getParentNode();
					} else {
						$push_to_use_case = true;
						break;
					}
				}
			} else {
				$push_to_use_case = true;
			}
			if ($push_to_use_case) {
				$uc = app()->getUseCase();
				if ($print) {
					$did = $uc->getDebugId();
					Debug::print("{$f} parent node is undefined; pushing subcommand directly to use case with debug ID \"{$did}\"");
				}
				if (app()->getResponse(app()->getUseCase())->getRefuseCommandsFlag()) {
					Debug::warning("{$f} response is refusing media commands");
					$this->debugPrintRootElement();
				}
				$uc->pushCommand($command);
			}
			return $command;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getCatchReportedSubcommandsFlag(): bool{
		return $this->getFlag("catchReportedSubcommands");
	}

	public function setCatchReportedSubcommandsFlag(bool $flag = true): bool{
		return $this->setFlag("catchReportedSubcommands", $flag);
	}

	public /*final*/ function __toString(): ?string{
		$f = __METHOD__;
		try {
			ob_start();
			$this->echo(false);
			return ob_get_clean();
		} catch (Exception $x) {
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
		if ($print) {
			if (! is_array($nodes)) {
				Debug::print("{$f} empty!");
			} else {
				Debug::print("{$f} setting successors to the following array:");
				Debug::printArray($nodes);
			}
		}
		return $this->setArrayProperty("successors", $nodes);
	}

	protected function generateSuccessors(): ?array{
		return null;
	}

	public final function getSuccessors(){
		return $this->getProperty("successors");
	}

	public function unshiftSuccessors(...$values): int{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			if ($this->hasSuccessors()) {
				Debug::print("{$f} successors already exist");
			} else {
				Debug::print("{$f} no successors");
			}
			foreach ($values as $value) {
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

	public function unshiftPredecessors(...$values): int{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			if ($this->hasPredecessors()) {
				Debug::print("{$f} predecessors already exist");
			} else {
				Debug::print("{$f} no predecessors");
			}
			foreach ($values as $value) {
				Debug::print("{$f} unshifting \"{$value}\"");
			}
		}
		return $this->unshiftArrayProperty("predecessors", ...$values);
	}

	protected function generatePredecessors(): ?array{
		return null;
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

	private function echoPredecessors(bool $destroy = false){
		$f = __METHOD__;
		try {
			$print = false;
			$mode = $this->getAllocationMode();
			if ($mode === ALLOCATION_MODE_ULTRA_LAZY) {
				if ($print) {
					Debug::print("{$f} generating ultra lazy predecessors");
				}
				$predecessors = $this->generatePredecessors();
				$this->setFlag("predecessorsGenerated", true);
			} elseif ($this->hasPredecessors()) {
				$predecessors = $this->getPredecessors();
				if ($destroy) {
					$this->setPredecessors(null);
				}
			} else {
				$predecessors = null;
			}
			if (isset($predecessors) && is_array($predecessors) && ! empty($predecessors)) {
				foreach ($predecessors as $predecessor) {
					if (is_object($predecessor)) {
						if ($predecessor instanceof Element) {
							if (! $predecessor->getAllocatedFlag()) {
								Debug::error("{$f} predecessor was already deallocated");
							}
							$predecessor->echo($destroy);
						} elseif ($predecessor instanceof ValueReturningCommandInterface) {
							if ($print) {
								Debug::print("{$f} predecessor is a value-returning media command");
							}
							echo $predecessor->evaluate();
						} else {
							$pc = $predecessor->getClass();
							Debug::error("{$f} predecessor is an object of class \"{$pc}\"");
						}
					} elseif (is_array($predecessor)) {
						Debug::error("{$f} predecessor is an array");
					} else {
						echo $predecessor;
					}
					if ($destroy) {
						unset($predecessor);
					}
				}
				if ($destroy) {
					unset($predecessors);
				}
			} elseif ($print) {
				Debug::print("{$f} no predecessor nodes");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getElementTag(): string{
		$f = __METHOD__;
		try {
			if ($this->hasElementTag()) {
				return $this->tag;
			}
			return static::getElementTagStatic();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * output this element to the user's browser as HTML
	 *
	 * @param boolean $destroy
	 * @return int
	 */
	public function echo(bool $destroy = false): void{
		$f = __METHOD__;
		try {
			$print = false;
			if (! $this->getAllocatedFlag()) {
				Debug::warning("{$f} this object was already deleted");
				$this->debugPrintRootElement();
			} elseif ($this->hasWrapperElement()) {
				$wrapper = $this->getWrapperElement();
				if ($print) {
					Debug::print("{$f} this element has a wrapper -- echoing it now");
					if ($wrapper->hasStyleProperties()) {
						Debug::print("{$f} wrapper has the following inline style properties:");
						Debug::printArray($wrapper->getStyleProperties());
					} else {
						Debug::print("{$f} wrapper does not have style properties");
					}
				}
				$this->setWrapperElement(null);
				$wrapper->appendChild($this);
				$wrapper->echo($destroy);
				if (! $destroy) {
					$wrapper->removeChild($this);
					$this->setWrapperElement($wrapper);
				}
				return;
			}
			if ($this->getHTMLCacheableFlag() && $this->isCacheable() && HTML_CACHE_ENABLED) {
				$cache_key = $this->getCacheKey() . ".html";
				if (cache()->has($cache_key)) {
					if ($print) {
						Debug::print("{$f} cached HTML is defined for key \"{$cache_key}\"");
					}
					echo cache()->getFile($cache_key);
					return;
				} else {
					if ($print) {
						Debug::print("{$f} HTML is not yet cached");
					}
					$cache = true;
					ob_start();
				}
			} else {
				if ($print) {
					Debug::print("{$f} this object is not cacheable");
				}
				$cache = false;
			}
			$this->generateContents();
			$this->echoPredecessors($destroy);
			$tag = $this->getElementTag();
			echo "<{$tag}";
			if ($print) {
				Debug::print("{$f} about to echo attribute string");
			}
			$this->echoAttributeString($destroy);
			if ($print) {
				Debug::print("{$f} echoed attribute string");
			}
			if ($this->isEmptyElement()) {
				echo "/";
			}
			echo ">";
			if (! $this->isEmptyElement()) {
				$this->echoInnerHTML($destroy);
				if ($print) {
					Debug::print("{$f} echoed inner HTML");
				}
				echo "</{$tag}>\n";
			} elseif ($print) {
				Debug::print("{$f} this is an empty element");
			}
			if ($destroy) {
				unset($this->attributes);
			}
			$this->echoSuccessors($destroy);
			if ($print) {
				Debug::print("{$f} echoed successors");
			}
			if ($cache) {
				$cache_key = $this->getCacheKey() . ".html";
				if ($print) {
					Debug::print("{$f} about to update cache for key \"{$cache_key}\"");
				}
				$html = ob_get_clean();

				cache()->set($cache_key, $html, time() + 30 * 60);
				echo $html;
				unset($html);
			} elseif ($print) {
				Debug::print("{$f} nothing to cache");
			}
			// flush();
			if (Request::isAjaxRequest()) {
				$this->dispatchCommands();
			}
			if ($destroy && ! $this instanceof ReusableInterface) {
				$this->dispose();
				// $this->setDeletedFlag(true);
			}
			return;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function scrollIntoView(bool $alignToTop): ScrollIntoViewCommand{
		return new ScrollIntoViewCommand($this, $alignToTop);
	}

	private function echoSuccessors(bool $destroy = false): void{
		$f = __METHOD__;
		try {
			$print = false;
			$mode = $this->getAllocationMode();
			if ($mode === ALLOCATION_MODE_ULTRA_LAZY) {
				if ($print) {
					Debug::print("{$f} generating ultra lazy successors");
				}
				$successors = $this->generateSuccessors();
			} elseif ($this->hasSuccessors()) {
				if ($print) {
					Debug::print("{$f} successors are already defined");
				}
				$successors = $this->getSuccessors();
				if ($destroy) {
					$this->setSuccessors(null);
				}
			} else {
				if ($print) {
					Debug::print("{$f} there are no successors, and this is not ultra lazy rendering mode");
				}
				$successors = null;
			}
			if (isset($successors) && is_array($successors) && ! empty($successors)) {
				$count = count($successors);
				if ($print) {
					Debug::print("{$f} {$count} successors");
					foreach ($successors as $s) {
						Debug::print("{$f} {$s}");
					}
				}
				foreach ($successors as $successor) {
					if (is_object($successor)) {
						if ($successor instanceof Element) {
							$class = $successor->getClass();
							if (! $successor->getAllocatedFlag()) {
								$decl = $successor->hasDeclarationLine() ? $successor->getDeclarationLine() : "unknown";
								Debug::error("{$f} successor of class \"{$class}\" was deallocated; it was declared at {$decl}");
							} elseif ($print) {
								Debug::print("{$f} successor of class \"{$class}\" was NOT deallocated");
							}
							$successor->echo($destroy);
						} elseif ($successor instanceof ValueReturningCommandInterface) {
							if ($print) {
								Debug::print("{$f} successor is a value-returning media command");
							}
							echo $successor->evaluate();
						} else {
							$sc = $successor->getClass();
							Debug::error("{$f} successor is an object of class \"{$sc}\"");
						}
					} elseif (is_array($successor)) {
						Debug::error("{$f} successor is an array");
					} else {
						echo $successor;
					}
					if ($destroy) {
						unset($successor);
					}
				}
				if ($destroy) {
					unset($successors);
				}
			} elseif ($print) {
				Debug::print("{$f} no successor nodes");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateChildNodes(): ?array{
		return $this->getChildNodes();
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
		try {
			$print = false;
			if (! isset($context)) {
				Debug::error("{$f} context is undefined");
			} elseif ($context instanceof UseCase && ! $this->allowUseCaseAsContext()) {
				Debug::error("{$f} context is a use case");
			}
			if ($print) {
				if ($this->hasContext()) {
					$context = $this->getContext();
					if (is_object($context)) {
						$cc = $context->getClass();
						if ($context instanceof Datum) {
							$cn = $context->getName();
							Debug::print("{$f} context is a {$cc} named \"{$cn}\"");
						} else {
							Debug::print("{$f} context is an object of class \"{$cc}\"");
						}
					} else {
						$gottype = gettype($context);
						Debug::print("{$f} context is a {$gottype}");
					}
				} else {
					Debug::print("{$f} context is undefined");
				}
			}
			if ($context instanceof ValueReturningCommandInterface) {
				while ($context instanceof ValueReturningCommandInterface) {
					$context = $context->evaluate();
				}
			}
			if (isset($this->context)) {
				Debug::error("{$f} context is already assigned. You can assign the same context to multiple elements but not multiple contexts to the same element");
			}
			$this->context = $context;
			$mode = $this->getAllocationMode();
			switch ($mode) {
				case ALLOCATION_MODE_EMAIL:
					if ($context instanceof SpamEmail) {
						$this->setEmbeddedImageCollector($context);
					}
					$this->generateChildNodes();
					break;
				case ALLOCATION_MODE_FORM:
				case ALLOCATION_MODE_FORM_TEMPLATE:
					if ($print) {
						Debug::print("{$f} form rendering mode");
					}
					if ($this instanceof InputInterface && $this->hasForm() && $this->getForm() instanceof RepeatingFormInterface) {
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
					if ($print) {
						Debug::print("{$f} rendering mode \"{$mode}\", skipping child node generation");
					}
					break;
			}
			return $context;
		} catch (Exception $x) {}
	}

	public function setResizeAttribute($resize){
		return $this->setAttribute("resize", $resize);
	}

	public static function getUriStatic(): ?string{
		$f = __METHOD__;
		if (! isset(static::$uriStatic)) {
			Debug::error("{$f} input form URI is undefined");
		}
		return static::$uriStatic;
	}

	public function hasEmbeddedImageCollector(): bool{
		return isset($this->embeddedImageCollector);
	}

	public function getEmbeddedImageCollector(){
		$f = __METHOD__;
		if (! $this->hasEmbeddedImageCollector()) {
			Debug::error("{$f} embedded image collector is undefined");
		}
		return $this->embeddedImageCollector;
	}

	public function setEmbeddedImageCollector($collector){
		return $this->embeddedImageCollector = $collector;
	}

	public function reportEmbeddedImage($data){
		$f = __METHOD__;
		$print = false;
		if ($this->hasEmbeddedImageCollector()) {
			if ($print) {
				Debug::print("{$f} this element has a designated embedded image collector");
			}
			return $this->getEmbeddedImageCollector()->reportEmbeddedImage($data);
		} elseif ($this->hasParentNode()) {
			if ($print) {
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

	public function echoInlineStyleAttribute(bool $destroy = false): void{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasInlineStyleAttribute()) {
			Debug::error("{$f} inline style attribute is undefined");
		}
		foreach ($this->style as $key => $value) {
			if ($print) {
				Debug::print("{$f} echoing attribute \"{$key}\" value \"{$value}\"");
				if ($key === "debug") {
					$decl = $this->getDeclarationLine();
					Debug::print("{$f} declared line \"{$decl}\"");
				}
			}
			if ($value instanceof Command) {
				$decl = $value->getDeclarationLine();
				Debug::error("{$f} value is a command, instantiated {$decl}");
			}
			echo "{$key}:{$value};";
		}
		if ($destroy) {
			unset($this->style);
		}
	}

	public function hasStyleProperties(): bool{
		return isset($this->style) && is_array($this->style) && ! empty($this->style);
	}

	public function hasInlineStyleAttribute(): bool{
		return $this->hasStyleProperties() || $this->hasAttribute("style");
	}

	public function getStyleProperties(): ?array{
		$f = __METHOD__;
		if (! $this->hasStyleProperties()) {
			Debug::error("{$f} inline style properties are undefined");
		}
		return $this->style;
	}

	public function ejectStyleProperties():?array{
		$style = $this->style;
		$this->style = null;
		return $style;
	}
	
	public function getInlineStyleAttribute(): ?string{
		$f = __METHOD__;
		if (! $this->hasInlineStyleAttribute()) {
			Debug::error("{$f} inline style attribute is undefined");
		} elseif ($this->hasAttribute("style")) {
			return $this->getAttribute("style");
		}
		$attr = "";
		foreach ($this->style as $key => $value) {
			$attr .= "{$key}:{$value};";
		}
		return $attr;
	}

	public function setAttribute(string $key, $value = null){
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if ($value instanceof Attribute) {
				if (! $this->getTemplateFlag()) {
					$key = $value->getName();
					while ($key instanceof ValueReturningCommandInterface) {
						$key = $key->evaluate();
					}
					$value = $value->getValue();
					while ($value instanceof ValueReturningCommandInterface) {
						$value = $value->evaluate();
					}
				}
			} elseif (! preg_match('([A-Za-z0-9_]+)', $key)) {
				Debug::error("{$f} key \"{$key}\" is not alphanumeric");
			} elseif (strlen($key) < 1) {
				Debug::error("{$f} key \"{$key}\" is zero length");
			} elseif ($value instanceof ValueReturningCommandInterface && ! $this->getTemplateFlag()) {
				$class = get_class($value);
				if ($print) {
					Debug::print("{$f} value is a value returning command of class \"{$class}\", and the template flag is not set");
				}
				$value = $value->evaluate();
				if ($value instanceof ValueReturningCommandInterface) {
					$class = $value->getClass();
					Debug::error("{$f} evalutation returned a command of class \"{$class}\"");
				}
			}
			if($print){
				if ($value instanceof Command){
					$err = "{$f} value for attribute \"{$key}\" is a command";
				}elseif($value instanceof Attribute){
					$err = "{$f} valus for attribute \'{$key}\" is an Attribute";
				} else {
					$err = "{$f} setting attribute \"{$key}\" to \"{$value}\"";
				}
				if ($this instanceof InputInterface) {
					if ($this->hasNameAttribute()) {
						$name = $this->getNameAttribute();
						$err .= " for input with name \"{$name}\"";
					} else {
						$err .= " for unnamed input";
					}
				}
				Debug::print($err);
			}
			if(! isset($this->attributes) || ! is_array($this->attributes)){
				$this->attributes = [];
			}
			if($value instanceof Command && !$this->getTemplateFlag()){
				Debug::error("{$f} command should have been evaluated by now");
			}
			return $this->attributes[$key] = $value;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setStyleProperty(string $key, $value){
		$f = __METHOD__;
		try {
			$print = false;
			$f = __METHOD__; //Element::getShortClass()."(".static::getShortClass().")->setStyleProperty()";
			if (! $this->getTemplateFlag()) {
				while ($key instanceof ValueReturningCommandInterface) {
					if ($print) {
						Debug::print("{$f} template flag is not set, key is a value returning command");
					}
					$key = $key->evaluate();
				}
				while ($value instanceof ValueReturningCommandInterface) {
					if ($print) {
						Debug::print("{$f} template flag is not set, value is a value returning command");
					}
					$value = $value->evaluate();
				}
			}
			if (! $this->hasStyleProperties()) {
				$this->style = [];
			}
			if ($value === null) {
				$this->style = array_remove_key($this->style, $key);
				return null;
			}
			return $this->style[$key] = $value;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setStyleProperties(?array $keyvalues): ?array{
		if ($keyvalues == null) {
			unset($this->style);
			return null;
		} elseif (! $this->hasStyleProperties()) {
			$this->style = [];
		}
		foreach ($keyvalues as $key => $value) {
			$this->setStyleProperty($key, $value);
		}
		return $keyvalues;
	}

	public function setStylePropertiesCommand(?array $properties): SetStylePropertiesCommand{
		return new SetStylePropertiesCommand($this, $properties);
	}

	public function getAttributes(...$keys): ?array{
		$f = __METHOD__;
		if (! is_array($this->attributes)) {
			Debug::error("{$f} attributes array was not defined");
		} elseif (isset($keys) && count($keys) > 0) {
			$ret = [];
			foreach ($keys as $key) {
				if (! $this->hasAttribute($key)) {
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
		if (! $this->hasIdAttribute()) {
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
		if ($this->hasClassList()) {
			return true;
		}
		return $this->hasAttribute("class");
	}

	public function hasClassList(): bool{
		return isset($this->classList) && is_array($this->classList) && ! empty($this->classList);
	}

	public function echoClassAttribute(bool $destroy = false): void{
		$f = __METHOD__; 
		if ($this->hasAttribute("class")) {
			echo $this->getAttribute("class");
		}
		if (! $this->hasClassList()) {
			return;
		}
		$i = 0;
		foreach ($this->classList as $class) {
			if ($i > 0) {
				echo " ";
			}
			echo $class;
			$i ++;
		}
		if ($destroy) {
			unset($this->classList);
		}
	}

	public function hide(): Element{
		$this->setHiddenAttribute();
		return $this;
	}

	public function getClassAttribute(){
		$f = __METHOD__;
		$print = false;
		if ($this->hasAttribute("class")) {
			if ($print) {
				$ret = $this->getAttribute("class");
				Debug::print("{$f} class is specifically defined as \"{$ret}\"");
			}
			return $this->getAttribute("class");
		} elseif (! $this->hasClassList()) {
			Debug::error("{$f} class list is undefined");
		} elseif ($print) {
			Debug::print("{$f} imploding class list");
		}
		return implode(' ', $this->classList);
	}

	public function getClassList(): ?array{
		if (isset($this->classList) && is_array($this->classList) && ! empty($this->classList)) {
			return $this->classList;
		}
		return null;
	}

	public function addClassCommand(...$classes): AddClassCommand{
		return new AddClassCommand($this, ...$classes);
	}

	public function addClassAttribute(...$classes): void{
		if (! is_array($this->classList)) {
			$this->classList = [];
		}
		foreach ($classes as $class) {
			if (false === array_search($class, $this->classList)) {
				array_push($this->classList, $class);
			}
		}
	}

	public function setClassAttribute(?string $class){
		unset($this->classList);
		return $this->setAttribute("class", $class);
	}

	public function withClassAttribute(...$class): Element{
		$this->addClassAttribute(...$class);
		return $this;
	}

	public function getDocumentFragment(): DocumentFragment{
		$f = __METHOD__;
		if (! $this->hasDocumentFragment()) {
			Debug::error("{$f} document fragment is undefined");
		}
		return $this->documentFragment;
	}

	public function hasDocumentFragment(): bool{
		return isset($this->documentFragment) && $this->documentFragment instanceof DocumentFragment;
	}

	public function setDocumentFragment(?DocumentFragment $df): ?DocumentFragment{
		return $this->documentFragment = $df;
	}

	/**
	 * Generates a JavaScriptFunction that when executed clientside, returns a JS element equivalent to this object as if it was rendered on the server (when bound to the same context, if necessary)
	 *
	 * @return JavaScriptFunction
	 */
	public final function generateTemplateFunction(): ?JavaScriptFunction{
		$f = __METHOD__;
		try {
			$generator = new TemplateElementFunctionGenerator();
			return $generator->generate($this);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasInnerHTML(): bool{
		return isset($this->innerHTML) && $this->innerHTML !== "";
	}

	public function hasIdOverride(): bool{
		return isset($this->variableName);
	}

	public function getIdOverride(){
		$f = __METHOD__;
		if (! $this->hasIdOverride()) {
			Debug::error("{$f} ID override is undefined");
		}
		return $this->variableName;
	}

	public function setIdOverride(?string $ido): ?string
	{
		return $this->variableName = $ido;
	}

	public function hasResponsiveStyleProperties(): bool{
		return is_array($this->responsiveStyleProperties) && ! empty($this->responsiveStyleProperties);
	}

	public function getResponsiveStyleProperties(): array{
		$f = __METHOD__;
		if (! $this->hasResponsiveStyleProperties()) {
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
		if ($this->hasIdOverride()) {
			return $this->getIdOverride();
		}
		$vname = $this->setIdOverride("e{$counter}");
		$counter ++;
		return $vname;
	}

	public function setLocalDeclarations(?array $values): ?array{
		$f = __METHOD__;
		if ($values !== null && ! is_array($values)) {
			Debug::error("{$f} local declarations must be an associative array");
		}
		return $this->setArrayProperty("localDeclarations", $values);
	}

	public function hasLocalDeclarations(): bool{
		return $this->hasArrayProperty("localDeclarations");
	}

	public function getLocalDeclarations(): ?Array{
		return $this->getProperty("localDeclarations");
	}

	public function pushLocalDeclarations(...$values): int{
		return $this->pushArrayProperty("localDeclarations", ...$values);
	}

	public function mergeLocalDeclarations(?array $values): ?array{
		return $this->mergeArrayProperty("localDeclarations", $values);
	}

	public function getLocalDeclarationCount(): int{
		return $this->getArrayPropertyCount("localDeclarations");
	}

	// XXX do not delete -- unused, but I forgot why I wrote it
	/*
	 private function getTemplateFunctionOnEventAttributeCommand($attr_str, $cmd_class){
		 $f = __METHOD__;
		 if(!$this->hasAttribute($attr_str)){
			 Debug::error("{$f} attribute \"{$attr_str}\" is undefined");
		 }
		 $onclick = $this->getAttribute($attr_str);
		 if(is_string($onclick)){
			 $cmd = new SetAttributeCommand($this, [$attr_str => $onclick]);
			 $cmd->setQuoteStyle(QUOTE_STYLE_BACKTICK);
		 }elseif($onclick instanceof CallFunctionCommand){
			 $set_onclick = new $cmd_class($this, $onclick);
			 if($onclick instanceof CallFunctionCommand && $onclick->hasEscapeType()){
			 	$set_onclick->setEscapeType($onclick->getEscapeType());
			 }
			 return $set_onclick;
		 }
		 Debug::error("{$f} neither of the above");
	 }
	 */

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
	public function getResolvedKey($context = null): GetDeclaredVariableCommand{
		$f = __METHOD__;
		try {
			// $print = false;
			if ($context == null) {
				$context = $this->getContext();
			}
			$keyname = $context->getIdentifierName();
			$scope = $this->getResolvedScope();
			if ($scope->hasLocalValue($keyname)) {
				return $scope->getDeclaredVariableCommand($keyname);
			}
			$this->resolveTemplateCommand(
				CommandBuilder::try($keyname)->catch(
					$scope->let($keyname)->withFlag("null", true)
				)->resolveCatch(), 
				CommandBuilder::if(
					$context->hasColumnValueCommand($keyname)
				)->then(
					$scope->redeclare(
						$keyname, 
						new GetColumnValueCommand($context, $keyname)
					)
				)->else($scope->redeclare($keyname, "new"))
			);
			return $scope->getDeclaredVariableCommand($keyname);
		} catch (Exception $x) {
			x($f, $x);
		}
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
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			$mode = $this->getAllocationMode();
			foreach ($commands as $command) {
				if (is_array($command)) {
					Debug::error("{$f} command is an array");
				}
				if ($command instanceof DeclareVariableCommand) { // variable declarations go at the top
					if ($print) {
						Debug::print("{$f} command is a DeclareVariableCommand");
					}
					if ($this->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE) {
						if ($print) {
							Debug::print("{$f} command is a declare variable command, and this is a templated object");
						}
						$this->pushLocalDeclarations($command);
					} else {
						if ($print) {
							Debug::print("{$f} command is a declare variable command, but this is not templated");
						}
						$command->resolve();
					}
				} elseif ($command instanceof NodeBearingCommandInterface) { // extract child nodes from node-bearing commands
					$cc = $command->getClass();
					if ($print) {
						Debug::print("{$f} command is a \"{$cc}\"");
					}
					if (($this->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE) && ! $command->extractAnyway()) {
						if ($print) {
							Debug::print("{$f} command is nodebearing, and it isn't flagged to force extraction");
						}
						$this->appendChild($command);
					} else {
						if ($print) {
							Debug::print("{$f} command is nodebearing, and either this object is not templated or the command is flagged to force extraction");
						}
						// $children = [];
						$mode = $this->getAllocationMode();
						$children = $command->extractChildNodes($mode);
						if (! empty($children)) {
							if ($print) {
								$count = count($children);
								Debug::print("{$f} extracted {$count} child nodes");
							}
							foreach ($children as $child) {
								$ccc = is_object($child) ? $child->getClass() : gettype($child);
								if ($child instanceof Command) {
									if ($child instanceof ValueReturningCommandInterface) {
										if ($print) {
											Debug::print("{$f} child is a value returning media command of class \"{$ccc}\"");
										}
										while ($child instanceof ValueReturningCommandInterface) {
											if ($child instanceof AllocationModeInterface) {
												$child->setAllocationMode($mode);
											}
											$child = $child->evaluate();
										}
										// array_push($children, $child); //[$child_key] = $child;
										$ccc = is_object($child) ? $child->getClass() : gettype($child);
										if ($print) {
											Debug::print("{$f} after evaluation, child is now a \"{$ccc}\"");
										}
									} else {
										Debug::error("{$f} media command \"{$cc}\" returned a child command of class \"{$ccc}\"");
									}
								} elseif ($print) {
									Debug::print("{$f} child is a {$ccc}");
								}
								if ($print) {
									Debug::print("{$f} appending a {$ccc}");
								}
								$this->appendChild($child);
							}
						} elseif ($print) {
							Debug::warning("{$f} extracted child nodes array is empty");
						}
					}
				} elseif ($command instanceof IfCommand || $command instanceof SwitchCommand) { // treated just like declared variables, but check for node-bearing conditonals/switch commands first
					if ($print) {
						Debug::print("{$f} command is an if or switch statement");
					}
					if ($this->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE) {
						if ($print) {
							Debug::print("{$f} pushing a local declaration");
						}
						$this->pushLocalDeclarations($command);
					} else {
						if ($print) {
							Debug::print("{$f} resolving command");
						}
						$command->resolve();
					}
				} elseif ($command instanceof ServerExecutableCommandInterface) { // everything else that can be resolved server side
					if ($print) {
						$cc = $command->getClass();
						Debug::print("{$f} {$cc} is server executable");
					}
					if ($this->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE) {
						if (! $command->getResolvedFlag()) {
							if ($print) {
								Debug::printStackTraceNoExit("{$f} appending a template command of class \"{$cc}\"");
							}
							$this->appendChild($command);
							$command->setResolvedFlag(true);
						} elseif ($print) {
							Debug::print("{$f} template command of class \"{$cc}\" has already been resolved");
						}
					} else {
						$command->resolve();
					}
				} elseif (! is_object($command)) {
					$gottype = gettype($command);
					Debug::error("{$f} received a {$gottype}");
				} else {
					$class = $command->getClass();
					Debug::error("{$f} this function doesn't yet support resolution of {$class} commands");
				}
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setChildNodes(?array $nodes): ?array{
		return $this->childNodes = $nodes;
	}

	public function setReplacementId(?string $id): ?string{
		if ($id == null) {
			unset($this->replacementId);
			return null;
		}
		return $this->replacementId = $id;
	}

	public function hasReplacementId(): bool{
		return isset($this->replacementId);
	}

	public function getReplacementId(): string{
		$f = __METHOD__;
		if (! $this->hasReplacementId()) {
			Debug::error("{$f} replacement ID is undefined");
		}
		return $this->replacementId;
	}

	public function getArrayKey(int $count){
		if ($this->hasReplacementId()) {
			return $this->getReplacementId();
		} elseif ($this->hasIdAttribute()) {
			$id = $this->getIdAttribute();
			if (is_string($id)) {
				return $id;
			} elseif ($id instanceof ValueReturningCommandInterface && ! $this->getTemplateFlag()) {
				while ($id instanceof ValueReturningCommandInterface) {
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
	protected function getScopeResolutionCommands($context, Scope $scope): ?array
	{
		return [];
	}

	protected final function getResolvedScope(): Scope{
		$f = __METHOD__;
		$print = false;
		if (isset($this->scope)) {
			return $this->scope;
		}
		$context = $this->hasContext() ? $this->getContext() : null;
		$scope = new Scope();
		$commands = $this->getScopeResolutionCommands($context, $scope);
		if (! is_array($commands) || empty($commands)) {
			if ($print) {
				Debug::warning("{$f} don't call this function unless there are actual commands to resolve");
			}
		} else {
			$this->resolveTemplateCommand(...$commands);
		}
		return $this->setScope($scope);
	}

	public function setDisposeContextFlag(bool $value = true): bool{
		return $this->setFlag("disposeContext", $value);
	}

	public function getDisposeContextFlag(): bool{
		return $this->getFlag("disposeContext");
	}

	public function dispose(): void{
		parent::dispose();
		if ($this->hasContext() && $this->getDisposeContextFlag() && $this->context instanceof DisposableInterface) {
			$this->context->dispose();
		}
		$this->setLocalDeclarations(null);
		$this->setPredecessors(null);
		$this->setReportedSubcommands(null);
		unset($this->attributes);
		unset($this->childElementCount);
		unset($this->childNodes);
		unset($this->classList);
		unset($this->context);
		unset($this->documentFragment);
		unset($this->embeddedImageCollector);
		unset($this->eventListeners);
		unset($this->innerHTML);
		unset($this->labelString);
		unset($this->parentNode); // why was this commented out
		unset($this->allocationMode);
		unset($this->replacementId);
		unset($this->responsiveStyleProperties);
		unset($this->scope);
		unset($this->style);
		unset($this->subcommandCollector);
		// unset($this->successorNodes); //successors must dispose themselves
		unset($this->tag);
		unset($this->uri);
		unset($this->variableName);
		unset($this->wrapperElement);
	}
}
