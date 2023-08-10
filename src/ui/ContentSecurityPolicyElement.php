<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\element\MetaElement;

class ContentSecurityPolicyElement extends MetaElement
{

	// XXX TODO rig this to pull content from modules
	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->setHttpEquivAttribute("Content-Security-Policy");
		$default_src = "'self' 'unsafe-inline'";
		$script_src = "'self' 'unsafe-inline' *." . WEBSITE_DOMAIN . " https://newassets.hcaptcha.com https://hcaptcha.com https://*.hcaptcha.com https://js.stripe.com";
		$connect_src = "'self' 'unsafe-inline'";
		$frame_src = "https://hcaptcha.com https://*.hcaptcha.com https://js.stripe.com https://www.google.com https://youtube.com https://www.youtube.com https://youtu.be";
		$img_src = "'self' blob: data:";
		$style_src = "'self' 'unsafe-inline' https://hcaptcha.com https://*.hcaptcha.com";
		$worker_src = "'self' 'unsafe-inline'";
		$report_uri = "https://" . WEBSITE_DOMAIN . "/report_csp";
		$content = "default-src {$default_src};
script-src {$script_src};
connect-src data: {$connect_src};
frame-src {$frame_src};
img-src {$img_src};
style-src {$style_src};
worker-src {$worker_src};
report-uri {$report_uri};"; // note: if this is just a /whatever URI without and not a valid URL, firefox will never register the sw, but mobile brave will seemingly ignore it and complete the registration
		$this->setContentAttribute($content);
	}
}
