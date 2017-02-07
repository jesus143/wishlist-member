<?php

global $WishListMemberInstance;
$rootOfFolders = trim( $WishListMemberInstance->GetOption( 'rootOfFolders' ) );
$folder_protection_full_path = $WishListMemberInstance->folder_protection_full_path( $rootOfFolders );

if ( $rootOfFolders and is_dir( $folder_protection_full_path ) ) {
	foreach ( glob( $folder_protection_full_path . '/*', GLOB_ONLYDIR ) as $dir_name ) {
		$dir_name = basename( $dir_name );
		$fullpath = $folder_protection_full_path . '/' . $dir_name;
		if ( is_dir( $fullpath ) ) {
			$folder_id          = $WishListMemberInstance->FolderID( $dir_name );
?>
			<div class="media-modal wp-core-ui media-modal-large" style="display:none;" id="wlm-folder-box-<?php echo $folder_id; ?>">
			    <a class="media-modal-close" href="javascript:void(0)" title="Close"><span class="media-modal-icon"></span></a>
			    <div class="media-modal-content">
			        <div class="media-frame hide-menu hide-router wp-core-ui">
			            <div class="media-frame-title">
			            	<h3><?php _e('Files inside the folder ','wishlist-member'); ?> <?php echo $fullpath; ?></h3>
			            </div>
			            <div class="media-frame-router">
			                <div class="media-router">
			                    <a href="#ppp-post-title" id="ppp-post-title" class="media-menu-item active"></a>
			                </div>
			            </div>
			            <div class="media-frame-content">
			                <div style="padding:10px 16px">
			                	<?php 
									if ($handle = opendir($fullpath)) {

									    /* This is the correct way to loop over the directory. */
									    while (false !== ($entry = readdir($handle))) {
									    	if ($entry == '.' or $entry == '..') continue;
									        echo $entry;
									        echo '<br>';
									    }

									    closedir($handle);
									}
			                	?>
			                </div>
			            </div>
			          
			        </div>
			    </div>
			</div>

<?php
		}
	}
}
?>