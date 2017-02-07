<table id="wlm-stripe-table2" class="wlm-stripe-invlist">
	<thead>
		<tr>
			<th class="wlm-stripe-invlist-id-head"><?php _e("ID", "wishlist-member") ?></th>
			<th class="wlm-stripe-invlist-date-head"><?php _e("Date", "wishlist-member") ?></th>
			<th class="wlm-stripe-invlist-total-head"><?php _e("Total", "wishlist-member") ?></th>
		</tr>
	</thead>
	<?php if (!empty($invoices)): ?>
		<?php foreach ($invoices as $i): ?>
			<?php if ($i['object'] == 'invoice'): ?>
				<tr class="wlm-stripe-invlist-row">
					<td class="wlm-stripe-invlist-id-col">
						<a data-id="<?php echo $i['id'] ?>" class="stripe-invoice-detail" href="#stripe-invoice-detail"><?php echo $i['id'] ?></a>
					</td>
					<td class="wlm-stripe-invlist-date-col"><?php echo date('M d, Y', $i['date']) ?></td>
					<td class="wlm-stripe-invlist-total-col"><?php echo strtoupper($i['currency'])?> <?php echo number_format($i['total'] / 100, 2) ?></td>
				</tr>
			<?php elseif ($i['object'] == 'charge'): ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="3"><p style="text-align: center"><?php _e("No previous invoices", "wishlist-member") ?></p></td>
		</tr>
	<?php endif; ?>
</table>
<p style="text-align: right; font-size: 11px;"><a href="#" class="stripe-invoices-close"><?php _e("Close", "wishlist-member") ?></a></p>