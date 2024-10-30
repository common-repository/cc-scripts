<?php

namespace Clearcode\Scripts;

use ReflectionClass;
use ReflectionMethod;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {

	/**
	 * Class Plugin
	 * @package Clearcode\Scripts
	 */
	class Plugin extends Singleton {
		/**
		 * @var null|array variable holding plugin data
		 */
		static protected $plugin = null;

		/**
		 * Return plugin property by name. If name is null return array contains plugin data. If name not exists return null.
		 *
		 * @param null|string $name Name of plugin property to return. Optional, default is null.
		 *
		 * @return array|null|string Property or array contains plugin data. Null on failure.
		 */
		static public function get( $name = null ) {
			$path = WP_PLUGIN_DIR . '/cc-scripts';
			$file = $path . '/plugin.php';
			$dir  = basename( $path );
			$url  = plugins_url( '', $file );

			if ( null === self::$plugin ) {
				self::$plugin = get_plugin_data( $file );
			}

			switch ( strtolower( $name ) ) {
				case 'file':
					return $file;
				case 'dir':
					return $dir;
				case 'path':
					return $path;
				case 'url':
					return $url;
				case 'slug':
					return __NAMESPACE__;
				case null:
					return self::$plugin;
				default:
					if ( ! empty( self::$plugin[ $name ] ) ) {
						return self::$plugin[ $name ];
					}

					return null;
			}
		}

		/**
		 * Set the activation and deactivation hook for a plugin. Reports information about method
		 */
		public function __construct() {
			register_activation_hook( self::get( 'file' ), array( $this, 'activation' ) );
			register_deactivation_hook( self::get( 'file' ), array( $this, 'deactivation' ) );

			add_action( 'activated_plugin', array( $this, 'switch_plugin_hook' ), 10, 2 );
			add_action( 'deactivated_plugin', array( $this, 'switch_plugin_hook' ), 10, 2 );

			$class = new ReflectionClass( $this );
			foreach ( $class->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ) {
				if ( $this->is_hook( $method->getName() ) ) {
					$hook     = self::apply_filters( 'hook', $this->get_hook( $method->getName() ), $class, $method );
					$priority = self::apply_filters( 'priority', $this->get_priority( $method->getName() ), $class, $method );
					$args     = self::apply_filters( 'args', $method->getNumberOfParameters(), $class, $method );

					add_filter( $hook, array( $this, $method->getName() ), $priority, $args );
				}
			}
		}

		/**
		 * The function hooked to the 'activate_PLUGIN' action.
		 */
		public function activation() {
		}

		/**
		 * The function hooked to the 'deactivate_PLUGIN' action.
		 */
		public function deactivation() {
		}

		/**
		 * If method has a priority return it.
		 *
		 * @param string $method Method name.
		 *
		 * @return int Priority value.
		 */
		protected function get_priority( $method ) {
			$priority = substr( strrchr( $method, '_' ), 1 );

			return is_numeric( $priority ) ? (int) $priority : 10;
		}

		/**
		 * Check if method has priority.
		 *
		 * @param string $method Method name.
		 *
		 * @return bool If method has priority - true, false otherwise.
		 */
		protected function has_priority( $method ) {
			$priority = substr( strrchr( $method, '_' ), 1 );

			return is_numeric( $priority ) ? true : false;
		}

		/**
		 * If method is a hook return its name without prefix ('filter_' or 'action_').
		 *
		 * @param string $method Method name.
		 *
		 * @return string Method name.
		 */
		protected function get_hook( $method ) {
			if ( $this->has_priority( $method ) ) {
				$method = substr( $method, 0, strlen( $method ) - strlen( $this->get_priority( $method ) ) - 1 );
			}
			if ( $this->is_hook( $method ) ) {
				$method = substr( $method, 7 );
			}

			return $method;
		}

		/**
		 * Check if method is a hook (has prefix 'filter_' or 'action_').
		 *
		 * @param string $method Method name.
		 *
		 * @return bool True on success, false on failure.
		 */
		protected function is_hook( $method ) {
			foreach ( array( 'filter_', 'action_' ) as $hook ) {
				if ( 0 === strpos( $method, $hook ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Return a translated string, using unique identifier for retrieving translated strings.
		 *
		 * @param string $text Text to translate.
		 *
		 * @return string|void Translated text.
		 */
		static public function __( $text ) {
			return __( $text, self::get( 'TextDomain' ) );
		}

		/**
		 * Call the functions added to a filter hook.
		 *
		 * @param string $tag The name of the filter hook.
		 * @param mixed $value The value which can be modified by filters hooked to $tag
		 *
		 * @return mixed The function result, or false on error.
		 */
		static public function apply_filters( $tag, $value ) {
			$args    = func_get_args();
			$args[0] = self::get( 'slug' ) . '\\' . $args[0];

			return call_user_func_array( 'apply_filters', $args );
		}

		/**
		 * Return template.
		 *
		 * @param string $template Template name.
		 * @param array $vars Optional. Array of arguments transferred to template.
		 *
		 * @return bool|string Template.
		 */
		static public function get_template( $template, $vars = array() ) {
			$template = self::apply_filters( 'template', self::get( 'path' ) . '/templates/' . $template, $vars );
			if ( ! is_file( $template ) ) {
				return false;
			}

			$vars = self::apply_filters( 'vars', $vars, $template );
			if ( is_array( $vars ) ) {
				extract( $vars, EXTR_SKIP );
			}

			ob_start();
			include $template;

			return ob_get_clean();
		}

		/**
		 * @param $plugin
		 * @param null $network_wide
		 */
		function switch_plugin_hook( $plugin, $network_wide = null ) {
			if ( ! $network_wide ) {
				return;
			}

			list( $hook ) = explode( '_', current_filter(), 2 );
			$hook = str_replace( 'activated', 'activate_', $hook );
			$hook .= plugin_basename( self::get( 'file' ) );

			$this->call_user_func_array( 'do_action', array( $hook, false ) );
		}

		/**
		 * @param $function
		 * @param array $args
		 */
		protected function call_user_func_array( $function, $args = array() ) {
			if ( is_multisite() ) {
				foreach ( get_sites( array( 'public' => 1 ) ) as $blog ) {
					switch_to_blog( $blog->blog_id );
					call_user_func_array( $function, $args );
				}
				restore_current_blog();
			} else {
				$function( $args );
			}
		}
	}
}
