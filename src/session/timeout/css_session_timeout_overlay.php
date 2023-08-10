<?php
$wait1 = SESSION_TIMEOUT_SECONDS - 120.5;
$total = SESSION_TIMEOUT_SECONDS + 2.5 - 60;
$p1 = floor(($wait1 / $total) * 100); // wait until sequence starts
                                      // $p2 =
$fade = (0.5 / $total) * 100; // popup opening, fading out
                              // $p3 = (60/$total) * 100; //popup is visible
                              // $p4 = (1/$total) * 100; //warning message fade in
$p5 = $p1 + (2 * $fade) + ((60 / $total) * 100); // $p3; //wait time for logo
$p6 = $p5 + $fade; // wait time for warning message
                   // $p7 =
$p2new = $p1 + $fade; // popup is open
$p8 = $p6 + ((1 / $total) * 100); // $p4;

/*
 * $arr = [
 * "zero", $p1, "deleted_2", "deleted_3", "deleted_4", $p2new, $p5, $p6, $p8, $wait1, $total
 * ];
 * Debug::printArray($arr);
 */
?>
@keyframes session_timeout_1{
	from{ 
		opacity:0;
		transform:scale3d(0,0,0);
		/*pointer-events:none;*/
		/*background-color:#000;*/
	}
	/*transparent, shrunken*/
	<?php echo $p1; ?>%{ /*fadein, enlargement start*/
		opacity:0;
		transform:scale3d(0,0,0);
		/*pointer-events:none;*/
		/*background-color:#f00;*/
	}
	/*fading in and enlarging*/
	<?php echo $p2new; ?>%{ /*fadein, enlargement end*/
		opacity:1;
		transform:scale3d(1,1,1);
		pointer-events:auto;
		/*background-color:#0f0;*/
	}
	/*opaque and enlarged*/
	<?php echo $p5; ?>%{ /*fadeout begin*/
		opacity:1;
		transform:scale3d(1,1,1);
		/*pointer-events:none;*/
		/*background-color:#0ff;*/
	}
	/*fading out*/
	<?php echo $p6; ?>%{ /*fadeout end*/
		opacity:0;
		transform:scale3d(1,1,1);
		/*pointer-events:none;*/
		/*background-color:#ff0;*/
	}
	/*faded out*/
	to{
		opacity:0;
		transform:scale3d(1,1,1);
		/*pointer-events:none;*/
		/*background-color:#f0f;*/
	}
}

@keyframes session_timeout_2{
	from{
		opacity:0;
		transform:translateX(100vw);
		pointer-events:none;
	}
	/*panned offscreen*/
	<?php echo $p6; ?>%{ /*pan start*/
		opacity:0;
		transform:translateX(100vw);
		pointer-events:none;
	}
	/*panning*/
	<?php echo $p8; ?>%{ /*pan complete*/
		opacity:1;
		transform:translateX(0);
		pointer-events:none;
	}
	/*panned onscreen*/
	to{
		opacity:1;
		transform:translateX(0);
		pointer-events:auto;
	}
}

@keyframes background_timeout{
	from{
		background-color:rgba(23, 23, 23, 0);
		pointer-events:none;
	}
	/*transparent*/
	<?php echo $p1; ?>%{ /*fadein start*/
		background-color:rgba(23, 23, 23, 0);
		pointer-events:none;
	}
	/*fading in*/
	<?php echo $p2new; ?>%{ /*fadein complete*/
		background-color:rgba(23, 23, 23, 0.85);
		pointer-events:auto;
	}
	/*faded in*/
	to{
		background-color:rgba(23, 23, 23, 1);
		pointer-events:auto;
	}
}

@keyframes blur_timeout{
	from{
		filter:blur(0px);
		pointer-events:auto;
	}
	98%{
		filter:blur(0px);
		pointer-events:auto;
	}
	to{
		filter:blur(5px);
		pointer-events:none;
	}
}

#session_timeout_overlay{
	-o-animation:background_timeout <?php echo $total; ?>s normal;
	-moz-animation:background_timeout <?php echo $total; ?>s normal;
	-webkit-animation:background_timeout <?php echo $total; ?>s normal;
	animation:background_timeout <?php echo $total; ?>s normal;
	-o-animation-fill-mode:forwards;
	-moz-animation-fill-mode:forwards;
	-webkit-animation-fill-mode:forwards;
	animation-fill-mode:forwards;
}

.session_timeout_overlay > div:nth-child(1){
	animation:session_timeout_1 <?php echo $total; ?>s normal;
	-o-animation-fill-mode:forwards;
	-moz-animation-fill-mode:forwards;
	-webkit-animation-fill-mode:forwards;
	animation-fill-mode:forwards;
	animation-play-state:running;
	pointer-events:inherit;
	/*background-color:rgba(0, 0, 0, 0.85);*/
}

.session_timeout_overlay > a:nth-child(2){
	animation:session_timeout_2 <?php echo $total; ?>s normal;
	-o-animation-fill-mode:forwards;
	-moz-animation-fill-mode:forwards;
	-webkit-animation-fill-mode:forwards;
	animation-fill-mode:forwards;
	animation-play-state:running;
	z-index:9999;
	/*display:none;*/
	color:#0dd;
}
