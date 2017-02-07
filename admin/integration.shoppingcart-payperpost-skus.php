<?php
/*
 * Pay Per Post SKUs Table
 * Original Author : Mike Lopez
 */

	$none = true;
	$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
	foreach($xposts AS &$x) {
		if(count($x)) {
			$none = false;
			break;
		}
	}
	unset($x);
	if($none) {
		return;
	}
?>
<?php if (isset($ppph2)) : ?>
	<h2 class="small"><?php echo $ppph2; ?></h2>
<?php else : ?>
	<h2 class="small"><?php _e('Pay Per Post SKUs:', 'wishlist-member'); ?></h2>
<?php endif; ?>

<?php if (isset($pppdesc)) : ?>
	<p><?php echo $pppdesc; ?></p>
<?php else : ?>
	<p><?php _e('The Pay Per Post SKUs specify the posts that should be connected to each transaction.', 'wishlist-member'); ?></p>
<?php endif; ?>

<?php
if (!isset($ppptitle_header)) {
	$ppptitle_header = __('Post Title', 'wishlist-member');
}
if (!isset($pppsku_header)) {
	$pppsku_header = __('SKU', 'wishlist-member');
}
if (!isset($pppsku_text)) {
	$pppsku_text = '%s';	
}

if(empty($ppp_colset)) {
	$ppp_colset = '<col width="200"></col>';
}

$xposts = $this->GetPayPerPosts(array('post_title', 'post_type'));
$post_types = get_post_types('', 'objects');
?>
<?php foreach ($xposts AS $post_type => $posts) : ?>
	<?php if(count($posts)) : ?>
	<h3 class="wlm-integration"><?php echo $post_types[$post_type]->labels->name; ?></h3>
	<table class="widefat">
		<?php echo $ppp_colset; ?>
		<thead>
			<tr>
				<th scope="col"><?php echo $ppptitle_header; ?></th>
				<th scope="col"><?php echo $pppsku_header; ?></th>
				<?php
				if (isset($ppp_extraheaders) && is_array($ppp_extraheaders) && !empty($ppp_extraheaders)) {
					foreach ($ppp_extraheaders AS $ppp_extraheader) {
						printf('<th scope="col">%s</th>', $ppp_extraheader);
					}
				}
				?>
			</tr>
		</thead>
	</table>
	<div style="max-height:130px;overflow:auto;">
		<table class="widefat" style="border-top:none">
			<?php echo $ppp_colset; ?>
			<tbody>
				<?php
				$alt = 0;
				foreach ($posts AS $post) :
					$ppp_productids = $cbproducts['payperpost-'.$post->ID];
					?>
					<tr class="<?php echo $alt++ % 2 ? '' : 'alternate'; ?>" >
						<td><b><?php echo $post->post_title; ?></b></td>
						<td><?php
					if (isset($cbproducts)):
						foreach((array)$ppp_productids as $key => $ppp_productid): 
						?>
							<u style="font-size:1.2em"><?php printf($pppsku_text, 'payperpost-' . $post->ID, $ppp_productid); ?></u>
						<?php endforeach; ?>
							<div class="content" id="wl_<?php echo 'payperpost-' . $post->ID ?>">
								<?php if (count($ppp_productids) == 0) : ?>
									<u style="font-size:1.2em"><?php printf($pppsku_text, 'payperpost-' . $post->ID, $ppp_productid); ?></u>
								<?php endif ?>
							</div>
							<a href="#<?php echo 'payperpost-' . $post->ID ?>" onclick="cbpppID_fields('<?php echo 'payperpost-' . $post->ID ?>'); "><strong>Add Another Item</strong></a>
					<?php else: ?>
							<u style="font-size:1.2em"><?php printf($pppsku_text, 'payperpost-' . $post->ID); ?></u>
					<?php endif ?>
					</td>
					<td><?php
						if (isset($ppp_extracolumns) && is_array($ppp_extracolumns) && !empty($ppp_extracolumns) && isset($cbproducts)) {
							foreach ($ppp_extracolumns AS $ppp_extracolumn) {
							foreach((array)$ppp_productids as $key => $ppp_productid) { ?>
								<?php printf('<div><span>%s</span></div></br>', sprintf($ppp_extracolumn, 'payperpost-' . $post->ID. '_' .$ppp_productid ));
									}
								}
						}else{
							foreach ( (array) $ppp_extracolumns AS $ppp_extracolumn) {
								printf('<td>%s</th>', sprintf($ppp_extracolumn, 'payperpost-' . $post->ID));
							}
						}
							?></td>
					</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php echo $ppp_table_end; ?>
	<?php endif; ?>
<?php endforeach; ?>
	<script>
			
			function cbpppID_fields(id) {
				var divID = 'wl_';
				var divID = divID.concat(id);			
				document.getElementById(divID).innerHTML += '<input type="text" name="cbproducts['+id+'][]" value="" size="16" style="text-align:center">\r\n';
			}			
	</script>