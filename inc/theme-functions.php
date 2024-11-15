<?php

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
