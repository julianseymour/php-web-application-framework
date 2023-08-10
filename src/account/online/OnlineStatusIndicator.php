<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\online;

use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;

class OnlineStatusIndicator extends SpanElement
{

	public function __construct($mode = ALLOCATION_MODE_LAZY, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("online_indicator");
	}

	/**
	 *
	 * @param AuthenticatedUser $context
	 * {@inheritdoc}
	 * @see SpanElement::bindContext()
	 */
	public function bindContext($context)
	{
		$f = __METHOD__; //OnlineStatusIndicator::getShortClass()."(".static::getShortClass().")->bindContext()";
		$ret = parent::bindContext($context);
		$this->resolveTemplateCommand(new UpdateOnlineStatusIndicatorCommand($context, $this));
		return $ret;
	}
}
