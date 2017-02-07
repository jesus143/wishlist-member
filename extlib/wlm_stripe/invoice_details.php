<h3><?php _e('Invoice Details', 'wishlist-member') ?></h3>
<table>
	<tr>
		<td><?php _e('Invoice ID', 'wishlist-member') ?></td>
		<td><?php echo $inv->id ?></td>
	</tr>
	<tr>
		<td><?php _e('Date', 'wishlist-member') ?></td>
		<td><?php echo date('M d Y', $inv->date) ?></td>
	</tr>
	<tr>
		<td><?php _e('Customer', 'wishlist-member') ?></td>
		<td><?php echo $cust->description ?></td>
	</tr>
</table>
<h3><?php _e('Summary', 'wishlist-member') ?></h3>
<table width="100%">
	<tr>
		<td width="50%"></td>
		<td><?php _e('Subtotal:', 'wishlist-member') ?> </td>
		<td><?php echo strtoupper($inv->currency)?> <strong><?php echo number_format($inv->subtotal / 100, 2); ?></strong></td>
	</tr>
	<tr>
		<td width="50%"></td>
		<td><?php _e('Total:', 'wishlist-member') ?> </td>
		<td><?php echo strtoupper($inv->currency)?> <strong><?php echo number_format($inv->total / 100, 2); ?></strong></td>
	</tr>
	<tr>
		<td width="50%"></td>
		<td><strong><?php _e('Amount Due:', 'wishlist-member') ?> </strong></td>
		<td><?php echo strtoupper($inv->currency)?> <strong><?php echo number_format($inv->total / 100, 2); ?></strong></td>
	</tr>
</table>
<h3><?php _e('Line Items', 'wishlist-member') ?></h3>
<table width="100%">
	<?php if ( isset($inv->lines->subscriptions ) && count( $inv->lines->subscriptions ) > 0 ) : ?>
		<?php foreach ($inv->lines->subscriptions as $s): ?>
			<tr>
				<td width="50%">
					<?php $plan = $s->plan ?>
					<?php echo strtoupper(($s->currency)) ?> <?php echo sprintf("%s (%s/%s)", $plan->name, number_format($plan->amount / 100, 2), $plan->interval) ?>
				</td>
				<td><?php echo sprintf("%s - %s", date("M d, Y", $s->period->start), date("M d, Y", $s->period->end)) ?></td>
				<td><?php echo strtoupper(($s->currency)) ?> <?php echo number_format($s->amount / 100, 2) ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( isset($inv->lines->invoiceitems ) && count( $inv->lines->invoiceitems ) > 0 ) : ?>
		<?php foreach ($inv->lines->invoiceitems as $s): ?>
			<tr>
				<td width="50%">
					<?php echo $s->description ?>
				</td>

				<td><?php echo date('M d, Y', $s->date) ?></td>
				<td><?php echo strtoupper(($s->currency))?> <?php echo number_format($s->amount / 100, 2) ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( isset($inv->lines->prorations ) && count( $inv->lines->prorations ) > 0 ) : ?>
		<?php foreach ($inv->lines->prorations as $s): ?>
			<tr>
				<td width="50%">
					<?php echo $s->description ?>
				</td>

				<td><?php echo date('M d, Y', $s->date) ?></td>
				<td><?php echo strtoupper(($s->currency))?> <?php echo number_format($s->amount / 100, 2) ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
</table>