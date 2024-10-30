<?php

namespace Clearcode;

use JSMin;
use JSMin_UnterminatedStringException;
use JSMin_UnterminatedCommentException;
use JSMin_UnterminatedRegExpException;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( __NAMESPACE__ . '\Scripts' ) ) {
	/**
	 * Class Scripts
	 * @package Clearcode
	 */
	class Scripts extends Scripts\Plugin {
		public function __construct() {
			parent::__construct();

			add_filter( 'plugin_action_links_' . plugin_basename( self::get( 'file' ) ), array( $this, 'plugin_action_links' ) );
		}

		/**
		 * If option is not exists, add option with default values on plugin activation.
		 */
		public function activation() {
			if ( ! self::get_option() ) {
				self::add_option( array(
					'head'   => array( 'scripts' => '', 'method' => 'wp_head',   'dependencies' => '', 'minify' => true ),
					'body'   => array( 'scripts' => '', 'method' => 'wp_body',   'dependencies' => '', 'minify' => true ),
					'footer' => array( 'scripts' => '', 'method' => 'wp_footer', 'dependencies' => '', 'minify' => true )
				) );
			}
		}

		/**
		 *  Remove option on deactivation.
		 */
		public function deactivation() {
			self::delete_option();
		}

		/**
		 * Return list of links to display on the plugins page.
		 *
		 * @param array $links List of links.
		 *
		 * @return mixed List of links.
		 */
		public function plugin_action_links( $links ) {
			array_unshift( $links, self::get_template( 'link.php', array(
				'url'  => get_admin_url( null, 'options-general.php?page=scripts' ),
				'link' => self::__( 'Settings' )
			) ) );

			return $links;
		}

		/**
		 * Return list of links to display on the plugins page.
		 *
		 * @param $plugin_meta
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return array List of links.
		 */
		public function filter_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( empty( self::get( 'Name' )  ) ) return $plugin_meta;
			if ( empty( $plugin_data['Name'] ) ) return $plugin_meta;
			if ( self::get( 'Name' ) == $plugin_data['Name'] ) {
				$plugin_meta[] = self::__( 'Author' ) . ' ' . self::get_template( 'link.php', array(
					'url'  => 'http://piotr.press/',
					'link' => 'PiotrPress'
				) );
			}

			return $plugin_meta;
		}

		/**
		 * Add JavaScript code to Scripts settings page.
		 */
		public function action_admin_enqueue_scripts( $page ) {
			wp_register_style( 'scripts', self::get( 'url' ) . '/assets/css/style.css', array( 'dashicons' ), self::get( 'Version' ) );
			wp_enqueue_style(  'scripts' );

			if ( 'settings_page_scripts' == $page ) {
				wp_register_script( 'scripts', self::get( 'url' ) . '/assets/js/script.js', array( 'jquery' ), self::get( 'Version' ), true );
				wp_enqueue_script(  'scripts' );
			}
		}

		/**
		 * Add Scripts settings page.
		 */
		public function action_admin_menu_999() {
			add_options_page(
				self::__( 'Scripts Settings' ),
				self::get_template( 'div.php', array(
					'id'      => 'scripts',
					'class'   => 'dashicons-before dashicons-editor-code',
					'content' => self::__( 'Scripts' ) ) ),
				'manage_options',
				'scripts',
				array( $this, 'page' )
			);
		}
		
		/**
		 * Echo custom settings page template.
		 */
		public function page() {
			echo self::get_template( 'page.php', array(
				'fields'   => 'scripts',
				'sections' => 'scripts'
			) );
		}
		
		/**
		 * Add fields to a Scripts section of a settings page.
		 * Register a setting and its sanitization callback.
		 */
		public function action_admin_init() {
			register_setting(     'scripts', self::get( 'slug' ),   array( $this, 'sanitize' ) );
			add_settings_section( 'scripts', self::__( 'Scripts' ), array( $this, 'section'  ), 'scripts' );

			add_settings_field( $field = 'head\scripts',
				self::__( 'Include scripts to' ) . ' ' . self::__( 'head' ),
				array(
					$this,
					'textarea'
				), 'scripts', 'scripts', array(
					'field'  => $field,
					'class'  => 'large-text',
					'id'     => self::get( 'slug' ) . '\\' . $field,
					'name'   => self::get( 'slug' ) . '[head][scripts]',
					'value'  => self::get_head( 'scripts', '' ),
					'cols'   => 50,
					'rows'   => 10,
					'before' => '<code>' . htmlspecialchars( '<script>' ) . '</code>',
					'after'  => '<code>' . htmlspecialchars( '</script>' ) . '</code>',
					'desc'   => ''
				)
			);

			add_settings_field( $field = 'head\dependencies', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'  => $field,
					'class'  => 'large-text',
					'type'   => 'text',
					'id'     => self::get( 'slug' ) . '\\' . $field,
					'name'   => self::get( 'slug' ) . '[head][dependencies]',
					'value'  => self::get_head( 'dependencies', '' ),
					'before' => self::__( 'Dependencies' ) . '<br />',
					'desc'   => sprintf(
						self::__( 'Comma separated %s handles. Works only with %s method.' ),
						'<code>wp_enqueue_script</code>',
						'<code>wp_head</code>'
					)
				)
			);

			add_settings_field( $field = 'head\method\wp_head', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'head_method',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[head][method]',
					'value'   => 'wp_head',
					'checked' => 'wp_head' == self::get_head( 'method', 'wp_head' ) ? 'checked' : '',
					'before'  => '',
					'after'   => 'wp_head',
					'desc'    => self::__( 'Preferred method.' )
				)
			);

			add_settings_field( $field = 'head\method\output_buffering', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'head_method',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[head][method]',
					'value'   => 'output_buffering',
					'checked' => 'output_buffering' == self::get_head( 'method' ) ? 'checked' : '',
					'before'  => '',
					'after'   => self::__( 'output buffering' ),
					'desc'    => sprintf(
						self::__( 'Use this option if you are not sure if the %s action is added to your theme.' ) . '<br />' .
						self::__( 'It will add the scripts directly before the %s tag using %s.' ),
						'<code>wp_head</code>',
						'<code>' . htmlspecialchars( '</head>' ) . '</code>',
						'<code>output buffering</code>'
					)
				)
			);

			add_settings_field( $field = 'head\minify', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'head_minify',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[head][minify]',
					'value'   => true,
					'checked' => ! empty( self::get_head( 'minify', false ) ) ? 'checked' : '',
					'before'  => '',
					'after'   => self::__( 'minify' ),
					'desc'    => sprintf(
						self::__( 'Enable/Disable %s scripts minification.' ),
						'head'
					)
				)
			);

			add_settings_field( $field = 'body\scripts',
				self::__( 'Include scripts to' ) . ' ' . self::__( 'body' ),
				array(
					$this,
					'textarea'
				), 'scripts', 'scripts', array(
					'field'  => $field,
					'class'  => 'large-text',
					'id'     => self::get( 'slug' ) . '\\' . $field,
					'name'   => self::get( 'slug' ) . '[body][scripts]',
					'value'  => self::get_body( 'scripts', '' ),
					'cols'   => 50,
					'rows'   => 10,
					'before' => '<code>' . htmlspecialchars( '<script>' ) . '</code>',
					'after'  => '<code>' . htmlspecialchars( '</script>' ) . '</code>',
					'desc'   => ''
				)
			);

			add_settings_field( $field = 'body\dependencies', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'  => $field,
					'class'  => 'large-text',
					'type'   => 'text',
					'id'     => self::get( 'slug' ) . '\\' . $field,
					'name'   => self::get( 'slug' ) . '[body][dependencies]',
					'value'  => self::get_body( 'dependencies', '' ),
					'before' => self::__( 'Dependencies' ) . '<br />',
					'desc'   => sprintf(
						self::__( 'Comma separated %s handles. Works only with %s method.' ),
						'<code>wp_enqueue_script</code>',
						'<code>wp_body</code>'
					)
				)
			);

			add_settings_field( $field = 'body\method\wp_body', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'body_method',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[body][method]',
					'value'   => 'wp_body',
					'checked' => 'wp_body' == self::get_body( 'method', 'wp_body' ) ? 'checked' : '',
					'before'  => '',
					'after'   => 'wp_body',
					'desc'    => sprintf(
						self::__( 'Add the following code directly after the %s tag in your theme (preferred method)' ) . ': ' .
						'<code>' . htmlspecialchars( "<?php do_action( 'wp_body' ); ?>" ) . '</code>',
						'<code>' . htmlspecialchars( '<body>' ) . '</code>'
					)
				)
			);

			add_settings_field( $field = 'body\method\output_buffering', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'body_method',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[body][method]',
					'value'   => 'output_buffering',
					'checked' => 'output_buffering' == self::get_body( 'method' ) ? 'checked' : '',
					'before'  => '',
					'after'   => self::__( 'output buffering' ),
					'desc'    => sprintf(
						self::__( 'Use this option if you cannot add the %s action to your theme.' ) . '<br />' .
						self::__( 'It will add the scripts directly after the %s tag using %s.' ),
						'<code>wp_body</code>',
						'<code>' . htmlspecialchars( '<body>' ) . '</code>',
						'<code>output buffering</code>'
					)
				)
			);

			add_settings_field( $field = 'body\minify', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'body_minify',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[body][minify]',
					'value'   => true,
					'checked' => ! empty( self::get_body( 'minify', false ) ) ? 'checked' : '',
					'before'  => '',
					'after'   => self::__( 'minify' ),
					'desc'    => sprintf(
						self::__( 'Enable/Disable %s scripts minification.' ),
						'body'
					)
				)
			);

			add_settings_field( $field = 'footer\scripts',
				self::__( 'Include scripts to' ) . ' ' . self::__( 'footer' ),
				array(
					$this,
					'textarea'
				), 'scripts', 'scripts', array(
					'field'  => $field,
					'class'  => 'large-text',
					'id'     => self::get( 'slug' ) . '\\' . $field,
					'name'   => self::get( 'slug' ) . '[footer][scripts]',
					'value'  => self::get_footer( 'scripts', '' ),
					'cols'   => 50,
					'rows'   => 10,
					'before' => '<code>' . htmlspecialchars( '<script>' ) . '</code>',
					'after'  => '<code>' . htmlspecialchars( '</script>' ) . '</code>',
					'desc'   => ''
				)
			);

			add_settings_field( $field = 'footer\dependencies', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'  => $field,
					'class'  => 'large-text',
					'type'   => 'text',
					'id'     => self::get( 'slug' ) . '\\' . $field,
					'name'   => self::get( 'slug' ) . '[footer][dependencies]',
					'value'  => self::get_footer( 'dependencies', '' ),
					'before' => self::__( 'Dependencies' ) . '<br />',
					'desc'   => sprintf(
						self::__( 'Comma separated %s handles. Works only with %s method.' ),
						'<code>wp_enqueue_script</code>',
						'<code>wp_footer</code>'
					)
				)
			);

			add_settings_field( $field = 'footer\method\wp_footer', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'footer_method',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[footer][method]',
					'value'   => 'wp_footer',
					'checked' => 'wp_footer' == self::get_footer( 'method', 'wp_footer' ) ? 'checked' : '',
					'before'  => '',
					'after'   => 'wp_footer',
					'desc'    => self::__( 'Preferred method.' )
				)
			);

			add_settings_field( $field = 'footer\method\output_buffering', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'footer_method',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[footer][method]',
					'value'   => 'output_buffering',
					'checked' => 'output_buffering' == self::get_footer( 'method' ) ? 'checked' : '',
					'before'  => '',
					'after'   => self::__( 'output buffering' ),
					'desc'    => sprintf(
						self::__( 'Use this option if you are not sure if the %s action is added to your theme.' ) . '<br />' .
						self::__( 'It will add the scripts directly before the %s tag using %s.' ),
						'<code>wp_footer</code>',
						'<code>' . htmlspecialchars( '</body>' ) . '</code>',
						'<code>output buffering</code>'
					)
				)
			);

			add_settings_field( $field = 'footer\minify', '', array(
					$this,
					'input'
				), 'scripts', 'scripts', array(
					'field'   => $field,
					'type'    => 'checkbox',
					'class'   => 'footer_minify',
					'id'      => self::get( 'slug' ) . '\\' . $field,
					'name'    => self::get( 'slug' ) . '[footer][minify]',
					'value'   => true,
					'checked' => ! empty( self::get_footer( 'minify', false ) ) ? 'checked' : '',
					'before'  => '',
					'after'   => self::__( 'minify' ),
					'desc'    => sprintf(
						self::__( 'Enable/Disable %s scripts minification.' ),
						'footer'
					)
				)
			);
		}

		/**
		 * A callback function that sanitizes the option's value.
		 *
		 * @param array $option Array of value to sanitize.
		 *
		 * @return mixed Sanitized value.
		 */
		public function sanitize( $option = array() ) {
			return array(
				'head' => array(
					'scripts'      => empty( $option['head']['scripts'] )      ? null  : $option['head']['scripts'],
					'dependencies' => empty( $option['head']['dependencies'] ) ? null  : sanitize_text_field( $option['head']['dependencies'] ),
					'minify'       => empty( $option['head']['minify'] )       ? false : true,
					'method'       => in_array( $option['head']['method'], array( 'wp_head', 'output_buffering' ) ) ? $option['head']['method'] : null
				),
				'body' => array(
					'scripts'      => empty( $option['body']['scripts'] )      ? null  : $option['body']['scripts'],
					'dependencies' => empty( $option['body']['dependencies'] ) ? null  : sanitize_text_field( $option['body']['dependencies'] ),
					'minify'       => empty( $option['body']['minify'] )       ? false : true,
					'method'       => in_array( $option['body']['method'], array( 'wp_body', 'output_buffering' ) ) ? $option['body']['method'] : null
				),
				'footer' => array(
					'scripts'      => empty( $option['footer']['scripts'] )      ? null  : $option['footer']['scripts'],
					'dependencies' => empty( $option['footer']['dependencies'] ) ? null  : sanitize_text_field( $option['footer']['dependencies'] ),
					'minify'       => empty( $option['body']['minify'] )         ? false : true,
					'method'       => in_array( $option['footer']['method'], array( 'wp_footer', 'output_buffering' ) ) ? $option['footer']['method'] : null
				)
			);
		}

		/**
		 * Echo custom section template.
		 */
		public function section() {
			echo self::get_template( 'section.php', array(
				'content' => self::__( 'Settings' )
			) );
		}

		/**
		 * Join array elements changing array representation key => value to key="value".
		 *
		 * @param array $atts Array of html properties
		 *
		 * @return string String containing a string representation of all the array
		 * elements in the same order, with the glue string between each element.
		 */
		protected function implode( $atts = array() ) {
			array_walk( $atts, function ( &$value, $key ) {
				$value = sprintf( '%s="%s"', $key, esc_attr( $value ) );
			} );

			return implode( ' ', $atts );
		}

		/**
		 * Echo custom field template with custom input.
		 *
		 * @param string $args Name of field.
		 */
		public function input( $args ) {
			$args = wp_parse_args( $args, array(
				'type'    => 'input',
				'class'   => '',
				'id'      => '',
				'name'    => '',
				'value'   => '',
				'checked' => '',
				'before'  => '',
				'after'   => '',
				'desc'    => ''
			) );
			extract( $args, EXTR_SKIP );

			echo self::get_template( 'input.php', array(
					'attrs' => self::implode( array(
							'type'  => $type,
							'class' => $class,
							'id'    => $id,
							'name'  => $name,
							'value' => $value
						)
					),
					'checked' => $checked,
					'before'  => $before,
					'after'   => $after,
					'desc'    => $desc
				)
			);
		}

		/**
		 * Echo custom field template with custom textarea.
		 *
		 * @param string $args Name of field.
		 */
		public function textarea( $args ) {
			extract( $args, EXTR_SKIP );

			echo self::get_template( 'textarea.php', array(
					'attrs' => self::implode( array(
							'class' => $class,
							'id'    => $id,
							'name'  => $name,
							'cols'  => $cols,
							'rows'  => $rows
						)
					),
					'value'  => $value,
					'before' => $before,
					'after'  => $after,
					'desc'   => $desc
				)
			);
		}

		/**
		 * Retrieve option value based on name of Scripts option and key.
		 *
		 * @param null|string $key Key of element in Scripts option array.
		 * @param false|mixed $default Default return element.
		 *
		 * @return array|mixed|void Element from Scripts option array.
		 */
		static public function get_head( $key = null, $default = false ) {
			if ( ! $option = self::get_option( 'head', $default ) ) {
				return $default;
			}
			if ( $key && is_array( $option ) ) {
				return array_key_exists( $key, $option ) ? $option[$key] : $default;
			}

			return $option;
		}

		/**
		 * Retrieve option value based on name of Scripts option and key.
		 *
		 * @param null|string $key Key of element in Scripts option array.
		 * @param false|mixed $default Default return element.
		 *
		 * @return array|mixed|void Element from Scripts option array.
		 */
		static public function get_body( $key = null, $default = false ) {
			if ( ! $option = self::get_option( 'body', $default ) ) {
				return $default;
			}
			if ( $key && is_array( $option ) ) {
				return array_key_exists( $key, $option ) ? $option[$key] : $default;
			}

			return $option;
		}

		/**
		 * Retrieve option value based on name of Scripts option and key.
		 *
		 * @param null|string $key Key of element in Scripts option array.
		 * @param false|mixed $default Default return element.
		 *
		 * @return array|mixed|void Element from Scripts option array.
		 */
		static public function get_footer( $key = null, $default = false ) {
			if ( ! $option = self::get_option( 'footer', $default ) ) {
				return $default;
			}
			if ( $key && is_array( $option ) ) {
				return array_key_exists( $key, $option ) ? $option[$key] : $default;
			}

			return $option;
		}

		/**
		 * Retrieve option value based on name of Scripts option and key.
		 *
		 * @param null|string $key Key of element in Scripts option array.
		 * @param false|mixed $default Default return element.
		 *
		 * @return array|mixed|void Element from Scripts option array.
		 */
		static public function get_option( $key = null, $default = false ) {
			if ( ! $option = get_option( self::get( 'slug' ) ) ) {
				return $default;
			}
			if ( $key && is_array( $option ) ) {
				return array_key_exists( $key, $option ) ? $option[$key] : $default;
			}

			return $option;
		}

		/**
		 * Add option value based on name of Scripts option and key.
		 *
		 * @param null|string $value Value of element in Scripts option array.
		 * @param null|string $key Key of element in Scripts option array.
		 * @param null|string $autoload Autoload of element in Scripts option array.
		 *
		 * @return bool True if success or false if not.
		 */
		static public function add_option( $value, $key = null, $autoload = 'yes' ) {
			if ( self::get_option() ) {
				return self::update_option( $value, $key, $autoload );
			} elseif ( $key ) {
				return add_option( self::get( 'slug' ), array( $key => $value ), null, $autoload );
			} else {
				return add_option( self::get( 'slug' ), $value, null, $autoload );
			}
		}

		/**
		 * Update option value based on name of Scripts option and key.
		 *
		 * @param null|string $value Value of element in Scripts option array.
		 * @param null|string $key Key of element in Scripts option array.
		 * @param null|string $autoload Autoload of element in Scripts option array.
		 *
		 * @return bool True if success or false if not.
		 */
		static public function update_option( $value, $key = null, $autoload = null ) {
			if ( ! $option = self::get_option() ) {
				return self::add_option( $value, $key, $autoload );
			}
			if ( $key ) {
				$option[$key] = $value;
				return update_option( self::get( 'slug' ), $option, $autoload );
			}
			return update_option( self::get( 'slug' ), $value, $autoload );
		}

		/**
		 * Delete option value based on name of Scripts option and key.
		 *
		 * @param null|string $key Key of element in Scripts option array.
		 *
		 * @return bool True if success or false if not.
		 */
		static public function delete_option( $key = null ) {
			if ( ! $option = self::get_option() ) {
				return false;
			}
			if ( $key && array_key_exists( $key, $option )) {
				unset( $option[$key] );
				return self::update_option( $option );
			}
			return delete_option( self::get( 'slug' ) );
		}

		/**
		 *  Return head & footer scripts tags.
		 */
		public function filter_script_loader_tag_0( $tag, $handle, $src ) {
			foreach( array( self::get( 'slug' ) . '\wp_head', self::get( 'slug' ) . '\wp_footer' ) as $script ) {
				if ( $handle == $script ) {
					$tag = explode( "\n", $tag );
					array_shift( $tag );
					return self::get_template( 'stamp.php', array( 'time' => date( 'Y-m-d H:i:s' ), 'version' => self::get( 'Version' ) ) ) . "\n" . implode( "\n", $tag );
				}
			}

			return $tag;
		}

		/**
		 *  Enqueue head & footer scripts.
		 */
		public function action_wp_enqueue_scripts() {
			if ( 'wp_head' == self::get_head( 'method', 'wp_head' ) ) {
				$handle       = self::get( 'slug' ) . '\wp_head';
				$dependencies = self::get_head( 'dependencies', '' );
				$dependencies = $dependencies ? explode( ',', $dependencies ) : array();
				$dependencies = array_map( 'trim', $dependencies );
				$scripts      = self::get_head( 'minify', true ) ? self::minify( self::get_scripts( 'head' ) ) : self::get_scripts( 'head' );
				wp_register_script(   $handle, true, $dependencies, self::get( 'Version' ), false );
				wp_enqueue_script(    $handle );
				wp_add_inline_script( $handle, $scripts );
			}

			if ( 'wp_footer' == self::get_footer( 'method', 'wp_footer' ) ) {
				$handle       = self::get( 'slug' ) . '\wp_footer';
				$dependencies = self::get_footer( 'dependencies', '' );
				$dependencies = $dependencies ? explode( ',', $dependencies ) : array();
				$dependencies = array_map( 'trim', $dependencies );
				$scripts      = self::get_footer( 'minify', true ) ? self::minify( self::get_scripts( 'footer' ) ) : self::get_scripts( 'footer' );
				wp_register_script(   $handle, true, $dependencies, self::get( 'Version' ), true );
				wp_enqueue_script(    $handle );
				wp_add_inline_script( $handle, $scripts );
			}
		}

		static public function wp_script_is( $handles, $list = 'enqueued' ) {
			$handles = $handles ? explode( ',', $handles ) : array();
			$handles = array_map( 'trim', $handles );
			foreach( $handles as $handle ) if ( ! wp_script_is( $handle, $list ) ) return false;
			return true;
		}

		/**
		 *  Echo body scripts.
		 */
		public function action_wp_body() {
			$dependencies = self::get_body( 'dependencies', '' );
			if ( ! empty( $dependencies ) && ! self::wp_script_is( $dependencies, 'done' ) ) return;

			if ( 'wp_body' == self::get_body( 'method', 'wp_body' ) && $scripts = self::get_scripts( 'body' ) ) {
				if ( self::get_body( 'minify', true ) ) $scripts = self::minify( $scripts );
				echo self::get_template( 'stamp.php',   array( 'time'    => date( 'Y-m-d H:i:s' ), 'version' => self::get( 'Version' ) ) ) . "\n" .
				     self::get_template( 'scripts.php', array( 'scripts' => $scripts ) )  . "\n";
			}
		}

		/**
		 *  Get scripts template - special container for scripts.
		 */
		static public function get_scripts( $method ) {
			if ( ! in_array( $method, array( 'head', 'footer', 'body' ) ) ) return '';

			$option = 'get_' . $method;
			$option = self::$option();
			return ! empty( $option['scripts'] ) ? $option['scripts'] : '';
		}

		/**
		 * Start output buffering.
		 *
		 * @param string Template name.
		 *
		 * @return string Template name.
		 */
		public function filter_template_include_0( $template ) {
			if ( 'output_buffering' == self::get_head(   'method' ) or
			     'output_buffering' == self::get_body(   'method' ) or
			     'output_buffering' == self::get_footer( 'method' ) ) {
				ob_start();
			}

			return $template;
		}

		/**
		 * Echo buffered output.
		 *
		 */
		public function filter_shutdown_0() {
			if ( is_admin() ) return;

			$content = '';
			if ( 'output_buffering' == self::get_head(   'method' ) or
			     'output_buffering' == self::get_body(   'method' ) or
			     'output_buffering' == self::get_footer( 'method' ) ) {
				$content = ob_get_clean();
			}

			if ( 'output_buffering' == self::get_head( 'method' ) ) {
				$scripts = self::get_head( 'minify', true ) ? self::minify( self::get_scripts( 'head' ) ) : self::get_scripts( 'head' );
				$scripts = self::get_template( 'scripts.php', array( 'scripts' => $scripts ) ) . "\n";
				$stamp   = self::get_template( 'stamp.php',   array( 'time'    => date( 'Y-m-d H:i:s' ), 'version' => self::get( 'Version' ) ) ) . "\n";
				$pos     = stripos( $content, '</head>' );
				$content = substr_replace( $content, "\n" . $stamp . $scripts, $pos, 0 );
			}

			if ( 'output_buffering' == self::get_body( 'method' ) ) {
				$scripts = self::get_body( 'minify', true ) ? self::minify( self::get_scripts( 'body' ) ) : self::get_scripts( 'body' );
				$scripts = self::get_template( 'scripts.php', array( 'scripts' => $scripts ) ) . "\n";
				$stamp   = self::get_template( 'stamp.php',   array( 'time'    => date( 'Y-m-d H:i:s' ), 'version' => self::get( 'Version' ) ) ) . "\n";
				$pos     = stripos( $content, '<body' );
				$pos     = stripos( $content, '>', $pos );
				$content = substr_replace( $content, "\n" . $stamp . $scripts, $pos + 1, 0 );
			}

			if ( 'output_buffering' == self::get_footer( 'method' ) ) {
				$scripts = self::get_footer( 'minify', true ) ? self::minify( self::get_scripts( 'footer' ) ) : self::get_scripts( 'footer' );
				$scripts = self::get_template( 'scripts.php', array( 'scripts' => $scripts ) ) . "\n";
				$stamp   = self::get_template( 'stamp.php',   array( 'time'    => date( 'Y-m-d H:i:s' ), 'version' => self::get( 'Version' ) ) ) . "\n";
				$pos     = stripos( $content, '</body>' );
				$content = substr_replace( $content, "\n" . $stamp . $scripts, $pos, 0 );
			}

			echo $content;
		}

		static public function minify( $scripts ) {
			if ( ! class_exists( 'JSMin' ) ) {
				require_once( self::get( 'path' ) . '/vendor/Minify/JSMin.php' );
			}

			try {
				return JSmin::minify( $scripts );
			} catch ( Exception $exception ) {
				if ( WP_DEBUG && WP_DEBUG_DISPLAY ) {
					return $exception->getMessage();
				}
			}

			return $scripts;
		}
	}
}
