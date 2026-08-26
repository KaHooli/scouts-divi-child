<?php
defined('ABSPATH') || exit;
define('SGD_VERSION', '1.3.4');
define('SGD_UPDATE_URI', 'https://github.com/KaHooli/scouts-divi-child');
define('SGD_RELEASES_API', 'https://api.github.com/repos/KaHooli/scouts-divi-child/releases/latest');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('divi-parent', get_template_directory_uri() . '/style.css', [], defined('ET_CORE_VERSION') ? ET_CORE_VERSION : null);
    wp_enqueue_style('sgd-theme', get_stylesheet_uri(), ['divi-parent'], SGD_VERSION);
    wp_enqueue_script('sgd-header', get_stylesheet_directory_uri() . '/assets/js/header.js', [], SGD_VERSION, true);
});

add_action('after_setup_theme', function () {
    register_nav_menus([
        'scout_group_primary' => __('Overlay Primary Menu', 'scouts-group-divi'),
        'scout_group_footer_one' => __('Footer: Group', 'scouts-group-divi'),
        'scout_group_footer_two' => __('Footer: Scouting', 'scouts-group-divi'),
        'scout_group_footer_three' => __('Footer: Important Links', 'scouts-group-divi'),
    ]);
});

function sgd_mod($key, $fallback = '') {
    $value = get_theme_mod('sgd_' . $key, $fallback);
    return is_string($value) ? trim($value) : $value;
}

function sgd_colour_schemes() {
    return [
        'australia'=>['label'=>'Scouts Australia','navy'=>'#222d65','blue'=>'#3e6397','purple'=>'#622599','magenta'=>'#c7197d','gold'=>'#f5b335'],
        'act'=>['label'=>'Australian Capital Territory','navy'=>'#4a4200','blue'=>'#776440','purple'=>'#685c04','magenta'=>'#9eb936','gold'=>'#d9ce8c'],
        'nsw'=>['label'=>'New South Wales','navy'=>'#0b1728','blue'=>'#5a5b75','purple'=>'#262661','magenta'=>'#44c8f5','gold'=>'#e7f5fd'],
        'nt'=>['label'=>'Northern Territory','navy'=>'#363739','blue'=>'#696969','purple'=>'#4f276f','magenta'=>'#86bd03','gold'=>'#f2b134'],
        'qld'=>['label'=>'Queensland','navy'=>'#0a0002','blue'=>'#681e35','purple'=>'#681e35','magenta'=>'#9e3153','gold'=>'#c9a15b'],
        'sa'=>['label'=>'South Australia','navy'=>'#193653','blue'=>'#0d6efd','purple'=>'#193653','magenta'=>'#db2b27','gold'=>'#e8e7e3'],
        'tas'=>['label'=>'Tasmania','navy'=>'#262627','blue'=>'#353538','purple'=>'#006f51','magenta'=>'#008f69','gold'=>'#b2b2b2'],
        'vic'=>['label'=>'Victoria','navy'=>'#18183c','blue'=>'#00335d','purple'=>'#005495','magenta'=>'#007bc3','gold'=>'#cccccc'],
        'wa'=>['label'=>'Western Australia','navy'=>'#28265c','blue'=>'#6b7c93','purple'=>'#3d5c93','magenta'=>'#008f88','gold'=>'#d6a947'],
    ];
}

function sgd_active_scheme() {
    $schemes=sgd_colour_schemes(); $selected=sgd_mod('colour_scheme','australia');
    return isset($schemes[$selected]) ? $selected : 'australia';
}

add_action('wp_enqueue_scripts', function () {
    $palette=sgd_colour_schemes()[sgd_active_scheme()];
    $css=':root{--sgd-navy:'.$palette['navy'].';--sgd-blue:'.$palette['blue'].';--sgd-purple:'.$palette['purple'].';--sgd-magenta:'.$palette['magenta'].';--sgd-gold:'.$palette['gold'].';}';
    wp_add_inline_style('sgd-theme',$css);
}, 20);

add_filter('body_class', function ($classes) {
    $classes[]='sgd-scheme-'.sanitize_html_class(sgd_active_scheme());
    return $classes;
});

add_action('customize_register', function ($wp_customize) {
    // Replace any earlier implementation of this theme's colour-scheme section.
    $wp_customize->remove_section('sgd_colours');
    $wp_customize->add_section('sgd_colours', [
        'title'=>__('Colour Schemes','scouts-group-divi'),
        'description'=>__('Select the national or Branch (State/Territory) palette. Upload only an approved logo supplied by that Branch.','scouts-group-divi'),
        'priority'=>31,
    ]);
    $choices=[];
    foreach(sgd_colour_schemes() as $slug=>$scheme) $choices[$slug]=$scheme['label'];
    $wp_customize->add_setting('sgd_colour_scheme',['default'=>'australia','sanitize_callback'=>function($value) use($choices){return isset($choices[$value])?$value:'australia';}]);
    $wp_customize->add_control('sgd_colour_scheme',['label'=>__('Brand palette','scouts-group-divi'),'section'=>'sgd_colours','type'=>'select','choices'=>$choices]);
    foreach($choices as $slug=>$label){
        $setting='sgd_branch_logo_'.$slug;
        $wp_customize->add_setting($setting,['sanitize_callback'=>'absint']);
        $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize,$setting,['label'=>sprintf(__('%s approved logo','scouts-group-divi'),$label),'description'=>__('Optional. This logo is used automatically when this scheme is selected.','scouts-group-divi'),'section'=>'sgd_colours','mime_type'=>'image']));
    }
    $wp_customize->add_section('sgd_group', ['title'=>__('Scout Group Details','scouts-group-divi'),'description'=>__('Configure this reusable theme for your local Scout Group.','scouts-group-divi'),'priority'=>30]);
    $fields = [
        'group_name'=>['Group name','West Centenary Scout Group','sanitize_text_field'],
        'region'=>['District / region','John Oxley District · Brisbane South Region','sanitize_text_field'],
        'join_url'=>['Join / enquire URL',home_url('/join/'),'esc_url_raw'],
        'members_url'=>['Members URL','https://scoutsqld.com.au/','esc_url_raw'],
        'donate_url'=>['Donate URL','','esc_url_raw'],
        'contact_url'=>['Contact URL',home_url('/contact/'),'esc_url_raw'],
        'phone'=>['Membership enquiries','','sanitize_text_field'],
        'facebook_url'=>['Facebook URL','','esc_url_raw'],
        'instagram_url'=>['Instagram URL','','esc_url_raw'],
        'youtube_url'=>['YouTube URL','','esc_url_raw'],
        'footer_about'=>['Footer about text','Scouting helps young people get outdoors, have fun, develop resilience and learn teamwork. We are child safe and open to all.','sanitize_textarea_field'],
    ];
    foreach ($fields as $id => [$label,$default,$sanitize]) {
        $wp_customize->add_setting('sgd_'.$id, ['default'=>$default,'sanitize_callback'=>$sanitize]);
        $wp_customize->add_control('sgd_'.$id, ['label'=>__($label,'scouts-group-divi'),'section'=>'sgd_group','type'=>$id==='footer_about'?'textarea':'text']);
    }
    $wp_customize->add_setting('sgd_logo', ['sanitize_callback'=>'absint']);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize,'sgd_logo',['label'=>__('Approved group logo','scouts-group-divi'),'section'=>'sgd_group','mime_type'=>'image']));
});

function sgd_render_header() {
    if (is_admin()) return;
    $name=sgd_mod('group_name',get_bloginfo('name')); $region=sgd_mod('region');
    $logo_id=absint(sgd_mod('branch_logo_'.sgd_active_scheme()));
    if(!$logo_id) $logo_id=absint(sgd_mod('logo'));
    ?>
    <a class="sgd-skip" href="#main-content"><?php esc_html_e('Skip to content','scouts-group-divi'); ?></a>
    <header class="sgd-header" role="banner">
      <div class="sgd-header-inner">
        <button class="sgd-menu-toggle" type="button" aria-expanded="false" aria-controls="sgd-overlay"><span class="sgd-burger" aria-hidden="true"><i></i><i></i><i></i></span><span><?php esc_html_e('Menu','scouts-group-divi'); ?></span></button>
        <a class="sgd-brand" href="<?php echo esc_url(home_url('/')); ?>">
          <?php if($logo_id) echo wp_get_attachment_image($logo_id,'medium',false,['class'=>'sgd-logo','alt'=>'']); ?>
          <span class="sgd-brand-text"><?php echo esc_html($name); ?><small><?php echo esc_html($region); ?></small></span>
        </a>
        <div class="sgd-controls"><?php get_search_form(); ?><div class="sgd-actions">
          <?php if($url=sgd_mod('donate_url')):?><a class="sgd-action sgd-donate" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Donate','scouts-group-divi'); ?></a><?php endif; ?>
          <a class="sgd-action sgd-join" href="<?php echo esc_url(sgd_mod('join_url',home_url('/join/'))); ?>"><?php esc_html_e('Join Now','scouts-group-divi'); ?></a>
        </div></div>
        <button class="sgd-search-toggle" type="button" aria-expanded="false" aria-controls="sgd-mobile-search" aria-label="<?php esc_attr_e('Search','scouts-group-divi'); ?>">⌕</button>
      </div>
      <div id="sgd-mobile-search" class="sgd-mobile-search" aria-hidden="true"><?php get_search_form(); ?></div>
      <div id="sgd-overlay" class="sgd-overlay" aria-hidden="true"><div class="sgd-overlay-inner">
        <div class="sgd-overlay-heading"><strong><?php esc_html_e('Explore','scouts-group-divi'); ?></strong><button class="sgd-overlay-close" type="button" aria-label="<?php esc_attr_e('Close menu','scouts-group-divi'); ?>">×</button></div>
        <nav aria-label="<?php esc_attr_e('Primary navigation','scouts-group-divi'); ?>"><?php wp_nav_menu(['theme_location'=>'scout_group_primary','container'=>false,'fallback_cb'=>'sgd_menu_fallback','depth'=>2]); ?></nav>
        <div class="sgd-overlay-actions"><a href="<?php echo esc_url(sgd_mod('members_url','https://scoutsqld.com.au/')); ?>"><?php esc_html_e('Members','scouts-group-divi'); ?></a><a href="<?php echo esc_url(sgd_mod('contact_url',home_url('/contact/'))); ?>"><?php esc_html_e('Contact Us','scouts-group-divi'); ?></a></div>
      </div></div>
    </header>
    <?php
}
add_action('wp_body_open','sgd_render_header',5);

function sgd_menu_fallback() {
    echo '<ul class="menu">';
    foreach([['Join Scouts','/join/'],['About Us','/about/'],['What We Do','/sections/'],['For Members','/resources/'],['News','/news/']] as [$label,$path]) echo '<li><a href="'.esc_url(home_url($path)).'">'.esc_html($label).'</a></li>';
    echo '</ul>';
}

function sgd_footer_menu($location,$fallback) {
    if(has_nav_menu($location)){wp_nav_menu(['theme_location'=>$location,'container'=>false,'depth'=>1,'fallback_cb'=>false]);return;}
    echo '<ul class="menu">'; foreach($fallback as [$label,$url]) echo '<li><a href="'.esc_url($url).'">'.esc_html($label).'</a></li>'; echo '</ul>';
}

function sgd_render_footer() {
    if(is_admin()) return; $socials=['facebook_url'=>'Facebook','instagram_url'=>'Instagram','youtube_url'=>'YouTube'];
    ?>
    <footer class="sgd-footer" role="contentinfo">
      <div class="sgd-footer-top"><div class="sgd-footer-grid">
        <section><h2><?php esc_html_e('Our Group','scouts-group-divi'); ?></h2><?php sgd_footer_menu('scout_group_footer_one',[['About Us',home_url('/about/')],['Our Sections',home_url('/sections/')],['News & Events',home_url('/news/')],['Contact Us',sgd_mod('contact_url',home_url('/contact/'))]]); ?></section>
        <section><h2><?php esc_html_e('Scouting','scouts-group-divi'); ?></h2><?php sgd_footer_menu('scout_group_footer_two',[['Scouts Queensland','https://scoutsqld.com.au/'],['Scouts Australia','https://scouts.com.au/'],['Scout Shop','https://scoutshop.com.au/'],['Terrain','https://terrain.scouts.com.au/'],['Training','https://training.scouts.com.au/']]); ?></section>
        <section><h2><?php esc_html_e('Important Links','scouts-group-divi'); ?></h2><?php sgd_footer_menu('scout_group_footer_three',[['Child Safety','https://scoutsqld.com.au/child-protection-safety/'],['Join Scouts',sgd_mod('join_url',home_url('/join/'))],['Members',sgd_mod('members_url','https://scoutsqld.com.au/')],['Privacy',home_url('/privacy-policy/')]]); ?></section>
        <section><h2><?php esc_html_e('Follow Us','scouts-group-divi'); ?></h2><div class="sgd-socials"><?php foreach($socials as $key=>$label) if($url=sgd_mod($key)) echo '<a href="'.esc_url($url).'" rel="noopener" target="_blank">'.esc_html($label).'</a>'; ?></div><?php if($phone=sgd_mod('phone')):?><h2 class="sgd-enquiries"><?php esc_html_e('Membership Enquiries','scouts-group-divi'); ?></h2><p><?php echo esc_html($phone); ?></p><?php endif; ?></section>
      </div></div>
      <div class="sgd-footer-bottom"><div class="sgd-footer-bottom-inner"><div><h2><?php esc_html_e('About Our Group','scouts-group-divi'); ?></h2><p><?php echo esc_html(sgd_mod('footer_about')); ?></p></div><div class="sgd-footer-identity"><strong><?php echo esc_html(sgd_mod('group_name',get_bloginfo('name'))); ?></strong><span><?php echo esc_html(sgd_mod('region')); ?></span></div></div></div>
    </footer>
    <?php
}
add_action('wp_footer','sgd_render_footer',5);

/**
 * Return the latest public GitHub release in a WordPress-friendly shape.
 *
 * Results are cached to avoid consuming GitHub's anonymous API allowance.
 */
function sgd_latest_release() {
    $cached = get_site_transient('sgd_latest_release');
    if (false !== $cached) return $cached;

    $response = wp_remote_get(SGD_RELEASES_API, [
        'timeout' => 10,
        'redirection' => 3,
        'headers' => [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'scouts-divi-child/' . SGD_VERSION . '; ' . home_url('/'),
            'X-GitHub-Api-Version' => '2022-11-28',
        ],
    ]);
    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
        set_site_transient('sgd_latest_release', [], HOUR_IN_SECONDS);
        return [];
    }

    $release = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($release) || empty($release['tag_name']) || !empty($release['draft']) || !empty($release['prerelease'])) {
        set_site_transient('sgd_latest_release', [], HOUR_IN_SECONDS);
        return [];
    }

    $package = '';
    foreach (($release['assets'] ?? []) as $asset) {
        $name = (string)($asset['name'] ?? '');
        if (str_ends_with(strtolower($name), '.zip') && str_contains(strtolower($name), 'scouts-divi-child')) {
            $package = esc_url_raw($asset['browser_download_url'] ?? '');
            break;
        }
    }
    $data = [
        'version' => ltrim((string)$release['tag_name'], 'vV'),
        'url' => esc_url_raw($release['html_url'] ?? SGD_UPDATE_URI . '/releases'),
        'package' => $package,
        'published_at' => sanitize_text_field($release['published_at'] ?? ''),
    ];
    set_site_transient('sgd_latest_release', $data, 6 * HOUR_IN_SECONDS);
    return $data;
}

/**
 * Integrate with WordPress 6.1+'s Update URI mechanism.
 */
add_filter('update_themes_github.com', function ($update, $theme_data, $theme_stylesheet) {
    if ('scouts-divi-child' !== $theme_stylesheet) return $update;
    $release = sgd_latest_release();
    if (empty($release['version'])) return false;

    return [
        'id' => SGD_UPDATE_URI,
        'theme' => $theme_stylesheet,
        'version' => $release['version'],
        'url' => $release['url'],
        'package' => $release['package'],
        'tested' => '6.8',
        'requires_php' => '8.0',
    ];
}, 10, 3);

// Clear our release cache whenever WordPress explicitly refreshes theme data.
add_action('delete_site_transient_update_themes', function () {
    delete_site_transient('sgd_latest_release');
});
