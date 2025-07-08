<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);
    wp_enqueue_style('gallery-style', get_stylesheet_directory_uri() . '/css/gallery.css', array('hello-elementor-child-style'));  

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

function enqueue_custom_js() {
    wp_enqueue_script(
        'custom-scroll-script', 
        get_stylesheet_directory_uri() . '/custom.js', 
        array('jquery'), 
        '1.0.38', 
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_custom_js');


function custom_scripts()
{
    wp_enqueue_style('slick-style', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css');
    wp_enqueue_script('slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array(), '1.2', true);

// Lightbox2 CSS
wp_enqueue_style('lightbox2-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');

// jQuery (already included with WordPress)
wp_enqueue_script('jquery');

  // Lightbox2 JS
    wp_enqueue_script('lightbox2-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', array('jquery'), null, true);
    wp_enqueue_script('cps-gallery-ajax', get_stylesheet_directory_uri() . '/js/cps-gallery-ajax.js', array('jquery'), '1.2.1', true);

    wp_localize_script('cps-gallery-ajax','cps_gallery_ajax',array('ajax_url' => admin_url('admin-ajax.php') ));
    wp_localize_script('artsmart-custom','ajax_object',array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'custom_scripts', 200);

include_once('includes/gallery_post_type.php');
include_once('includes/cps_gallery_filter.php');




// Register custom image sizes
add_image_size('porfyri-thumb-600', 600, 150, true);
add_image_size('porfyri-thumb-300', 300, 100, true);
add_image_size('porfyri-full', 1000, 600, true);
add_image_size('porfyri-full', 1340, 500, true);
add_image_size('porfyri-blog', 620, 300, true);

// Allow image sizes in media manager
add_filter('image_size_names_choose', 'porfyri_custom_image_sizes');
function porfyri_custom_image_sizes($sizes) {
    return array_merge($sizes, array(
        'porfyri-thumb-600' => __('600px by 150px'),
        'porfyri-thumb-300' => __('300px by 100px'),
        'porfyri-full' => __('1000px by 600px'),
    ));
}

// Function to get first image from post content if no featured image
function catch_that_image() {
    global $post;
    $first_img = '';
    ob_start();
    ob_end_clean();
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
    $first_img = !empty($matches[1][0]) ? $matches[1][0] : '';

    if (empty($first_img)) {
        $first_img = get_template_directory_uri() . "/images/default-image.jpg";
    }
    return $first_img;
}


// Custom Pagination for Category Archive
function custom_category_pagination() {
    global $wp_query;

    $big = 999999999; // Arbitrary large number

    $pages = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $wp_query->max_num_pages,
        'prev_text' => __('« Previous', 'porfyritheme'),
        'next_text' => __('Next »', 'porfyritheme'),
        'type'      => 'array' // Return as an array for custom markup
    ));

    if (is_array($pages)) {
        echo '<nav class="custom-category-pagination"><ul>';
        foreach ($pages as $page) {
            echo '<li>' . $page . '</li>';
        }
        echo '</ul></nav>';
    }
}

/*****************************************
Custom Gallery 
***********************************/

function cps_gallery_admin_menu() {
    add_menu_page(
        'Gallery Bulk Upload',
        'Gallery Upload',
        'manage_options',
        'cps-gallery-upload',
        'cps_gallery_upload_page',
        'dashicons-upload',
        20
    );
}
add_action('admin_menu', 'cps_gallery_admin_menu');

function cps_gallery_upload_page() {
    ?>
    <div class="wrap">
        <h1>Upload Multiple Images to Gallery</h1>

        <label for="gallery_category">Select Category:</label>
        <select id="gallery_category">
            <option value="">-- Select Category --</option>
            <?php
            $categories = get_terms(array(
                'taxonomy'   => 'cps_gallery_category', // Ensure correct taxonomy
                'hide_empty' => false, // Show all categories even if empty
            ));

            if (!is_wp_error($categories) && !empty($categories)) {
                foreach ($categories as $category) {
                    // Ensure category is an object before accessing properties
                    if (is_object($category)) {
                        echo "<option value='" . esc_attr($category->term_id) . "'>" . esc_html($category->name) . "</option>";
                    }
                }
            } else {
                echo '<option value="">No categories found</option>';
            }
            ?>
        </select>

        <button id="bulk-upload-btn" class="button button-primary">Upload Multiple Images</button>
        <input type="hidden" id="gallery_category_id" value="">
        <div id="uploaded-images-preview"></div>
    </div>
    <?php
}




function cps_gallery_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_cps-gallery-upload') {
        return;
    }
    
    wp_enqueue_media(); 
    wp_enqueue_script('cps-gallery-upload-script', get_stylesheet_directory_uri() . '/js/cps-gallery-upload.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'cps_gallery_enqueue_admin_scripts');



function cps_bulk_upload_gallery() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission Denied');
    }

    if (empty($_POST['image_ids']) || empty($_POST['category_id'])) {
        wp_send_json_error('Missing Data');
    }

    $image_ids = $_POST['image_ids'];
    $category_id = intval($_POST['category_id']);

    foreach ($image_ids as $image_id) {
        // Get Image Info
        $attachment = get_post($image_id);
        $image_url = wp_get_attachment_url($image_id);

        if ($attachment) {
            // Create a new post for each image
            $post_data = array(
                'post_title'    => $attachment->post_title,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_type'     => 'image-gallery',
                'post_author'   => get_current_user_id(),
            );

            $post_id = wp_insert_post($post_data);

            if ($post_id) {
                // Set the image as the featured image
                set_post_thumbnail($post_id, $image_id);

                // Assign the category
                wp_set_object_terms($post_id, $category_id, 'cps_gallery_category');
            }
        }
    }

    wp_send_json_success('Images Uploaded Successfully!');
}
add_action('wp_ajax_cps_bulk_upload_gallery', 'cps_bulk_upload_gallery');












?>
