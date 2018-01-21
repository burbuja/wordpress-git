<?php

/*
 * Git Smart HTTP Wrapper
 *
 * Plugin Name:       Git Smart HTTP Wrapper
 * Plugin URI:        https://burbuja.cl/proyectos/wordpress/
 * Description:       Access to your own Git repositories through WordPress
 * Version:           0.1
 * Author:            Rodrigo Sepúlveda Heerwagen
 * Author URI:        https://burbuja.cl/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gitsmarthttpwrapper
 * Domain Path:       /languages
 *
 * Git Smart HTTP Wrapper is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Git Smart HTTP Wrapper is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Git Smart HTTP Wrapper. If not, see http://www.gnu.org/licenses/gpl-2.0 .
 */

if ( ! defined( 'WPINC' ) )
 	die;

define( 'GITSMARTHTTPWRAPPER__DIR_PATH', plugin_dir_path( __FILE__ ) );

if ( is_admin() )
  include_once( GITSMARTHTTPWRAPPER__DIR_PATH . 'admin/functions.php' );

if ( ! empty( get_option( 'gitsmarthttpwrapper_permalink' ) ) ) :

if ( ! is_admin() )
  include_once( GITSMARTHTTPWRAPPER__DIR_PATH . 'functions.php' );

function gitsmarthttpwrapper_init() {
  if (
    strstr( 'http' . ( isset($_SERVER['HTTPS']) ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'], site_url( 'git' ) )
    && is_404()
  ) {
    status_header(301);
    header( 'Location: ' . trailingslashit( $_SERVER['REQUEST_URI'] ) );
    exit();
  }
  add_rewrite_rule(
    '^' . get_option( 'gitsmarthttpwrapper_permalink' ) . '\/?$',
    'index.php?gitsmarthttpwrapper',
    'top'
  );
  add_rewrite_rule(
    '^' . get_option( 'gitsmarthttpwrapper_permalink' ) . '\/+(*.)$',
    'index.php?gitsmarthttpwrapper=$matches[1]',
    'top'
  );
  add_rewrite_tag( '%gitsmarthttpwrapper%', '(*.)' );
}

function gitsmarthttpwrapper_generate_rewrite_rules( $wp_rewrite ) {
  $feed_rules = array(
    get_option( 'gitsmarthttpwrapper_permalink' ) . '\/?$' => 'index.php?gitsmarthttpwrapper',
    get_option( 'gitsmarthttpwrapper_permalink' ) . '\/+(.*)$' => 'index.php?gitsmarthttpwrapper=$matches[1]',
  );

  $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
  return $wp_rewrite->rules;
}

function gitsmarthttpwrapper_query_vars( $query_vars ) {
  $query_vars[] = 'gitsmarthttpwrapper';
  return $query_vars;
}

register_activation_hook( __FILE__, 'flush_rewrite_rules' );

add_action( 'init', 'gitsmarthttpwrapper_init' );
add_filter( 'generate_rewrite_rules', 'gitsmarthttpwrapper_generate_rewrite_rules' );
add_filter( 'query_vars', 'gitsmarthttpwrapper_query_vars', 0, 1 );

endif;

function gitsmarthttpwrapper_plugins_loaded() {
  load_plugin_textdomain( 'gitsmarthttpwrapper', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action( 'plugins_loaded', 'gitsmarthttpwrapper_plugins_loaded' );
