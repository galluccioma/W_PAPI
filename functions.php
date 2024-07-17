<?php

//////////////////// Funzioni del tema Minimal Headless Theme
//Reindirizza gli utenti non registrati al login

function restrict_site_to_admins() {
    // Verifica se l'utente non è loggato o non è un amministratore
    if ( ! is_user_logged_in() || ! current_user_can('administrator') ) {
        // Reindirizza alla pagina di login
        auth_redirect();
    }
}
add_action('template_redirect', 'restrict_site_to_admins');


// Forza il no index del backend
function force_no_index() {
    echo '<meta name="robots" content="noindex, nofollow" />';
}
add_action('wp_head', 'force_no_index', 1);

// Abilita il supporto per le immagini in evidenza
add_theme_support('post-thumbnails');

////////// Disabilita tutte le classi dei blocchi Gutenberg
function remove_gutenberg_block_classes($content) {
    // Rimuove tutte le classi dagli elementi HTML
    $content = preg_replace('/\s+class="[^"]*"/', '', $content);
    $content = preg_replace("/\s+class='[^']*'/", '', $content);

    // Restituisce il contenuto senza le classi
    return $content;
}

// Applica la funzione al contenuto dei post
add_filter('the_content', 'remove_gutenberg_block_classes', 20);

////////// CSS Backend
function enqueue_custom_stylesheet() {
    // Registriamo e enqueuiamo il file CSS per il frontend
    wp_enqueue_style('custom-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_stylesheet');

// CSS per l'area di amministrazione
function enqueue_custom_admin_stylesheet() {
    // Registriamo e enqueuiamo il file CSS per l'admin
    wp_enqueue_style('custom-admin-style', get_template_directory_uri() . '/style.css');
}
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_stylesheet');

//////////// REGISTRAZIONE DEL CUSTOM POST TYPE "FORM SUBMISSION", NON VISUALIZZABILE NELL`API

function custom_register_form_submission_post_type() {
    $labels = array(
        'name'               => 'Form Submissions',
        'singular_name'      => 'Form Submission',
        'menu_name'          => 'Form Submissions',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Form Submission',
        'edit_item'          => 'Edit Form Submission',
        'new_item'           => 'New Form Submission',
        'view_item'          => 'View Form Submission',
        'view_items'         => 'View Form Submissions',
        'search_items'       => 'Search Form Submissions',
        'not_found'          => 'No Form Submissions found',
        'not_found_in_trash' => 'No Form Submissions found in Trash',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'form-submission'),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array('title'),
        'show_in_rest'        => false,  // Disabilita la visualizzazione nell'API REST
    );

    register_post_type('form_submission', $args);
}
add_action('init', 'custom_register_form_submission_post_type');

//////////// RIMUOVI I CAMPI PERSONALIZZATI DALL'API REST

function remove_custom_fields_from_rest_api($response, $post, $request) {
    if ($post->post_type === 'form_submission') {
        // Rimuovi i meta fields dal risultato dell'API REST
        $response->data['meta'] = array();
    }
    return $response;
}
add_filter('rest_prepare_form_submission', 'remove_custom_fields_from_rest_api', 10, 3);

//////////// AGGIUNTA DEI METABOX PERSONALIZZATI

function custom_add_meta_boxes() {
    add_meta_box(
        'form_submission_meta_box',
        'Form Submission Details',
        'custom_form_submission_meta_box_callback',
        'form_submission',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'custom_add_meta_boxes');

function custom_form_submission_meta_box_callback($post) {
    wp_nonce_field('save_form_submission_meta_box_data', 'form_submission_meta_box_nonce');

    $firstname = get_post_meta($post->ID, '_firstname', true);
    $lastname = get_post_meta($post->ID, '_lastname', true);
    $email = get_post_meta($post->ID, '_email', true);
    $url = get_post_meta($post->ID, '_url', true);
    $message = get_post_meta($post->ID, '_message', true);

    echo '<label for="firstname">Nome</label>';
    echo '<input type="text" id="firstname" name="firstname" value="' . esc_attr($firstname) . '" class="widefat" />';

    echo '<label for="lastname">Cognome</label>';
    echo '<input type="text" id="lastname" name="lastname" value="' . esc_attr($lastname) . '" class="widefat" />';

    echo '<label for="email">Email</label>';
    echo '<input type="email" id="email" name="email" value="' . esc_attr($email) . '" class="widefat" />';

    echo '<label for="url">URL del sito</label>';
    echo '<input type="text" id="url" name="url" value="' . esc_attr($url) . '" class="widefat" />';

    echo '<label for="message">Messaggio</label>';
    echo '<textarea id="message" name="message" class="widefat">' . esc_textarea($message) . '</textarea>';
}

function custom_save_form_submission_meta_box_data($post_id) {
    if (!isset($_POST['form_submission_meta_box_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['form_submission_meta_box_nonce'], 'save_form_submission_meta_box_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

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
}
add_action('save_post', 'custom_save_form_submission_meta_box_data');

//////////// VISUALIZZAZIONE DEI CAMPI PERSONALIZZATI NELLA LISTA DEGLI ARTICOLI

function custom_set_custom_edit_form_submission_columns($columns) {
    $columns['firstname'] = 'Nome';
    $columns['lastname'] = 'Cognome';
    $columns['email'] = 'Email';
    $columns['url'] = 'URL del sito';
    $columns['message'] = 'Messaggio';
    return $columns;
}
add_filter('manage_form_submission_posts_columns', 'custom_set_custom_edit_form_submission_columns');

function custom_custom_form_submission_column($column, $post_id) {
    switch ($column) {
        case 'firstname':
            echo esc_html(get_post_meta($post_id, '_firstname', true));
            break;
        case 'lastname':
            echo esc_html(get_post_meta($post_id, '_lastname', true));
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, '_email', true));
            break;
        case 'url':
            echo esc_url(get_post_meta($post_id, '_url', true));
            break;
        case 'message':
            echo esc_html(get_post_meta($post_id, '_message', true));
            break;
    }
}
add_action('manage_form_submission_posts_custom_column', 'custom_custom_form_submission_column', 10, 2);

///// INVIO MAIL AGLI ADMIN OGNI VOLTA CHE VIENE CREATO IL CUSTOM POST TYPE
add_action( 'transition_post_status', 'send_form_submission_email_to_admins', 10, 3 );

function send_form_submission_email_to_admins( $new_status, $old_status, $post ) {
    // Verifica se il post è del tipo 'form_submission' e il nuovo stato è 'publish'
    if ( $post->post_type === 'form_submission' && $new_status === 'publish' && $old_status !== 'publish' ) {
        // Recupera i meta field del post
        $firstname = get_post_meta( $post->ID, '_firstname', true );
        $lastname  = get_post_meta( $post->ID, '_lastname', true );
        $email     = get_post_meta( $post->ID, '_email', true );
        $url       = get_post_meta( $post->ID, '_url', true );
        $message   = get_post_meta( $post->ID, '_message', true );

        // Ottieni tutti gli amministratori del sito
        $admins = get_users( array( 'role__in' => array( 'administrator' ) ) );

        // Prepara l'array di destinatari
        $to = array();
        foreach ( $admins as $admin ) {
            $to[] = $admin->user_email;
        }

        // Prepara il soggetto e il corpo dell'email
        $subject = 'Nuova compilazione Form dal sito';
        
        $body   .= '<p>Puoi visualizzare il Form Submission qui: ' . get_permalink( $post->ID ) . '</p>';

        // Imposta gli headers per l'email
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        // Invia l'email agli amministratori
        wp_mail( $to, $subject, $body, $headers );
    }
}


//////////////////// FUNZIONI API

////////// Mostra lurl delle immagini nellAPI
function ws_register_images_field() {
    // Ottieni tutti i tipi di post personalizzati registrati
    $post_types = get_post_types(array('public' => true), 'names');

    // Loop attraverso tutti i tipi di post
    foreach ($post_types as $post_type) {
        // Registra il campo personalizzato 'images' per ciascun tipo di post
        register_rest_field(
            $post_type,
            'images',
            array(
                'get_callback'    => 'ws_get_images_urls',
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
}

add_action('rest_api_init', 'ws_register_images_field');

function ws_get_images_urls($object, $field_name, $request) {
    // Ottieni l'URL dell'immagine in dimensione 'medium'
    $medium = wp_get_attachment_image_src(get_post_thumbnail_id($object['id']), 'medium');
    $medium_url = $medium ? $medium[0] : '';

    // Ottieni l'URL dell'immagine in dimensione 'large'
    $large = wp_get_attachment_image_src(get_post_thumbnail_id($object['id']), 'large');
    $large_url = $large ? $large[0] : '';

    return array(
        'medium' => $medium_url,
        'large'  => $large_url,
    );
}

////////// AGGIUNGE IL VALORE DEI TAGS ALL API
add_action('rest_api_init', 'bs_rest_api_hooks');
function bs_rest_api_hooks() {
    register_rest_field(
        'post',
        'mtags',
        array(
            'get_callback' => 'm_get_tags',
        )
    );
}

function m_get_tags($array, $field_name, $request) {
    $tags = get_the_tags($array['id']);
    if (empty($tags) || is_wp_error($tags)) {
        return [];
    }

    $tag_array = array();
    foreach ($tags as $tag) {
        $tag_array[] = $tag->name;  // Puoi aggiungere altri campi come 'id', 'slug', ecc.
    }

    return $tag_array;
}

////////// MOSTRA I CUSTOM FIELD WP NELLE API REST
add_action('rest_api_init', 'add_custom_fields_to_rest_api');
function add_custom_fields_to_rest_api() {
    // Recupera tutti i tipi di post registrati
    $post_types = get_post_types(array('public' => true), 'names');

    // Per ciascun tipo di post, aggiungi il campo personalizzato
    foreach ($post_types as $post_type) {
        register_rest_field(
            $post_type,
            'custom_fields', // Nuovo nome del campo nella risposta JSON
            array(
                'get_callback'    => 'get_custom_fields', // Nome della funzione personalizzata
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
}

function get_custom_fields($object, $field_name, $request) {
    // Recupera i custom fields per il post corrente
    $post_id = $object['id'];
    $custom_fields = get_post_meta($post_id);

    // Opzionalmente, puoi filtrare o elaborare i custom fields qui
    // Ad esempio, se vuoi escludere alcuni campi specifici
    // $excluded_fields = array('_edit_lock', '_edit_last');
    // foreach ($excluded_fields as $excluded_field) {
    //     if (isset($custom_fields[$excluded_field])) {
    //         unset($custom_fields[$excluded_field]);
    //     }
    // }

    return $custom_fields;
}



//RIMOSSE AUTORIZZAZIONI
function handle_form_submission($request) {
    $params = $request->get_params();

    $post_id = wp_insert_post(array(
        'post_title' => sanitize_text_field($params['title']),
        'post_type' => 'form_submission',
        'post_status' => 'publish',
    ));

    if (is_wp_error($post_id)) {
        return new WP_Error('post_insert_failed', $post_id->get_error_message(), array('status' => 500));
    }

    update_post_meta($post_id, '_firstname', sanitize_text_field($params['meta']['_firstname']));
    update_post_meta($post_id, '_lastname', sanitize_text_field($params['meta']['_lastname']));
    update_post_meta($post_id, '_email', sanitize_email($params['meta']['_email']));
    update_post_meta($post_id, '_url', esc_url_raw($params['meta']['_url']));
    update_post_meta($post_id, '_message', sanitize_textarea_field($params['meta']['_message']));

    return new WP_REST_Response('Form submission successful', 200);
}

add_action('rest_api_init', function() {
    register_rest_route('wp/v2', '/form-submissions', array(
        'methods' => 'POST',
        'callback' => 'handle_form_submission',
        'permission_callback' => '__return_true', // Permette l'accesso senza autenticazione
    ));
});
