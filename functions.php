<?php


//////////////////// Funzioni del tema Minimal Headless Theme

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



//////////////////// FUNZIONI API

////////// Mostra l`url delle immagini nell`API
function ws_register_images_field() {
    // Ottieni tutti i tipi di post personalizzati registrati
    $post_types = get_post_types( array( 'public' => true ), 'names' );

    // Loop attraverso tutti i tipi di post
    foreach ( $post_types as $post_type ) {
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

add_action( 'rest_api_init', 'ws_register_images_field' );

function ws_get_images_urls( $object, $field_name, $request ) {
    // Ottieni l'URL dell'immagine in dimensione 'medium'
    $medium = wp_get_attachment_image_src( get_post_thumbnail_id( $object['id'] ), 'medium' );
    $medium_url = $medium ? $medium[0] : '';

    // Ottieni l'URL dell'immagine in dimensione 'large'
    $large = wp_get_attachment_image_src( get_post_thumbnail_id( $object['id'] ), 'large' );
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



