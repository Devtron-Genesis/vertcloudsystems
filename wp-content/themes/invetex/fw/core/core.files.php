<?php
/**
 * Invetex Framework: file system manipulations, styles and scripts usage, etc.
 *
 * @package	invetex
 * @since	invetex 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }


/* File system utils
------------------------------------------------------------------------------------- */

// Return list folders inside specified folder in the child theme dir (if exists) or main theme dir
if (!function_exists('invetex_get_list_folders')) {	
	function invetex_get_list_folders($folder, $only_names=true) {
		$dir = invetex_get_folder_dir($folder);
		$url = invetex_get_folder_url($folder);
		$list = array();
		if ( is_dir($dir) ) {
			$hdir = @opendir( $dir );
			if ( $hdir ) {
				while (($file = readdir( $hdir ) ) !== false ) {
					if ( substr($file, 0, 1) == '.' || !is_dir( ($dir) . '/' . ($file) ) )
						continue;
					$key = $file;
					$list[$key] = $only_names ? invetex_strtoproper($key) : ($url) . '/' . ($file);
				}
				@closedir( $hdir );
			}
		}
		return $list;
	}
}

// Return list files in folder
if (!function_exists('invetex_get_list_files')) {	
	function invetex_get_list_files($folder, $ext='', $only_names=false) {
		$dir = invetex_get_folder_dir($folder);
		$url = invetex_get_folder_url($folder);
		$list = array();
		if ( is_dir($dir) ) {
			$hdir = @opendir( $dir );
			if ( $hdir ) {
				while (($file = readdir( $hdir ) ) !== false ) {
					$pi = pathinfo( ($dir) . '/' . ($file) );
					if ( substr($file, 0, 1) == '.' || is_dir( ($dir) . '/' . ($file) ) || (!empty($ext) && $pi['extension'] != $ext) )
						continue;
					$key = invetex_substr($file, 0, invetex_strrpos($file, '.'));
					if (invetex_substr($key, -4)=='.min') $key = invetex_substr($file, 0, invetex_strrpos($key, '.'));
					$list[$key] = $only_names ? invetex_strtoproper(str_replace('_', ' ', $key)) : ($url) . '/' . ($file);
				}
				@closedir( $hdir );
			}
		}
		return $list;
	}
}

// Return list files in subfolders
if (!function_exists('invetex_collect_files')) {	
	function invetex_collect_files($dir, $ext=array()) {
		if (!is_array($ext)) $ext = array($ext);
		if (invetex_substr($dir, -1)=='/') $dir = invetex_substr($dir, 0, invetex_strlen($dir)-1);
		$list = array();
		if ( is_dir($dir) ) {
			$hdir = @opendir( $dir );
			if ( $hdir ) {
				while (($file = readdir( $hdir ) ) !== false ) {
					$pi = pathinfo( $dir . '/' . $file );
					if ( substr($file, 0, 1) == '.' )
						continue;
					if ( is_dir( $dir . '/' . $file ))
						$list = array_merge($list, invetex_collect_files($dir . '/' . $file, $ext));
					else if (empty($ext) || in_array($pi['extension'], $ext))
						$list[] = $dir . '/' . $file;
				}
				@closedir( $hdir );
			}
		}
		return $list;
	}
}

// Return path to directory with uploaded images
if (!function_exists('invetex_get_uploads_dir_from_url')) {	
	function invetex_get_uploads_dir_from_url($url) {
		$upload_info = wp_upload_dir();
		$upload_dir = $upload_info['basedir'];
		$upload_url = $upload_info['baseurl'];
		
		$http_prefix = "http://";
		$https_prefix = "https://";
		
		if (!strncmp($url, $https_prefix, invetex_strlen($https_prefix)))			//if url begins with https:// make $upload_url begin with https:// as well
			$upload_url = str_replace($http_prefix, $https_prefix, $upload_url);
		else if (!strncmp($url, $http_prefix, invetex_strlen($http_prefix)))		//if url begins with http:// make $upload_url begin with http:// as well
			$upload_url = str_replace($https_prefix, $http_prefix, $upload_url);		
	
		// Check if $img_url is local.
		if ( false === invetex_strpos( $url, $upload_url ) ) return false;
	
		// Define path of image.
		$rel_path = str_replace( $upload_url, '', $url );
		$img_path = ($upload_dir) . ($rel_path);
		
		return $img_path;
	}
}

// Replace uploads url to current site uploads url
if (!function_exists('invetex_replace_uploads_url')) {	
	function invetex_replace_uploads_url($str, $uploads_folder='uploads') {
		static $uploads_url = '', $uploads_len = 0;
		if (is_array($str) && count($str) > 0) {
			foreach ($str as $k=>$v) {
				$str[$k] = invetex_replace_uploads_url($v, $uploads_folder);
			}
		} else if (is_string($str)) {
			if (empty($uploads_url)) {
				$uploads_info = wp_upload_dir();
				$uploads_url = $uploads_info['baseurl'];
				$uploads_len = invetex_strlen($uploads_url);
			}
			$break = '\'" ';
			$pos = 0;
			while (($pos = invetex_strpos($str, "/{$uploads_folder}/", $pos))!==false) {
				$pos0 = $pos;
				$chg = true;
				while ($pos0) {
					if (invetex_strpos($break, invetex_substr($str, $pos0, 1))!==false) {
						$chg = false;
						break;
					}
					if (invetex_substr($str, $pos0, 5)=='http:' || invetex_substr($str, $pos0, 6)=='https:')
						break;
					$pos0--;
				}
				if ($chg) {
					$str = ($pos0 > 0 ? invetex_substr($str, 0, $pos0) : '') . ($uploads_url) . invetex_substr($str, $pos+invetex_strlen($uploads_folder)+1);
					$pos = $pos0 + $uploads_len;
				} else 
					$pos++;
			}
		}
		return $str;
	}
}

// Replace site url to current site url
if (!function_exists('invetex_replace_site_url')) {	
	function invetex_replace_site_url($str, $old_url) {
		static $site_url = '', $site_len = 0;
		if (is_array($str) && count($str) > 0) {
			foreach ($str as $k=>$v) {
				$str[$k] = invetex_replace_site_url($v, $old_url);
			}
		} else if (is_string($str)) {
			if (empty($site_url)) {
				$site_url = get_site_url();
				$site_len = invetex_strlen($site_url);
				if (invetex_substr($site_url, -1)=='/') {
					$site_len--;
					$site_url = invetex_substr($site_url, 0, $site_len);
				}
			}
			if (invetex_substr($old_url, -1)=='/') $old_url = invetex_substr($old_url, 0, invetex_strlen($old_url)-1);
			$break = '\'" ';
			$pos = 0;
			while (($pos = invetex_strpos($str, $old_url, $pos))!==false) {
				$str = invetex_unserialize($str);
				if (is_array($str) && count($str) > 0) {
					foreach ($str as $k=>$v) {
						$str[$k] = invetex_replace_site_url($v, $old_url);
					}
					$str = serialize($str);
					break;
				} else {
					$pos0 = $pos;
					$chg = true;
					while ($pos0 >= 0) {
						if (invetex_strpos($break, invetex_substr($str, $pos0, 1))!==false) {
							$chg = false;
							break;
						}
						if (invetex_substr($str, $pos0, 5)=='http:' || invetex_substr($str, $pos0, 6)=='https:')
							break;
						$pos0--;
					}
					if ($chg && $pos0>=0) {
						$str = ($pos0 > 0 ? invetex_substr($str, 0, $pos0) : '') . ($site_url) . invetex_substr($str, $pos+invetex_strlen($old_url));
						$pos = $pos0 + $site_len;
					} else 
						$pos++;
				}
			}
		}
		return $str;
	}
}


// Autoload templates, widgets, etc.
// Scan subfolders and require file with same name in each folder
if (!function_exists('invetex_autoload_folder')) {	
	function invetex_autoload_folder($folder, $from_subfolders=true) {
		if ($folder[0]=='/') $folder = invetex_substr($file, 1);
		$theme_dir = get_template_directory();
		$child_dir = get_stylesheet_directory();
		$dirs = array(
			($child_dir).'/'.($folder),
			($theme_dir).'/'.($folder),
			($child_dir).'/'.(INVETEX_FW_DIR).'/'.($folder),
			($theme_dir).'/'.(INVETEX_FW_DIR).'/'.($folder)
		);
		$loaded = array();
		foreach ($dirs as $dir) {
			if ( is_dir($dir) ) {
				$hdir = @opendir( $dir );
				if ( $hdir ) {
					$files = array();
					$folders = array();
					while ( ($file = readdir($hdir)) !== false ) {
						if (substr($file, 0, 1) == '.' || in_array($file, $loaded))
							continue;
						if ( is_dir( ($dir) . '/' . ($file) ) ) {
							if ($from_subfolders && file_exists( ($dir) . '/' . ($file) . '/' . ($file) . '.php' ) ) {
								$folders[] = $file;
							}
						} else {
							$files[] = $file;
						}
					}
					@closedir( $hdir );
					// Load sorted files
					if ( count($files) > 0) {
						sort($files);
						foreach ($files as $file) {
							$loaded[] = $file;
							require_once ($dir) . '/' . ($file);
						}
					}
					// Load sorted subfolders
					if ( count($folders) > 0) {
						sort($folders);
						foreach ($folders as $file) {
							$loaded[] = $file;
							require_once ($dir) . '/' . ($file) . '/' . ($file) . '.php';
						}
					}
				}
			}
		}
	}
}



// Get domain part from URL
if (!function_exists('invetex_get_domain_from_url')) {
	function invetex_get_domain_from_url($url) {
		if (($pos=strpos($url, '://'))!==false) $url = substr($url, $pos+3);
		if (($pos=strpos($url, '/'))!==false) $url = substr($url, 0, $pos);
		return $url;
	}
}

// Return file extension from full name/path
if (!function_exists('invetex_get_file_ext')) {
	function invetex_get_file_ext($file) {
		$parts = pathinfo($file);
		return $parts['extension'];
	}
}




/* File system utils
------------------------------------------------------------------------------------- */


// Init WP Filesystem
if (!function_exists('invetex_init_filesystem')) {
	add_action( 'after_setup_theme', 'invetex_init_filesystem', 0);
	function invetex_init_filesystem() {
		if( !function_exists('WP_Filesystem') ) {
			require_once( ABSPATH .'/wp-admin/includes/file.php' );
		}
		if (is_admin()) {
			$url = admin_url();
			$creds = false;
			// First attempt to get credentials.
			if ( function_exists('request_filesystem_credentials') && false === ( $creds = request_filesystem_credentials( $url, '', false, false, array() ) ) ) {
				// If we comes here - we don't have credentials
				// so the request for them is displaying no need for further processing
				return false;
			}

			// Now we got some credentials - try to use them.
			if ( !WP_Filesystem( $creds ) ) {
				// Incorrect connection data - ask for credentials again, now with error message.
				if ( function_exists('request_filesystem_credentials') ) request_filesystem_credentials( $url, '', true, false );
				return false;
			}

			return true; // Filesystem object successfully initiated.
		} else {
			WP_Filesystem();
		}
		return true;
	}
}


// Put data into specified file
if (!function_exists('invetex_fpc')) {
	function invetex_fpc($file, $data, $flag=0) {
		global $wp_filesystem;
		if (!empty($file)) {
			if (isset($wp_filesystem) && is_object($wp_filesystem)) {
				$file = str_replace(ABSPATH, $wp_filesystem->abspath(), $file);
				// Attention! WP_Filesystem can't append the content to the file!
				// That's why we have to read the contents of the file into a string,
				// add new content to this string and re-write it to the file if parameter $flag == FILE_APPEND!
				return $wp_filesystem->put_contents($file, ($flag==FILE_APPEND ? $wp_filesystem->get_contents($file) : '') . $data, false);
			} else {
				if (invetex_param_is_on(invetex_get_theme_option('debug_mode')))
					throw new Exception(sprintf(esc_html__('WP Filesystem is not initialized! Put contents to the file "%s" failed', 'invetex'), $file));
			}
		}
		return false;
	}
}

// Get text from specified file
if (!function_exists('invetex_fgc')) {
	function invetex_fgc($file) {
		global $wp_filesystem;
		if (!empty($file)) {
			if (isset($wp_filesystem) && is_object($wp_filesystem)) {
				$file = str_replace(ABSPATH, $wp_filesystem->abspath(), $file);
				return $wp_filesystem->get_contents($file);
			} else {
				if (invetex_param_is_on(invetex_get_theme_option('debug_mode')))
					throw new Exception(sprintf(esc_html__('WP Filesystem is not initialized! Get contents from the file "%s" failed', 'invetex'), $file));
			}
		}
		return '';
	}
}

// Get array with rows from specified file
if (!function_exists('invetex_fga')) {
	function invetex_fga($file) {
		global $wp_filesystem;
		if (!empty($file)) {
			if (isset($wp_filesystem) && is_object($wp_filesystem)) {
				$file = str_replace(ABSPATH, $wp_filesystem->abspath(), $file);
				return $wp_filesystem->get_contents_array($file);
			} else {
				if (invetex_param_is_on(invetex_get_theme_option('debug_mode')))
					throw new Exception(sprintf(esc_html__('WP Filesystem is not initialized! Get rows from the file "%s" failed', 'invetex'), $file));
			}
		}
		return array();
	}
}

// Remove unsafe characters from file/folder path
if (!function_exists('invetex_esc')) {
	function invetex_esc($file) {
        return sanitize_file_name($file);
	}
}

// Get text from specified file (local or remote)
if (!function_exists('invetex_get_local_or_remote_file')) {	
	function invetex_get_local_or_remote_file($file) {
		$rez = '';
		if (substr($file, 0, 5)=='http:' || substr($file, 0, 6)=='https:') {
			$tm = round( 0.9 * max(30, ini_get('max_execution_time')));
			$response = wp_remote_get($file, array(
									'timeout'     => $tm,
									'redirection' => $tm
									)
								);
			if (is_array($response) && isset($response['response']['code']) && $response['response']['code']==200)
				$rez = $response['body'];
		} else {
			if (($file = invetex_get_file_dir($file)) != '')
				$rez = invetex_fgc($file);
		}
		return $rez;
	}
}


// Create folder
if (!function_exists('invetex_mkdir')) {	
	function invetex_mkdir($folder, $addindex = true) {
		if (is_dir($folder) && $addindex == false) return true;
		$created = wp_mkdir_p(trailingslashit($folder));
		@chmod($folder, 0777);
		if ($addindex == false) return $created;
		$index_file = trailingslashit($folder) . 'index.php';
		if (file_exists($index_file)) return $created;
		invetex_fpc($index_file, "<?php\n// Silence is golden.\n");
		return $created;
	}
}



/* Check if file/folder present in the child theme and return path (url) to it. 
   Else - path (url) to file in the main theme dir
------------------------------------------------------------------------------------- */

// Detect file location with next algorithm:
// 1) check in the child theme folder
// 2) check in the framework folder in the child theme folder
// 3) check in the main theme folder
// 4) check in the framework folder in the main theme folder
if (!function_exists('invetex_get_file_dir')) {	
	function invetex_get_file_dir($file, $return_url=false) {
		if ($file[0]=='/') $file = invetex_substr($file, 1);
		$theme_dir = get_template_directory();
		$theme_url = get_template_directory_uri();
		$child_dir = get_stylesheet_directory();
		$child_url = get_stylesheet_directory_uri();
		$dir = '';
		if (file_exists(($child_dir).'/'.($file)))
			$dir = ($return_url ? $child_url : $child_dir).'/'.($file);
		else if (file_exists(($child_dir).'/'.(INVETEX_FW_DIR).'/'.($file)))
			$dir = ($return_url ? $child_url : $child_dir).'/'.(INVETEX_FW_DIR).'/'.($file);
		else if (file_exists(($theme_dir).'/'.($file)))
			$dir = ($return_url ? $theme_url : $theme_dir).'/'.($file);
		else if (file_exists(($theme_dir).'/'.(INVETEX_FW_DIR).'/'.($file)))
			$dir = ($return_url ? $theme_url : $theme_dir).'/'.(INVETEX_FW_DIR).'/'.($file);
		return $dir;
	}
}

// Detect file location with next algorithm:
// 1) check in the main theme folder
// 2) check in the framework folder in the main theme folder
// and return file slug (relative path to the file without extension)
// to use it in the get_template_part()
if (!function_exists('invetex_get_file_slug')) {	
	function invetex_get_file_slug($file) {
		if ($file[0]=='/') $file = invetex_substr($file, 1);
		$theme_dir = get_template_directory();
		$dir = '';
		if (file_exists(($theme_dir).'/'.($file)))
			$dir = $file;
		else if (file_exists(($theme_dir).'/'.INVETEX_FW_DIR.'/'.($file)))
			$dir = INVETEX_FW_DIR.'/'.($file);
		if (invetex_substr($dir, -4)=='.php') $dir = invetex_substr($dir, 0, invetex_strlen($dir)-4);
		return $dir;
	}
}

if (!function_exists('invetex_get_file_url')) {	
	function invetex_get_file_url($file) {
		return invetex_get_file_dir($file, true);
	}
}

// Detect folder location with same algorithm as file (see above)
if (!function_exists('invetex_get_folder_dir')) {	
	function invetex_get_folder_dir($folder, $return_url=false) {
		if ($folder[0]=='/') $folder = invetex_substr($folder, 1);
		$theme_dir = get_template_directory();
		$theme_url = get_template_directory_uri();
		$child_dir = get_stylesheet_directory();
		$child_url = get_stylesheet_directory_uri();
		$dir = '';
		if (is_dir(($child_dir).'/'.($folder)))
			$dir = ($return_url ? $child_url : $child_dir).'/'.($folder);
		else if (is_dir(($child_dir).'/'.(INVETEX_FW_DIR).'/'.($folder)))
			$dir = ($return_url ? $child_url : $child_dir).'/'.(INVETEX_FW_DIR).'/'.($folder);
		else if (file_exists(($theme_dir).'/'.($folder)))
			$dir = ($return_url ? $theme_url : $theme_dir).'/'.($folder);
		else if (file_exists(($theme_dir).'/'.(INVETEX_FW_DIR).'/'.($folder)))
			$dir = ($return_url ? $theme_url : $theme_dir).'/'.(INVETEX_FW_DIR).'/'.($folder);
		return $dir;
	}
}

if (!function_exists('invetex_get_folder_url')) {	
	function invetex_get_folder_url($folder) {
		return invetex_get_folder_dir($folder, true);
	}
}

// Return path to social icon (if exists)
if (!function_exists('invetex_get_socials_dir')) {	
	function invetex_get_socials_dir($soc, $return_url=false) {
		return invetex_get_file_dir('images/socials/' . sanitize_file_name($soc) . (invetex_strpos($soc, '.')===false ? '.png' : ''), $return_url, true);
	}
}

if (!function_exists('invetex_get_socials_url')) {	
	function invetex_get_socials_url($soc) {
		return invetex_get_socials_dir($soc, true);
	}
}

// Detect theme version of the template (if exists), else return it from fw templates directory
if (!function_exists('invetex_get_template_dir')) {	
	function invetex_get_template_dir($tpl) {
		return invetex_get_file_dir('templates/' . sanitize_file_name($tpl) . (invetex_strpos($tpl, '.php')===false ? '.php' : ''));
	}
}
?>