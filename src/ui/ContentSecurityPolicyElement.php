<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\element\MetaElement;

class ContentSecurityPolicyElement extends MetaElement{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$csp = mods()->getContentSecurityPolicyDirectives();
		parent::__construct($mode, $context);
		$this->setHttpEquivAttribute("Content-Security-Policy");
		$content = "";
		$default_src = "";
		if(array_key_exists('default-src', $csp)){
			foreach($csp['default-src'] as $d){
				$default_src .= " {$d}";
			}
		}
		if(!empty($default_src)){
			$content .= "default-src{$default_src};\n";
		}
		$script_src = "";
		if(array_key_exists('script-src', $csp)){
			foreach($csp['script-src'] as $d){
				$script_src .= " {$d}";
			}
		}
		if(!empty($script_src)){
			$content .= "script-src{$script_src};\n";
		}
		$connect_src = "";
		if(array_key_exists('connect-src', $csp)){
			foreach($csp['connect-src'] as $d){
				$connect_src .= " {$d}";
			}
		}
		if(!empty($connect_src)){
			$content .= "connect-src{$connect_src};\n";
		}
		$frame_src = "";
		if(array_key_exists('frame-src', $csp)){
			foreach($csp['frame-src'] as $d){
				$frame_src .= " {$d}";
			}
		}
		if(!empty($frame_src)){
			$content .= "frame-src{$frame_src};\n";
		}
		$img_src = "";
		if(array_key_exists('img-src', $csp)){
			foreach($csp['img-src'] as $d){
				$img_src .= " {$d}";
			}
		}
		if(!empty($img_src)){
			$content .= "img-src{$img_src};\n";
		}
		$style_src = "";
		if(array_key_exists('style-src', $csp)){
			foreach($csp['style-src'] as $d){
				$style_src .= " {$d}";
			}
		}
		if(!empty($style_src)){
			$content .= "style-src{$style_src};\n";
		}
		$worker_src = "";
		if(array_key_exists('worker-src', $csp)){
			foreach($csp['worker-src'] as $d){
				$worker_src .= " {$d}";
			}
		}
		if(!empty($worker_src)){
			$content .= "worker-src{$worker_src};\n";
		}
		$content .= "report-uri https://".DOMAIN_LOWERCASE."/report_csp;"; // note: if this is just a /whatever URI without and not a valid URL, firefox will never register the sw, but mobile brave will seemingly ignore it and complete the registration
		$this->setContentAttribute($content);
	}
}
