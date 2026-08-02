<?php
namespace OpenEvents;
/**
 * Form Accedi/Registrati con toggle via JS (vedi assets/community/community.js).
 * Incluso da Widget_Community_Auth::render() con `include`: condivide lo
 * scope locale del metodo chiamante ($this, $settings, $initial_tab,
 * $error_code, $redirect_to, $show_register_tab, $side_image_url,
 * $side_image_position, $is_editor_preview).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( is_user_logged_in() && ! $is_editor_preview ) {
	$current_user  = wp_get_current_user();
	$dashboard_url = $settings['redirect_page_id'] ? get_permalink( $settings['redirect_page_id'] ) : home_url( '/' );
	?>
	<div class="oe-community">
		<div class="oe-community-alert oe-community-alert-success" data-redirect-url="<?php echo esc_url( $dashboard_url ); ?>" data-redirect-seconds="5">
			<p>
				<?php printf( esc_html__( 'Hai già effettuato l\'accesso come %s.', 'open-events' ), esc_html( $current_user->display_name ) ); ?>
			</p>
			<p class="oe-community-redirect-note">
				<?php esc_html_e( 'Verrai reindirizzato alla dashboard tra', 'open-events' ); ?>
				<span class="oe-community-countdown">5</span>
				<?php esc_html_e( 'secondi…', 'open-events' ); ?>
				<a href="<?php echo esc_url( $dashboard_url ); ?>"><?php esc_html_e( 'Vai subito', 'open-events' ); ?></a>
				·
				<a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Esci', 'open-events' ); ?></a>
			</p>
		</div>
	</div>
	<?php
	return;
}

$error_messages = [
	'login_failed'         => esc_html__( 'Email/username o password errati.', 'open-events' ),
	'registration_disabled' => esc_html__( 'La registrazione non è al momento disponibile.', 'open-events' ),
	'missing_first_name'   => esc_html__( 'Il nome è obbligatorio.', 'open-events' ),
	'missing_last_name'    => esc_html__( 'Il cognome è obbligatorio.', 'open-events' ),
	'missing_city'         => esc_html__( 'Il comune di riferimento è obbligatorio.', 'open-events' ),
	'invalid_email'        => esc_html__( 'Indirizzo email non valido.', 'open-events' ),
	'email_exists'         => esc_html__( 'Esiste già un account con questa email. Prova ad accedere.', 'open-events' ),
	'weak_password'        => esc_html__( 'La password deve essere di almeno 8 caratteri.', 'open-events' ),
	'registration_failed'  => esc_html__( 'Registrazione non riuscita, riprova.', 'open-events' ),
	'oauth_not_configured'    => esc_html__( 'Accesso social non configurato correttamente.', 'open-events' ),
	'oauth_state_invalid'     => esc_html__( 'Sessione di accesso scaduta, riprova.', 'open-events' ),
	'oauth_token_exchange_failed' => esc_html__( 'Accesso non riuscito, riprova.', 'open-events' ),
	'oauth_userinfo_failed'   => esc_html__( 'Impossibile recuperare i dati del profilo, riprova.', 'open-events' ),
	'social_email_required'   => esc_html__( 'Il provider non ha condiviso un indirizzo email: impossibile creare l\'account.', 'open-events' ),
];
$error_text = $error_messages[ $error_code ] ?? '';
$fields = $settings['fields'];
$show_editor_notice = $is_editor_preview && is_user_logged_in();

$shell_classes = [ 'oe-community-shell' ];
if ( $side_image_url ) {
	$shell_classes[] = 'has-image';
	$shell_classes[] = 'image-' . $side_image_position;
}
?>
<?php if ( $show_editor_notice ) : ?>
	<p class="oe-community-editor-notice"><?php esc_html_e( 'Anteprima editor: i form vengono mostrati anche se sei loggato, solo qui in Elementor. Per un visitatore già loggato, questa pagina mostrerebbe invece il messaggio di redirect alla dashboard.', 'open-events' ); ?></p>
<?php endif; ?>
<div class="<?php echo esc_attr( implode( ' ', $shell_classes ) ); ?>">
	<?php if ( $side_image_url ) : ?>
		<div class="oe-community-image-side" style="background-image:url('<?php echo esc_url( $side_image_url ); ?>');"></div>
	<?php endif; ?>
<div class="oe-community" data-initial-tab="<?php echo esc_attr( $initial_tab ); ?>">
	<div class="oe-community-tabs" role="tablist">
		<button type="button" class="oe-community-tab" data-tab="accedi" role="tab"><?php esc_html_e( 'Accedi', 'open-events' ); ?></button>
		<?php if ( $show_register_tab ) : ?>
			<button type="button" class="oe-community-tab" data-tab="registrati" role="tab"><?php esc_html_e( 'Registrati', 'open-events' ); ?></button>
		<?php endif; ?>
	</div>

	<?php if ( $error_text ) : ?>
		<div class="oe-community-alert oe-community-alert-error"><?php echo esc_html( $error_text ); ?></div>
	<?php endif; ?>

	<div class="oe-community-panel" data-panel="accedi">
		<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="oe_community_login">
			<?php wp_nonce_field( 'oe_community_login', 'oe_community_nonce' ); ?>
			<?php if ( $redirect_to ) : ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
			<?php endif; ?>

			<div class="oe-community-field">
				<label for="oe_login_user"><?php esc_html_e( 'Email', 'open-events' ); ?></label>
				<input type="text" id="oe_login_user" name="user_login" required autocomplete="username">
			</div>
			<div class="oe-community-field">
				<label for="oe_login_pass"><?php esc_html_e( 'Password', 'open-events' ); ?></label>
				<input type="password" id="oe_login_pass" name="user_pass" required autocomplete="current-password">
			</div>
			<div class="oe-community-field oe-community-field-inline">
				<label><input type="checkbox" name="remember" value="1"> <?php esc_html_e( 'Ricordami', 'open-events' ); ?></label>
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="oe-community-link"><?php esc_html_e( 'Password dimenticata?', 'open-events' ); ?></a>
			</div>

			<button type="submit" class="oe-community-submit"><?php esc_html_e( 'Accedi', 'open-events' ); ?></button>

			<?php if ( ! empty( $settings['google']['enabled'] ) || ! empty( $settings['facebook']['enabled'] ) ) : ?>
				<div class="oe-community-social">
					<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=oe_community_google_start' ) ); ?>" class="oe-community-social-btn oe-community-google<?php echo empty( $settings['google']['enabled'] ) ? ' oe-hidden' : ''; ?>"><?php $this->render_social_icon( 'google' ); ?> <?php esc_html_e( 'Accedi con Google', 'open-events' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=oe_community_facebook_start' ) ); ?>" class="oe-community-social-btn oe-community-facebook<?php echo empty( $settings['facebook']['enabled'] ) ? ' oe-hidden' : ''; ?>"><?php $this->render_social_icon( 'facebook' ); ?> <?php esc_html_e( 'Accedi con Facebook', 'open-events' ); ?></a>
				</div>
			<?php endif; ?>
		</form>
	</div>

	<?php if ( $show_register_tab ) : ?>
		<div class="oe-community-panel" data-panel="registrati">
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="oe_community_register">
				<input type="hidden" name="oe_community_action" value="register">
				<?php wp_nonce_field( 'oe_community_register', 'oe_community_nonce' ); ?>

				<?php if ( 'hidden' !== ( $fields['first_name'] ?? 'optional' ) ) : ?>
					<div class="oe-community-field">
						<label for="oe_reg_first_name"><?php esc_html_e( 'Nome', 'open-events' ); ?><?php echo 'required' === $fields['first_name'] ? ' *' : ''; ?></label>
						<input type="text" id="oe_reg_first_name" name="first_name" <?php echo 'required' === $fields['first_name'] ? 'required' : ''; ?>>
					</div>
				<?php endif; ?>

				<?php if ( 'hidden' !== ( $fields['last_name'] ?? 'optional' ) ) : ?>
					<div class="oe-community-field">
						<label for="oe_reg_last_name"><?php esc_html_e( 'Cognome', 'open-events' ); ?><?php echo 'required' === $fields['last_name'] ? ' *' : ''; ?></label>
						<input type="text" id="oe_reg_last_name" name="last_name" <?php echo 'required' === $fields['last_name'] ? 'required' : ''; ?>>
					</div>
				<?php endif; ?>

				<?php if ( 'hidden' !== ( $fields['city'] ?? 'optional' ) ) : ?>
					<div class="oe-community-field">
						<label for="oe_reg_city"><?php esc_html_e( 'Comune di riferimento', 'open-events' ); ?><?php echo 'required' === $fields['city'] ? ' *' : ''; ?></label>
						<?php $this->render_city_field( 'city', '' ); ?>
					</div>
				<?php endif; ?>

				<div class="oe-community-field">
					<label for="oe_reg_email"><?php esc_html_e( 'Email', 'open-events' ); ?> *</label>
					<input type="email" id="oe_reg_email" name="user_email" required autocomplete="email">
				</div>
				<div class="oe-community-field">
					<label for="oe_reg_pass"><?php esc_html_e( 'Password', 'open-events' ); ?> *</label>
					<input type="password" id="oe_reg_pass" name="user_pass" required minlength="8" autocomplete="new-password">
				</div>

				<button type="submit" class="oe-community-submit"><?php esc_html_e( 'Registrati', 'open-events' ); ?></button>

				<?php if ( ! empty( $settings['google']['enabled'] ) || ! empty( $settings['facebook']['enabled'] ) ) : ?>
					<div class="oe-community-social">
						<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=oe_community_google_start' ) ); ?>" class="oe-community-social-btn oe-community-google<?php echo empty( $settings['google']['enabled'] ) ? ' oe-hidden' : ''; ?>"><?php $this->render_social_icon( 'google' ); ?> <?php esc_html_e( 'Registrati con Google', 'open-events' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=oe_community_facebook_start' ) ); ?>" class="oe-community-social-btn oe-community-facebook<?php echo empty( $settings['facebook']['enabled'] ) ? ' oe-hidden' : ''; ?>"><?php $this->render_social_icon( 'facebook' ); ?> <?php esc_html_e( 'Registrati con Facebook', 'open-events' ); ?></a>
					</div>
				<?php endif; ?>
			</form>
		</div>
	<?php endif; ?>
</div>
</div>
