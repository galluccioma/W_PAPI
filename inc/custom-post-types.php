<?php

///////////////////POST TYPE FORM SUBMISSION
function register_form_submission_cpt() {
    $labels = array(
        'name'               => 'Form Submissions',
        'singular_name'      => 'Form Submission',
        'menu_name'          => 'Form Submissions',
        'name_admin_bar'     => 'Form Submission',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Submission',
        'new_item'           => 'New Submission',
        'edit_item'          => 'Edit Submission',
        'view_item'          => 'View Submission',
        'all_items'          => 'All Submissions',
        'search_items'       => 'Search Submissions',
        'not_found'          => 'No submissions found.',
        'not_found_in_trash' => 'No submissions found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title'),
    );

    register_post_type('form_submission', $args);
}
add_action('init', 'register_form_submission_cpt');

// Registra i metadati personalizzati per il CPT "form_submission"
function register_form_submission_meta() {
    // Register meta fields
    register_post_meta('form_submission', '_firstname', [
        'type' => 'string',
        'description' => 'Nome del mittente',
        'single' => true,
        'show_in_rest' => true,
    ]);

    register_post_meta('form_submission', '_lastname', [
        'type' => 'string',
        'description' => 'Cognome del mittente',
        'single' => true,
        'show_in_rest' => true,
    ]);

    register_post_meta('form_submission', '_email', [
        'type' => 'string',
        'description' => 'Email del mittente',
        'single' => true,
        'show_in_rest' => true,
    ]);

    register_post_meta('form_submission', '_url', [
        'type' => 'string',
        'description' => 'URL del sito',
        'single' => true,
        'show_in_rest' => true,
    ]);

    register_post_meta('form_submission', '_message', [
        'type' => 'string',
        'description' => 'Messaggio del mittente',
        'single' => true,
        'show_in_rest' => true,
    ]);

    register_post_meta('form_submission', '_attachment_url', [
        'type' => 'string',
        'description' => 'URL del file allegato',
        'single' => true,
        'show_in_rest' => true,
    ]);

    register_post_meta('form_submission', '_provenance', [
        'type' => 'string',
        'description' => 'Provenienza del modulo',
        'single' => true,
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_form_submission_meta');

// Aggiungi il metabox per visualizzare i metadati nel backend
function add_form_submission_metabox() {
    add_meta_box(
        'form_submission_meta',         // ID del metabox
        'Form Submission Details',      // Titolo del metabox
        'display_form_submission_meta', // Funzione di callback per visualizzare i metadati
        'form_submission',              // Post type
        'normal',                       // Contesto del metabox (posizione)
        'high'                          // Priorità
    );
}
add_action('add_meta_boxes', 'add_form_submission_metabox');

// Funzione di callback per visualizzare i metadati nel metabox
function display_form_submission_meta($post) {
    // Recupera i metadati
    $firstname = get_post_meta($post->ID, '_firstname', true);
    $lastname = get_post_meta($post->ID, '_lastname', true);
    $email = get_post_meta($post->ID, '_email', true);
    $url = get_post_meta($post->ID, '_url', true);
    $message = get_post_meta($post->ID, '_message', true);
    $attachment_url = get_post_meta($post->ID, '_attachment_url', true);
    $provenance = get_post_meta($post->ID, '_provenance', true);

    // Visualizza i campi del metabox
    ?>
    <table class="form-table">
        <tr>
            <th><label for="firstname">First Name</label></th>
            <td><input type="text" id="firstname" name="firstname" value="<?php echo esc_attr($firstname); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="lastname">Last Name</label></th>
            <td><input type="text" id="lastname" name="lastname" value="<?php echo esc_attr($lastname); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="email">Email</label></th>
            <td><input type="email" id="email" name="email" value="<?php echo esc_attr($email); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="url">URL</label></th>
            <td><input type="text" id="url" name="url" value="<?php echo esc_attr($url); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="message">Message</label></th>
            <td><textarea id="message" name="message" class="widefat"><?php echo esc_textarea($message); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="attachment_url">Attachment URL</label></th>
            <td><input type="text" id="attachment_url" name="attachment_url" value="<?php echo esc_attr($attachment_url); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="provenance">Provenance</label></th>
            <td><input type="text" id="provenance" name="provenance" value="<?php echo esc_attr($provenance); ?>" class="widefat"></td>
        </tr>
    </table>
    <?php
}

// Salva i metadati personalizzati quando il post viene salvato
function save_form_submission_meta($post_id) {
    // Verifica che il post sia del tipo 'form_submission' e che non sia un salvataggio automatico
    if (get_post_type($post_id) !== 'form_submission' || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Salva i metadati
    if (isset($_POST['firstname'])) {
        update_post_meta($post_id, '_firstname', sanitize_text_field($_POST['firstname']));
    }
    if (isset($_POST['lastname'])) {
        update_post_meta($post_id, '_lastname', sanitize_text_field($_POST['lastname']));
    }
    if (isset($_POST['email'])) {
        update_post_meta($post_id, '_email', sanitize_email($_POST['email']));
    }
    if (isset($_POST['url'])) {
        update_post_meta($post_id, '_url', esc_url_raw($_POST['url']));
    }
    if (isset($_POST['message'])) {
        update_post_meta($post_id, '_message', sanitize_textarea_field($_POST['message']));
    }
    if (isset($_POST['attachment_url'])) {
        update_post_meta($post_id, '_attachment_url', esc_url_raw($_POST['attachment_url']));
    }
    if (isset($_POST['provenance'])) {
        update_post_meta($post_id, '_provenance', sanitize_text_field($_POST['provenance']));
    }
}
add_action('save_post', 'save_form_submission_meta');
