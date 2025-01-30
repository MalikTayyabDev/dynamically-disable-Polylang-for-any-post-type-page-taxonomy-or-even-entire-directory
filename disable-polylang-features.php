// 1. Add Custom Settings to Disable Polylang on Selected Items
add_action('admin_init', function () {
    register_setting('reading', 'disable_polylang_items', [
        'type' => 'array',
        'sanitize_callback' => 'array_map',
        'default' => [],
    ]);

    add_settings_field(
        'disable_polylang_items',
        __('Disable Polylang for Items', 'textdomain'),
        function () {
            $post_types = get_post_types(['public' => true], 'objects');
            $taxonomies = get_taxonomies(['public' => true], 'objects');
            $selected = get_option('disable_polylang_items', []);

            echo '<strong>Post Types:</strong><br>';
            foreach ($post_types as $post_type) {
                echo '<label><input type="checkbox" name="disable_polylang_items[]" value="post_type:' . esc_attr($post_type->name) . '" ' . checked(in_array('post_type:' . $post_type->name, $selected), true, false) . '> ' . esc_html($post_type->label) . '</label><br>';
            }

            echo '<br><strong>Taxonomies:</strong><br>';
            foreach ($taxonomies as $taxonomy) {
                echo '<label><input type="checkbox" name="disable_polylang_items[]" value="taxonomy:' . esc_attr($taxonomy->name) . '" ' . checked(in_array('taxonomy:' . $taxonomy->name, $selected), true, false) . '> ' . esc_html($taxonomy->label) . '</label><br>';
            }

            echo '<br><strong>Other:</strong><br>';
            echo '<label><input type="checkbox" name="disable_polylang_items[]" value="archives" ' . checked(in_array('archives', $selected), true, false) . '> Disable on all Archives</label><br>';
            echo '<label><input type="checkbox" name="disable_polylang_items[]" value="directory" ' . checked(in_array('directory', $selected), true, false) . '> Disable Polylang URL Filtering</label><br>';
        },
        'reading'
    );
});

// 2. Remove Polylang Functionality for Selected Post Types
add_filter('pll_get_post_types', function ($post_types) {
    $disabled_items = get_option('disable_polylang_items', []);
    foreach ($disabled_items as $item) {
        if (strpos($item, 'post_type:') === 0) {
            $type = str_replace('post_type:', '', $item);
            unset($post_types[$type]);
        }
    }
    return $post_types;
});

// 3. Remove Polylang for Selected Taxonomies
add_filter('pll_get_taxonomies', function ($taxonomies) {
    $disabled_items = get_option('disable_polylang_items', []);
    foreach ($disabled_items as $item) {
        if (strpos($item, 'taxonomy:') === 0) {
            $tax = str_replace('taxonomy:', '', $item);
            unset($taxonomies[$tax]);
        }
    }
    return $taxonomies;
});

// 4. Prevent Polylang from Assigning Languages to Selected Post Types
add_filter('pll_get_the_language', function ($language, $post_id) {
    $disabled_items = get_option('disable_polylang_items', []);
    foreach ($disabled_items as $item) {
        if (strpos($item, 'post_type:') === 0) {
            $type = str_replace('post_type:', '', $item);
            if (get_post_type($post_id) === $type) {
                return null;
            }
        }
    }
    return $language;
}, 10, 2);

// 5. Disable Language Filtering on Archives if Selected
add_action('pre_get_posts', function ($query) {
    $disabled_items = get_option('disable_polylang_items', []);
    if (!is_admin() && $query->is_main_query()) {
        if (in_array('archives', $disabled_items) && (is_archive() || is_home())) {
            $query->set('lang', ''); // Disable Polylang on archives
        }
    }
});

// 6. Disable Polylang URL Filtering (Removes Language Prefix from URLs)
add_filter('pll_get_the_languages', function ($languages) {
    if (in_array('directory', get_option('disable_polylang_items', []))) {
        return []; // Prevent Polylang from modifying URLs
    }
    return $languages;
});

<!-- How to Use It

Go to Settings > Reading in your WordPress dashboard.
Check the boxes for:
Post types (Posts, Pages, CPTs)
Taxonomies (Categories, Tags, Custom Taxonomies)
Archives (To remove language filtering from all archives)
Directory (To prevent Polylang from modifying URLs)
Save Changes – The selected items will no longer use Polylang.

What This Covers

✔ Post Types – Disable Polylang for specific post types like posts, pages, and CPTs.
✔ Taxonomies – Remove Polylang from specific categories, tags, or custom taxonomies.
✔ Archives – Remove Polylang filtering from archive pages (e.g., blog, taxonomy pages).
✔ Directory Filtering – Prevent Polylang from modifying URLs (useful for multilingual directory structures).

Why This is the Best Approach

🔹 No Code Editing Required – Just use checkboxes in settings.
🔹 Works on All Polylang Features – Including post types, taxonomies, archives, and URLs.
🔹 Future-Proof – Works for new post types and taxonomies as they are added.

Let me know if you need any tweaks or improvements! 

 -->

