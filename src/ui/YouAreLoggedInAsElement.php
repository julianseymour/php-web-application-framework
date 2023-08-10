<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class YouAreLoggedInAsElement extends DivElement{

	use StyleSheetPathTrait;
	
	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("slide_menu_label", "background_color_1");
		$this->setIdAttribute("logged_in_as");
	}

	public function generateChildNodes(): ?array{
		$name = user()->getName();
		$this->appendChild(Document::createElement("div")->withInnerHTML(substitute(_("You are logged in as %1%"), $name)));
		return $this->getChildNodes();
	}
}
