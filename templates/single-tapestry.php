<?php

/*
 Template Name: Tapestry Page Template
 */

function enqueue_vue_app_build()
{
    // register the Vue build script.
    wp_register_script( // the app build script generated by Webpack.
        'tapestry_d3_vue',
        // plugin_dir_url(__FILE__) . 'tapestry-d3-vue/dist/build.js',
        'http://localhost:8080/dist/build.js',
        array(),
        null,
        true
    );

    // make custom data available to the Vue app with wp_localize_script.
    global $post;
    wp_localize_script(
        'tapestry_d3_vue', // vue script handle defined in wp_register_script.
        'wpData', // javascript object that will made availabe to Vue.
        array( // wordpress data to be made available to the Vue app in 'wpData'
            'directory_uri' => plugin_dir_url(__FILE__) . 'tapestry-d3-vue/dist', // child theme directory path.
            'rest_url' => untrailingslashit(esc_url_raw(rest_url())), // URL to the REST endpoint.
            'app_path' => $post->post_name, // page where the custom page template is loaded.
            'post_categories' => get_terms(array(
                'taxonomy' => 'category', // default post categories.
                'hide_empty' => true,
                'fields' => 'names',
            )),
        )
    );

    // enqueue the Vue app script with localized data.
    wp_enqueue_script('tapestry_d3_vue');
}
add_action('wp_enqueue_scripts', 'enqueue_vue_app_build');

/**
 * Register Script with Nonce
 * 
 * @return Object null
 */
function addNonceToScript()
{
    $params = array(
        'nonce'  => wp_create_nonce('wp_rest')
    );

    wp_register_script(
        'wp_tapestry_functions_script',
        plugin_dir_url(__FILE__) . 'libs/tapestry-functions.js',
        array('jquery', 'wp_tapestry_script'),
        null,
        true
    );
    wp_enqueue_script('wp_tapestry_functions_script');

    wp_register_script(
        'wp_tapestry_script',
        plugin_dir_url(__FILE__) . 'libs/tapestry.js',
        array('jquery'),
        null,
        true
    );
    wp_localize_script('wp_tapestry_script', 'wpApiSettings', $params);
    wp_enqueue_script('wp_tapestry_script');


    wp_add_inline_script('wp_tapestry_script', "
        var thisTapestryTool = new tapestryTool({
            'containerId': 'tapestry',
            'apiUrl': '" . get_rest_url(null, 'tapestry-tool/v1') . "',
            'wpUserId': '" . apply_filters('determine_current_user', false) . "',
            'wpPostId': '" . get_the_ID() . "',
            'wpIsAdmin': '" . current_user_can('administrator') . "',
            'addNodeModalUrl': '" . plugin_dir_url(__FILE__) . "modal-add-node.html',
        });
    ");
}
add_action('wp_enqueue_scripts', 'addNonceToScript');

get_header(); ?>

<div id="primary" class="content-area col-md-12">
    <main id="main" class="post-wrap" role="main">

        <div id="tapestry-container"></div>

        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('content', 'page'); ?>
            <?php
            // If comments are open or we have at least one comment, load up the comment template
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>

        <?php endwhile; // end of the loop. 
        ?>

        <link crossorigin="anonymous" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" rel="stylesheet" />
        <link href="<?php echo plugin_dir_url(__FILE__) ?>tapestry-d3/tapestry.css" rel="stylesheet" />
        <link href="<?php echo plugin_dir_url(__FILE__) ?>libs/jquery-ui.min.css" rel="stylesheet" />
        <link href="<?php echo plugin_dir_url(__FILE__) ?>libs/bootstrap.min.css" rel="stylesheet" />

        <script src="<?php echo plugin_dir_url(__FILE__) ?>libs/jquery.min.js" type="application/javascript"></script>
        <script src="<?php echo plugin_dir_url(__FILE__) ?>libs/jquery-ui.min.js" type="application/javascript"></script>
        <script src="<?php echo plugin_dir_url(__FILE__) ?>libs/jscookie.js" type="application/javascript"></script>
        <script src="<?php echo plugin_dir_url(__FILE__) ?>libs/d3.v5.min.js" type="application/javascript"></script>
        <script src="<?php echo plugin_dir_url(__FILE__) ?>libs/h5p-resizer.min.js" charset="UTF-8"></script>
        <script src="<?php echo plugin_dir_url(__FILE__) ?>libs/bootstrap.min.js" charset="UTF-8"></script>

        <script>
            // EXAMPLE OF USAGE:
            // thisTapestryTool.setDataset({'abc':'123'});
            // thisTapestryTool.redraw(false);

            var wpPostId = "<?php echo get_the_ID(); ?>";
            var apiUrl = "<?php echo get_rest_url(null, 'tapestry-tool/v1'); ?>";

            // Capture click events anywhere inside or outside tapestry
            $(document).ready(function() {
                document.body.addEventListener('click', function(event) {
                    var x = event.clientX + $(window).scrollLeft();
                    var y = event.clientY + $(window).scrollTop();
                    recordAnalyticsEvent('user', 'click', 'screen', null, {
                        'x': x,
                        'y': y
                    });
                }, true);

                document.getElementById('tapestry').addEventListener('click', function(event) {
                    var x = event.clientX + $(window).scrollLeft();
                    var y = event.clientY + $(window).scrollTop();
                    recordAnalyticsEvent('user', 'click', 'tapestry', null, {
                        'x': x,
                        'y': y
                    });
                }, true);
            });
        </script>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>