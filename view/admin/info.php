<?php KalturaHelpers::protectView( $this ); ?>
<?php
$switchAccountLink = admin_url( 'options-general.php?page=kaltura_options&partner_login=true&switch=true' );
if (is_network_admin()) {
	$switchAccountLink = admin_url( 'network/settings.php?page=all-in-one-video-pack-mu-settings&partner_login=true' );
}

if( isset( $_GET[ 'tab' ] ) ) {
	$active_tab = $_GET[ 'tab' ];
} else {
	$active_tab = 'sys';
}

//sys
$sys_hidden = '';
$option_sys_root_category = KalturaHelpers::getOption( 'kaltura_root_category' );
$option_sys_default_player_type = KalturaHelpers::getOption( 'kaltura_default_player_type' );
$option_sys_player_dimension = KalturaHelpers::getPlayerDimension();
$option_sys_show_media_from = KalturaHelpers::getOption( 'kaltura_show_media_from' );
//sys hidden input
$sys_hidden .= '<input class="hidden" type="hidden" name="root_category" id="root_category" value="'.$option_sys_root_category.'"/>';
$sys_hidden .= '<input class="hidden" type="hidden" name="default_player_type" id="default_player_type" value="'.$option_sys_default_player_type.'"/>';
$sys_hidden .= '<input class="hidden" type="hidden" name="default_player_dimensions" id="default_player_dimensions" value="'.$option_sys_player_dimension.'"/>';
$sys_hidden .= '<input class="hidden" type="hidden" name="show_media_from" id="show_media_from_'.$option_sys_show_media_from.'" value="'.$option_sys_show_media_from.'"/>';
$sys_hidden .= (strlen( checked($this->playlistEmbedAllowed, true, false)) > 1 ) ? '<input class="hidden" type="hidden" name="allow_embed_playlist" id="allow_embed_playlist" value="on"/>' : '';

//adv
$adv_hidden = '';
$option_adv_user_identifier = KalturaHelpers::getOption( 'kaltura_user_identifier', 'user_login' );
$option_adv_enable_kcw = KalturaHelpers::getOption( 'kaltura_enable_kcw', false );
$option_adv_kcw_ui_conf_id_admin = KalturaHelpers::getOption( 'kcw_ui_conf_id_admin' );
$option_adv_default_kcw_type = KalturaHelpers::getOption( 'kaltura_default_kcw_type', $option_adv_kcw_ui_conf_id_admin);
//adv hidden input
$adv_hidden .= '<input class="hidden" type="hidden" name="kaltura_user_identifier" id="kaltura_user_identifier_'.$option_adv_user_identifier.'" value="'.$option_adv_user_identifier.'"/>';
$adv_hidden .= (strlen(checked( $option_adv_enable_kcw, true, false ) ) > 1 ) ? '<input class="hidden" type="hidden" name="enable_kcw" id="enable_kcw" value="on"/>' : '';
$adv_hidden .= (strlen(checked( $option_adv_default_kcw_type, true, false ) ) > 1 ) ? '<input class="hidden" type="hidden" name="default_kcw_type" id="default_kcw_type" value="on"/>' : '';
foreach ( $this->players as $player ) {
	$adv_hidden .= (strlen( checked( isset($this->allowedPlayers[$player->id]), true, false ) ) > 1) ? '<input class="hidden" type="hidden" name="allowed_players[]" value="'.esc_attr( $player->id ).'"/>' : '';
}
foreach ( $this->playlistPlayers as $playlistPlayer ) {
	$adv_hidden .= (strlen( checked( isset($this->allowedPlaylistPlayers[$playlistPlayer->id]), true, false ) ) > 1 ) ? '<input class="hidden" type="hidden" name="allowed_playlist_players[]" value="'.esc_attr( $playlistPlayer->id ).'"/>' : '';
}

//kmc
$kmc_hidden = '';
$option_server_url = KalturaHelpers::getOption('kaltura_server_url');
$option_cdn_url = KalturaHelpers::getOption('kaltura_cdn_url');
//kmc hidden input
$kmc_hidden .= '';
$kmc_hidden .= '<input class="hidden" type="hidden" name="server_url" id="server_url" value="'.$option_server_url.'"/>';
$kmc_hidden .= '<input class="hidden" type="hidden" name="cdn_url" id="cdn_url" value="'.$option_cdn_url.'"/>';

$secret = KalturaHelpers::getOption('kaltura_secret');
$asecret = KalturaHelpers::getOption('kaltura_admin_secret');

?>
<?php if ( $this->error || empty($secret) || empty($asecret)): ?>
	<div class="wrap">
		<h2>Kaltura Video Settings</h2>
		<br />

		<div id="message" class="updated">
			<p>
				<strong>Failed to verify partner details</strong> (<?php echo esc_html( $this->error ); ?>)
			</p></div>
		<form name="form1" method="post" >

		<p class="submit" style="text-align: left; ">
			<input type="button" value="Click here to edit partner details manually" onclick="window.location = '<?php echo is_network_admin() ? 'settings.php?page=all-in-one-video-pack-mu-settings' : esc_url( admin_url( 'options-general.php?page=kaltura_options&partner_login=true' ) ); ?>';" />
		</p>
		<input type="hidden" id="manual_edit" name="manual_edit" value="true" />
		</form>
	</div>
<?php else : ?>
	<div class="wrap">
		<?php if ( $this->showMessage ): ?>
			<div id="message" class="updated">
				<p>
					<strong>The Kaltura Video settings have been saved.</strong>
				</p>
			</div>
		<?php endif; ?>
		<?php if ( $this->isNetworkActive ): ?>
			<div class="notice notice-warning">
				<p>
					<strong>
						<?php echo __( 'Note: The Kaltura Video plugin settings are controlled by the network admin. You may view the settings, but not update them.' ); ?>
					</strong>
				</p>
			</div>
		<?php endif; ?>
		<h1>Kaltura Video Settings</h1>
		<hr />

		<h2 class="nav-tab-wrapper">
			<a href="?page=<?php echo $_REQUEST['page']; ?>&tab=kmc" class="nav-tab  <?php echo $active_tab == 'kmc' ? 'nav-tab-active' : ''; ?>"><?php _e('Server'); ?></a>
			<a href="?page=<?php echo $_REQUEST['page']; ?>&tab=sys" class="nav-tab  <?php echo $active_tab == 'sys' ? 'nav-tab-active' : ''; ?>"><?php _e('System'); ?></a>
			<a href="?page=<?php echo $_REQUEST['page']; ?>&tab=adv" class="nav-tab  <?php echo $active_tab == 'adv' ? 'nav-tab-active' : ''; ?>"><?php _e('Advanced'); ?></a>
			<a href="?page=<?php echo $_REQUEST['page']; ?>&tab=inf" class="nav-tab  <?php echo $active_tab == 'inf' ? 'nav-tab-active' : ''; ?>"><?php _e('Info'); ?></a>
		
		</h2>

		<form name="form1" method="post">
			<?php wp_nonce_field( 'info', '_kalturanonce' ); ?>
			<br />

			<?php //Info Tab
				if ($active_tab == 'inf') {
					?>
						<table id="kaltura-cms-login">
							<tr class="kalturaFirstRow">
								<th align="left">Partner ID:</th>
								<td style="padding-right: 90px;">
									<strong><?php echo esc_html(KalturaHelpers::getOption( 'kaltura_partner_id' )); ?></strong></td>
							</tr>
							<?php if($this->showEmail): ?>
							<tr>
								<th align="left">KMC username:</th>
								<td style="padding-right: 90px;">
									<strong><?php echo esc_html(KalturaHelpers::getOption( 'kaltura_cms_user' )); ?></strong>
									<span>
									<?php if ( !$this->isNetworkActive ): ?>
										<a href="<?php echo $switchAccountLink; ?>">Change account</a>
									<?php endif; ?>
									</span>
								</td>
							</tr>
							<?php endif; ?>
							<tr class="kalturaLastRow">
								<td colspan="2" align="left" style="padding-top: 10px;padding-left:10px">
									<a href="<?php echo $option_server_url; ?>/index.php/kmc" target="_blank">Login</a> to the Kaltura Management Console (KMC) for advanced
									<br />media management<br />
									Learn more about the
									<a href="http://corp.kaltura.com/Products/Video-Applications/WordPress-Video-Plugin" target="_blank">new plugin features</a>
								</td>
							</tr>
						</table>
					<?php
				}
			?>

			<table>

				<?php
					if ($active_tab =='sys') { //System Tab
						echo $adv_hidden;
						echo $kmc_hidden;
						?>
							<tr valign="top">
								<td><label for="root_category">Root Category:</label></td>
								<td>
									<select name="root_category" id="root_category" size="1">
										<option id="root_category_default" value="0" <?php echo selected( $option_sys_root_category, 0 ); ?>>Root (default)</option>
										<?php foreach ( $this->categories->objects as $category ): ?>
											<option id="root_category<?php echo esc_attr( $category->id ); ?>" value="<?php echo esc_attr( $category->id ); ?>" <?php echo selected( $option_sys_root_category, esc_attr( $category->id ) ); ?>><?php echo esc_html( $category->fullName ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>

							<tr valign="top">
								<td><label for="default_player_type">Default player design:</label></td>
								<td>
									<select name="default_player_type" id="default_player_type">
										<?php foreach ( $this->players as $player ): ?>
											<option id="default_player_type_<?php echo esc_attr( $player->id ); ?>" value="<?php echo esc_attr( $player->id ); ?>" <?php echo selected( $option_sys_default_player_type, $player->id ); ?>><?php echo esc_html( $player->name ); ?></option>
										<?php endforeach; ?>
									</select>
									<br />
								</td>
							</tr>

							<tr valign="top">
								<td><label for="default_player_dimensions">Player Dimensions:</label></td>
								<td>
									<select name="default_player_dimensions" id="default_player_dimensions">
										<?php foreach ( $this->playerDimensionsList as $title => $playerDimension ): ?>
											<option id="default_player_dimension_<?php echo esc_attr( $playerDimension ); ?>" value="<?php echo esc_attr( $playerDimension ); ?>" <?php echo selected( $option_sys_player_dimension, $playerDimension); ?>><?php echo esc_html( $title ); ?></option>
										<?php endforeach; ?>
									</select>
									<br />
								</td>
							</tr>

							<tr valign="top">
								<td><label>Allow users to select existing media:</label></td>
								<td>
									<input type="radio" name="show_media_from" id="show_media_from_all_account" value="all_account" <?php echo checked( $option_sys_show_media_from === 'all_account' ); ?>/>
									<label for="show_media_from_all_account">All media in publisher account</label>
									<br />
									<input type="radio" name="show_media_from" id="show_media_from_logged_in_user" value="logged_in_user" <?php echo checked( $option_sys_show_media_from === 'logged_in_user' ); ?> />
									<label for="show_media_from_logged_in_user">Only the user’s own media</label>
								</td>
							</tr>

							<tr valign="top">
								<td><label>Allow users to embed their playlists:</label></td>
								<td>
									<input type="checkbox" name="allow_embed_playlist" id="allow-embed-playlist" <?php echo checked($this->playlistEmbedAllowed); ?> />
									<br />
								</td>
							</tr>
						<?php

					} 

					if ($active_tab == 'adv') { //Advanced Tab
						echo $sys_hidden;
						echo $kmc_hidden;
						?>
							<!--<tr>
								<td colspan="2"><a href="javascript:;" id="advanced-button">Enable Advanced settings</a>
								</td>
							</tr>-->

							<tr valign="top" class="advanced user_identifier">
								<td width="200">WordPress user identifier field to be used by Kaltura:</td>
								<td>
									<input type="radio" id="kaltura_user_identifier_user_login" name="kaltura_user_identifier" value="user_id" <?php echo checked( $option_adv_user_identifier == "user_id" ); ?> />
									<label for="kaltura_user_identifier_user_login">ID</label>
									<br />

									<div class="user_identifier_desc">
										This identifier was used in previous versions of Kaltura All in One WordPress plugin. Choose this option if you have upgraded from a previous version of Kaltura and want to keep the existing media content associated with the users that uploaded it.
									</div>

									<input type="radio" id="kaltura_user_identifier_user_id" name="kaltura_user_identifier" value="user_login" <?php echo checked( $option_adv_user_identifier == "user_login" ); ?> />
									<label for="kaltura_user_identifier_user_id">user_login</label>
									<br />

									<div class="user_identifier_desc">
										This identifier is a unique identifier across WordPress Multisite. Choose this option if this is a new installation of Kaltura All in one WordPress plugin.
									</div>
									<br />
									<br />
								</td>
							</tr>

							<tr valign="top" class="advanced">
								<td><label for="enable_kcw">Enable legacy flash uploader:</label></td>
								<td>
									<input type="checkbox" name="enable_kcw" id="enable_kcw" <?php echo checked( $option_adv_enable_kcw ); ?> />
									<br />
								</td>
							</tr>

							<tr valign="top" class="advanced">
								<td><label for="default_kcw_type">UICONF for Kaltura Contribution Wizard:</label></td>
								<td>
									<input name="default_kcw_type" id="default_kcw_type" value="<?php echo esc_attr( $option_adv_default_kcw_type ); ?>" />
									<br />
								</td>
							</tr>

							<tr valign="top" class="advanced available-players">
								<td><label>Allowed players:</label></td>
								<td>
									<div class="players-scroll">
									<?php foreach ( $this->players as $player ): ?>
										<label><input type="checkbox" class="radio" value="<?php echo esc_attr( $player->id ); ?>" <?php echo checked(isset($this->allowedPlayers[$player->id])); ?> name="allowed_players[]" /><?php echo esc_html( $player->name ); ?></label>
									<?php endforeach; ?>
									</div>
									<br />
								</td>
							</tr>

							<?php if ($this->playlistEmbedAllowed): ?>
								<tr valign="top" class="advanced available-players">
									<td><label>Allowed Playlist players:</label></td>
									<td>
										<div class="players-scroll">
											<?php foreach ( $this->playlistPlayers as $playlistPlayer ): ?>
												<label><input type="checkbox" class="radio" value="<?php echo esc_attr( $playlistPlayer->id ); ?>" <?php echo checked(isset($this->allowedPlaylistPlayers[$playlistPlayer->id])); ?> name="allowed_playlist_players[]" /><?php echo esc_html( $playlistPlayer->name ); ?></label>
											<?php endforeach; ?>
										</div>
										<br />
									</td>
								</tr>
							<?php endif; ?>
						<?php
					}

					if ($active_tab == 'kmc') { //Server Tab
						echo $sys_hidden;
						echo $adv_hidden;

						?>
							<tr valign="top">
								<td><label for="server_url">KMC Server URL:</label></td>
								<td>
									<input name="server_url" id="server_url" value="<?php echo esc_attr( $option_server_url ); ?>" size="70"/>
									<br />
								</td>
							</tr>

							<tr valign="top" >
								<td colspan="2"><label >You have to re-login to Kaltura Server if you change this URL!</label></td>
							</tr>

							<tr valign="top">
								<td><label for="cdn_url">KMC CDN URL:</label></td>
								<td>
									<input name="cdn_url" id="cdn_url" value="<?php echo esc_attr( $option_cdn_url ); ?>" size="70"/>
									<br />
								</td>
							</tr>


						<?php

					}

				?>

				<?php
					if ($active_tab !== 'inf' && !$this->isNetworkActive) {
						?>
							<tr>
								<td colspan="2">
									<br />
									<p class="submit" style="text-align: left; ">
										<input type="submit" name="update" value="Update" />
									</p>
								</td>
							</tr>
						<?php
					}
				?>

			</table>
			<br />
			<br />

		</form>

		<script type="text/javascript">
			<?php if($this->isNetworkActive): ?>
				jQuery( 'input:not(.hidden), select' ).attr( 'disabled', true );
				jQuery( '.kalturaLastRow' ).hide();
			<?php endif; ?>
			/* jQuery('#advanced-button').click(function () {
				jQuery(this).hide();
				jQuery('tr.advanced').show();
			}); */

			function setKcwState() {
				if (jQuery('#enable_kcw').prop('checked'))
					jQuery('#default_kcw_type').prop('disabled', false);
				else
					jQuery('#default_kcw_type').prop('disabled', true);
			}

			jQuery('#enable_kcw').change(setKcwState);
			setKcwState();

		</script>
	</div>
<?php endif;
