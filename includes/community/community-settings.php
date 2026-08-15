<?php
namespace OpenEvents;
if ( ! defined( 'ABSPATH' ) ) { exit; }

const COMMUNITY_SETTINGS_OPTION = 'open_events_community_settings';

/**
 * Un'unica option strutturata (array associativo) invece di una option per
 * ogni impostazione come nel resto del plugin: qui il documento di feature
 * lo richiede esplicitamente, per semplificare export/import di un intero
 * modulo a sé stante. get_option() + merge sui default cosi' un aggiornamento
 * del plugin che aggiunge nuove chiavi (es. Google/Facebook nelle fasi
 * successive) non lascia mai una chiave mancante da controllare a mano nel
 * resto del codice.
 */
function open_events_get_community_settings() {
	$defaults = [
		'registration_enabled'     => true,
		'fields'                   => [
			'first_name' => 'required',
			'last_name'  => 'optional',
			'city'       => 'optional',
		],
		'default_role'             => 'subscriber',
		'require_email_confirm'    => false,
		'redirect_page_id'         => 0,
		'login_page_id'            => 0,
		'forgot_password_page_id'  => 0,
		'google'                   => [
			'enabled'       => false,
			'client_id'     => '',
			'client_secret' => '',
		],
		'facebook'                 => [
			'enabled'   => false,
			'app_id'    => '',
			'app_secret'=> '',
		],
		'recaptcha'                => [
			'enabled'    => false,
			'site_key'   => '',
			'secret_key' => '',
			'threshold'  => 0.5,
		],
		'import_social_avatar'     => true,
		'emails'                   => [
			'sender_name'  => get_bloginfo( 'name' ),
			'sender_email' => get_option( 'admin_email' ),
			'registration'    => [
				'subject' => esc_html__( 'Benvenuto su {sito_nome}', 'open-events' ),
				'body'    => esc_html__( "Ciao {nome},\n\ngrazie per esserti registrato su {sito_nome}. Il tuo account è attivo, puoi accedere da qui: {login_url}", 'open-events' ),
			],
			'event_submitted' => [
				'subject' => esc_html__( 'Nuovo evento inserito: {evento_titolo}', 'open-events' ),
				'body'    => esc_html__( "Un nuovo evento è stato inserito ed è in attesa di revisione.\n\nTitolo: {evento_titolo}\nAutore: {autore_nome}\n\nRevisionalo qui: {evento_link}", 'open-events' ),
			],
			'event_published'  => [
				'subject' => esc_html__( 'Il tuo evento è stato pubblicato: {evento_titolo}', 'open-events' ),
				'body'    => esc_html__( "Ciao {autore_nome},\n\nil tuo evento \"{evento_titolo}\" è stato pubblicato ed è ora visibile online: {evento_link}", 'open-events' ),
			],
		],
	];

	$saved = get_option( COMMUNITY_SETTINGS_OPTION, [] );
	if ( ! is_array( $saved ) ) {
		$saved = [];
	}

	$merged = array_replace_recursive( $defaults, $saved );
	// I campi extra (nome/cognome/città) devono restare esattamente quelli
	// salvati anche se l'admin toglie un campo: array_replace_recursive su
	// 'fields' andrebbe bene solo se il set di chiavi non cambia mai, quindi
	// lo sovrascriviamo per intero quando presente per evitare valori fantasma.
	if ( isset( $saved['fields'] ) && is_array( $saved['fields'] ) ) {
		$merged['fields'] = $saved['fields'];
	}

	return $merged;
}

function open_events_update_community_settings( array $data ) {
	update_option( COMMUNITY_SETTINGS_OPTION, $data );
}

/**
 * Il checkbox "Importa la foto profilo" è stato aggiunto al form nella Fase
 * 2/3, DOPO che l'opzione era già salvabile (default vero) fin dalla Fase 1:
 * chi ha salvato la pagina Community prima che il checkbox esistesse si è
 * ritrovato l'opzione silenziosamente disattivata (checkbox assente nel
 * form inviato = non spuntato). Una tantum, al prossimo caricamento di
 * wp-admin: la riporta ad attiva una sola volta, poi non tocca più
 * un'eventuale disattivazione voluta in seguito dall'admin.
 */
function open_events_community_migrate_avatar_default() {
	if ( get_option( 'open_events_community_avatar_default_migrated' ) ) {
		return;
	}

	$settings = open_events_get_community_settings();
	$settings['import_social_avatar'] = true;
	open_events_update_community_settings( $settings );

	update_option( 'open_events_community_avatar_default_migrated', '1' );
}
add_action( 'admin_init', __NAMESPACE__ . '\\open_events_community_migrate_avatar_default' );

function open_events_register_community_admin_menu() {
	add_submenu_page(
		'open-events',
		esc_html__( 'Community', 'open-events' ),
		esc_html__( 'Community', 'open-events' ),
		'manage_options',
		'open-events-community',
		__NAMESPACE__ . '\\open_events_render_community_settings_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\\open_events_register_community_admin_menu', 20 );

function open_events_render_community_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// get_editable_roles() non è caricata di default su una pagina admin
	// qualunque, solo sulle schermate utenti native di WordPress.
	require_once ABSPATH . 'wp-admin/includes/user.php';

	$saved = false;

	if ( isset( $_POST['open_events_community_settings_submit'] ) && wp_verify_nonce( $_POST['open_events_community_settings_nonce'] ?? '', 'open_events_save_community_settings' ) ) {
		$field_names = [ 'first_name', 'last_name', 'city' ];
		$fields = [];
		foreach ( $field_names as $field_name ) {
			$mode = sanitize_text_field( wp_unslash( $_POST[ 'field_' . $field_name ] ?? 'optional' ) );
			$fields[ $field_name ] = in_array( $mode, [ 'required', 'optional', 'hidden' ], true ) ? $mode : 'optional';
		}

		$editable_roles = get_editable_roles();
		$default_role = sanitize_text_field( wp_unslash( $_POST['default_role'] ?? 'subscriber' ) );
		if ( ! isset( $editable_roles[ $default_role ] ) ) {
			$default_role = 'subscriber';
		}

		$data = [
			'registration_enabled'    => isset( $_POST['registration_enabled'] ),
			'fields'                  => $fields,
			'default_role'            => $default_role,
			'require_email_confirm'   => isset( $_POST['require_email_confirm'] ),
			'redirect_page_id'        => absint( wp_unslash( $_POST['redirect_page_id'] ?? 0 ) ),
			'login_page_id'           => absint( wp_unslash( $_POST['login_page_id'] ?? 0 ) ),
			'forgot_password_page_id' => absint( wp_unslash( $_POST['forgot_password_page_id'] ?? 0 ) ),
			'google'                  => [
				'enabled'       => isset( $_POST['google_enabled'] ),
				'client_id'     => sanitize_text_field( wp_unslash( $_POST['google_client_id'] ?? '' ) ),
				'client_secret' => sanitize_text_field( wp_unslash( $_POST['google_client_secret'] ?? '' ) ),
			],
			'facebook'                => [
				'enabled'    => isset( $_POST['facebook_enabled'] ),
				'app_id'     => sanitize_text_field( wp_unslash( $_POST['facebook_app_id'] ?? '' ) ),
				'app_secret' => sanitize_text_field( wp_unslash( $_POST['facebook_app_secret'] ?? '' ) ),
			],
			'recaptcha'               => [
				'enabled'    => isset( $_POST['recaptcha_enabled'] ),
				'site_key'   => sanitize_text_field( wp_unslash( $_POST['recaptcha_site_key'] ?? '' ) ),
				'secret_key' => sanitize_text_field( wp_unslash( $_POST['recaptcha_secret_key'] ?? '' ) ),
				'threshold'  => min( 1, max( 0, (float) ( $_POST['recaptcha_threshold'] ?? 0.5 ) ) ),
			],
			'import_social_avatar'    => isset( $_POST['import_social_avatar'] ),
			'emails'                  => [
				'sender_name'  => sanitize_text_field( wp_unslash( $_POST['email_sender_name'] ?? '' ) ),
				'sender_email' => sanitize_email( wp_unslash( $_POST['email_sender_email'] ?? '' ) ),
				'registration'     => [
					'subject' => sanitize_text_field( wp_unslash( $_POST['email_registration_subject'] ?? '' ) ),
					'body'    => sanitize_textarea_field( wp_unslash( $_POST['email_registration_body'] ?? '' ) ),
				],
				'event_submitted'  => [
					'subject' => sanitize_text_field( wp_unslash( $_POST['email_event_submitted_subject'] ?? '' ) ),
					'body'    => sanitize_textarea_field( wp_unslash( $_POST['email_event_submitted_body'] ?? '' ) ),
				],
				'event_published' => [
					'subject' => sanitize_text_field( wp_unslash( $_POST['email_event_published_subject'] ?? '' ) ),
					'body'    => sanitize_textarea_field( wp_unslash( $_POST['email_event_published_body'] ?? '' ) ),
				],
			],
		];

		open_events_update_community_settings( $data );
		$saved = true;
	}

	$settings = open_events_get_community_settings();
	$editable_roles = get_editable_roles();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Open Events - Community', 'open-events' ); ?></h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impostazioni salvate.', 'open-events' ); ?></p></div>
		<?php endif; ?>

		<form method="POST">
			<?php wp_nonce_field( 'open_events_save_community_settings', 'open_events_community_settings_nonce' ); ?>
			<input type="hidden" name="open_events_community_settings_submit" value="1">

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Registrazione classica', 'open-events' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="registration_enabled" value="1" <?php checked( $settings['registration_enabled'] ); ?>>
							<?php esc_html_e( 'Attiva', 'open-events' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Se disattivata, il widget Community Auth mostra solo il form di accesso.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Campi form registrazione', 'open-events' ); ?></th>
					<td>
						<?php
						$field_labels = [
							'first_name' => esc_html__( 'Nome', 'open-events' ),
							'last_name'  => esc_html__( 'Cognome', 'open-events' ),
							'city'       => esc_html__( 'Comune di riferimento', 'open-events' ),
						];
						foreach ( $field_labels as $field_name => $label ) :
							$current_mode = $settings['fields'][ $field_name ] ?? 'optional';
							?>
							<p style="margin: 0 0 10px;">
								<strong><?php echo esc_html( $label ); ?>:</strong>
								<label style="margin-left: 10px;"><input type="radio" name="field_<?php echo esc_attr( $field_name ); ?>" value="required" <?php checked( $current_mode, 'required' ); ?>> <?php esc_html_e( 'Obbligatorio', 'open-events' ); ?></label>
								<label style="margin-left: 10px;"><input type="radio" name="field_<?php echo esc_attr( $field_name ); ?>" value="optional" <?php checked( $current_mode, 'optional' ); ?>> <?php esc_html_e( 'Opzionale', 'open-events' ); ?></label>
								<label style="margin-left: 10px;"><input type="radio" name="field_<?php echo esc_attr( $field_name ); ?>" value="hidden" <?php checked( $current_mode, 'hidden' ); ?>> <?php esc_html_e( 'Nascosto', 'open-events' ); ?></label>
							</p>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_default_role"><?php esc_html_e( 'Ruolo assegnato ai nuovi utenti', 'open-events' ); ?></label></th>
					<td>
						<select name="default_role" id="oe_default_role">
							<?php foreach ( $editable_roles as $role_slug => $role ) : ?>
								<option value="<?php echo esc_attr( $role_slug ); ?>" <?php selected( $settings['default_role'], $role_slug ); ?>><?php echo esc_html( translate_user_role( $role['name'] ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Ruolo WordPress assegnato automaticamente a chi si registra tramite il widget Community, sia via form classico sia via social.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Conferma email', 'open-events' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="require_email_confirm" value="1" <?php checked( $settings['require_email_confirm'] ); ?>>
							<?php esc_html_e( 'Richiedi conferma email prima di attivare l\'account', 'open-events' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Non ancora implementato: l\'opzione è salvata ma al momento l\'account viene sempre creato e attivato subito, indipendentemente da questa spunta.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_redirect_page"><?php esc_html_e( 'Pagina di redirect dopo login/registrazione', 'open-events' ); ?></label></th>
					<td>
						<?php
						wp_dropdown_pages( [
							'name'              => 'redirect_page_id',
							'id'                => 'oe_redirect_page',
							'selected'          => $settings['redirect_page_id'],
							'show_option_none'  => esc_html__( '-- Home del sito --', 'open-events' ),
							'option_none_value' => 0,
						] );
						?>
						<p class="description"><?php esc_html_e( 'Tipicamente la pagina con il widget "Front-end Events Manager" (la dashboard utente).', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_login_page"><?php esc_html_e( 'Pagina Accedi/Registrati', 'open-events' ); ?></label></th>
					<td>
						<?php
						wp_dropdown_pages( [
							'name'              => 'login_page_id',
							'id'                => 'oe_login_page',
							'selected'          => $settings['login_page_id'],
							'show_option_none'  => esc_html__( '-- Nessuna (usa il login WordPress di default) --', 'open-events' ),
							'option_none_value' => 0,
						] );
						?>
						<p class="description"><?php esc_html_e( 'La pagina su cui hai messo il widget Elementor "Community Auth". wp-login.php e i link di accesso/registrazione del sito puntano qui.', 'open-events' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="oe_forgot_password_page"><?php esc_html_e( 'Pagina password dimenticata', 'open-events' ); ?></label></th>
					<td>
						<?php
						wp_dropdown_pages( [
							'name'              => 'forgot_password_page_id',
							'id'                => 'oe_forgot_password_page',
							'selected'          => $settings['forgot_password_page_id'],
							'show_option_none'  => esc_html__( '-- Nessuna (usa il recupero password WordPress di default) --', 'open-events' ),
							'option_none_value' => 0,
						] );
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Login Google', 'open-events' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="google_enabled" value="1" <?php checked( $settings['google']['enabled'] ); ?>>
							<?php esc_html_e( 'Attivo', 'open-events' ); ?>
						</label>
						<p>
							<label for="oe_google_client_id"><?php esc_html_e( 'Client ID', 'open-events' ); ?></label><br>
							<input type="text" id="oe_google_client_id" name="google_client_id" class="regular-text" value="<?php echo esc_attr( $settings['google']['client_id'] ); ?>">
						</p>
						<p>
							<label for="oe_google_client_secret"><?php esc_html_e( 'Client Secret', 'open-events' ); ?></label><br>
							<input type="password" id="oe_google_client_secret" name="google_client_secret" class="regular-text" value="<?php echo esc_attr( $settings['google']['client_secret'] ); ?>" autocomplete="off">
						</p>
						<p class="description">
							<?php esc_html_e( 'Crea un progetto su Google Cloud Console → Credenziali → ID client OAuth 2.0 (tipo "Applicazione web"), e imposta come "URI di reindirizzamento autorizzati" esattamente questo indirizzo:', 'open-events' ); ?><br>
							<code><?php echo esc_html( admin_url( 'admin-post.php?action=oe_community_google_callback' ) ); ?></code>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Login Facebook', 'open-events' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="facebook_enabled" value="1" <?php checked( $settings['facebook']['enabled'] ); ?>>
							<?php esc_html_e( 'Attivo', 'open-events' ); ?>
						</label>
						<p>
							<label for="oe_facebook_app_id"><?php esc_html_e( 'App ID', 'open-events' ); ?></label><br>
							<input type="text" id="oe_facebook_app_id" name="facebook_app_id" class="regular-text" value="<?php echo esc_attr( $settings['facebook']['app_id'] ); ?>">
						</p>
						<p>
							<label for="oe_facebook_app_secret"><?php esc_html_e( 'App Secret', 'open-events' ); ?></label><br>
							<input type="password" id="oe_facebook_app_secret" name="facebook_app_secret" class="regular-text" value="<?php echo esc_attr( $settings['facebook']['app_secret'] ); ?>" autocomplete="off">
						</p>
						<p class="description">
							<?php esc_html_e( 'Crea un\'app su Meta for Developers (prodotto "Accesso Facebook"), e imposta come "URI di reindirizzamento OAuth validi" esattamente questo indirizzo:', 'open-events' ); ?><br>
							<code><?php echo esc_html( admin_url( 'admin-post.php?action=oe_community_facebook_callback' ) ); ?></code>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'reCAPTCHA v3 (anti-bot registrazione)', 'open-events' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="recaptcha_enabled" value="1" <?php checked( $settings['recaptcha']['enabled'] ); ?>>
							<?php esc_html_e( 'Attivo', 'open-events' ); ?>
						</label>
						<p>
							<label for="oe_recaptcha_site_key"><?php esc_html_e( 'Chiave sito', 'open-events' ); ?></label><br>
							<input type="text" id="oe_recaptcha_site_key" name="recaptcha_site_key" class="regular-text" value="<?php echo esc_attr( $settings['recaptcha']['site_key'] ); ?>">
						</p>
						<p>
							<label for="oe_recaptcha_secret_key"><?php esc_html_e( 'Chiave segreta', 'open-events' ); ?></label><br>
							<input type="password" id="oe_recaptcha_secret_key" name="recaptcha_secret_key" class="regular-text" value="<?php echo esc_attr( $settings['recaptcha']['secret_key'] ); ?>" autocomplete="off">
						</p>
						<p>
							<label for="oe_recaptcha_threshold"><?php esc_html_e( 'Soglia punteggio minimo (0-1)', 'open-events' ); ?></label><br>
							<input type="number" id="oe_recaptcha_threshold" name="recaptcha_threshold" step="0.1" min="0" max="1" value="<?php echo esc_attr( $settings['recaptcha']['threshold'] ); ?>">
						</p>
						<p class="description">
							<?php esc_html_e( 'Crea le chiavi su Google reCAPTCHA (tipo v3), dominio del sito. Sotto la soglia impostata la registrazione viene rifiutata come probabile bot; 0.5 è un buon punto di partenza.', 'open-events' ); ?><br>
							<a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">https://www.google.com/recaptcha/admin/create</a>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Foto profilo social', 'open-events' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="import_social_avatar" value="1" <?php checked( $settings['import_social_avatar'] ); ?>>
							<?php esc_html_e( 'Importa la foto profilo da Google/Facebook al primo accesso', 'open-events' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Mittente email', 'open-events' ); ?></th>
					<td>
						<p>
							<label for="oe_email_sender_name"><?php esc_html_e( 'Nome mittente', 'open-events' ); ?></label><br>
							<input type="text" id="oe_email_sender_name" name="email_sender_name" class="regular-text" value="<?php echo esc_attr( $settings['emails']['sender_name'] ); ?>">
						</p>
						<p>
							<label for="oe_email_sender_email"><?php esc_html_e( 'Indirizzo email mittente', 'open-events' ); ?></label><br>
							<input type="email" id="oe_email_sender_email" name="email_sender_email" class="regular-text" value="<?php echo esc_attr( $settings['emails']['sender_email'] ); ?>">
						</p>
					</td>
				</tr>
				<?php
				$email_templates = [
					'registration'    => esc_html__( 'Email di benvenuto (registrazione)', 'open-events' ),
					'event_submitted' => esc_html__( 'Email "nuovo evento in attesa di revisione" (all\'admin)', 'open-events' ),
					'event_published' => esc_html__( 'Email "il tuo evento è stato pubblicato" (all\'autore)', 'open-events' ),
				];
				foreach ( $email_templates as $template_key => $template_label ) :
					$template = $settings['emails'][ $template_key ];
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $template_label ); ?></th>
						<td>
							<p>
								<label for="oe_email_<?php echo esc_attr( $template_key ); ?>_subject"><?php esc_html_e( 'Oggetto', 'open-events' ); ?></label><br>
								<input type="text" id="oe_email_<?php echo esc_attr( $template_key ); ?>_subject" name="email_<?php echo esc_attr( $template_key ); ?>_subject" class="large-text" value="<?php echo esc_attr( $template['subject'] ); ?>">
							</p>
							<p>
								<label for="oe_email_<?php echo esc_attr( $template_key ); ?>_body"><?php esc_html_e( 'Testo', 'open-events' ); ?></label><br>
								<textarea id="oe_email_<?php echo esc_attr( $template_key ); ?>_body" name="email_<?php echo esc_attr( $template_key ); ?>_body" rows="5" class="large-text"><?php echo esc_textarea( $template['body'] ); ?></textarea>
							</p>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Segnaposto email disponibili', 'open-events' ); ?></th>
					<td>
						<p class="description">
							<?php esc_html_e( 'Benvenuto: {nome}, {sito_nome}, {login_url}. Nuovo evento/Pubblicato: {evento_titolo}, {evento_link}, {autore_nome}.', 'open-events' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button( esc_html__( 'Salva Modifiche', 'open-events' ) ); ?>
		</form>
	</div>
	<?php
}
