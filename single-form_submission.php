<?php
/**
 * Template Name: Single Form Submission
 */


// Verifica se è presente un post valido
if (have_posts()) :
    while (have_posts()) : the_post();

        // Recupera i meta field del post
        $firstname = get_post_meta(get_the_ID(), '_firstname', true);
        $lastname = get_post_meta(get_the_ID(), '_lastname', true);
        $email = get_post_meta(get_the_ID(), '_email', true);
        $url = get_post_meta(get_the_ID(), '_url', true);
        $message = get_post_meta(get_the_ID(), '_message', true);
?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <div class="entry-content">
                <?php if (!empty($firstname)) : ?>
                    <p><strong>Nome:</strong> <?php echo esc_html($firstname); ?></p>
                <?php endif; ?>
                <?php if (!empty($lastname)) : ?>
                    <p><strong>Cognome:</strong> <?php echo esc_html($lastname); ?></p>
                <?php endif; ?>
                <?php if (!empty($email)) : ?>
                    <p><strong>Email:</strong> <?php echo esc_html($email); ?></p>
                <?php endif; ?>
                <?php if (!empty($url)) : ?>
                    <p><strong>URL del sito:</strong> <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($url); ?></a></p>
                <?php endif; ?>
                <?php if (!empty($message)) : ?>
                    <p><strong>Messaggio:</strong><br><?php echo esc_html($message); ?></p>
                <?php endif; ?>

                <?php
                // Contenuto principale del post
                the_content();

                // Se desideri aggiungere altre informazioni o elaborazioni personalizzate, questo è il posto giusto
                ?>

            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->

    <?php endwhile;
endif;
