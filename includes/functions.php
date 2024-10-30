<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wp_body_open' ) ) {
	/*
	 * Deprecated function & action
	 */
	function wp_body_open() {
		_deprecated_function( 'wp_body_open', '2.0.0', 'wp_body' );
		do_action_deprecated( 'wp_body_open', array(), '2.0.0', 'wp_body' );
		do_action( 'wp_body' );
	}
}

if ( ! function_exists( 'wp_body' ) ) {
	/**
	 * Execute functions hooked on a specific custom action hook - 'wp_body'.
	 * According to: https://core.trac.wordpress.org/ticket/12563
	 * Add the following code directly after the <body> tag in your theme:
	 */
	function wp_body() {
		do_action( 'wp_body' );
	}
}
