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

///////////////////POST TYPE FORM SUBMISSION
// Registrazione del Custom Post Type
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

// Aggiungi il supporto per l'immagine in evidenza nelle risposte API
add_action('rest_api_init', function () {
    // Aggiungi un campo personalizzato alla risposta della REST API
    register_rest_field('post', 'featured_image_url', [
        'get_callback' => function($post_arr) {
            // Ottieni l'ID del post
            $post_id = $post_arr['id'];
            // Ottieni l'URL dell'immagine in evidenza
            $image_id = get_post_thumbnail_id($post_id);
            if ($image_id) {
                return wp_get_attachment_url($image_id);
            }
            return null;
        },
    ]);
});

add_filter('rest_prepare_post', function($response, $post, $request) {
    // Controlla il contesto della richiesta
    $context = isset($request['context']) ? $request['context'] : 'view';

    // Se il contesto è "edit" (quindi è l'editor di WordPress), restituisci la risposta completa
    if ($context === 'edit') {
        return $response;
    }

    // Ottieni le categorie e i loro nomi
    $categories = get_the_category($post->ID);
    $category_names = [];
    
    foreach ($categories as $category) {
        $category_names[] = $category->name;
    }

    // Ottieni i tag e i loro nomi
    $tags = get_the_tags($post->ID);
    $tag_names = [];
    
    if ($tags) {
        foreach ($tags as $tag) {
            $tag_names[] = $tag->name;
        }
    }

    // Crea una nuova risposta con i dati desiderati per le richieste esterne
    $new_response = [
        'title'             => $response->data['title']['rendered'],
        'slug'              => $post->post_name, // Aggiungi lo slug del post
        'content'           => $response->data['content']['rendered'],
        'categories'        => $category_names, // Array di nomi delle categorie
        'tags'              => $tag_names, // Array di nomi dei tag
        'featured_image_url' => $response->data['featured_image_url'],
    ];

    return rest_ensure_response($new_response);
}, 10, 3);


// Endpoint REST per salvare le form submission
function handle_form_submission() {
    $params = json_decode(file_get_contents('php://input'), true);

    if (empty($params['meta']['_firstname']) || empty($params['meta']['_lastname']) || empty($params['meta']['_email'])) {
        return new WP_REST_Response('Required fields missing.', 400);
    }

    $post_id = wp_insert_post(array(
        'post_type'    => 'form_submission',
        'post_title'   => sanitize_text_field($params['title']),
        'post_status'  => 'publish',
        'meta_input'   => array(
            '_firstname' => sanitize_text_field($params['meta']['_firstname']),
            '_lastname'  => sanitize_text_field($params['meta']['_lastname']),
            '_email'     => sanitize_email($params['meta']['_email']),
            '_url'       => esc_url_raw($params['meta']['_url']),
            '_message'   => sanitize_textarea_field($params['meta']['_message']),
			'_provenance' => sanitize_text_field($params['meta']['_provenance']), // Aggiungi questa riga

        ),
    ));

    if (is_wp_error($post_id)) {
        return new WP_REST_Response('Error saving form submission.', 500);
    }

    return new WP_REST_Response('Form submission saved successfully.', 200);
}

function register_form_submission_endpoint() {
    register_rest_route('wp/v2/endpoint', '/form-submissions', array(
        'methods'  => 'POST',
        'callback' => 'handle_form_submission',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_form_submission_endpoint');
