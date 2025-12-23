<?php
/**
 * Professional WordPress Theme Functions
 * @package MyPremiumTheme
 * @version 2.0.0
 */

// ==================== SECURITY & PERFORMANCE ====================

// Prevent Direct Access
defined('ABSPATH') || exit;

// Disable XML-RPC for Security
add_filter('xmlrpc_enabled', '__return_false');

// Remove WordPress Version
remove_action('wp_head', 'wp_generator');

// Disable Emojis (for performance)
function disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'disable_emojis');

// ==================== THEME SETUP ====================

function mpt_theme_setup() {
    // Theme Supports
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ]);
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    
    // Gutenberg Support
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');
    
    // Custom Logo
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    
    // WooCommerce Support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Custom Background
    add_theme_support('custom-background', [
        'default-color' => 'ffffff',
        'default-image' => '',
    ]);
    
    // Feed Links
    add_theme_support('automatic-feed-links');
    
    // Navigation Menus
    register_nav_menus([
        'primary'   => __('Primary Menu', 'mpt'),
        'secondary' => __('Secondary Menu', 'mpt'),
        'footer'    => __('Footer Menu', 'mpt'),
        'mobile'    => __('Mobile Menu', 'mpt'),
        'social'    => __('Social Menu', 'mpt'),
    ]);
    
    // Image Sizes
    add_image_size('mpt-featured', 1200, 800, true);
    add_image_size('mpt-medium', 768, 512, true);
    add_image_size('mpt-thumbnail', 300, 200, true);
    add_image_size('mpt-square', 400, 400, true);
    
    // Load Text Domain
    load_theme_textdomain('mpt', get_template_directory() . '/languages');
    
    // Theme Activation Hook
    if (is_admin() && isset($_GET['activated'])) {
        mpt_theme_activation();
    }
}
add_action('after_setup_theme', 'mpt_theme_setup');

// ==================== ASSETS MANAGEMENT ====================

function mpt_enqueue_assets() {
    $theme_version = wp_get_theme()->get('Version');
    
    // Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap', [], null);
    
    // Main Stylesheet
    wp_enqueue_style('mpt-main', get_template_directory_uri() . '/assets/css/main.min.css', [], $theme_version);
    
    // RTL Support
    if (is_rtl()) {
        wp_enqueue_style('mpt-rtl', get_template_directory_uri() . '/assets/css/rtl.min.css', ['mpt-main'], $theme_version);
    }
    
    // Custom Styles (for Customizer)
    wp_add_inline_style('mpt-main', mpt_custom_css());
    
    // JavaScript
    wp_enqueue_script('mpt-vendor', get_template_directory_uri() . '/assets/js/vendor.min.js', ['jquery'], $theme_version, true);
    wp_enqueue_script('mpt-main', get_template_directory_uri() . '/assets/js/main.min.js', ['mpt-vendor'], $theme_version, true);
    
    // Localize Script
    wp_localize_script('mpt-main', 'mpt_ajax', [
        'ajax_url'   => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('mpt_nonce'),
        'is_mobile'  => wp_is_mobile(),
        'theme_path' => get_template_directory_uri(),
    ]);
    
    // Comment Reply
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    // Dequeue Block Library CSS
    if (!is_admin()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
    }
}
add_action('wp_enqueue_scripts', 'mpt_enqueue_assets');

// Admin Assets
function mpt_admin_assets() {
    wp_enqueue_style('mpt-admin', get_template_directory_uri() . '/assets/css/admin.css', [], wp_get_theme()->get('Version'));
    wp_enqueue_script('mpt-admin', get_template_directory_uri() . '/assets/js/admin.js', ['jquery'], wp_get_theme()->get('Version'), true);
}
add_action('admin_enqueue_scripts', 'mpt_admin_assets');

// ==================== CUSTOMIZER SETTINGS ====================

require_once get_template_directory() . '/inc/customizer/customizer.php';
require_once get_template_directory() . '/inc/customizer/sanitization.php';

// ==================== CUSTOM POST TYPES & TAXONOMIES ====================

function mpt_register_custom_post_types() {
    // Portfolio CPT
    $portfolio_labels = [
        'name'               => __('Portfolio', 'mpt'),
        'singular_name'      => __('Portfolio Item', 'mpt'),
        'menu_name'          => __('Portfolio', 'mpt'),
        'add_new'            => __('Add New', 'mpt'),
        'add_new_item'       => __('Add New Portfolio Item', 'mpt'),
        'edit_item'          => __('Edit Portfolio Item', 'mpt'),
        'new_item'           => __('New Portfolio Item', 'mpt'),
        'view_item'          => __('View Portfolio Item', 'mpt'),
        'search_items'       => __('Search Portfolio', 'mpt'),
        'not_found'          => __('No portfolio items found', 'mpt'),
        'not_found_in_trash' => __('No portfolio items found in Trash', 'mpt'),
    ];
    
    register_post_type('portfolio', [
        'labels'              => $portfolio_labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => ['slug' => 'portfolio'],
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-portfolio',
        'supports'            => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
        'show_in_rest'        => true,
    ]);
    
    // Team CPT
    register_post_type('team', [
        'labels'              => [
            'name'          => __('Team', 'mpt'),
            'singular_name' => __('Team Member', 'mpt'),
        ],
        'public'              => true,
        'menu_position'       => 21,
        'menu_icon'           => 'dashicons-groups',
        'supports'            => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'        => true,
    ]);
}
add_action('init', 'mpt_register_custom_post_types');

function mpt_register_taxonomies() {
    // Portfolio Categories
    register_taxonomy('portfolio_category', 'portfolio', [
        'labels'            => [
            'name'          => __('Portfolio Categories', 'mpt'),
            'singular_name' => __('Portfolio Category', 'mpt'),
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'portfolio-category'],
        'show_in_rest'      => true,
    ]);
}
add_action('init', 'mpt_register_taxonomies');

// ==================== WIDGETS & SIDEBARS ====================

function mpt_register_sidebars() {
    // Main Sidebar
    register_sidebar([
        'name'          => __('Main Sidebar', 'mpt'),
        'id'            => 'sidebar-main',
        'description'   => __('Main sidebar that appears on the right.', 'mpt'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
    
    // Footer Sidebars
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name'          => sprintf(__('Footer Column %d', 'mpt'), $i),
            'id'            => 'footer-' . $i,
            'description'   => sprintf(__('Footer column %d widget area.', 'mpt'), $i),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        ]);
    }
    
    // WooCommerce Sidebar
    register_sidebar([
        'name'          => __('WooCommerce Sidebar', 'mpt'),
        'id'            => 'sidebar-shop',
        'description'   => __('Sidebar for WooCommerce pages.', 'mpt'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'mpt_register_sidebars');

// Custom Widgets
require_once get_template_directory() . '/inc/widgets/social-links-widget.php';
require_once get_template_directory() . '/inc/widgets/recent-posts-widget.php';
require_once get_template_directory() . '/inc/widgets/newsletter-widget.php';

// ==================== AJAX FUNCTIONS ====================

// Load More Posts
function mpt_load_more_posts() {
    check_ajax_referer('mpt_nonce', 'nonce');
    
    $paged = $_POST['page'] ? intval($_POST['page']) : 2;
    $post_type = $_POST['post_type'] ? sanitize_text_field($_POST['post_type']) : 'post';
    
    $args = [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'paged'          => $paged,
        'posts_per_page' => get_option('posts_per_page'),
    ];
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            get_template_part('template-parts/content', get_post_type());
        endwhile;
        wp_reset_postdata();
    else :
        echo '<div class="no-more-posts">' . __('No more posts to load', 'mpt') . '</div>';
    endif;
    
    wp_die();
}
add_action('wp_ajax_mpt_load_more', 'mpt_load_more_posts');
add_action('wp_ajax_nopriv_mpt_load_more', 'mpt_load_more_posts');

// Contact Form
function mpt_contact_form() {
    check_ajax_referer('mpt_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);
    $phone = sanitize_text_field($_POST['phone']);
    
    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error(['message' => __('Please fill all required fields.', 'mpt')]);
    }
    
    if (!is_email($email)) {
        wp_send_json_error(['message' => __('Invalid email address.', 'mpt')]);
    }
    
    // Email headers
    $to = get_option('admin_email');
    $subject = sprintf(__('New Contact Form Message from %s', 'mpt'), get_bloginfo('name'));
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    
    $body = "
    <html>
    <body>
    <h2>" . __('Contact Form Submission', 'mpt') . "</h2>
    <p><strong>" . __('Name:', 'mpt') . "</strong> {$name}</p>
    <p><strong>" . __('Email:', 'mpt') . "</strong> {$email}</p>
    <p><strong>" . __('Phone:', 'mpt') . "</strong> {$phone}</p>
    <p><strong>" . __('Message:', 'mpt') . "</strong></p>
    <p>{$message}</p>
    </body>
    </html>
    ";
    
    if (wp_mail($to, $subject, $body, $headers)) {
        // Save to database
        $contact_data = [
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'message' => $message,
            'date'    => current_time('mysql'),
            'ip'      => $_SERVER['REMOTE_ADDR'],
        ];
        
        // You can save to custom table or as a post type
        // mpt_save_contact($contact_data);
        
        wp_send_json_success(['message' => __('Message sent successfully!', 'mpt')]);
    } else {
        wp_send_json_error(['message' => __('Failed to send message. Please try again.', 'mpt')]);
    }
}
add_action('wp_ajax_mpt_contact_form', 'mpt_contact_form');
add_action('wp_ajax_nopriv_mpt_contact_form', 'mpt_contact_form');

// ==================== PERFORMANCE OPTIMIZATIONS ====================

// Lazy Load Images
function mpt_lazy_load_images($content) {
    if (is_feed() || is_admin() || is_preview()) {
        return $content;
    }
    
    $content = preg_replace_callback('/(<\s*img[^>]+)(src\s*=\s*"[^"]+")([^>]+>)/i', function($matches) {
        if (strpos($matches[0], 'loading=') === false) {
            $new_img = str_replace('src=', 'src="' . get_template_directory_uri() . '/assets/images/placeholder.jpg" data-src=', $matches[0]);
            $new_img = preg_replace('/(<img[^>]+)/i', '$1 loading="lazy"', $new_img);
            return $new_img;
        }
        return $matches[0];
    }, $content);
    
    return $content;
}
add_filter('the_content', 'mpt_lazy_load_images');

// Defer JavaScript
function mpt_defer_scripts($tag, $handle, $src) {
    $defer = ['mpt-main', 'mpt-vendor'];
    
    if (in_array($handle, $defer)) {
        return '<script src="' . $src . '" defer="defer"></script>';
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'mpt_defer_scripts', 10, 3);

// Remove Query Strings from Static Resources
function mpt_remove_query_strings($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src', 'mpt_remove_query_strings', 10, 2);
add_filter('script_loader_src', 'mpt_remove_query_strings', 10, 2);

// ==================== SEO OPTIMIZATIONS ====================

// Schema Markup
function mpt_schema_markup() {
    if (is_single()) {
        $schema = [
            '@context'  => 'https://schema.org',
            '@type'     => 'Article',
            'headline'  => get_the_title(),
            'image'     => get_the_post_thumbnail_url(get_the_ID(), 'full'),
            'datePublished' => get_the_date('c'),
            'dateModified'  => get_the_modified_date('c'),
            'author'    => [
                '@type' => 'Person',
                'name'  => get_the_author(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name'  => get_bloginfo('name'),
                'logo'  => [
                    '@type'  => 'ImageObject',
                    'url'    => get_custom_logo_url(),
                ],
            ],
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
add_action('wp_head', 'mpt_schema_markup');

// Meta Description
function mpt_meta_description() {
    if (is_single() || is_page()) {
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 30);
        echo '<meta name="description" content="' . esc_attr($description) . '">';
    } elseif (is_archive()) {
        echo '<meta name="description" content="' . esc_attr(get_the_archive_description()) . '">';
    }
}
add_action('wp_head', 'mpt_meta_description');

// ==================== SECURITY ENHANCEMENTS ====================

// Disable File Editing
define('DISALLOW_FILE_EDIT', true);

// Hide Login Errors
function mpt_hide_login_errors() {
    return __('Something went wrong. Please try again.', 'mpt');
}
add_filter('login_errors', 'mpt_hide_login_errors');

// Limit Login Attempts
function mpt_limit_login_attempts($user, $username, $password) {
    static $failed_attempts = [];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    if (!isset($failed_attempts[$ip])) {
        $failed_attempts[$ip] = 0;
    }
    
    $failed_attempts[$ip]++;
    
    if ($failed_attempts[$ip] > 5) {
        $lockout_time = 30 * 60; // 30 minutes
        wp_die(sprintf(__('Too many failed login attempts. Please try again in %d minutes.', 'mpt'), $lockout_time / 60));
    }
    
    return $user;
}
add_filter('authenticate', 'mpt_limit_login_attempts', 30, 3);

// ==================== CUSTOM FUNCTIONS ====================

// Breadcrumbs
function mpt_breadcrumbs() {
    if (is_front_page()) return;
    
    echo '<nav class="breadcrumb" aria-label="Breadcrumb">';
    echo '<ol>';
    echo '<li><a href="' . home_url() . '">' . __('Home', 'mpt') . '</a></li>';
    
    if (is_category() || is_single()) {
        $categories = get_the_category();
        if ($categories) {
            echo '<li><a href="' . get_category_link($categories[0]->term_id) . '">' . $categories[0]->name . '</a></li>';
        }
        if (is_single()) {
            echo '<li>' . get_the_title() . '</li>';
        }
    } elseif (is_page()) {
        echo '<li>' . get_the_title() . '</li>';
    } elseif (is_search()) {
        echo '<li>' . __('Search Results', 'mpt') . '</li>';
    }
    
    echo '</ol>';
    echo '</nav>';
}

// Pagination
function mpt_pagination() {
    global $wp_query;
    
    $big = 999999999;
    $pages = paginate_links([
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $wp_query->max_num_pages,
        'type'      => 'array',
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ]);
    
    if (is_array($pages)) {
        echo '<nav class="pagination"><ul>';
        foreach ($pages as $page) {
            echo '<li>' . $page . '</li>';
        }
        echo '</ul></nav>';
    }
}

// Custom Excerpt
function mpt_excerpt($length = 30, $more = '...') {
    $excerpt = get_the_excerpt();
    if (strlen($excerpt) > $length) {
        $excerpt = substr($excerpt, 0, $length) . $more;
    }
    return $excerpt;
}

// Estimated Reading Time
function mpt_reading_time() {
    $content = get_post_field('post_content', get_the_ID());
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // 200 words per minute
    
    return sprintf(_n('%d min read', '%d min read', $reading_time, 'mpt'), $reading_time);
}

// Share Buttons
function mpt_share_buttons() {
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    
    $networks = [
        'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
        'twitter'   => 'https://twitter.com/intent/tweet?text=' . $title . '&url=' . $url,
        'linkedin'  => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $url . '&title=' . $title,
        'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . $url . '&description=' . $title,
    ];
    
    echo '<div class="share-buttons">';
    foreach ($networks as $network => $link) {
        echo '<a href="' . $link . '" target="_blank" rel="noopener" class="share-' . $network . '">';
        echo '<i class="icon-' . $network . '"></i>';
        echo '</a>';
    }
    echo '</div>';
}

// ==================== WOOCOMMERCE FUNCTIONS ====================

if (class_exists('WooCommerce')) {
    
    // Remove WooCommerce Styles
    add_filter('woocommerce_enqueue_styles', '__return_empty_array');
    
    // Custom WooCommerce Functions
    function mpt_woocommerce_setup() {
        // Add theme support
        add_theme_support('woocommerce');
        
        // Remove WooCommerce breadcrumbs
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        
        // Change number of products per row
        add_filter('loop_shop_columns', function() {
            return 3;
        });
        
        // Change number of related products
        add_filter('woocommerce_output_related_products_args', function($args) {
            $args['posts_per_page'] = 3;
            $args['columns'] = 3;
            return $args;
        });
    }
    add_action('after_setup_theme', 'mpt_woocommerce_setup');
    
    // Custom Add to Cart Text
    add_filter('woocommerce_product_add_to_cart_text', function($text) {
        return __('Buy Now', 'mpt');
    });
    
    // Sale Badge Percentage
    function mpt_sale_percentage_badge() {
        global $product;
        if (!$product->is_on_sale()) return;
        
        if ($product->is_type('simple')) {
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
            echo '<span class="onsale">-' . $percentage . '%</span>';
        }
    }
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
    add_action('woocommerce_before_shop_loop_item_title', 'mpt_sale_percentage_badge', 10);
}

// ==================== ADMIN CUSTOMIZATIONS ====================

// Add Custom Dashboard Widget
function mpt_dashboard_widget() {
    wp_add_dashboard_widget(
        'mpt_theme_info',
        __('Theme Information', 'mpt'),
        function() {
            echo '<p>' . __('Welcome to My Premium Theme!', 'mpt') . '</p>';
            echo '<p><strong>' . __('Version:', 'mpt') . '</strong> ' . wp_get_theme()->get('Version') . '</p>';
            echo '<p><a href="' . admin_url('customize.php') . '">' . __('Customize Theme', 'mpt') . '</a></p>';
        }
    );
}
add_action('wp_dashboard_setup', 'mpt_dashboard_widget');

// Custom Admin Footer
function mpt_admin_footer() {
    echo '<span id="footer-thankyou">' . __('Thank you for using My Premium Theme', 'mpt') . '</span>';
}
add_filter('admin_footer_text', 'mpt_admin_footer');

// ==================== CUSTOM CSS FROM CUSTOMIZER ====================

function mpt_custom_css() {
    $css = '';
    
    // Custom Colors
    $primary_color = get_theme_mod('primary_color', '#0073aa');
    $secondary_color = get_theme_mod('secondary_color', '#23282d');
    
    if ($primary_color) {
        $css .= "
        :root {
            --primary-color: {$primary_color};
            --secondary-color: {$secondary_color};
        }
        
        a, .primary-color {
            color: {$primary_color};
        }
        
        .button-primary, button.primary {
            background-color: {$primary_color};
        }
        ";
    }
    
    // Custom Fonts
    $heading_font = get_theme_mod('heading_font', 'Inter');
    $body_font = get_theme_mod('body_font', 'Inter');
    
    if ($heading_font) {
        $css .= "
        h1, h2, h3, h4, h5, h6 {
            font-family: '{$heading_font}', sans-serif;
        }
        ";
    }
    
    // Custom CSS from Customizer
    $custom_css = get_theme_mod('custom_css');
    if ($custom_css) {
        $css .= $custom_css;
    }
    
    return $css;
}

// ==================== THEME ACTIVATION ====================

function mpt_theme_activation() {
    // Set default options
    if (!get_option('mpt_theme_activated')) {
        // Set default image sizes
        update_option('thumbnail_size_w', 300);
        update_option('thumbnail_size_h', 200);
        update_option('medium_size_w', 768);
        update_option('medium_size_h', 512);
        update_option('large_size_w', 1200);
        update_option('large_size_h', 800);
        
        // Set permalink structure
        update_option('permalink_structure', '/%postname%/');
        
        // Create default pages
        $pages = [
            'home'     => ['title' => 'Home', 'template' => 'template-home.php'],
            'blog'     => ['title' => 'Blog', 'template' => ''],
            'contact'  => ['title' => 'Contact', 'template' => 'template-contact.php'],
            'portfolio' => ['title' => 'Portfolio', 'template' => 'template-portfolio.php'],
        ];
        
        foreach ($pages as $key => $page) {
            if (!get_page_by_title($page['title'])) {
                $page_id = wp_insert_post([
                    'post_title'   => $page['title'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_content' => '',
                ]);
                
                if ($page['template']) {
                    update_post_meta($page_id, '_wp_page_template', $page['template']);
                }
            }
        }
        
        // Set homepage
        $homepage = get_page_by_title('Home');
        if ($homepage) {
            update_option('page_on_front', $homepage->ID);
            update_option('show_on_front', 'page');
        }
        
        // Set blog page
        $blogpage = get_page_by_title('Blog');
        if ($blogpage) {
            update_option('page_for_posts', $blogpage->ID);
        }
        
        update_option('mpt_theme_activated', true);
    }
}

// ==================== CUSTOM SHORTCODES ====================

// Button Shortcode
function mpt_button_shortcode($atts, $content = null) {
    $atts = shortcode_atts([
        'url'    => '#',
        'target' => '_self',
        'style'  => 'primary',
        'size'   => 'medium',
    ], $atts);
    
    return '<a href="' . esc_url($atts['url']) . '" class="btn btn-' . $atts['style'] . ' btn-' . $atts['size'] . '" target="' . $atts['target'] . '">' . do_shortcode($content) . '</a>';
}
add_shortcode('button', 'mpt_button_shortcode');

// Icon Shortcode
function mpt_icon_shortcode($atts) {
    $atts = shortcode_atts([
        'name'  => 'star',
        'size'  => '16',
        'color' => '',
    ], $atts);
    
    $style = '';
    if ($atts['color']) {
        $style = ' style="color: ' . $atts['color'] . ';"';
    }
    
    return '<i class="icon-' . $atts['name'] . '" style="font-size: ' . $atts['size'] . 'px;"' . $style . '></i>';
}
add_shortcode('icon', 'mpt_icon_shortcode');

// ==================== HELPER FUNCTIONS ====================

// Get Custom Logo URL
function get_custom_logo_url() {
    $custom_logo_id = get_theme_mod('custom_logo');
    return $custom_logo_id ? wp_get_attachment_url($custom_logo_id) : '';
}

// Is Plugin Active
function mpt_is_plugin_active($plugin) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    return is_plugin_active($plugin);
}

// Get SVG Icon
function mpt_get_svg($icon) {
    $svg_path = get_template_directory() . '/assets/svg/' . $icon . '.svg';
    if (file_exists($svg_path)) {
        return file_get_contents($svg_path);
    }
    return '';
}

// ==================== CLEANUP FUNCTIONS ====================

// Remove Unnecessary Header Links
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

// Clean up wp_head()
function mpt_cleanup_head() {
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'feed_links', 2);
}
add_action('init', 'mpt_cleanup_head');

// ==================== ERROR HANDLING ====================

// Custom Error Page
function mpt_custom_error_page() {
    if (is_404()) {
        status_header(404);
        nocache_headers();
        
        // You can redirect to custom 404 page
        // $custom_404 = get_page_by_path('404');
        // if ($custom_404) {
        //     wp_redirect(get_permalink($custom_404->ID));
        //     exit;
        // }
    }
}
add_action('template_redirect', 'mpt_custom_error_page');

// ==================== DEPRECATED FUNCTIONS HANDLING ====================

// Backward Compatibility
if (!function_exists('wp_body_open')) {
    function wp_body_open() {
        do_action('wp_body_open');
    }
}

// ==================== REQUIRED FILES ====================

// Include Additional Files
$theme_includes = [
    '/inc/custom-header.php',
    '/inc/template-tags.php',
    '/inc/template-functions.php',
    '/inc/customizer.php',
    '/inc/jetpack.php',
    '/inc/woocommerce.php',
    '/inc/demo-importer.php',
];

foreach ($theme_includes as $file) {
    if (file_exists(get_template_directory() . $file)) {
        require_once get_template_directory() . $file;
    }
}

// ==================== THEME UPDATER ====================

// For premium themes with update functionality
function mpt_theme_updater() {
    if (!class_exists('EDD_Theme_Updater')) {
        include(get_template_directory() . '/inc/updater/theme-updater.php');
    }
    
    $config = [
        'remote_api_url' => 'https://your-theme-site.com',
        'item_name'      => 'My Premium Theme',
        'theme_slug'     => 'my-premium-theme',
        'version'        => wp_get_theme()->get('Version'),
        'author'         => 'Your Name',
        'download_id'    => '',
        'renew_url'      => '',
        'beta'           => false,
    ];
    
    new EDD_Theme_Updater($config);
}
// add_action('after_setup_theme', 'mpt_theme_updater');

// ==================== END OF FILE ====================
?>