<?php
/**
 * Plugin Name: Scouts Divi Child Updater
 * Description: Enables GitHub release updates and WordPress automatic-update controls for the Scouts Group Divi child theme on single-site and multisite installations.
 * Version: 0.0.0
 * Plugin URI: https://github.com/KaHooli/scouts-divi-child
 * Update URI: https://github.com/KaHooli/scouts-divi-child
 * Author: West Centenary Scout Group
 * Network: true
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

define('SGDUP_THEME', 'scouts-divi-child');
define('SGDUP_PLUGIN', 'scouts-divi-child-updater/scouts-divi-child-updater.php');
define('SGDUP_VERSION', '0.0.0');
define('SGDUP_UPDATE_URI', 'https://github.com/KaHooli/scouts-divi-child');
define('SGDUP_RELEASES_API', 'https://api.github.com/repos/KaHooli/scouts-divi-child/releases/latest');

function sgdup_latest_release() {
    $cached=get_site_transient('sgdup_latest_release');
    if(false!==$cached) return $cached;
    $response=wp_remote_get(SGDUP_RELEASES_API,[
        'timeout'=>10,
        'redirection'=>3,
        'headers'=>[
            'Accept'=>'application/vnd.github+json',
            'User-Agent'=>'scouts-divi-child-updater/'.SGDUP_VERSION.'; '.network_home_url('/'),
            'X-GitHub-Api-Version'=>'2022-11-28',
        ],
    ]);
    if(is_wp_error($response)||200!==wp_remote_retrieve_response_code($response)){
        set_site_transient('sgdup_latest_release',[],HOUR_IN_SECONDS);
        return [];
    }
    $release=json_decode(wp_remote_retrieve_body($response),true);
    if(!is_array($release)||empty($release['tag_name'])||!empty($release['draft'])||!empty($release['prerelease'])){
        set_site_transient('sgdup_latest_release',[],HOUR_IN_SECONDS);
        return [];
    }
    $package=''; $updater_package='';
    foreach(($release['assets']??[]) as $asset){
        $name=strtolower((string)($asset['name']??''));
        if(str_ends_with($name,'.zip')&&str_contains($name,'scouts-divi-child')&&!str_contains($name,'updater')){
            $package=esc_url_raw($asset['browser_download_url']??'');
        }
        if(str_ends_with($name,'.zip')&&str_contains($name,'scouts-divi-child-updater')) $updater_package=esc_url_raw($asset['browser_download_url']??'');
    }
    $data=[
        'version'=>ltrim((string)$release['tag_name'],'vV'),
        'url'=>esc_url_raw($release['html_url']??SGDUP_UPDATE_URI.'/releases'),
        'package'=>$package,
        'updater_package'=>$updater_package,
    ];
    set_site_transient('sgdup_latest_release',$data,6*HOUR_IN_SECONDS);
    return $data;
}

add_filter('update_themes_github.com',function($update,$theme_data,$stylesheet){
    if(SGDUP_THEME!==$stylesheet) return $update;
    $release=sgdup_latest_release();
    if(empty($release['version'])) return false;
    return [
        'id'=>SGDUP_UPDATE_URI,
        'theme'=>SGDUP_THEME,
        'version'=>$release['version'],
        'url'=>$release['url'],
        'package'=>$release['package'],
        'tested'=>'6.8',
        'requires_php'=>'8.0',
    ];
},10,3);

add_filter('update_plugins_github.com',function($update,$plugin_data,$plugin_file){
    if(SGDUP_PLUGIN!==$plugin_file) return $update;
    $release=sgdup_latest_release();
    if(empty($release['version'])) return false;
    return ['id'=>SGDUP_UPDATE_URI,'slug'=>'scouts-divi-child-updater','version'=>$release['version'],'url'=>$release['url'],'package'=>$release['updater_package'],'tested'=>'6.8','requires_php'=>'8.0'];
},10,3);

/**
 * WordPress only renders its automatic-update control for extensions represented
 * in the update transient. Keep an explicit current-version record there even
 * when GitHub has no newer release.
 */
function sgdup_plugin_update_payload($release) {
    return (object) [
        'id' => SGDUP_UPDATE_URI,
        'slug' => 'scouts-divi-child-updater',
        'plugin' => SGDUP_PLUGIN,
        'new_version' => $release['version'],
        'url' => $release['url'],
        'package' => $release['updater_package'],
        'tested' => '6.8',
        'requires_php' => '8.0',
    ];
}

add_filter('site_transient_update_plugins', function ($transient) {
    if (!is_object($transient)) $transient = new stdClass();
    if (!isset($transient->response) || !is_array($transient->response)) $transient->response = [];
    if (!isset($transient->no_update) || !is_array($transient->no_update)) $transient->no_update = [];

    $release = sgdup_latest_release();
    if (empty($release['version']) || empty($release['updater_package'])) return $transient;

    $payload = sgdup_plugin_update_payload($release);
    if (version_compare($release['version'], SGDUP_VERSION, '>')) {
        $transient->response[SGDUP_PLUGIN] = $payload;
        unset($transient->no_update[SGDUP_PLUGIN]);
    } else {
        $transient->no_update[SGDUP_PLUGIN] = $payload;
        unset($transient->response[SGDUP_PLUGIN]);
    }
    return $transient;
});

/**
 * Also mark support directly in the installed-plugins table. This is important
 * on Network Admin screens before the first scheduled update check has run.
 */
add_filter('all_plugins', function ($plugins) {
    if (isset($plugins[SGDUP_PLUGIN])) {
        $plugins[SGDUP_PLUGIN]['update-supported'] = true;
    }
    return $plugins;
});

add_action('delete_site_transient_update_themes',function(){
    delete_site_transient('sgdup_latest_release');
});
add_action('delete_site_transient_update_plugins',function(){delete_site_transient('sgdup_latest_release');});
