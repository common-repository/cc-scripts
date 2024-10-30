<?php

/*
	Plugin Name: CC-Scripts
	Plugin URI: https://wordpress.org/plugins/cc-scripts
	Description: Add custom JavaScript code to all your WordPress pages at once via the Admin panel.
	Version: 1.2.0
	Author: Clearcode.cc
	Author URI: http://clearcode.cc
	Text Domain: cc-scripts
	Domain Path: /languages/
	License: GPLv3
	License URI: http://www.gnu.org/licenses/gpl-3.0.txt

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace Clearcode\Scripts;

use Clearcode\Scripts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

foreach ( array( 'class-singleton.php', 'class-plugin.php', 'class-scripts.php', 'functions.php' ) as $file ) {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/' . $file );
}

if ( ! has_action( Scripts::get( 'slug' ) ) ) {
	do_action( Scripts::get( 'slug' ), Scripts::instance() );
}
