<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\control\NodeBearingIfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\BindElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\Scope;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\dismiss\DismissNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\notification\pin\PinNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\notification\pin\RepinNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\notification\pin\UnpinNotificationForm;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;
use JulianSeymour\PHPWebApplicationFramework\ui\ThreeDotOptionsLabel;
use Exception;

class NotificationOptionsElement extends SpanElement implements TemplateElementInterface{

	protected $labelContainer;

	protected $hiddenCheckboxInput;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("expand_me");
		$this->addClassAttribute("single_notification_options");
		$this->addClassAttribute("background_color_4");
	}

	protected static function getWidgetName(): string{
		return "notification";
	}

	public static function isTemplateworthy(): bool{
		return true;
	}

	public function hasHiddenCheckboxInput(){
		return isset($this->hiddenCheckboxInput);
	}

	public function getHiddenCheckboxInput(){
		$f = __METHOD__;
		if($this->hasHiddenCheckboxInput()){
			return $this->hiddenCheckboxInput;
		}
		$mode = $this->getAllocationMode();
		$input = new RadioButtonInput($mode);
		$input->setNameAttribute("single_notification_options");
		$input->addClassAttribute("hidden");
		$input->addClassAttribute("expand_toggle");
		$context = $this->getContext();
		$suffix = new NotificationIdSuffixCommand($context);
		$widget = $this->getWidgetName();
		// $widget->setDisallowNullFlag(true);
		$input->setIdAttribute(new ConcatenateCommand("radio-", $widget, "_options-", $suffix));
		$input->setNoUpdateFlag(true);
		return $this->hiddenCheckboxInput = $input;
	}

	protected function getSelfGeneratedPredecessors(): ?array{
		return [
			$this->getHiddenCheckboxInput()
		];
	}

	/**
	 *
	 * @param NotificationData $context
	 * {@inheritdoc}
	 * @see SpanElement::bindContext()
	 */
	public function bindContext($context){
		$f = __METHOD__;
		$return = parent::bindContext($context);
		$this->setIdAttribute(
			new ConcatenateCommand(
				$this->getWidgetName(), 
				"_options-", 
				new NotificationIdSuffixCommand($context)
			)
		);
		// generate successor nodes right now, because they'll get prematurely deleted later
		$this->getLabelContainer();
		return $return;
	}

	protected function getSelfGeneratedSuccessors(): ?array{
		return [
			$this->getLabelContainer()
		];
	}

	public function setLabelContainer($labelContainer){
		return $this->labelContainer = $labelContainer;
	}

	public function hasLabelContainer(){
		return !empty($this->labelContainer);
	}

	public function getLabelContainer(){
		if(!$this->hasLabelContainer()){
			$mode = $this->getAllocationMode();
			$lc = new SpanElement($mode);
			$lc->addClassAttribute("single_notification_options_label_container");
			$lc->addClassAttribute("label_container");
			$dots = ThreeDotOptionsLabel::getDots();
			$lc->appendChild(...$dots);
			$open_label = new LabelElement($mode);
			$open_label->setAllocationMode($mode);
			$open_label->addClassAttribute("single_notification_options_label");
			$open_label->addClassAttribute("open");
			$open_label->setForAttribute($this->getHiddenCheckboxInput()
				->getIdAttribute());
			$open_label->setAllowEmptyInnerHTML(true);
			$lc->appendChild($open_label);
			$close_label = new LabelElement($mode);
			$close_label->addClassAttribute("single_notification_options_label");
			$close_label->addClassAttribute("close");
			$close_label->setAllowEmptyInnerHTML(true);
			$context = $this->getContext();
			$widget = $this->getWidgetName();
			$close_label->setForAttribute(new ConcatenateCommand($widget, "_options-none"));
			$lc->appendChild($close_label);
			$lc->setIdAttribute(
				new ConcatenateCommand("snolc-", new NotificationIdSuffixCommand($context))
			);
			$lc->setNoUpdateFlag(true);
			return $this->setLabelContainer($lc);
		}
		return $this->labelContainer;
	}

	protected static function getUnpinNotificationFormClass(): string{
		return UnpinNotificationForm::class;
	}

	protected static function getPinNotificationFormClass(): string{
		return PinNotificationForm::class;
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$unpin_form_class = $this->getUnpinNotificationFormClass();
			$pin_form_class = $this->getPinNotificationFormClass();
			$context = $this->getContext();
			$mode = $this->getAllocationMode();
			$this->setIdOverride("note_optns");
			$this->resolveTemplateCommand(NodeBearingIfCommand::hasColumnValue($context, "pinnedTimestamp")->then(new AppendChildCommand($this->getIdOverride(), new BindElementCommand(new RepinNotificationForm($mode), $context)), new AppendChildCommand($this->getIdOverride(), new BindElementCommand(new $unpin_form_class($mode), $context)))->else(new AppendChildCommand($this->getIdOverride(), new BindElementCommand(new $pin_form_class($mode), $context))));
			$dismissal_form = new BindElementCommand(DismissNotificationForm::class, $context); //new DismissNotificationForm($mode);
			//$dismissal_form->setIdOverride("dismissal_form");
			$is_dismissable = $context->hasColumnValueCommand("dismissable");
			$is_dismissable->setParseType("bool");
			$this->resolveTemplateCommand(
				NodeBearingIfCommand::if($is_dismissable)->then(
					new AppendChildCommand(
						$this->getIdOverride(), 
						$dismissal_form
					)
				)
			);
			$label5 = new LabelElement($mode);
			$label5->addClassAttribute("button-like");
			$label5->setInnerHTML(new ConcatenateCommand("❌", _("Close")));
			$label5->setAttribute("manual-style");
			$widget = $this->getWidgetName();
			$label5->setForAttribute(new ConcatenateCommand($widget, "_options-none"));
			$this->appendChild($label5);
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function getTemplateContextClass(): string{
		return RetrospectiveNotificationData::class;
	}
}
