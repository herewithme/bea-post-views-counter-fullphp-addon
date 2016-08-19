<?php
/*
 Plugin Name: BEA Post Views Counter - FullPHP Addon
 Version: 0.1
 Plugin URI: https://github.com/herewithme/bea-post-views-counter
 Description: This addon to BEA Post Views Counter add a full PHP implementation for increment counter.
 Author: Amaury Balmer
 Author URI: http://www.beapi.fr
 Domain Path: languages
 Network: false
 Text Domain: bea-post-views-counter-fullphp-addon
 Depends: bea-post-views-counter

 ----

 Copyright 2013 Amaury Balmer (amaury@beapi.fr)

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
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Plugin URL and PATH
define( 'BEA_PVC_FPA_URL', plugin_dir_url( __FILE__ ) );
define( 'BEA_PVC_FPA_DIR', plugin_dir_path( __FILE__ ) );

// Get class
require_once( BEA_PVC_FPA_DIR . "/classes/main.php" );

// Init
add_action( 'plugins_loaded', 'bea_pvcf_phpaddon', 11 );
/**
 * Init plugin files
 *
 * @author Nicolas JUEN
 */
function bea_pvcf_phpaddon() {
	new BEA_PVC_FPA_Main();
}
