<?php

/**
 * Plugin Name:     Notice
 * Plugin URI: 		https://essential-blocks.com
 * Description:     Put Spotlight On News, Announcements & Let The Visitors Find It Easily
 * Version:         1.3.5
 * Author:          WPDeveloper
 * Author URI: 		https://wpdeveloper.net
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     notice-block
 *
 * @package         notice-block
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */


define('NOTICE_BLOCK_VERSION', "1.3.5");
define('NOTICE_BLOCK_ADMIN_URL', plugin_dir_url(__FILE__));
define('NOTICE_BLOCK_ADMIN_PATH', dirname(__FILE__));

require_once __DIR__ . '/includes/font-loader.php';
require_once __DIR__ . '/includes/post-meta.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/lib/style-handler/style-handler.php';

function create_block_notice_block_init()
{

	$script_asset_path = NOTICE_BLOCK_ADMIN_PATH . "/dist/index.asset.php";
	if (!file_exists($script_asset_path)) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "notice-block/notice" block first.'
		);
	}
	$script_asset = require($script_asset_path);
	$all_dependencies = array_merge($script_asset['dependencies'], array(
		'wp-blocks',
		'wp-i18n',
		'wp-element',
		'wp-block-editor',
		'notice-block-controls-util',
		'essential-blocks-eb-animation'
	));

	$index_js     = NOTICE_BLOCK_ADMIN_URL . 'dist/index.js';
	wp_register_script(
		'create-block-notice-block-editor',
		$index_js,
		$all_dependencies,
		$script_asset['version'],
		true
	);

	$load_animation_js = NOTICE_BLOCK_ADMIN_URL . 'assets/js/eb-animation-load.js';
	wp_register_script(
		'essential-blocks-eb-animation',
		$load_animation_js,
		array(),
		NOTICE_BLOCK_VERSION,
		true
	);

	$animate_css = NOTICE_BLOCK_ADMIN_URL . 'assets/css/animate.min.css';
	wp_register_style(
		'essential-blocks-animation',
		$animate_css,
		array(),
		NOTICE_BLOCK_VERSION
	);


	$frontend_js = NOTICE_BLOCK_ADMIN_URL . 'dist/frontend/index.js';
	wp_register_script(
		'essential-blocks-notice-frontend',
		$frontend_js,
		array('essential-blocks-eb-animation'),
		NOTICE_BLOCK_VERSION,
		true
	);

	$style_css     = NOTICE_BLOCK_ADMIN_URL . 'dist/style.css';
	wp_register_style(
		'notice-block-editor-css',
		$style_css,
		array('essential-blocks-animation'),
		NOTICE_BLOCK_VERSION,
		"all"
	);

	if (!WP_Block_Type_Registry::get_instance()->is_registered('essential-blocks/number-counter')) {
		register_block_type(
			Notice_Block_Helper::get_block_register_path("number-counter/number-counter", NOTICE_BLOCK_ADMIN_PATH),
			array(
				'editor_script' => 'create-block-notice-block-editor',
				'editor_style' 	=> 'notice-block-editor-css',
				'render_callback' => function ($attributes, $content) {
					if (!is_admin()) {
						wp_enqueue_style('notice-block-editor-css');
						wp_enqueue_script('essential-blocks-notice-frontend');
					}
					return $content;
				}
			)
		);
	}
}

add_action('init', 'create_block_notice_block_init');
