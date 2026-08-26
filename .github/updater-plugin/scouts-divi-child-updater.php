<?php
/**
 * Plugin Name: Scouts Divi Child Updater
 * Description: Enables GitHub release updates and WordPress automatic-update controls for the Scouts Group Divi child theme on single-site and multisite installations.
 * Version: 1.0.0
 * Author: West Centenary Scout Group
 * Network: true
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

define('SGDUP_THEME', 'scouts-divi-child');
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
            'User-Agent'=>'scouts-divi-child-updater/1.0.0; '.network_home_url('/'),
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
    $package='';
    foreach(($release['assets']??[]) as $asset){
        $name=strtolower((string)($asset['name']??''));
        if(str_ends_with($name,'.zip')&&str_contains($name,'scouts-divi-child')&&!str_contains($name,'updater')){
            $package=esc_url_raw($asset['browser_download_url']??'');
            break;
        }
    }
    $data=[
        'version'=>ltrim((string)$release['tag_name'],'vV'),
        'url'=>esc_url_raw($release['html_url']??SGDUP_UPDATE_URI.'/releases'),
        'package'=>$package,
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

add_action('delete_site_transient_update_themes',function(){
    delete_site_transient('sgdup_latest_release');
});
