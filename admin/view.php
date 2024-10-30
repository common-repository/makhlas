<?php 
    // In our file that handles the request, verify the nonce.
    if (!empty($_POST)) {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'makhlas_options') || !current_user_can('manage_options')) {
             echo '<div class="notice notice-error"><p>' . __("Failed security check.", "makhlas") . '</p></div>';
             exit;
        }
    }

	$Makhlas = new Makhlas_Plugin();
	$makhlas_domains = $Makhlas->makhlas_get_user_domain_names();

    if (isset($_POST['makhlas_api_key'])) {
        update_option('makhlas_api_key', trim(sanitize_text_field($_POST['makhlas_api_key'])));
    }

    if (isset($_POST['makhlas_default_domain'])) {
        update_option('makhlas_default_domain', trim(sanitize_text_field($_POST['makhlas_default_domain'])));
    }

    $makhlas_url = '<a href="http://makhlas.com" target="_blank">makhlas.com</a>';
    $selected_domain = get_option('makhlas_default_domain');
?>
<div class="wrap">
    <h1><?php _e('Makhlas', 'makhlas'); ?></h1>
	<form method="post">
		<?php wp_nonce_field('makhlas_options'); ?>
        <table class="form-table">
            <tbody>
            	<tr valign="top">
					<th scope="row">
						<label for="makhlas_api_key"><?php _e('Api Key', 'makhlas'); ?></label>
					</th>
					<td>
						<input class="regular-text" id="makhlas_api_key" type="text" name="makhlas_api_key" value="<?php echo get_option( 'makhlas_api_key' ); ?>"/>
						<p id="tagline-description" class="description">
							<?php printf(__( 'Sign up for an %s plan to get an API key.', 'makhlas'), $makhlas_url); ?>
						</p>
					</td>
				</tr>
                <tr>
                    <th scope="row">
                    	<label for="makhlas_default_domain"><?php _e('Default domain', 'makhlas'); ?></label>
                    </th>
                    <td>
                        <select id="makhlas_default_domain" name="makhlas_default_domain">
                        	<?php if(!empty($makhlas_domains)): ?>
                        		<?php foreach($makhlas_domains as $makhlas_domain): ?>
                        			<?php if($makhlas_domain['status'] == 'active'): ?>
                            			<option <?php selected($selected_domain, $makhlas_domain['domain']); ?> value="<?php echo $makhlas_domain['domain']; ?>"><?php echo $makhlas_domain['domain']; ?></option>
                        			<?php endif; ?>
                            	<?php endforeach; ?>
                        	<?php endif; ?>
                        </select>
                        <p id="tagline-description" class="description">
                        	<?php printf(__( 'You can set default domain in %s admin panel.', 'makhlas'), $makhlas_url); ?>
                        </p>
                    </td>
                </tr>
			</tbody>
		</table>
		<?php submit_button(); ?>
	</form>
</div>