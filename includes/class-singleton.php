<?php

namespace Clearcode\Scripts;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Singleton' ) ) {
	/**
	 * Class Singleton
	 * @package Clearcode\Scripts
	 *
	 * Design pattern that restricts the instantiation of a class to one object.
	 */
	abstract class Singleton {

		final private function __clone() {
		}

		protected function __construct() {
		}

		/**
		 * Return instance of singleton.
		 *
		 * @return static Instance of singleton.
		 */
		public static function instance() {
			static $instance = null;

			return ( null === $instance ) ? $instance = new static() : $instance;
		}
	}
}