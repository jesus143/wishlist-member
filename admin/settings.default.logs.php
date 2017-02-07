<?php
if (isset($_POST['submit'])) {
	update_option('wishlist_enable_debug', $_POST['wishlist_enable_debug']);

	if (wlm_arrval($_POST,'clear_logs')) {
		WishlistDebug::clear_logs();
	}
}
$enabled = get_option('wishlist_enable_debug');
if ($enabled) {
	$checked = 'checked="checked"';
} else {
	$checked = null;
}
$logs = WishlistDebug::fetch_logs();

if(isset($_POST["submit"])){
	$info_to_send = $_POST;
	unset($info_to_send["wishlist_enable_debug"]);
	unset($info_to_send["clear_logs"]);
	unset($info_to_send["submit"]);
	$this->SaveOption('WLMSiteTracking',maybe_serialize($info_to_send));
}

$info_to_send = $this->GetOption('WLMSiteTracking');
if($info_to_send) $info_to_send = maybe_unserialize($info_to_send);
else $info_to_send = array();

$inf = array( 
	"send_wlmversion"=>1,"send_phpversion"=>1,"send_apachemod"=>1,
	"send_webserver"=>1,"send_language"=>1,"send_apiused"=>1,
	"send_payment"=>1,"send_autoresponder"=>1,"send_webinar"=>1,
	"send_nlevels"=>1,"send_nmembers"=>1,"send_sequential"=>1,"send_customreg"=>1
);
$site_info = $this->GetSiteInfo($inf);

if(isset($site_info["payment"]) && is_array($site_info["payment"])){

	$str = "";
	foreach($site_info["payment"] as $key=>$sc){
		$str .= $key ."{" .$sc ."},";
	}
	$str = trim($str,",");
	$site_info["payment"] = $str;
}

if(isset($site_info["apiused"]) && is_array($site_info["apiused"])){
	$str = "";
	foreach($site_info["apiused"] as $key=>$sc){
		$str .= $key ."({$sc["date"]})=" .$sc["request"] .",";
	}
	$str = trim($str,",");
	$site_info["apiused"] = $str;
}
?>
<p>
<?php if ($enabled && $logs): ?>
	<textarea style="width: 100%; height: 200px;"><?php echo $logs ?></textarea>
<?php endif; ?>
<form method="post" action="">
    <span>
		<?php _e('Enabling the debugging feature can help guide WishList Member developers in isolating possible issues. Please turn on this feature below if requested by a WishList Member developer.', 'wishlist-member'); ?>
    </span> <br/><br/>
    
	<label><input type="checkbox" <?php echo $checked ?> name="wishlist_enable_debug"/>&nbsp;Enable Debugging</label><br/><br/>
    <label><input type="checkbox" name="clear_logs" />&nbsp;Clear Logs</label>

    <h2>Anonymous Data Submission</h2>
    <span>
		<?php _e('Allow WishList Member to send the following information to the WishList Products server anonymously by selecting the corresponding checkboxes.', 'wishlist-member'); ?>
    </span><br/><br/>
    <h3>Technical</h3>
    <blockquote>
	    <label><input type="checkbox" name="send_wlmversion" <?php echo isset($info_to_send["send_wlmversion"]) ? 'checked="checked"':''; ?> />&nbsp;WLM Version</label>
	    (<small><?php echo $site_info["wlmversion"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_phpversion" <?php echo isset($info_to_send["send_phpversion"]) ? 'checked="checked"':''; ?> />&nbsp;PHP Version</label>
	    (<small><?php echo $site_info["phpversion"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_apachemod" <?php echo isset($info_to_send["send_apachemod"]) ? 'checked="checked"':''; ?> />&nbsp;Apache Module</label>
	    (<small><?php echo  $site_info["apachemod"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_webserver" <?php echo isset($info_to_send["send_webserver"]) ? 'checked="checked"':''; ?> />&nbsp;Web Server</label>
	    (<small><?php echo  $site_info["webserver"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_language" <?php echo isset($info_to_send["send_language"]) ? 'checked="checked"':''; ?> />&nbsp;Language</label>
	    (<small><?php echo  $site_info["language"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_apiused" <?php echo isset($info_to_send["send_apiused"]) ? 'checked="checked"':''; ?> />&nbsp;WLM API Usage</label>
	    (<small><?php echo (empty($site_info["apiused"])) ? 'None' : $site_info["apiused"]; ?></small>)<br/><br/>
    </blockquote>
    <h3>Integrations</h3>
    <blockquote>
	    <label><input type="checkbox" name="send_payment" <?php echo isset($info_to_send["send_payment"]) ? 'checked="checked"':''; ?> />&nbsp;Payment Integration</label>
	    (<small><?php echo (empty($site_info["payment"])) ? 'None' : $site_info["payment"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_autoresponder" <?php echo isset($info_to_send["send_autoresponder"]) ? 'checked="checked"':''; ?> />&nbsp;Autoresponder Integration</label>
	    (<small><?php echo (empty($site_info["autoresponder"])) ? 'None' : $site_info["autoresponder"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_webinar" <?php echo isset($info_to_send["send_webinar"]) ? 'checked="checked"':''; ?> />&nbsp;Webinar Integration</label>
	    (<small><?php echo (empty($site_info["webinar"])) ? 'None' : $site_info["webinar"]; ?></small>)<br/><br/>
   	</blockquote>
    <h3>Membership</h3>
    <blockquote>
	    <label><input type="checkbox" name="send_nlevels" <?php echo isset($info_to_send["send_nlevels"]) ? 'checked="checked"':''; ?> />&nbsp;Number of Levels</label>
	    (<small><?php echo (empty($site_info["nlevels"])) ? 'None' : $site_info["nlevels"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_nmembers" <?php echo isset($info_to_send["send_nmembers"]) ? 'checked="checked"':''; ?> />&nbsp;Number of Members</label>
	    (<small><?php echo (empty($site_info["nmembers"])) ? 'None' : $site_info["nmembers"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_sequential" <?php echo isset($info_to_send["send_sequential"]) ? 'checked="checked"':''; ?> />&nbsp;Using Sequential</label>
	    (<small><?php echo (empty($site_info["sequential"])) ? 'None' : $site_info["sequential"]; ?></small>)<br/><br/>
	    <label><input type="checkbox" name="send_customreg" <?php echo isset($info_to_send["send_customreg"]) ? 'checked="checked"':''; ?> />&nbsp;Using Custom Regsitration Form</label>
	    (<small><?php echo (empty($site_info["customreg"])) ? 'None' : $site_info["customreg"]; ?></small>)<br/><br/>			
	</blockquote>

	<br/><br/><input type="submit" name="submit" value="Save" class="button-primary"/>
</form>
</p>

