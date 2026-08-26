<?php
defined('ABSPATH') || exit;

define('SGD_VERSION', '1.0.0');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('divi-parent', get_template_directory_uri() . '/style.css', [], defined('ET_CORE_VERSION') ? ET_CORE_VERSION : null);
    wp_enqueue_style('sgd-theme', get_stylesheet_uri(), ['divi-parent'], SGD_VERSION);
    wp_enqueue_script('sgd-header', get_stylesheet_directory_uri() . '/assets/js/header.js', [], SGD_VERSION, true);
});

add_action('after_setup_theme', function () {
    register_nav_menus(['scout_group_primary' => __('Scout Group Primary Menu', 'scouts-group-divi')]);
});

function sgd_mod($key, $fallback = '') {
    $value = get_theme_mod('sgd_' . $key, $fallback);
    return is_string($value) ? trim($value) : $value;
}

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('sgd_group', [
        'title' => __('Scout Group Details', 'scouts-group-divi'),
        'description' => __('Configure this child theme for your local Scout Group.', 'scouts-group-divi'),
        'priority' => 30,
    ]);
    $fields = [
        'group_name' => ['Group name', 'West Centenary Scout Group', 'sanitize_text_field'],
        'region' => ['District / region', 'John Oxley District · Brisbane South Region', 'sanitize_text_field'],
        'join_url' => ['Join / enquire URL', home_url('/join/'), 'esc_url_raw'],
        'members_url' => ['Members URL', 'https://scoutsqld.com.au/', 'esc_url_raw'],
        'donate_url' => ['Donate URL', '', 'esc_url_raw'],
        'contact_url' => ['Contact URL', home_url('/contact/'), 'esc_url_raw'],
    ];
    foreach ($fields as $id => [$label, $default, $sanitize]) {
        $wp_customize->add_setting('sgd_' . $id, ['default' => $default, 'sanitize_callback' => $sanitize]);
        $wp_customize->add_control('sgd_' . $id, ['label' => __($label, 'scouts-group-divi'), 'section' => 'sgd_group', 'type' => 'text']);
    }
    $wp_customize->add_setting('sgd_logo', ['sanitize_callback' => 'absint']);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'sgd_logo', [
        'label' => __('Approved group logo', 'scouts-group-divi'), 'section' => 'sgd_group', 'mime_type' => 'image'
    ]));
});

function sgd_render_header() {
    if (is_admin()) return;
    $name = sgd_mod('group_name', get_bloginfo('name'));
    $region = sgd_mod('region');
    $logo_id = absint(sgd_mod('logo'));
    ?>
    <a class="sgd-skip" href="#main-content"><?php esc_html_e('Skip to content', 'scouts-group-divi'); ?></a>
    <header class="sgd-header" role="banner">
      <div class="sgd-actions"><div class="sgd-wrap">
        <?php foreach ([['members_url','Members',false],['contact_url','Contact',false],['donate_url','Donate',true],['join_url','Join / Enquire',true]] as [$key,$label,$primary]) : $url=sgd_mod($key); if (!$url) continue; ?>
          <a class="sgd-action <?php echo $primary ? 'sgd-action--primary ' : ''; ?><?php echo $key === 'donate_url' ? 'sgd-action--donate' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
      </div></div>
      <div class="sgd-main sgd-wrap">
        <a class="sgd-brand" href="<?php echo esc_url(home_url('/')); ?>">
          <?php if ($logo_id) echo wp_get_attachment_image($logo_id, 'medium', false, ['alt' => '']); ?>
          <span class="sgd-brand-text"><?php echo esc_html($name); ?><span class="sgd-brand-meta"><?php echo esc_html($region); ?></span></span>
        </a>
        <nav id="sgd-nav" class="sgd-nav" aria-label="<?php esc_attr_e('Primary navigation', 'scouts-group-divi'); ?>">
          <?php wp_nav_menu(['theme_location'=>'scout_group_primary','container'=>false,'fallback_cb'=>'sgd_menu_fallback','depth'=>3]); ?>
        </nav>
        <div><button class="sgd-icon-button sgd-search-toggle" type="button" aria-expanded="false" aria-controls="sgd-search" aria-label="<?php esc_attr_e('Open search', 'scouts-group-divi'); ?>">⌕</button> <button class="sgd-icon-button sgd-menu-toggle" type="button" aria-expanded="false" aria-controls="sgd-nav" aria-label="<?php esc_attr_e('Open menu', 'scouts-group-divi'); ?>">☰</button></div>
      </div>
      <div id="sgd-search" class="sgd-search" aria-hidden="true"><div class="sgd-wrap"><?php get_search_form(); ?></div></div>
    </header>
    <?php
}
add_action('wp_body_open', 'sgd_render_header', 5);

function sgd_menu_fallback() {
    echo '<ul class="menu"><li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'scouts-group-divi') . '</a></li><li><a href="' . esc_url(home_url('/about/')) . '">' . esc_html__('About', 'scouts-group-divi') . '</a></li><li><a href="' . esc_url(home_url('/sections/')) . '">' . esc_html__('Sections', 'scouts-group-divi') . '</a></li><li><a href="' . esc_url(home_url('/news/')) . '">' . esc_html__('News', 'scouts-group-divi') . '</a></li></ul>';
}
