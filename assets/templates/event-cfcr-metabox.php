<!-- assets/templates/event-cfcr-metabox.php -->
<style>
.civi_eo_event_cfcr_post_link p {
	font-size: 120%;
}

.cfcr-redirect-switcher-wrapper .cfcr-delete {
	color: #a00;
	text-decoration: underline;
	cursor: pointer;
}
</style>

<div class="civi_eo_event_option_block civi_eo_event_cfcr_block">
	<p >
		<?php _e( 'Registration Page Redirect', 'civicrm-event-organiser' ); ?><br />
	</p>

	<div class="civi_eo_event_cfcr_post_link">
		<p class="civi_eo_event_redirect_post_link">
			<?php echo $page; ?>
		</p>
	</div>

	<p class="cfcr-redirect-switcher-wrapper">
		<button type="button" class="button cfcr-redirect-switcher"><?php _e( 'Choose New', 'civicrm-event-organiser' ); ?></button> <span class="cfcr-delete"><?php _e( 'Delete', 'civicrm-event-organiser' ); ?></span>
	</p>

	<p>
		<label for="civi_eo_event_redirect_active"><?php _e( 'Is active?', 'civicrm-event-organiser' ); ?></label>
		<input type="checkbox" id="civi_eo_event_redirect_active" name="civi_eo_event_redirect_active" value="1"<?php echo $is_active; ?> />
	</p>

	<input type="hidden" id="civi_eo_event_redirect_post_id" name="civi_eo_event_redirect_post_id" value="<?php echo $post_id; ?>" />
	<input type="text" style="display: none !important;" id="cfcr-redirect-switcher-field">

	<p class="description">
		<?php _e( 'Redirect from an Event Registration Form to a Post or Page containing a Caldera Form.', 'civicrm-event-organiser' ); ?>
	</p>
</div>
