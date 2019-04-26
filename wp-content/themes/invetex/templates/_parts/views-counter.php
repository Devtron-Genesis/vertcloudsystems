<?php
if (is_singular() && invetex_get_theme_option('use_ajax_views_counter')=='no') {
    invetex_set_post_views(get_the_ID());
}
?>