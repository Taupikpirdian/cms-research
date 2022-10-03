<?php

/*
* CACTUAR FINDER
* @author : cactuar
*/

namespace Cactuar\Admin\Helpers;

use Intervention\Image\ImageManagerStatic as Image;

class cfind {
	private $conf; //all configureation value
	private $is_search; //search mode flag, and store search keyword
	private $founds = []; //store found items
	
	public static function install($conf) {
		return new cfind($conf);
	}
	
	public function __construct($conf) {
		
		/* ------------- INITIALIZE --------------------- */
		
		//define search mode
		if (g($_POST, 'search'))
			$this->is_search = trim(strtolower(g($_POST, 'search')));
		
		$conf['rootPath'] = config('cadmin.media.sourcepath');
        
		$url = asset('');
        $url = explode('/',str_replace('//','',$url));
        array_shift($url);
        
        $conf['rootURL'] = preg_replace('#/+#','/','/'.implode('/',$url).'/'.$conf['rootPath']);
		
        //define type
		$conf['type'] = strtolower(g($_GET, 'type'));
		
		if (!g($conf, 'type')
			|| !array_key_exists(g($conf, 'type'), (array) g($conf, 'types'))) {
			$keys = array_keys((array) g($conf, 'types'));
			$conf['type'] = array_shift($keys);
		}
		
		if (!file_exists($this->_sanitize(g($conf, 'rootPath') . '/' . $this->_path(g($conf, 'type')))))
			mkdir($this->_sanitize(g($conf, 'rootPath') . '/' . $this->_path(g($conf, 'type'))), 0775, true);
		
		//define current-path
		$conf['path'] = $conf['type'];
        if (g($_GET, 'path')) 
            $conf['path'] = \Crypt::decrypt(g($_GET, 'path'));
		
        if (!file_exists($this->_sanitize(g($conf, 'rootPath') . '/' . $this->_path(g($conf, 'path')))))
			$conf['path'] = $conf['type'];
		
		if ($this->is_search)
			$conf['path'] = $conf['type'];
		
        //define available extension
		$conf['ext'] = g($conf, 'types.'.g($conf, 'type').'.ext');
		
		//define view
		if (!g($conf, 'view'))
			$conf['view'] = 'view.php';
		
        if (request()->query('opener') == 'cfind' && request()->query('thumb')) {
            $thumb = config('cadmin.media.thumbs.'.request()->query('thumb'));
            if (is_array($thumb)) {
                $thumb['code'] = request()->query('thumb');
                if (g($thumb, 'width') && g($thumb, 'height') && g($thumb, 'type') != 'contain') {
                    $thumb['crop-ratio'] = g($thumb, 'width').':'.g($thumb, 'height');
                    $thumb['is-crop'] = true;
                }
                $conf['thumb'] = $thumb;
            }
        }
        
		$this->conf = $conf;
		
        $this->_validPath($conf['path']);
        
		/* ------------------ SEARCH FOR ACTION --------------- */
		
		$this->_do_create_folder();
		$this->_do_delete();
		$this->_do_resize();
		$this->_do_use();
		
		$reload = false;
		for ($i=1; $i<=5; $i++) {
			if ($this->_do_upload_file($i)) {
				$reload = true;
			}
		}
		if ($reload) {
			header('location:'.$this->_url());
			die();
		}
	}
	
	/*
	*	Draw HTML
	*/
	public function draw() {
		$m = $this->_msg();
		
		//modelling title
		$t = explode('/', g($this->conf, 'path'));
		
		$p = '';
		foreach ((array) $t AS $key => $val) {
			if ($p) $p .= '/' . $val;
			else $p = $val;
			
			$t[$key] = '<a href="'.$this->_url(['path' => $p]).'">'.$val.'</a>';
		}
		
		$title = implode(' / ', $t);
		
		//overide if on search mode
		if ($this->is_search) {
			$title = 'Search result for "<b>'.htmlspecialchars($this->is_search).'</b>"';
		}
		
		//set configuration
		$conf = [
				'dir' => '
						<li class="active">
							<a href="'.$this->_url(array('path' => g($this->conf, 'type'))).'">'.g($this->conf, 'type').'</a>'.$this->_htmlDir().'
						</li>',
                'thumb' => g($this->conf, 'thumb'),
				'file'=> $this->_htmlFile(),
				'path'=> $title,
				'is_search' => $this->is_search,
				'msg' => ((g($m, 'type')) ? '
							<div class="cfind-div cfind-message cfind-message-'.g($m, 'type').'">'.g($m, 'message').'</div>' : ''),
				'url_back' => $this->conf['type'] == $this->conf['path'] ? '' : $this->_url(['path' => dirname(g($this->conf, 'path'))]),
				'disable' => [
                        'add' => g($this->conf, 'disable.add'),
                        'delete' => g($this->conf, 'disable.delete')
                    ],
			];
		
		//use view
		return view(g($this->conf, 'view'), ['conf' => $conf]);
	}
	
	/* 
	*	Construct Directory HTML
	*/
	private function _htmlDir($path = '') {
		if (!$path) $path = g($this->conf, 'type');
		
		$rpath = $this->_path($path);
		if (!file_exists($rpath) || !is_dir($rpath)) return '';
		
		//AUUTO CREATE [index.html]
		$findex = $this->_sanitize($rpath.'/index.html');
		if (!file_exists($findex)) {
			$hnd = fopen($findex, 'w');
			fwrite($hnd, 'No direct access allowed');
			fclose($hnd);
		}	
		
		$html = '<ul>';
		
		$hnd = opendir($rpath);
		while ($read = readdir($hnd)) {
			if ($read == '.' || $read == '..') continue;
			
			$mypath = $this->_sanitize($path.'/'.$read);
			
			if (!is_dir($this->_path($mypath))) {
				
				//AUTO REMOVE UN-EXPECTED FILES
				$ext = pathinfo($mypath, PATHINFO_EXTENSION);
				if (!in_array(strtolower($ext), (array) g($this->conf, 'ext')) && $read != 'index.html') {
					$this->quarantine($this->_path($mypath));
					continue;
				}
				
				//try searching for match (file type)
				if ($this->is_search && $this->_match($this->is_search, $read)) {
					array_push($this->founds, [
								'type' => 'file',
								'path' => $path,
								'name' => $read
								]);
				}
				continue;
			}
			
			//try searching for match (folder type)
			if ($this->is_search && $this->_match($this->is_search, $read)) {
				array_push($this->founds, [
							'type' => 'folder',
							'path' => $path,
							'name' => $read
							]);
			}
			
			$spath = g($this->conf, 'path'); //original path (by request)
			
			$html .= '<li '.((substr($spath.'/', 0, strlen($mypath.'/')) == $mypath.'/') ? 'class="active"' : '').'>
						<a href="'.$this->_url(array('path' => $mypath)).'">'.htmlspecialchars(strtolower($read)).'</a>';
			
			$html .= $this->_htmlDir($mypath);
			$html .= '</li>';
		}
		closedir($hnd);
		
		$html .= '</ul>';
		
		return (string) $html;
	}
	
	/* 
	*	Construct Files HTML
	*/
	private function _htmlFile() {
		if ($this->is_search) { //on search mode
		
			if (empty($this->founds)) {
				return '<div class="cfind-div cfind-message">No content found</div>';
			}
			
			$html = '';
			
			//first : folder items
			foreach ((array) $this->founds AS $key => $val) {
				if (g($val, 'type') != 'folder') continue;
				$html .= $this->_listFolder($val);
			}
			
			//second : file items
			foreach ((array) $this->founds AS $key => $val) {
				if (g($val, 'type') != 'file') continue;
				$html .= $this->_listFile($val);
			}
			
			return $html;
		}
	
		$path = g($this->conf, 'path');
		
		$rpath = $this->_path($path);
		if (!file_exists($rpath) || !is_dir($rpath)) return '';
		
		$dir = $files = array();
		$hnd = opendir($rpath);
		while ($read = readdir($hnd)) {
			if ($read == '.' || $read == '..') continue;
			
			$mypath = $this->_sanitize($path.'/'.$read);
			if (is_dir($this->_path($mypath)))
				array_push($dir, ['name' => $read, 'time' => filemtime($rpath.'/'.$read)]);
			else {
				if (g($_GET, 'ext')) {
					$ext = explode(',',str_replace(' ', '', g($_GET, 'ext')));
				
					if (!in_array(strtolower(pathinfo($read, PATHINFO_EXTENSION)), $ext))
						continue;
				}

				if (request()->get('thumb') //check available file for resizze
					&& !in_array(strtolower(pathinfo($read, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp']))
					continue;

				array_push($files, ['name' => $read, 'time' => filemtime($rpath.'/'.$read)]);
			}
				
		}
		
		usort($files,function($a,$b) { return $b['time'] - $a['time']; });
		$files = array_column($files,'name');
		
		usort($dir,function($a,$b) { return $b['time'] - $a['time']; });
		$dir = array_column($dir,'name');
		
		closedir($hnd);
		
		$html = '';
		
		//first : folder items
		foreach ((array) $dir AS $key => $val) {
			$html .= $this->_listFolder(['path' => $path, 'name' => $val]);
		}
		
		//second : file items
		foreach ((array) $files AS $key => $val) {
			$html .= $this->_listFile(['path' => $path, 'name' => $val]);
		}
		
		if (empty($html)) return '<div class="cfind-div cfind-message">Folder is empty</div>';
			
		return $html;
	}
	
	/*
	* individual draw folder items
	*/
	private function _listFolder($val) {
		$path = g($val, 'path');
		$name = g($val, 'name');
			
		$mypath = $this->_sanitize($path . '/' . $name);
		
		return '
				<li class="cfind-item-folder">
					<a href="'.$this->_url(['path' => $mypath]).'" title="'.htmlspecialchars($name).'">
						<figure></figure>
						<span>'.htmlspecialchars($name).'</span>
					</a>
				</li>';
	}
	
	/*
	*	individual draw file items
	*/
	private function _listFile($val) {
		
		$path = g($val, 'path');
		$name = g($val, 'name');
		
		$mypath = $this->_sanitize($path . '/' . $name);
		
		if (!file_exists($this->_path($mypath))) return;
			
		$ext = strtolower(pathinfo($this->_path($mypath), PATHINFO_EXTENSION));
		if (is_array(g($this->conf, 'ext')) && !in_array($ext, g($this->conf, 'ext'))) return; //extension not match
		
		//define attributes
		$attr = 'data-is-file="true"';
		$attr .= 'data-type="' . $ext . '"';
		$attr .= 'data-path="' . $mypath . '"';
		$attr .= 'data-size="' . filesize($this->_path($mypath)) . '" ';
		$attr .= 'data-name="' . htmlspecialchars($name). '"';
		$attr .= 'data-ctime="' . filectime($this->_path($path.'/'.$name)). '"';
		$attr .= 'data-mtime="' . filemtime($this->_path($path.'/'.$name)). '"';
		$attr .= 'data-url="' . g($this->conf, 'rootURL').'/'.$path.'/'.$name . '" ';
		$attr .= 'data-path-enc="' . \Crypt::encrypt($mypath) . '" ';
		$attr .= 'data-mbsize="'. mbsize(filesize($this->_path($mypath))) .' " ';
        $attr .= 'data-ctime-readable="'.date('D, d M Y H:i',filectime($this->_path($path.'/'.$name))).'" ';
        $attr .= 'data-mtime-readable="'.date('D, d M Y H:i',filemtime($this->_path($path.'/'.$name))).'" ';
        //image types
		if (in_array($ext, array('jpg', 'jpeg', 'png', 'bmp', 'gif', 'ico'))) {
			$info = getimagesize($this->_path($path.'/'.$name));
			$attr .= ' data-is-image="true" ';
			$attr .= ' data-image-width="' . g($info, 0) . '" ';
			$attr .= ' data-image-height="' . g($info, 1) . '" ';
		}
		
		$html = '
				<li class="cfind-item-file" '.$attr.'>
					<a href="" title="'.htmlspecialchars($name).'" >
						<figure></figure>
						<span>'.htmlspecialchars($name).'</span>
					</a>
				</li>';
				
		return $html;
	}	
	
	/*
	*	query for match keyword
	*/
	private function _match($keyword, $string) {
		//sanitize
		foreach (array(' ', '-', '_') AS $key => $val) {
			$keyword = str_replace($val, '', $keyword);
			$string = str_replace($val, '', $string);
		}
		
		$keyword = strtolower($keyword);
		$string = ' '.strtolower($string);
		if (!$keyword || !$string) return false;
		
		if (strpos($string, $keyword) >= 1) return true;
	}
	
	/*
	*	set or get message
	*/
	private function _msg($message = '', $type = 'success') {
		if (!isset($_SESSION))
			session_start();
		
		if ($message) { //set message
			if (g($_SESSION, 'cfind-message.message')) {
				$message = g($_SESSION, 'cfind-message.message').'<br>'.$message;
			}
			$_SESSION['cfind-message'] = array('message' => $message, 'type' => $type);
			return;
		}
		
		if (!is_array(g($_SESSION, 'cfind-message'))) return array();
		
		$message = g($_SESSION, 'cfind-message');
		$_SESSION['cfind-message'] = '';
		
		return $message;
	}
	
	/*
	*	get 'real' path
	*/
	private function _path($path = '') {
		if (!$path) $path = g($this->conf, 'path');
		return $this->_sanitize(g($this->conf, 'rootPath').'/'.$path);
	}
	
	/*
	*	get url, and overide url query (_GET)
	*/
	private function _url($par = array()) {
		$get = $_GET;
		
		foreach ((array) $par AS $key => $val) { //overide value
			if ($key == 'path') //if path, encode
				$get['path'] = \Crypt::encrypt($val);
			else
				$get[$key] = $val;
		}
		
		$url = '//';
		
		$url .= $this->_sanitize(g($_SERVER, 'HTTP_HOST').'/'.strtok(g($_SERVER, 'REQUEST_URI'), '?'));
		
		return $url.'?'.http_build_query($get);
	}
	
	/*
	*	sanitize path string
	*/
	private function _sanitize($path) {
		$path = str_replace('\\', '/', $path);
		while (strpos($path, '//')) {
			$path = str_replace('//', '/', $path);
		}
		
		return ($path);
	}
	
	/* ------------- ACTION ------------------ */
	
	/*
	*	action create folder
	*	trigger by $_POST['folder']
	*/
	private function _do_create_folder() {
		if (g($_POST, 'folder')) {
			if (g($this->conf, 'disable.add')) die('Forbidden');
		
			$folder = strtolower(str_replace(' ', '-', g($_POST, 'folder')));
			$folder = preg_replace("/[^a-z0-9_-]+/i", "", $folder);
			
			$path = $this->_path(g($this->conf, 'path') . '/' . $folder);
			
			if (file_exists($path)) {
				$this->_msg('Cannot create folder ['.htmlspecialchars($folder).'] : folder/file exists', 'error');
				return;
			}
		
			if (@mkdir($path, 0775)) {
				$this->_msg('Success create folder ['.htmlspecialchars($folder).']');
				header('location:'.$this->_url(array('path' => $this->_sanitize(g($this->conf, 'path') . '/' . $folder))));
				die();
			} else {
				$this->_msg('Failed create folder ['.htmlspecialchars($folder).'] : please call your administrator', 'error');
			}
		}
	}
	
	/*
	*	action delete file/folder
	*	trigger by $_POST['delete']
	*/
	private function _do_delete() {
		if (g($_POST, 'delete')) {
			if (g($this->conf, 'disable.delete')) die('Forbidden');
			
            $path = g($this->conf, 'path');
            if (g($_POST, 'path'))
                $path = \Crypt::decrypt(g($_POST, 'path'));
            
            $this->_validPath($path);
            
			$parent = dirname($path);
			
			if ($path == g($this->conf, 'type')) {
				$this->_msg('Cannot delete root folder', 'error');
				return;
			}
			if ($this->_delete($this->_path($path))) {
				$this->_msg('Success delete ['.$path.']');
				header('location:'.$this->_url(['path' => $parent]));
				die();
			} else {
				$this->_msg('Failed delete ['.$path.'] : please call your administator', 'error');
			}
		}
	}
    
    private function _do_resize()
    {   
        if (g($_POST, 'do') == 'resize' && g($_POST, 'source') && g($this->conf, 'thumb.type')) {
            $this->_validPath(g($_POST, 'source'));
            
            //add validation for custom size
            if (g($_POST, 'width') && g($_POST, 'height')) {
                $ratio1 = round($_POST['width'] / $_POST['height']);
                $ratio2 = round($this->conf['thumb']['width'] / $this->conf['thumb']['height']);
                if ($ratio1 != $ratio2) {
                    die('invalid resize');
                }
            }
            
            $path = self::thumb(g($_POST, 'source'), g($this->conf, 'thumb.width'), g($this->conf, 'thumb.height'), g($this->conf, 'thumb.type'), g($_POST, 'width'), g($_POST, 'height'), g($_POST, 'x1'), g($_POST, 'y1'));
            
			$this->cfindSet($path);
        }
    }
	
	private function _do_use()
	{
		if (g($_POST,'do') != 'use')
			return;
		return $this->cfindSet($_POST['source']);
	}
	
	private function cfindSet($path)
	{
		$realFile = public_path($this->conf['rootPath'].'/'.$path);
		$size = getimagesize($realFile);
		
		$json = [
			'path' => $path,
			'ext' => strtolower(pathinfo($realFile,PATHINFO_EXTENSION)),
			'type' => strtolower(pathinfo($realFile,PATHINFO_EXTENSION)),
			'url' => $this->conf['rootURL'].'/'.$path,
			'size' => filesize($realFile),
			'ctime' => filectime($realFile),
			'mtime' => filemtime($realFile),
			'image-width' => is_array($size) ? array_get($size,0) : 0,
			'image-height' => is_array($size) ? array_get($size,1) : 0,
		];
		
		if (g($_GET,'convert') && in_array($_GET['convert'],['jpg','jpeg','png','webp'])) {
			$convert = $_GET['convert'];
			$ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			if ($ext == $convert) {
				$json['convert-'.$convert] = $path;
			} else {
				$basePath = config('cadmin.media.convertpath');
				$convertPath = $convert.'/'.dirname($path);
				if (!file_exists($basePath.'/'.$convertPath))
					mkdir($basePath.'/'.$convertPath,0775,true);
				
				$convertPath .= '/'.basename($path).'.'.$convert;
				if (!file_exists($basePath.'/'.$convertPath)) {
					$img = Image::make(config('cadmin.media.sourcepath').'/'.$path);
					$img->save($basePath.'/'.$convertPath);
				}
				$json['convert-'.$convert] = $convertPath;
			}
		}
		
		echo '<script>window.opener.cfind.set(\''.json_encode($json).'\', true);window.close()</script>';
        die();
	}
    
    public static function thumb($source, $width, $height, $type = 'cover', $toWidth = 0, $toHeight = 0, $x1 = 0, $y1 = 0)
	{
        $basePath = config('cadmin.media.sourcepath').'/'.dirname($source);
        $baseName = basename($source);
        $baseFPath = $basePath.'/'.$baseName;
        
        $targetName = $baseName;
        if ($toWidth && $toHeight) {
            $targetName = 'tw'.$toWidth.'_th'.$toHeight.'_x1'.$x1.'_y1'.$y1.'_'.$targetName;
        }
        
        $targetName = $type.'_w'.$width.'_h'.$height.'_'.$targetName;
        $targetPath = config('cadmin.media.sourcepath').'/thumb/'.dirname($source);
        
        $info = pathinfo($targetName);
        if ($type == 'contain' && array_get($info, 'extension') != 'png')
            $targetName .= '.png';
        
        if (!file_exists($targetPath))
            mkdir($targetPath, 0775, true);
        
        $targetFPath = $targetPath.'/'.$targetName;
        
        if (file_exists($targetFPath))
            @unlink($targetFPath);
        
        $img = Image::make($baseFPath);
		
        $output = 'thumb/'.dirname($source).'/'.$targetName;
        
        if ($width == 0) {
            $img->resize(null, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($targetFPath);
            
            return $output;
        }
        
        if ($height == 0) {
            $img->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($targetFPath);
            return $output;
        }
        
        if ($type == 'cover') {
            if ($toWidth && $toHeight) {
                $img->crop($toWidth, $toHeight, $x1, $y1);
                $img->resize($width, $height);
            } else {
                $img->fit($width, $height);
            }
            
            $img->save($targetFPath);
            
            return $output;
        }
        
		if ($type == 'contain') {
			if ($img->height() > $img->width()) {
				if ($img->height() > $height) {
					$img->resize(null, $height, function ($constraint) {
						$constraint->aspectRatio();
					});
				}
				if ($img->width() > $width) {
					$img->resize($width, null, function ($constraint) {
						$constraint->aspectRatio();
					});
				}
			} else {
				if ($img->width() > $width) {
					$img->resize($width, null, function ($constraint) {
						$constraint->aspectRatio();
					});
				}
				if ($img->height() > $height) {
					$img->resize(null, $height, function ($constraint) {
						$constraint->aspectRatio();
					});
				}
			}
			$canvas = Image::canvas($width, $height);
			$canvas->insert($img, 'center');
            
            $canvas->save($targetFPath);
            
            return $output;
		}
        
        
	}
    
    private function _validPath($path) {
        if (substr(realpath(g($this->conf, 'rootPath').'/'.$path), 0, strlen(realpath(g($this->conf, 'rootPath').'/'.g($this->conf, 'type')))) != realpath(g($this->conf, 'rootPath').'/'.g($this->conf, 'type')))
            die('?');
		  
    }
	
	/*
	*	action upload file/zip
	*	trigger by $_FILES['name']
	*/
	private function _do_upload_file($i) {
		if (g($_FILES, 'name_'.$i.'.tmp_name')) {
			if (g($this->conf, 'disable.add')) die('Forbidden');
			
			if (in_array(g($_FILES, 'name_'.$i.'.type'), [
				'application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed', 'application/octet-stream', 'application/octet'
				])) {
				return $this->_do_upload_zip($i);
			}
			
			$file = g($_FILES, 'name_'.$i);
			
			$fname = strtolower(preg_replace("/[^a-zA-Z0-9\/_|+ .-]/", "", g($file, 'name')));
			$fname = strtolower(str_replace(' ', '-', $fname));
			
			$ext = pathinfo($fname, PATHINFO_EXTENSION);
			
			if (!in_array($ext, (array) g($this->conf, 'ext'))) {
				$this->_msg('Failed upload file : Extension not supported : File ['.$fname.']', 'error');
				return;
			}
			
			$fpath = $this->_path(g($this->conf, 'path') . '/' . $fname);
			
			if (file_exists($fpath) && !g($_POST, 'overide')) {
				$this->_msg('Failed upload file : File ['.$fname.'] already exists', 'error');
				return;
			}
			
			if (!move_uploaded_file(g($file, 'tmp_name'), $fpath)) {
				$this->_msg('Failed upload file : please call your administrator', 'error');
				return;
			}
			
			$this->_msg('Success upload file ['.$fname.']');
			return true;
		}
	}
	
	/*
	*	unpack zip
	*/
	private function _do_upload_zip($i) {
		return; //disable zip upload
		if (!class_exists('ZipArchive')) {
			$this->_msg('ZipArchive library not installed', 'error');
			return;
		}
		
		$file = g($_FILES, 'name_'.$i);
		$fname = strtolower(preg_replace("/[^a-zA-Z0-9\/_|+ .-]/", "", g($file, 'name')));
		
		$fpath = $this->_path(g($this->conf, 'path') . '/' . $fname);
		
		if (!@move_uploaded_file(g($file, 'tmp_name'), $fpath)) {
			$this->_msg('Failed upload file : please call your administrator', 'error');
			return;
		}
			
		$zip = new \ZipArchive();
		$x = $zip->open($fpath);
		if ($x === true) {
			$zip->extractTo($this->_path(g($this->conf, 'path'))); // change this to the correct site path
			$zip->close();
	
			unlink($fpath);
		
			$this->_msg('Success upload file ['.$fname.']');
			return true;
		} else {
			$this->_msg('Failed open Zip Archive', 'error');
			return;
		}
	}
	
	/*
	*	delete file/folder (recursive)
	*/
	private function _delete($path) {
		if (!file_exists($path)) {
			return true;
		}

		if (!is_dir($path)) {
			return unlink($path);
		}

		foreach (scandir($path) as $item) {
			if ($item == '.' || $item == '..') continue;
			
			if (!$this->_delete($this->_sanitize($path. '/'. $item))) return false;
		}

		return rmdir($path);
	}

	private function quarantine($path)
	{
		
		$basePath = storage_path('cfind-quarantine');
		if (!file_exists($basePath))
			mkdir($basePath,0775,true);

		$log = fopen($basePath.'/log.log','a+');
		fwrite($log,date('Y-m-d H:i:s').' : '.$path.PHP_EOL);
		fclose($log);

		$basePath .= '/'.dirname($path);
		if (!file_exists($basePath))
			mkdir($basePath,0775,true);

		$fname = basename($path);
		$ex = explode('.',$fname);
		$baseExt = array_pop($ex);
		$baseName = implode('.',$ex);

		$idx = 1;
		while(file_exists($basePath.'/'.$fname)) {
			$fname = $baseName.'-'.$idx.'.'.$baseExt;
			$idx++;
		}
		
		@rename($path,$basePath.'/'.$fname);
	}

}


function g($o, $key) {
		
	if (strpos($key, '.')) { //recursive
		$r = explode('.', $key);
		
		$val = array_pop($r);
		return g(g($o, implode('.', $r)), $val);
	}
	
	if (is_object($o)
		&& property_exists($o, $key)) { //return as object
			return $o->$key;
		}
	if (is_array($o)
		&& array_key_exists($key, $o)) { //return as array
			return $o[$key];
		}
	
	return '';//not object or array return empty
}

function mbsize($v) {
	
	if (is_numeric($v)) {
		$bytes = $v;
	} else { //get size by file path
		$path = $v;
		if (!file_exists($path)) return '0B';
		$bytes = sprintf('%u', filesize($path));
	}
	
	if ($bytes > 0)
	{
		$unit = intval(log($bytes, 1024));
		$units = array('B', 'KB', 'MB', 'GB');

		if (array_key_exists($unit, $units) === true)
		{
			return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
		}
	}

	return $bytes;
}

?>