<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa\settings;

use function JulianSeymour\PHPWebApplicationFramework\base64url_encode;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\MfaSeedDatum;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\image\ImageElement;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;
use Exception;

class MfaQrCodeElement extends DivElement{

	use StyleSheetPathTrait;
	
	protected $revealQrCodeCheckboxInput;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("mfa_qr_c");
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->revealQrCodeCheckboxInput);
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try {
			$children = [];
			$context = $this->getContext();
			$img = new ImageElement();
			$img->addClassAttribute("fade");
			$otp_key = MfaSeedDatum::provisionOTPKey($context->getMfaSeed(), $context->getName());
			$img->setSourceAttribute('/qr/' . base64url_encode($otp_key));
			$this->appendChild($img);
			array_push($children, $img);
			$qr_label_c = new DivElement();
			$qr_label_c->addClassAttribute("qr_label_c");
			$label_span = new SpanElement();
			$show = new LabelElement();
			$show->addClassAttribute("label_show_qr");
			// $checkbox = $this->getRevealQrCodeCheckboxInput();
			$show->setForAttribute("mfa_qr_check");
			$show->setInnerHTML(_("Show"));
			$label_span->appendChild($show);
			$hide = new LabelElement();
			$hide->addClassAttribute("label_hide_qr");
			$hide->setForAttribute("mfa_qr_check");
			$hide->setInnerHTML(_("Hide"));
			$label_span->appendChild($hide);
			$qr_label_c->setInnerHTML($label_span . _("QR code"));
			$this->appendChild($qr_label_c);
			array_push($children, $qr_label_c);
			return $children;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getRevealQrCodeCheckboxInput()
	{
		if (isset($this->revealQrCodeCheckboxInput)) {
			return $this->revealQrCodeCheckboxInput;
		}
		$input = new CheckboxInput();
		$input->setIdAttribute("mfa_qr_check");
		$input->addClassAttribute("hidden");
		return $this->revealQrCodeCheckboxInput = $input;
	}

	protected function generatePredecessors(): ?array
	{
		$f = __METHOD__; //MfaQrCodeElement::getShortClass()."(".static::getShortClass().")->generatePredecessors()";
		return [
			$this->getRevealQrCodeCheckboxInput()
		];
	}
}
