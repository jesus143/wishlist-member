<?php
/*
 * Authorize.Net Payment Integration
 * Original Author : Mike Lopez / Ramil R. Lacambacal
 * Version: $Id: integration.shoppingcart.authorize.php 2590 2015-03-26 19:33:38Z ronaldo $
 */

$__index__ = 'an';
$__sc_options__[$__index__] = 'Authorize.Net - Simple Checkout';
$__sc_videotutorial__[$__index__] = wlm_video_tutorial ( 'integration', 'sc', $__index__ );

if (wlm_arrval($_GET,'cart') == $__index__) {
  if (!$__INTERFACE__) {
    // BEGIN Initialization

    $anloginid = $this->GetOption('anloginid');
    if (isset($_POST['anloginid']) && wlm_arrval($_POST,'anloginid') != $anloginid) {
      $this->SaveOption('anloginid', $anloginid = trim(wlm_arrval($_POST,'anloginid')));
      echo "<div class='updated fade'>" . __('<p>Authorize.Net API Login ID Changed.</p>', 'wishlist-member') . "</div>";
    }

    $antransid = $this->GetOption('antransid');
    if (isset($_POST['antransid']) && wlm_arrval($_POST,'antransid') != $antransid) {
      $this->SaveOption('antransid', $antransid = trim(wlm_arrval($_POST,'antransid')));
      echo "<div class='updated fade'>" . __('<p>Authorize.Net Transaction ID Changed.</p>', 'wishlist-member') . "</div>";
    }

    $anmd5hash = $this->GetOption('anmd5hash');
    if (isset($_POST['anmd5hash']) && wlm_arrval($_POST,'anmd5hash') != $anmd5hash) {
      $this->SaveOption('anmd5hash', $anmd5hash = trim(wlm_arrval($_POST,'anmd5hash')));
      echo "<div class='updated fade'>" . __('<p>Authorize.Net MD5 Hash Key Changed.</p>', 'wishlist-member') . "</div>";
    }

    $anetsandbox = $this->GetOption('anetsandbox');
    if (isset($_POST['anetsandbox'])) {
      if($ppsandbox != trim(wlm_arrval($_POST,'anetsandbox'))){
        if (wlm_arrval($_POST,'anetsandbox') == 1) {
          echo "<div class='updated fade'>" . __('<p>Authorize.Net Sandbox Enabled.</p>', 'wishlist-member') . "</div>";
        } else {
          echo "<div class='updated fade'>" . __('<p>Authorize.Net Disabled.</p>', 'wishlist-member') . "</div>";
        }
      }
      $this->SaveOption('anetsandbox', $anetsandbox = trim(wlm_arrval($_POST,'anetsandbox')));
    }
    $anetsandbox = (int) $anetsandbox;

    $anurl = $this->GetOption('anurl');
    if (isset($_POST['anurl']) && wlm_arrval($_POST,'anurl') != $anurl) {
      $this->SaveOption('anurl', $anurl = trim(wlm_arrval($_POST,'anurl')));
      if (wlm_arrval($_POST,'anurl')) {
        $msg = __('Authorize.Net is now in Live Mode', 'wishlist-member');
      } else {
        $msg = __('Authorize.Net is now in Test Mode', 'wishlist-member');
      }
      echo "<div class='updated fade'><p>" . $msg . "</p></div>";
    }

    $anthankyou = $this->GetOption('anthankyou');
    if (!$anthankyou) {
      $this->SaveOption('anthankyou', $anthankyou = implode('', array_rand(array_flip(array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9))), 6)));
    }

    // save POST URL
    if (wlm_arrval($_POST,'anthankyou')) {
      $_POST['anthankyou'] = trim(wlm_arrval($_POST,'anthankyou'));
      $wpmx = trim(preg_replace('/[^A-Za-z0-9]/', '', $_POST['anthankyou']));
      if ($wpmx == $_POST['anthankyou']) {
        $this->SaveOption('anthankyou', $anthankyou = $wpmx);
        echo "<div class='updated fade'>" . __('<p>PayPal Thank You URL Changed.&nbsp; Make sure to update your PayPal products with the same Thank You URL to make it work.</p>', 'wishlist-member') . "</div>";
      } else {
        echo "<div class='updated fade'>" . __('<p><b>Error:</b>PayPal Thank You URL may only contain letters and numbers.</p>', 'wishlist-member') . "</div>";
      }
    }
    $anthankyou_url = $wpm_scregister . $anthankyou;
    // END Initialization
  } else {
    // START Interface
?>
    
    <blockquote>
	    <h2 class="wlm-integration-steps"><?php _e('Step 1. Set Up Authorize.net API Credentials:','wishlist-member'); ?></h2>
	    <p><?php _e('API Credentials are located in the Authorize.net Merchant Interface in the following section: <br> Account > Settings > Security Settings > API Login ID and Transaction Key','wishlist-member'); ?></strong></p>
      <form method="post">
        <table class="form-table">
          <tr>
            <th scope="row">API Login ID</th>
            <td><input type="text" size="50" name="anloginid" value="<?php echo $anloginid; ?>" />
              <?php echo $this->Tooltip("integration-shoppingcart-authorize-tooltips-API-Login-ID"); ?>

            </td>
          </tr>
          <tr>
            <th scope="row">Trasaction Key</th>
            <td><input type="text" size="50" name="antransid" value="<?php echo $antransid; ?>" />
              <?php echo $this->Tooltip("integration-shoppingcart-authorize-tooltips-API-Key"); ?>

            </td>
          </tr>
          <tr>
            <th scope="row">MD5 Hash Key</th>
            <td><input type="text" size="50" name="anmd5hash" value="<?php echo $anmd5hash; ?>" />
              <?php echo $this->Tooltip("integration-shoppingcart-authorize-tooltips-API-md5hash"); ?>

            </td>
          </tr>
        </table>
        <p>
        	<input type="submit" class="button-secondary" value="<?php _e('Update API Credentials', 'wishlist-member'); ?>" />
        </p>
      </form>
      <form method="post">
        <h2 class="wlm-integration-steps"><?php _e('Step 2. Set the Thank You URL in Authorize.net to the following URL:', 'wishlist-member'); ?></h2>
        <p>&nbsp;&nbsp;<a href="<?php echo $anthankyou_url ?>" onclick="return false"><?php echo $anthankyou_url ?></a> &nbsp; (<a href="javascript:;" onclick="document.getElementById('anthankyou').style.display='block';"><?php _e('change', 'wishlist-member'); ?></a>)
          <?php echo $this->Tooltip("integration-shoppingcart-authorize-tooltips-thankyouurl"); ?>

        </p>
        <div id="anthankyou" style="display:none">
          <p>&nbsp;&nbsp;<?php echo $wpm_scregister ?><input type="text" name="anthankyou" value="<?php echo $anthankyou ?>" size="8" /><input type="submit" class="button-secondary" value="<?php _e('Change', 'wishlist-member'); ?>" /></p>
        </div>
      </form>
      <h2 class="wlm-integration-steps"><?php _e('Step 3. Create a Simple Checkout item for each Membership Level using the Item IDs below:', 'wishlist-member'); ?></h2>
      <br>
      <table class="widefat">
        <thead>
          <tr>
            <th scope="col" width="200"><?php _e('Membership Level', 'wishlist-member'); ?></th>
            <th scope="col"><?php _e('Item ID', 'wishlist-member'); ?></th>
          </tr>
        </thead>
        <tbody>
<?php
    $alt = 0;
    foreach ((array) $wpm_levels AS $sku => $level):
?>
            <tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" id="wpm_level_row_<?php echo $sku ?>">
              <td><b><?php echo $level['name'] ?></b></td>
              <td><?php echo $sku ?></td>
            </tr>
    <?php endforeach; ?>
        </tbody>
      </table>
    </blockquote>

    <h2 class="wlm-integration-steps"><?php _e('Sandbox Testing', 'wishlist-member'); ?></h2>
    <form method="post">
    <p><?php printf(__('Enable the optional Authorize.net Sandbox to test the Authorize.net Integration.', 'wishlist-member'), 'https://sandbox.authorize.net/'); ?></p>
      <blockquote>
        <p><label>
          <input type="radio" name="anetsandbox" value="1" <?php $this->Checked($anetsandbox, 1); ?> /><?php _e('Enabled', 'wishlist-member'); ?>
        </label>
            <br />
        <label>
            <input type="radio" name="anetsandbox" value="0" <?php $this->Checked($anetsandbox, 0); ?> /><?php _e('Disabled', 'wishlist-member'); ?>
        </label>
        </p>
      </blockquote>
    <p><input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wishlist-member'); ?>" />
    </form>
<?php
      include_once($this->pluginDir . '/admin/tooltips/integration.shoppingcart.authorize.tooltips.php');
    // END Interface
  }
}
?>
