<?php
namespace JulianSeymour\PHPWebApplicationFramework\template;

/**
 * interface for objects compatible as the context of a templateworthy element that uses generateTemplateFunction() or AjaxForm->generateFormDataSubmissionFunction()
 *
 * @author j
 *        
 */
interface TemplateContextInterface
{

	/**
	 * evoke behavior in the object to make it compatible with templating
	 */
	function template();
}
