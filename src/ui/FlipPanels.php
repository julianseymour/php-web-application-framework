<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\LoginForm;
use JulianSeymour\PHPWebApplicationFramework\account\register\RegisteringUser;
use JulianSeymour\PHPWebApplicationFramework\account\register\RegistrationForm;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputTrait;
use Exception;

class FlipPanels extends DivElement{

	use InputTrait;
	
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("horizontal_container");
		$this->setIdAttribute("flip_panels");
	}

	public function getInput(){
		if($this->hasInput()){
			return parent::getInput();
		}
		$input = new CheckboxInput();
		$input->setIdAttribute("login_panel_flip");
		$input->hide();
		return $this->setInput($input);
	}

	protected function getSelfGeneratedPredecessors(): ?array{
		return [
			$this->getInput()
		];
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$login_forms = new DivElement($mode);
			$login_forms->addClassAttribute("login_forms");
			$login_forms->addClassAttribute("list_scroll");
			$flip_panel = new DivElement($mode);
			$flip_panel->addClassAttribute("flip_panel", "background_color_1");
			$login_header = new DivElement($mode);
			$login_header->addClassAttribute("flip_header");
			$login_header->setInnerHTML(_("Login"));
			$flip_panel->appendChild($login_header);
			$login_notice = new DivElement($mode);
			$login_notice->addClassAttribute("flip_notice");
			$login_notice->setIdAttribute("login_notice");
			$login_notice->setInnerHTML(_("Enter your credentials below"));
			$flip_panel->appendChild($login_notice);
			$user_class = mods()->getUserClass(NormalUser::getSubtypeStatic());
			$user = new $user_class(ALLOCATION_MODE_LAZY);
			$user->allocateColumns();
			$login_form = new LoginForm($mode);
			$login_form->bindContext($user);
			$flip_panel->appendChild($login_form);
			$login_or = new DivElement($mode);
			$login_or->addClassAttribute("login_or");
			$span1 = new SpanElement();
			$span1->setAllowEmptyInnerHTML(true);
			$login_or->appendChild($span1);
			$or1 = new SpanElement($mode);
			$or1->setInnerHTML(_("or"));
			$login_or->appendChild($or1);
			$span2 = new SpanElement($mode);
			$span2->setAllowEmptyInnerHTML(true);
			$login_or->appendChild($span2);
			$flip_panel->appendChild($login_or);
			$align1 = new DivElement($mode);
			$align1->addClassAttribute("text-align_center");
			$register_label = new LabelElement($mode);
			$register_label->addClassAttribute("flip_label");
			$register_label->setForAttribute("login_panel_flip");
			$register_span = new SpanElement($mode);
			$register_span->setInnerHTML(_("Register"));
			$register_label->appendChild($register_span);
			$align1->appendChild($register_label);
			$flip_panel->appendChild($align1);
			$login_forms->appendChild($flip_panel);
			$lenap_pilf = new DivElement($mode);
			$lenap_pilf->addClassAttribute("lenap_pilf", "background_color_1");
			$lenap_pilf->setIdAttribute("lenap_pilf");
			$register_header = new DivElement($mode);
			$register_header->addClassAttribute("flip_header");
			$register_header->setInnerHTML(_("Register"));
			$lenap_pilf->appendChild($register_header);
			$register_notice = new DivElement($mode);
			$register_notice->addClassAttribute("flip_notice");
			$register_notice->setIdAttribute("register_notice");
			$register_notice->setAllowEmptyInnerHTML(true);
			$lenap_pilf->appendChild($register_notice);
			$register_me = new RegisteringUser(ALLOCATION_MODE_LAZY);
			$register_me->allocateColumns();
			$registration_form = new RegistrationForm($mode);
			$registration_form->setDisposeContextFlag(true);
			$registration_form->bindContext($register_me);
			$lenap_pilf->appendChild($registration_form);
			$register_or = new DivElement($mode);
			$register_or->addClassAttribute("login_or");
			$span3 = new SpanElement($mode);
			$span3->setAllowEmptyInnerHTML(true);
			$register_or->appendChild($span3);
			$or2 = new SpanElement($mode);
			$or2->setInnerHTML(_("or"));
			$register_or->appendChild($or2);
			$span4 = new SpanElement($mode);
			$span4->setAllowEmptyInnerHTML(true);
			$register_or->appendChild($span4);
			$lenap_pilf->appendChild($register_or);
			$align2 = new DivElement();
			$align2->addClassAttribute("text-align_center");
			$login_label = new LabelElement($mode);
			$login_label->addClassAttribute("flip_label");
			$login_label->setForAttribute("login_panel_flip");
			$login_span = new SpanElement($mode);
			$login_span->setInnerHTML(_("Sign in"));
			$login_label->appendChild($login_span);
			$align2->appendChild($login_label);
			$lenap_pilf->appendChild($align2);
			$login_forms->appendChild($lenap_pilf);
			$this->appendChild($login_forms);
			return [
				$login_forms
			];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
