<script>
	jQuery(function($) {
		$('.dismiss-wlm4is-ad').click(function() {
			var data = {
				action : 'wlm_dismiss_nag',
				nag_name : 'dismiss-wlm4is-ad'
			}
			$.post(ajaxurl, data);
			$('.wlm4is-ad').hide();
			$('.wlm4is-ad').remove();
		});
	});
</script>
<?php
$adtext = __('Click Here for more information on an Advanced Integration with Infusionsoft', 'wishlist-member');
$adlink = 'http://wlplink.com/go/wlmis/29it';
if(!$this->GetOption('dismiss-wlm4is-ad')) {
	printf ('
	<div style="position:absolute;right:0;padding-top:24px" class="wlm4is-ad">
		<div style="position:absolute;right:0;margin-right:-8px;margin-top:-10px;font-size:20px">
			<i class="icon-remove-sign dismiss-wlm4is-ad" style="cursor:pointer"></i>
		</div>
		<a href="%s" title="%s" target="_blank"><img src="https://wlm-infusionsoft.s3.amazonaws.com/ads/125x125.gif" border="0" height="125" width="125"></a>
	</div>
	', $adlink, $adtext);
} else {
	printf('<p style="float:right; margin:-2em 0 0 0"><a href="%s" target="_blank">%s</a></p>', $adlink, $adtext);
}
