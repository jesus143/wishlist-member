wlm_base_url = document.getElementsByTagName("script"),
	wlm_base_url = wlm_base_url[wlm_base_url.length-1].src;
wlm_base_url = wlm_base_url.split('/wp-content',1);

var tb_pathToImage=wlm_base_url+"/wp-includes/js/thickbox/loadingAnimation.gif";
var tb_closeImage=wlm_base_url+"/wp-includes/js/thickbox/tb-close.png";
