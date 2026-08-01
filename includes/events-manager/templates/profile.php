<?php
namespace OpenEvents;
/**
 * Vista "Modifica Profilo". Incluso da Widget_Events_Manager::render() con
 * `include` (non `include_once`): condivide lo scope locale del metodo
 * chiamante, quindi $this e tutte le variabili preparate lì sopra
 * ($current_user, $profile_success, $profile_error, ecc.) sono già disponibili.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="em-form-container em-form-view">
    <?php
    $this->render_breadcrumbs( [
        [ 'label' => esc_html__( 'Dashboard', 'open-events' ), 'url' => remove_query_arg( [ 'view' ] ) ],
        [ 'label' => esc_html__( 'Profilo', 'open-events' ), 'url' => '' ],
    ] );
    ?>
    <div class="em-back-link">
        <a href="<?php echo esc_url( remove_query_arg( [ 'view' ] ) ); ?>">← <?php esc_html_e( 'Torna alla Dashboard', 'open-events' ); ?></a>
    </div>

    <h2><?php esc_html_e( 'Modifica Profilo', 'open-events' ); ?></h2>

    <?php if ( $profile_success ): ?>
        <div class="em-alert success"><?php echo esc_html( $profile_success ); ?></div>
    <?php endif; ?>
    <?php if ( $profile_error ): ?>
        <div class="em-alert error"><?php echo esc_html( $profile_error ); ?></div>
    <?php endif; ?>

    <form method="POST">
        <?php wp_nonce_field( 'profile_save', 'profile_nonce' ); ?>
        <input type="hidden" name="profile_submit" value="1">

        <div class="em-form-group">
            <label><?php esc_html_e( 'Username (non modificabile)', 'open-events' ); ?></label>
            <input type="text" value="<?php echo esc_attr( $current_user->user_login ); ?>" disabled style="background-color:#f0f2f5;">
        </div>

        <div class="em-form-group">
            <label><?php esc_html_e( 'Nome', 'open-events' ); ?></label>
            <input type="text" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>">
        </div>

        <div class="em-form-group">
            <label><?php esc_html_e( 'Cognome', 'open-events' ); ?></label>
            <input type="text" name="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>">
        </div>

        <div class="em-form-group">
            <label><?php esc_html_e( 'Email', 'open-events' ); ?></label>
            <input type="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
        </div>

        <div class="em-form-group">
            <label><?php esc_html_e( 'Sito Web', 'open-events' ); ?></label>
            <input type="url" name="website" value="<?php echo esc_attr( $current_user->user_url ); ?>">
        </div>

        <div class="em-form-group">
            <label><?php esc_html_e( 'Nuova Password (lascia vuoto per non cambiare)', 'open-events' ); ?></label>
            <input type="password" name="pass1" autocomplete="new-password">
        </div>

        <div class="em-form-group">
            <label><?php esc_html_e( 'Conferma Nuova Password', 'open-events' ); ?></label>
            <input type="password" name="pass2" autocomplete="new-password">
        </div>

        <button type="submit" class="em-submit-btn">
            <?php esc_html_e( 'Salva Profilo', 'open-events' ); ?>
        </button>
    </form>
</div>
