<?php
/**
 * Library Book Search Plugin
 * 
 * PHP Version 7.4.1
 *
 * @category Library
 * 
 * @package Library_Book_Search
 * 
 * @author Dhaval Baria
 * 
 * @copyright 2023 Dhaval
 * 
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Library Book Search
 * Description: A plugin that allows users to search for books 
 *              based on book name, author, publisher, price, and rating.
 * Version:     1.0.0
 * Author:      Dhaval Baria
 * Text Domain: book-search
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 * @link https://github.com/dhavalwebd
 */
if (! class_exists('LibraryBookSearch') ) {
    /**
     * LibraryBoockSearch Class 
     * Add functionality for plugin
     * 
     * @category Library
     * 
     * @package Library_Book_Search
     * 
     * @author Dhaval Baria
     * 
     * @copyright 2023 Dhaval
     * 
     * @license http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
     * 
     * @link https://github.com/dhavalwebd
     */
    class LibraryBookSearch
    {
        /**
         * Registering plugin scripts, css
         * and registring custom post type and taxonomies 
         */
        public function __construct()
        {
            add_action('init', array( $this, 'registerPostType'));
            add_action('init', array( $this, 'registerTaxonomies'));
            add_action('add_meta_boxes', array( $this, 'registerBooksMetaBoxes'));
            add_action('save_post', array( $this, 'savePost'));
            add_action('wp_enqueue_scripts', array( $this, 'enqueueScripts'));
            add_action('wp_ajax_bookSearch', array( $this, 'bookSearch'));
            add_action('wp_ajax_nopriv_bookSearch', array( $this, 'bookSearch'));
            add_shortcode('book_search', array( $this, 'displaySearchForm'));
            add_filter('the_content', array( $this, 'singleDetailPageData'), 1);
        }
            /**
             * Display author, publisher, price, star ratings 
             * amd post details inti post single page 
             * 
             * @param $content as an argument
             * 
             * @return $content
             */
        public function singleDetailPageData($content)
        {
            if (is_singular('book')) {
                global $post;
                $postid = $post->ID;
                $bookprice = get_post_meta($postid, 'lbsp-book-price', true);
                $bookauthor = get_the_terms($postid, 'authors')[0]->name;
                $bookpublisher = get_the_terms($postid, 'publisher')[0]->name;
                $bookratings = get_post_meta(get_the_ID(), 'lbsp-book-star-rating', true);
                $content .= '<p>Book Price: ' . $bookprice . '</p>';
                $content .= '<p>Book Author: <a href=' . site_url() . '/authors/' . $bookauthor . '>' . $bookauthor . '</a></p>';
                $content .= '<p>Book Publisher: <a href=' . site_url() . '/publisher/' . $bookpublisher .'>' . $bookpublisher . '</a></p>';
                $content .= '<p>Book Ratings:  ' . $bookratings . '</p>';
            }
            return $content;
        }
        /**
         * Register custom post type book
         * 
         * @return void
         */
        public function registerPostType()
        {
            $labels = array(
                'name'                  => _x('Books', 'General', 'book-search'),
                'singular_name'         => _x('Book', 'Singular', 'book-search'),
                'name_admin_bar'        => __('Book', 'book-search'),
                'archives'              => __('Book Archives', 'book-search'),
                'attributes'            => __('Book Attributes', 'book-search'),
                'parent_item_colon'     => __('Parent Book:', 'book-search'),
                'all_items'             => __('All Books', 'book-search'),
                'add_new_item'          => __('Add New Book', 'book-search'),
                'add_new'               => __('Add New', 'book-search'),
                'new_item'              => __('New Book', 'book-search'),
                'edit_item'             => __('Edit Book', 'book-search'),
                'update_item'           => __('Update Book', 'book-search'),
                'view_item'             => __('View Book', 'book-search'),
                'view_items'            => __('View Books', 'book-search'),
                'search_items'          => __('Search Book', 'book-search'),
                'not_found'             => __('Not found', 'book-search'),
                'not_found_in_trash'    => __('Not found in Trash', 'book-search'),
                'featured_image'        => __('Featured Image', 'book-search'),
                'set_featured_image'    => __('Set featured image', 'book-search'),
                'remove_featured_image' => __('Remove featured image', 'book-search'),
                'use_featured_image'    => __('Use as featured image', 'book-search'),
                'insert_into_item'      => __('Insert into book', 'book-search'),
                'uploaded_to_this_item' => __('Uploaded to this book', 'book-search'),
                'items_list'            => __('Books list', 'book-search'),
                'items_list_navigation' => __('Books list navigation', 'book-search'),
                'filter_items_list'     => __('Filter Books list', 'book-search'),
            );
            $args = array(
                'label'                 => __('Book', 'book-search'),
                'description'           => __('Post Type Description', 'book-search'),
                'labels'                => $labels,
                'supports'              => array( 'title', 'editor', 'thumbnail' ),
                'taxonomies'            => array( 'authors', 'publisher' ),
                'hierarchical'          => true,
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 5,
                'show_in_admin_bar'     => true,
                'show_in_nav_menus'     => true,
                'can_export'            => true,
                'has_archive'           => true,
                'exclude_from_search'   => false,
                'publicly_queryable'    => true,
                'capability_type'       => 'page',
            );
            register_post_type('book', $args);
        }
        /**
         * Register custom taxonomies author and publisher
         * for custom post type book
         * 
         * @return void
         */
        public function registerTaxonomies()
        {
            register_taxonomy(
                'authors',
                'book',
                array(
                'public' => true,
                'hierarchical' => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'authors' ),
                'labels' => array(
                    'name'              => _x('Authors', 'General name', 'book-search'),
                    'singular_name'     => _x('Author', 'Singular name', 'book-search'),
                    'search_items'      => __('Search Authors', 'book-search'),
                    'all_items'         => __('All Authors', 'book-search'),
                    'parent_item'       => __('Parent Author', 'book-search'),
                    'parent_item_colon' => __('Parent Author:', 'book-search'),
                    'edit_item'         => __('Edit Author', 'book-search'),
                    'update_item'       => __('Update Author', 'book-search'),
                    'add_new_item'      => __('Add New Author', 'book-search'),
                    'new_item_name'     => __('New Author Name', 'book-search'),
                    'menu_name'         => __('Author', 'book-search'),
                ),
                )
            );
            register_taxonomy(
                'publisher',
                'book',
                array(
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'publisher' ),
                'labels' => array(
                    'name'              => _x('Publishers', 'General', 'book-search'),
                    'singular_name'     => _x('Publisher', 'Singular', 'book-search'),
                    'search_items'      => __('Search Publishers', 'book-search'),
                    'all_items'         => __('All Publishers', 'book-search'),
                    'parent_item'       => __('Parent Publisher', 'book-search'),
                    'parent_item_colon' => __('Parent Publisher:', 'book-search'),
                    'edit_item'         => __('Edit Publisher', 'book-search'),
                    'update_item'       => __('Update Publisher', 'book-search'),
                    'add_new_item'      => __('Add New Publisher', 'book-search'),
                    'new_item_name'     => __('New Publisher Name', 'book-search'),
                    'menu_name'         => __('Publisher', 'book-search'),
                ),
                )
            );
        }
        /**
         * Register custom meta boxes to save book price
         * and book star ratings
         * 
         * @return void
         */
        public function registerBooksMetaBoxes()
        {
            add_meta_box(
                'book-price',
                __('Book Price', 'book-search'),
                array($this, 'bookPriceDisplayCallback'),
                'book',
                'normal',
                'default'
            );

            add_meta_box(
                'book-star-rating',
                __('Book Star Rating', 'book-search'),
                array($this, 'bookStarRatingDisplayCallback'),
                'book',
                'normal',
                'default'
            );
        }
        /**
         * Add price meta box into custom post type book
         * 
         * @param $post object
         * 
         * @return display price metabox into respactive post
         */
        public function bookPriceDisplayCallback($post)
        {
            wp_nonce_field('bookPriceDisplay', 'bookPriceDisplayNonce');
            ?>
                <input type="text" name="lbsp-book-price" id="lbsp-book-price" size= "" value="<?php echo esc_attr(get_post_meta($post->ID, 'lbsp-book-price', true)); ?>" />
            <?php
        }
        /**
         * Add star ratings meta box into custom post type book
         * 
         * @param $post object
         * 
         * @return display star ratings metabox into respactive post
         */
        public function bookStarRatingDisplayCallback($post)
        {
            wp_nonce_field('bookStarRatingDisplay', 'bookStarRatingDisplayNonce');
            ?>
            <input type="text" name="lbsp-book-star-rating" id="lbsp-book-star-rating" size= "    " value="<?php echo esc_attr(get_post_meta($post->ID, 'lbsp-book-star-rating', true)); ?>" />
            <?php
        }
        /**
         * Save post meta value for the book post type
         * 
         * @param $post_id take the id of the post
         * 
         * @return void
         */
        public function savePost($post_id)
        {
            if (isset($_POST['lbsp-book-price'])) {
                if (! isset($_POST['bookPriceDisplayNonce']) || ! wp_verify_nonce($_POST['bookPriceDisplayNonce'], 'bookPriceDisplay')) {
                    return;
                }
                update_post_meta($post_id, 'lbsp-book-price', sanitize_text_field($_POST['lbsp-book-price']));
            }
            if (isset($_POST['lbsp-book-star-rating'])) {
                if (! isset($_POST['bookStarRatingDisplayNonce']) || ! wp_verify_nonce($_POST['bookStarRatingDisplayNonce'], 'bookStarRatingDisplay')) {
                    return;
                }
                update_post_meta($post_id, 'lbsp-book-star-rating', sanitize_text_field($_POST['lbsp-book-star-rating']));
            }
        }
        /**
         * Enqueue plugin scripts and styles
         * 
         * @return void
         */
        public function enqueueScripts()
        {
            wp_enqueue_style('jquery-ui-css', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css');
            wp_enqueue_script('jquery-ui-core', false, array('jquery'));
            wp_enqueue_script('jquery-ui-slider');
            wp_enqueue_script('book-search-script', plugin_dir_url(__FILE__) . 'js/book-search.js', array( 'jquery' ), '1.0', true);
            wp_localize_script('book-search-script', 'bookSearch', array( 'ajaxUrl' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('submittocheck')));
            wp_enqueue_style('book-search-css', plugin_dir_url(__FILE__) . 'css/book-search-css.css', '', '1.0', 'all');
        }
        /**
         * Display books details based on serach quuery
         * 
         * @return serach results
         */
        public function bookSearch()
        {
            check_ajax_referer('submittocheck', 'security');
            $post_type = 'book';
            $paged = isset($_POST["page"]) ? $_POST["page"] : 1;
            $post_per_page = 3;
            $search_query = $_POST["searchString"];
            parse_str($search_query);
            $book_search_title = $book_search_title;
            $book_search_author = $book_search_author;
            $book_search_publisher = $book_search_publisher;
            $book_search_rating = $book_search_rating;
            $book_search_price = $book_search_price;
            $book_price = explode("-", $book_search_price);
                    $args = array(
                        'post_type' => $post_type,
                        'posts_per_page' => $post_per_page,
                        'paged' => $paged,
                        'post_status' => 'publish'
                    );
                    if (isset($book_search_title) && !empty($book_search_title)) {
                        $args['s'] = $book_search_title;
                    }
                    if (isset($book_search_title) && !empty($book_search_title) 
                        && isset($book_search_author) && !empty($book_search_author)
                    ) {
                        $args['s'] = $book_search_title;
                        $args['tax_query'] = array(
                            array(
                            'taxonomy' => 'authors',
                            'field' => 'slug',
                            'terms' => array( $book_search_author ),
                            )
                        );
                    }
                    if (isset($book_search_title) && !empty($book_search_title) 
                        && isset($book_search_author) && !empty($book_search_author)
                        && isset($book_search_publisher) && !empty($book_search_publisher)
                    ) {
                        $args['s'] = $book_search_title;
                        $args['tax_query'] = array(
                            'relation' => 'AND',
                            array(
                            'taxonomy' => 'authors',
                            'field' => 'slug',
                            'terms' => array( $book_search_author ),
                            ),
                            array(
                                'taxonomy' => 'publisher',
                                'field' => 'slug',
                                'terms' => array( $book_search_publisher ),
                            ),
                            );
                    }
                    if (isset($book_search_title) && !empty($book_search_title) 
                        && isset($book_search_author) && !empty($book_search_author)
                        && isset($book_search_publisher) && !empty($book_search_publisher)
                        && isset($book_search_rating) && !empty($book_search_rating)
                    ) {
                        $args['s'] = $book_search_title;
                        $args['tax_query'] = array(
                            'relation' => 'AND',
                            array(
                            'taxonomy' => 'authors',
                            'field' => 'slug',
                            'terms' => array( $book_search_author ),
                            ),
                            array(
                                'taxonomy' => 'publisher',
                                'field' => 'slug',
                                'terms' => array( $book_search_publisher ),
                            ),
                            );
                            $args['meta_query'] = array(
                                array(
                                    'key' => 'lbsp-book-star-rating',
                                    'value' => $book_search_rating,
                                    'type' => 'numeric',
                                    'compare' => '=',
                                ),
                            );
                    }
                    if (isset($book_search_author) && !empty($book_search_author)) {
                        $args['tax_query'] = array(
                            array(
                            'taxonomy' => 'authors',
                            'field' => 'slug',
                            'terms' => array( $book_search_author ),
                            )
                        );
                    }
                    if (isset($book_search_publisher) && !empty($book_search_publisher)) {
                        $args['tax_query'] = array(
                            array(
                            'taxonomy' => 'publisher',
                            'field' => 'slug',
                            'terms' => array( $book_search_publisher ),
                            )
                        );
                    }
                    if (isset($book_search_author) && !empty($book_search_author)
                        && isset($book_search_publisher) && !empty($book_search_publisher)
                    ) {
                        $args['tax_query'] = array(
                            'relation' => 'AND',
                            array(
                                'taxonomy' => 'authors',
                                'field' => 'slug',
                                'terms' => array($book_search_author),
                            ),
                            array(
                                'taxonomy' => 'publisher',
                                'field' => 'slug',
                                'terms' => array($book_search_publisher),
                            ),
                        );
                    }
                    if (isset($book_search_rating) && !empty($book_search_rating)) {
                        $args['meta_query'] = array(
                            array(
                                'key' => 'lbsp-book-star-rating',
                                'value' => $book_search_rating,
                                'type' => 'numeric',
                                'compare' => '=',
                            ),
                        );
                    }
                    if (isset($book_search_price) && !empty($book_search_price)) {
                        $args['meta_query'] = array(
                            array(
                                'key' => 'lbsp-book-price',
                                'value' => array($book_price[0], $book_price[1]),
                                'type' => 'numeric',
                                'compare' => 'BETWEEN',
                            ),
                        );
                    }
                    if (isset($book_search_rating) && !empty($book_search_rating) 
                        && isset($book_search_price) && !empty($book_search_price) 
                    ) {
                        $args['meta_query'] = array(
                            'relation' => 'AND',
                            array(
                                'key' => 'lbsp-book-price',
                                'value' => array($book_price[0], $book_price[1]),
                                'type' => 'numeric',
                                'compare' => 'BETWEEN',
                            ),
                            array(
                                'key' => 'lbsp-book-star-rating',
                                'value' => $book_search_rating,
                                'type' => 'numeric',
                                'compare' => '=',
                            ),
                        );
                    }
                    if (isset($book_search_title) && !empty($book_search_title)
                        && isset($book_search_author) && !empty($book_search_author)
                        && isset($book_search_publisher) && !empty($book_search_publisher)
                        && isset($book_search_rating) && !empty($book_search_rating)
                        && isset($book_search_price) && !empty($book_search_price)
                    ) {
                        $args['s'] = $book_search_title;
                        $args['tax_query'] = array(
                            'relation' => 'AND',
                            array(
                                'taxonomy' => 'authors',
                                'field' => 'slug',
                                'terms' => array( $book_search_author ),
                            ),
                            array(
                                'taxonomy' => 'publisher',
                                'field' => 'slug',
                                'terms' => array( $book_search_publisher ),
                            ),
                        );
                        $args['meta_query'] = array(
                            'relation' => 'AND',
                            array(
                                'key' => 'lbsp-book-price',
                                'value' => array($book_price[0], $book_price[1]),
                                'type' => 'numeric',
                                'compare' => 'BETWEEN',
                            ),
                            array(
                                'key' => 'lbsp-book-star-rating',
                                'value' => $book_search_rating,
                                'type' => 'numeric',
                                'compare' => '=',
                            ),
                        );
                    }
                    $books = new WP_Query($args);
                    $count = ($post_per_page * $paged) - ($post_per_page - 1);
                    if ($books->have_posts()) :
                        echo '
                            <div class="result-body">
                                <div class="result-header">
                                    <div class="result-col">No</div>
                                    <div class="result-col">Book Name</div>
                                    <div class="result-col">Price</div>
                                    <div class="result-col">Author</div>
                                    <div class="result-col">Publisher</div>
                                    <div class="result-col">Rating</div>
                            </div>
                        ';
                        while ($books->have_posts()) :
                            $books->the_post();
                            echo '
                                <div class="result-row">
                                    <div class="result-col">' . esc_attr(+$count) . '</div>
                                    <div class="result-col">' . '<a href="'. esc_url(get_permalink(get_the_ID())) . '">' . get_the_title() . '</a>' . '</div>
                                    <div class="result-col">' . esc_attr(get_post_meta(get_the_ID(), 'lbsp-book-price', true)) . '</div>
                                    <div class="result-col">' . esc_attr(get_the_terms(get_the_ID(), 'authors')[0]->name) . '</div>
                                    <div class="result-col">'
                                    . esc_attr(get_the_terms(get_the_ID(), 'publisher')[0]->name) . '</div>
                                    <div class="result-col">';
                            if (!empty(get_post_meta(get_the_ID(), 'lbsp-book-star-rating', true))) {
                                for ($i = 1; $i <= get_post_meta(get_the_ID(), 'lbsp-book-star-rating', true); $i++) {
                                        echo '<span class="star-ratings">
                                        <img src=' . esc_url(plugin_dir_url(__FILE__)) . 'images/star.png />'
                                        . '</span>';
                                }
                            }
                            echo '</div>
                                </div>
                            ';
                            $count++;
                        endwhile;
                        echo '<div id="pagination">';
                        echo paginate_links(
                            array(
                            'base' => esc_attr(get_pagenum_link(1) . '%_%'),
                            'format' => esc_attr('?paged=%#%'),
                            'current' => esc_attr(max(1, $paged)),
                            'total' => esc_attr($books->max_num_pages)
                            )
                        );
                        echo '</div>';
                    else :
                        echo "<h6 class='no-data'>No data foind!!!</h6>";
                    endif;
                    wp_reset_postdata();
                    wp_die();
        }
        /**
         * Display search form
         * 
         * @return void
         */
        public function displaySearchForm()
        {
            ob_start();
            ?>
            <div class="form-body">
                <h4 style="text-align:center"><?php echo esc_attr(__('Book Search', 'twentyfourteen')); ?></h4>
                <form id="book-search-form" class="flex-container" action="">
                    <div class="flex-item">
                        <label for="book-search-title">Book Title</label>
                        <input type="text" id="book-search-title" name="book_search_title">
                    </div>
                    <div class="flex-item">
                        <label for="book-search-author">Author</label>
                        <input type="text" id="book-search-author" name="book_search_author">
                    </div>
                    <div class="flex-item">
                        <label for="book-search-publisher">Publisher</label>
                        <input type="text" id="book-search-publisher" name="book_search_publisher">
                    </div>
                    <div class="flex-item">
                        <label for="book-search-rating">Rating</label>
                        <select id="book-search-rating" name="book_search_rating">
                            <option value=""></option>
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_attr($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="flex-item">
                        <div class="slider-box">
                            <label for="priceRange">Price Range</label>
                            <div id="price-range" class="slider"></div>
                            <input type="text" class="priceRange priceStart" readonly>
                        </div>
                    </div>
                    <div class="btn-wrap flex-item" style="flex: 1 100%;">
                        <button id="lbsp-submit" type="submit">Search</button>
                    </div>
                </form>
            </div>
            <div id="book-search-results"></div>
            <?php
            return ob_get_clean();
        }
    }
}
new LibraryBookSearch();

