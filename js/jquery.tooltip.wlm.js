/* 
 * jQuery Tooltip for WLM
 * Author: Andy
 * version: 1.0
 */

function initialize_tooltip($){

	$(".help").tooltip({
		track: true,
		showURL: false,
		fade:250,
		extraClass: "pretty",
		fixPNG: true,
		opacity: 0.95,
		left: -120,
		bodyHandler: function() {
			// return $($(this).attr("rel")).html();
			var helpid='';
			helpid=$(this).attr("rel");
			if ($(helpid).html()){
				return $(helpid).html();
			}else{
				$(this).attr({
					href: helpid
				});
				return  '<h3>No tooltip availible</h3><p>'+ helpid+'</p>';
			}

		}
	});

	$(".help").click(function(){
		helpBaseUrl="http://www.wishlistproducts.com/help/";
		thislink=$(this).attr("href");
		return false; // we dont want handel click on the help link for now! if we remove this user will nevigate to WishList Member website to read more help!

		if (thislink!=null){
			thisHelpUrl=helpBaseUrl + thislink;
			$(this).attr({
				href: thisHelpUrl
			});
			$(this).attr({
				target: "_blank"
			});
		}else{
			return false;
		}
	});
}

jQuery(document).ready(function($) {
	initialize_tooltip($);

});


