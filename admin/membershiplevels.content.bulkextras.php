<!-- bulk action select action extras -->
<div style="display:none">
    <span id="select-actions-extras">
        <select name="protection" class="protection select-actions-extras" data-placeholder="Select Protection Status" data-error="Please select a Protection Status" style="width:250px">
            <option></option>
            <option value="Unprotected">Unprotected</option>
            <option value="Protected">Protected</option>
            <?php if($content_type != 'folders') : ?>
                <option value="Inherited">Inherited</option>
            <?php endif; ?>
        </select>
        <select name="wlm_levels[]" class="add_levels remove_levels select-actions-extras" multiple="multiple" style="width:250px" data-placeholder="Select Membership Level(s)" data-error="Please select at least one Membership Level">
            <?php
                foreach($wlm_levels AS $level) {
                    printf('<option value="%d">%s</option>', $level['id'], $level['text']);
                }
            ?>
        </select>
        <select name="payperpost" class="ppp select-actions-extras" data-placeholder="Select Pay Per Post Status" style="width:250px" data-error="Please select a Pay Per Post Status">
            <option></option>
            <option>Disabled</option>
            <option>Paid</option>
            <option>Free</option>
        </select>
        <select name="force_download" class="force_download select-actions-extras" data-placeholder="Select Force Download Status" style="width:250px" data-error="Please select a Force Download Status">
            <option></option>
            <option value="Yes">Enabled</option>
            <option value="No">Disabled</option>
        </select>
    </span>
</div>
