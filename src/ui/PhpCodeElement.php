<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\CodeElement;

class PhpCodeElement extends DivElement
{

	public function __construct(string $uri)
	{
		$this->setUri($uri);
		parent::__construct();
		$this->addClassAttribute("code");
		$innerHTML = file_get_contents($this->getUri());
		if (starts_with($innerHTML, "<?php")) {
			$innerHTML = substr($innerHTML, 5);
		}
		$code = new CodeElement();
		$code->setInnerHTML($innerHTML);
		$this->appendChild(Document::createElement("div")->withInnerHTML($this->getUri() . ":")
			->withClassAttribute("uri"), $code);
	}
}