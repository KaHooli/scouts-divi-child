<?php defined('ABSPATH') || exit; ?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
  <label class="screen-reader-text" for="sgd-search-input"><?php esc_html_e('Search for:', 'scouts-group-divi'); ?></label>
  <input id="sgd-search-input" type="search" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php esc_attr_e('SEARCH', 'scouts-group-divi'); ?>" autocomplete="off">
  <button type="submit"><?php esc_html_e('Search', 'scouts-group-divi'); ?></button>
</form>
