<?php
//////////////////// Funzioni del tema Minimal Headless Theme

// Reindirizza gli utenti non registrati al login
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

// Disabilita tutte le classi dei blocchi Gutenberg
function remove_gutenberg_block_classes($content) {
    // Rimuove tutte le classi dagli elementi HTML
    $content = preg_replace('/\s+class="[^"]*"/', '', $content);
    $content = preg_replace("/\s+class='[^']*'/", '', $content);

    // Restituisce il contenuto senza le classi
    return $content;
}
add_filter('the_content', 'remove_gutenberg_block_classes', 20);

// CSS Backend
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


// REGISTRAZIONE DEL CUSTOM POST TYPE "FORM SUBMISSION", NON VISUALIZZABILE NELL'API
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


// AGGIUNTA DEI METABOX PERSONALIZZATI
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

// VISUALIZZAZIONE DEI CAMPI PERSONALIZZATI NELLA LISTA DEGLI ARTICOLI
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

// // Disabilita gli endpoint API REST predefiniti
function disable_default_rest_endpoints($endpoints) {
    // Elenco degli endpoint da disabilitare
    $endpoints_to_disable = array(
        '/wp/v2/posts',
        '/wp/v2/pages',
        '/wp/v2/users',
        // Aggiungi altri endpoint se necessario
    );

    foreach ($endpoints_to_disable as $endpoint) {
        if (isset($endpoints[$endpoint])) {
            unset($endpoints[$endpoint]);
        }
    }

    return $endpoints;
}
add_filter('rest_endpoints', 'disable_default_rest_endpoints', 10, 1);

////////FUNZIONI API
//
// Funzione per restituire i dati specificati
function get_custom_post_data($post) {
    // Recupera i metadati personalizzati
    $mtags = get_post_meta($post->ID, '_mtags', true);

    // Costruisce l'array di risposta
    return array(
    'id'      => $post->ID,
    'title'   => array(
        'rendered' => get_the_title($post->ID)
    ),
    'slug'    => $post->post_name,
    'content' => array(
        'rendered' => apply_filters('the_content', $post->post_content)
    ),
    'mtags'   => $mtags,
    'images'  => ws_get_images_urls($post, 'images', null),
    'tags'    => wp_get_post_tags($post->ID, array('fields' => 'names')), // Ottieni solo i nomi dei tag
    'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')) // Ottieni solo i nomi delle categorie
);
}

// Funzione di callback per l'endpoint personalizzato
function custom_post_endpoint_callback($data) {
    // Recupera tutti i tipi di post pubblici
    $post_types = get_post_types(array('public' => true), 'names');

    $results = array();

    foreach ($post_types as $post_type) {
        // Recupera i post del tipo corrente
        $posts = get_posts(array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
			'post_status'    => 'publish'
        ));

        foreach ($posts as $post) {
            $results[] = get_custom_post_data($post);
        }
    }

    return $results;
}

// Registrazione dell'endpoint personalizzato
function register_custom_post_endpoint() {
    register_rest_route('wp/v2', '/endpoint/posts', array(
        'methods'  => 'GET',
        'callback' => 'custom_post_endpoint_callback',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_custom_post_endpoint');

// Funzione per ottenere gli URL delle immagini
function ws_get_images_urls($post, $field_name, $request) {
    $post_id = $post->ID;

    $images = array();

    // Recupera l'ID dell'immagine in evidenza
    $thumbnail_id = get_post_thumbnail_id($post_id);

    if ($thumbnail_id) {
        // URL dell'immagine in evidenza per dimensioni 'large' e 'medium'
        $image_src_large = wp_get_attachment_image_src($thumbnail_id, 'large');
        if ($image_src_large) {
            $images['large'] = $image_src_large[0];
        }

        $image_src_medium = wp_get_attachment_image_src($thumbnail_id, 'medium');
        if ($image_src_medium) {
            $images['medium'] = $image_src_medium[0];
        }
    }

    return $images;
}
