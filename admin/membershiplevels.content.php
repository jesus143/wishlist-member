<?php
$custom_post_types = get_post_types( array( '_builtin' => false ), 'object' );
$wpm_levels        = $this->GetOption( 'wpm_levels' );
$sub_two           = in_array( wlm_arrval( $_GET, 'show' ), array( 'files', 'categories', 'folders' ) ) ? '' : ' with-sub-two ';
?>
<?php if ( isset($show_page_menu) && $show_page_menu ) : ?>
    <ul class="wlm-sub-menu <?php echo $sub_two; ?>">
        <?php if ( $this->access_control->current_user_can( 'wishlistmember_managecontent_posts' ) ): ?>
            <li<?php echo ( ! wlm_arrval( $_GET, 'show' ) ) ? ' class="current has-sub-menu"' : '' ?>><a href="?<?php echo $this->QueryString( 'show', 'offset', 'paged', 's', 's_status', 's_level' ) ?>"><?php _e( 'Posts', 'wishlist-member' ); ?></a></li>
        <?php endif; ?>

        <?php if ( $this->access_control->current_user_can( 'wishlistmember_managecontent_pages' ) ): ?>
            <li<?php echo ( wlm_arrval( $_GET, 'show' ) == 'pages' ) ? ' class="current has-sub-menu"' : '' ?>><a href="?<?php  echo $this->QueryString( 'show', 'offset', 'paged', 's', 's_status' ) ?>&show=pages"><?php _e( 'Pages', 'wishlist-member' ); ?></a></li>
        <?php endif; ?>

        <?php if ( count( $custom_post_types ) ): ?>
            <?php foreach ( $custom_post_types as $custom_post_type ): 
                    // Don't show BBPress Topics or Replies
                    // if($custom_post_type->description == 'bbPress Topics' || $custom_post_type->description == 'bbPress Replies') 
                    //     continue;
            ?>
                <?php if ( $this->PostTypeEnabled( $custom_post_type->name ) ) : ?>
                <li<?php echo ( wlm_arrval( $_GET, 'show' ) == $custom_post_type->name ) ? ' class="current has-sub-menu"' : '' ?>><a href="?<?php echo $this->QueryString( 'show', 'offset', 'paged', 's', 's_status' ) ?>&show=<?php echo $custom_post_type->name; ?>"><?php echo $custom_post_type->labels->name; ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ( $this->access_control->current_user_can( 'wishlistmember_managecontent_categories' ) ): ?>
            <li<?php echo ( wlm_arrval( $_GET, 'show' ) == 'categories' ) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString( 'show', 'offset', 'paged', 's', 's_status', 's_level' ) ?>&show=categories"><?php _e( 'Categories', 'wishlist-member' ); ?></a></li>
        <?php endif; ?>

        <?php if ( $this->access_control->current_user_can( 'wishlistmember_managecontent_files' ) ): ?>
            <li<?php echo ( wlm_arrval( $_GET, 'show' ) == 'files' ) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString( 'show', 'offset', 'paged', 's', 's_status', 's_level' ) ?>&show=files"><?php _e( 'Attachments', 'wishlist-member' ); ?></a></li>
        <?php endif; ?>

        <?php if ( $this->access_control->current_user_can( 'wishlistmember_managecontent_folders' ) ): ?>
            <li<?php echo ( wlm_arrval( $_GET, 'show' ) == 'folders' ) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString( 'show', 'offset', 'paged', 's', 's_status', 's_level' ) ?>&show=folders"><?php _e( 'Folders', 'wishlist-member' ); ?></a></li>
        <?php endif; ?>
        <li><?php echo $this->Tooltip( "membershiplevels-content-tooltips-Manage-Specific-Membership-Content" ); ?></li>
    </ul>
    <?php

if ( $sub_two ) :
?>
<ul class="wlm-sub-menu sub-sub">
    <li<?php echo ( ! wlm_arrval( $_GET, 'manage_comments' )  ) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString( 'manage_comments' ) ?>"><?php _e( 'Content', 'wishlist-member' ); ?></a></li>
    <li<?php echo ( wlm_arrval( $_GET, 'manage_comments' ) ) ? ' class="current"' : '' ?>><a href="?<?php echo $this->QueryString( 'manage_comments' ) ?>&manage_comments=1"><?php _e( 'Comments', 'wishlist-member' ); ?></a></li>
</ul>
<?php
endif;


return;
endif;
?>
<h2>
    <?php _e( 'Manage Content', 'wishlist-member' ); ?>
    <?php
$show_name = isset($_GET['show']) ? $_GET['show'] : 'posts';
if ( isset($custom_post_types[$show_name]) ) {
	$show_name = $custom_post_types[$show_name]->labels->name;
}
$show_name = ucwords( strtolower( $show_name ) );

echo ' &raquo; ' . $show_name;
?>
</h2>
<?php
/*
 * Membership Levels -> Membership Content
 */

$show     = isset($_GET['show']) ? $_GET['show']: '';
$contents = array_merge( array( 'pages', 'categories', 'comments', 'posts', 'files', 'folders1', 'folders' ), array_keys( $custom_post_types ) );
if ( ! in_array( $show, $contents ) || ( (isset($cprotect) && $cprotect) && $show == 'comments' ) ) {
	$show = 'posts';
	unset( $_GET['show'] );
}

require $this->pluginDir . '/resources/tables/class-manage-content-table.php';
$content_table = new WLM_Manage_Content_Table;
$content_table->prepare_items();
if(!in_array($show, array('categories','folders'))) {
	echo '<form method="get">';
	echo '<input type="hidden" name="paged" value="0">';
	foreach($_GET AS $k=>$v) {
		if(!in_array($k, array('s', 's_status', 's_level', 'paged'))) {
			printf('<input type="hidden" name="%s" value="%s">', $k, $v);
		}
	}
	$content_table->search_box('Filter', 'content-search');
	echo '</form>';
}
$content_table->display();
$wlm_levels    = $this->GetOption( 'wpm_levels' );
foreach ( $wlm_levels as $key => &$level ) {
	$level = array(
		'id'   => $key,
		'text' => $level['name'],
	);
}
unset($level);

include_once $this->pluginDir . '/admin/tooltips/membershiplevels.content.tooltips.php';

switch ( $_GET['show'] ) {
case 'pages':
	$content_type = 'page';
	break;
case 'posts':
	$content_type = 'post';
	break;
case 'files':
	$content_type = 'attachment';
	break;
default:
	$content_type = $_GET['show'];
}

if ( empty( $content_type ) ) {
	$content_type = 'post';
}
?>

<?php 
if ( $show == 'folders' ) : 
$parentFolder = trim( $this->GetOption( 'parentFolder' ) );
?>
<blockquote>
    <h2 style="font-size:18px;">
        Parent Folder
    </h2>
    <p>The path to the parent folder</p>
    <form method="post" onsubmit="return wlm_confirm_change_parent_folder(this)">
        <input type="hidden" name="WishListMemberAction" value="FolderProtectionParentFolder">
        <div>
            <code><span><?php echo ABSPATH; ?></span><span class="parent_folder_noedit"><strong><?php echo $parentFolder; ?></strong></span></code><input class="parent_folder_edit" type="text" name="parentFolder" value="<?php echo $parentFolder; ?>" data-original="<?php echo $parentFolder; ?>">
            &nbsp;
            <span class="parent_folder_noedit">(<a href="#" onclick="return wlm_parent_folder_edit()">change</a>)</span>
            <span class="parent_folder_edit">
                <input type="button" class="button" onclick="return wlm_parent_folder_edit()" value="Cancel">
                <input type="submit" class="button button-primary" value="Save">
            </span>
        </div>
    </form>

    <h2 style="font-size:18px;">
        <?php
            $button_name = $parentFolder ? __( 'Reset and Auto-Configure', 'wishlist-member' ) : __( 'Auto-Configure', 'wishlist-member' );
            echo $button_name;
        ?>
    </h2>

    <form method="post" onsubmit="return wlm_confirm_autoconfigure(this);">
        <input type="hidden" name="WishListMemberAction" value="EasyFolderProtection">
        <p>Clicking the button below will perform the following actions:</p>
        <ol class="wlm-folder-autoconfig-actions">
            <li>Un-protect all folders being protected by WishList Member</li>
            <li>Create a folder at <code><?php echo ABSPATH; ?><strong>files</strong></code> if it does not exist</li>
            <li>Create a sub-folder for each membership level if necessary and protect them accordingly</li>
        </ol>
        <input type="submit" class="button button-primary" value="<?php echo $button_name; ?>">
    </form>
</blockquote>
<?php
endif;

include_once('membershiplevels.content.pppmodal.php');
include_once('membershiplevels.content.bulkextras.php');

if(wlm_arrval($_GET,'show') == 'folders')
   include_once('membershiplevels.content.foldermodal.php');

$user_level = '';
if (preg_match('/^U-(\d+)/', wlm_arrval($_GET, 's_level'), $match)) {
	$user_level = get_user_by( 'id', $match[1] );
	if($user_level) {
		$user_level = sprintf('%s (%s)', $user_level->display_name, $user_level->user_email);
	} else {
		$user_level = '';
	}
}
?>

<script>
    jQuery(function($) {
        wlm_levels = <?php echo json_encode( array_values( $wlm_levels ) ); ?>;
        wlm_content_type = '<?php echo $content_type; ?>';
        wlm_manage_comments = <?php echo isset( $_GET['manage_comments'] ) ? 'true' : 'false'; ?>;
        wlm_no_inherit = <?php echo $content_type == 'folders' ? 'true' : 'false'; ?>;
        if('' === '<?php echo $parentFolder; ?>') {
            wlm_parent_folder_edit();
        }
        wlm_s_perpage = <?php echo (int) $this->GetOption('content-tab-perpage'); ?>;
        wlm_s_status = '<?php echo wlm_arrval($_GET, 's_status') . ''; ?>';
        wlm_s_level = '<?php echo wlm_arrval($_GET, 's_level') . ''; ?>';

        $('p.search-box input[name=s]').css('width', '180px').attr('placeholder', 'Search Text');

        $('p.search-box').prepend('<select class="select2" name="s_perpage" style="width:140px"></select>');
        $('select[name=s_perpage]')
        	.append('<option value="15">Show 15 / page</option>')
        	.append('<option value="30">Show 30 / page</option>')
        	.append('<option value="50">Show 50 / page</option>')
        	.append('<option value="100">Show 100 / page</option>')
        	.append('<option value="200">Show 200 / page</option>');
        $('select[name=s_perpage]').val(wlm_s_perpage).select2({width:'copy', minimumResultsForSearch: -1, closeOnSelect: false});

        $('p.search-box').prepend('<select class="select2" name="s_status"></select>&nbsp;');
        $('select[name=s_status]')
        	.append('<option value="">All Statuses</option>')
	        .append('<option value="publish">Published</option>')
	        .append('<option value="future">Scheduled</option>')
	        .append('<option value="draft">Draft</option>')
	        .append('<option value="pending">Pending</option>')
	        .append('<option value="private">Private</option>');
        $('select[name=s_status]').val(wlm_s_status).select2({width:'110px', minimumResultsForSearch: -1, closeOnSelect: false});

        $('p.search-box').prepend('<select class="select2" name="s_level"></select>&nbsp;');
        $('select[name=s_level]').append('<option value="">All Levels</option><optgroup label="Levels"></optgroup>');
        $.each(wlm_levels, function(idx, level){
        	$('select[name=s_level] optgroup').append('<option value="'+level.id+'">'+level.text+'</option>');
        });

        if('<?php echo $user_level; ?>' != '') {
	        if(!wlm_content_type.match(/(folders|categories|attachment)/i)) {
	        	$('select[name=s_level]').append('<optgroup label="Pay Per Post User"><option value="<?php echo wlm_arrval($_GET, 's_level'); ?>"><?php echo $user_level; ?></option></optgroup>');
	        }
        }
        // if(!wlm_content_type.match(/(folders|categories|attachment)/i)) {
        // 	$('select[name=s_level]').append('<optgroup label="Pay Per Post User"><option value="ppp">Choose User</option></optgroup>');
        // }
        // $('select[name=s_level]').change(function() {
        // 	if($(this).val() == 'ppp') {

      		// }
        // });

        $('select[name=s_level]').val(wlm_s_level).select2({width:'130px', dropdownCssClass: 'select2-nowrap', dropdownAutoWidth: true, minimumResultsForSearch: -1, closeOnSelect: false});

        $('p.search-box input[type=submit]').replaceWith('<button type="submit" class="button-secondary"><i class="icon icon-search"></i></button>');

    });
</script>
