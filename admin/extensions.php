<?php if ($show_page_menu) : ?>
	<?php
	return;
endif;
?>
<?php
$extensions = $this->GetRegisteredExtensions();
?>
<p class="search-box" style="margin-top:1em">
	<select onchange="top.location = '?<?php echo $this->QueryString('ex'); ?>&ex=' + this.value">
		<option value="">--- Extension Quick Jump ---</option>
		<?php foreach ((array) $extensions AS $ex): ?>
			<option value="<?php echo $ex['File']; ?>" <?php if ($ex['File'] == $_GET['ex']) echo ' selected="true" '; ?>><?php echo $ex['Name']; ?></option>
<?php endforeach; ?>
	</select>
</p>
<h2>
<?php _e('Extensions', 'wishlist-member'); ?>
</h2>
<br />
<?php
if ($extensions[wlm_arrval($_GET,'ex')]) {
	do_action('wishlistmember_extension_page', $_GET['ex'], $this);
} else {
	?>
	<table class="widefat" id="wpm_extensions">
		<thead>
			<tr>
				<th scope="col">Extensions</th>
				<th scope="col">Description</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ((array) $extensions AS $ex):
				?>
				<tr>
					<td class="plugin-title">
						<div><strong><a href="?<?php echo $this->QueryString('ex'); ?>&ex=<?php echo $ex['File']; ?>"><?php echo $ex['Name']; ?></a></strong></div>
						<div><a href="?<?php echo $this->QueryString('ex'); ?>&ex=<?php echo $ex['File']; ?>">[Settings]</a></div>
					</td>
					<td class="desc">
						<div><?php echo $ex['Description']; ?></div>
						<div>
							Version <?php echo $ex['Version']; ?>
							| By <?php if ($ex['AuthorURL']): ?><a href="<?php echo $ex['AuthorURL']; ?>"><?php endif; ?><?php echo $ex['Author']; ?><?php if ($ex['AuthorURL']): ?></a><?php endif; ?>
		<?php if ($ex['URL']): ?> | <a href="<?php echo $ex['URL']; ?>">Visit Extension's Website</a><?php endif; ?>
						</div>
					</td>
				</tr>
				<?php
			endforeach;
			?>
		</tbody>
	</table>
	<?php
} // endif;
?>