<style type="text/css">

body {
	/*Tahoma, Verdana, Arial, Helvetica;*/
	font-size: 12px;
}

#nav, #nav ul { /* all lists */
	padding: 0;
	margin: 0;
	list-style: none;
	line-height: 1;
	
}

#nav a {
	width: 19em;
	text-decoration:none;
}

.nav-2 {
	width: 12em;
    height: 8em;
    line-height: 2em;
    border: 1px solid #ccc;
    padding: 0;
    margin: 0;
    overflow: scroll;

}

#nav li ul li a {
	text-decoration:none;
	color: #000000;
	padding-left:10px;
	padding-top:10px;

    vertical-align: middle; /* | top | bottom */
}

img
{
    vertical-align: middle; /* | top | bottom */
}


#nav li { /* all list items */
	float: left;
	width: 19em; /* width needed or else Opera goes nuts */
	height:20px;
}

#nav li ul { /* second-level lists */
	position: absolute;
	/*background: #EBEAEB;*/
	padding-left:10px;
	padding-top:10px;
	width: 19em;
	left: -999em; /* using left instead of display to hide menus because display: none isn't read by screen readers */
}

#nav li ul li:hover {
	background: #F5F5F5;
}

#nav li:hover ul, #nav li.sfhover ul { /* lists nested under hovered list items */
	left: auto;
	background: #EBEAEB;
}

#content {
	clear: left;
	color: #ccc;
}
.form-table th{
	 padding-left: 10px;
}
p {
	width: 250px;
}

</style>

<script type="text/javascript"><!--//--><![CDATA[//><!--

sfHover = function() {
	var sfEls = document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

//--><!]]></script>
<?php
$mergecode = __('Merge Codes', 'wishlist-member') . '<br />';
$mergecode.='&nbsp; &nbsp; [level] : ' . __('Membership Level', 'wishlist-member') . '<br />&nbsp; &nbsp; ' . __('Registration Links', 'wishlist-member') . '<br />';
$mergecode.='&nbsp; &nbsp; [newlink] : ' . __('New Member', 'wishlist-member') . '<br />';
$mergecode.='&nbsp; &nbsp; [existinglink] : ' . __('Existing Member', 'wishlist-member') . '<br />';

$activate = false;
if (wlm_arrval($_POST,'reg_instructions_new_reset')) {
	$activate = true;
	$this->DeleteOption('reg_instructions_new');
}
if (wlm_arrval($_POST,'reg_instructions_new_noexisting_reset')) {
	$activate = true;
	$this->DeleteOption('reg_instructions_new_noexisting');
}
if (wlm_arrval($_POST,'reg_instructions_existing_reset')) {
	$activate = true;
	$this->DeleteOption('reg_instructions_existing');
}
if ($activate) {
	$this->Activate();
}
?>
<form method="post">
	<h2><?php _e('Registration Instructions', 'wishlist-member'); ?></h2>
	<h3><?php _e('New Member Registration', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Full Instructions', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-advanced-tooltips-New-Member-Registration-Full-Instructions"); ?><p><?php echo $mergecode; ?></p></th>
		<td width="150px">
			<textarea name="<?php $this->Option($x = 'reg_instructions_new', true); ?>" id="<?php echo $x; ?>" cols="70" rows="10" size="70px"><?php $this->OptionValue(); ?></textarea>
			<br />
			<label><input type="checkbox" name="reg_instructions_new_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>			
		</td>
		<td style="margin-bottom: 200px; vertical-align: top;">
			<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[level]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[newlink]','<?php echo $x; ?>')"><?php _e('Insert New Member Link', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[existinglink]','<?php echo $x; ?>')"><?php _e('Insert Existing Member Link', 'wishlist-member'); ?></a></li>
						</ul>
					</li>
				</ul>
		</td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Instructions if "Existing Users Link" is Disabled', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-advanced-tooltips-Instructions-if-Existing-Users-Link-is-Disabled"); ?><p><?php echo $mergecode; ?></p></th>
		<td width="150px">
			<textarea name="<?php $this->Option($x = 'reg_instructions_new_noexisting', true); ?>" id="<?php echo $x; ?>" cols="70" rows="10"><?php $this->OptionValue(); ?></textarea>
			<br />
			<label><input type="checkbox" name="reg_instructions_new_noexisting_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
		</td>
		<td style="margin-bottom: 200px; vertical-align: top;">
			<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[level]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[newlink]','<?php echo $x; ?>')"><?php _e('Insert New Member Link', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[existinglink]','<?php echo $x; ?>')"><?php _e('Insert Existing Member Link', 'wishlist-member'); ?></a></li>
						</ul>
					</li>
				</ul>
		</td>
		</tr>
	</table>
	<h3><?php _e('Existing Member Registration', 'wishlist-member'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Full Instructions', 'wishlist-member'); ?><?php echo $this->Tooltip("settings-advanced-tooltips-Existing-Member-Registration-Full-Instructions"); ?><p><?php echo $mergecode; ?></p></th>
		<td width="150px">
			<textarea name="<?php $this->Option($x = 'reg_instructions_existing', true); ?>" id="<?php echo $x; ?>" cols="70" rows="10"><?php $this->OptionValue(); ?></textarea>
			<br />
			<label><input type="checkbox" name="reg_instructions_existing_reset" value="1" /> Reset to Default <?php echo $this->Tooltip("settings-advanced-tooltips-Reset-to-Default"); ?></label>
		</td>
		<td style="margin-bottom: 200px; vertical-align: top;">
			<ul id="nav">
					<li><a href="javascript:return false;"> <img src="<?php echo $this->pluginURL.'/images/WishList-Icon-Blue-16.png'; ?>"> &nbsp; Merge codes </a> <?php echo $this->Tooltip("settings-email-tooltips-insert-body"); ?>
						<ul class="nav-2">
							<li><a href="javascript:;" onclick="wpm_insertHTML('[level]','<?php echo $x; ?>')"><?php _e('Insert Membership Level', 'wishlist-member'); ?></a> </li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[newlink]','<?php echo $x; ?>')"><?php _e('Insert New Member Link', 'wishlist-member'); ?></a></li>
							<li><a href="javascript:;" onclick="wpm_insertHTML('[existinglink]','<?php echo $x; ?>')"><?php _e('Insert Existing Member Link', 'wishlist-member'); ?></a></li>
						</ul>
					</li>
				</ul>
		</td>
		</tr>
	</table>
	<p class="submit">
		<?php
		$this->Options();
		$this->RequiredOptions();
		?>
		<input type="hidden" name="WishListMemberAction" value="Save" />
		<input type="submit" class="button-primary" value="<?php _e('Save', 'wishlist-member'); ?>" />
	</p>
</form>
