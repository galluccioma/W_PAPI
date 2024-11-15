<?php

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