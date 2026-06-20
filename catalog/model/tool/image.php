<?php
class ModelToolImage extends Model {
	public function exists($filename) {
		if (!is_file(DIR_IMAGE . $filename)) {
			return false;
		}

		return true;
	}

	public function resize($filename, $width, $height = null, $type = null) {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;

		$size = getimagesize(DIR_IMAGE . $image_old);

		list($width_orig, $height_orig, $image_type) = $size;

		if($height_orig != 0) {
			$ratio = $width_orig / $height_orig;
		} else {
			$ratio = 1;
		}

		if (!$height && $width) {
			$height  = ceil($width / $ratio);
		}

		if($type == 'webp') {
			$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;
			if('jpg' == $extension || 'jpeg' == $extension) {
				$image_new = str_replace('.jpg', '.webp', $image_new);
				$image_new = str_replace('.jpeg', '.webp', $image_new);
			} else if('png' == $extension) {
				$image_new = str_replace('.png', '.webp', $image_new);
			} else if('gif' == $extension) {
				$image_new = str_replace('.gif', '.webp', $image_new);
			}

			if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
				$path = '';
	
				$directories = explode('/', dirname($image_new));
	
				foreach ($directories as $directory) {
					$path = $path . '/' . $directory;
	
					if (!is_dir(DIR_IMAGE . $path)) {
						@mkdir(DIR_IMAGE . $path, 0777);
					}
				}
	
				if($extension == 'jpg' || $extension == 'jpeg') {
					$webp = imagecreatefromjpeg(DIR_IMAGE . $image_old);
				} else if($extension == 'png') {
					$webp = imagecreatefrompng(DIR_IMAGE . $image_old);
				} else if($extension == 'gif') {
					$webp = imagecreatefromgif(DIR_IMAGE . $image_old);
				} else if($extension == 'webp') {
					$webp = imagecreatefromwebp(DIR_IMAGE . $image_old);
				}
				
				if($extension == 'png') {
					$webp = imagescale($webp, $width);
				} else {
					$webp = imagescale($webp, $width, $height);
				}
	
				imagepalettetotruecolor($webp);
				imagealphablending($webp, true);
				imagesavealpha($webp, true);
				imagewebp($webp, DIR_IMAGE . $image_new, 90);
				imagedestroy($webp);
			}
		} else {
			$extension = 'webp';
			$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

			if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
				list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
					 
				if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP))) { 
					return DIR_IMAGE . $image_old;
				}
							
				$path = '';
	
				$directories = explode('/', dirname($image_new));
	
				foreach ($directories as $directory) {
					$path = $path . '/' . $directory;
	
					if (!is_dir(DIR_IMAGE . $path)) {
						@mkdir(DIR_IMAGE . $path, 0777);
					}
				}
	
				if ($width_orig != $width || $height_orig != $height) {
					$image = new Image(DIR_IMAGE . $image_old);
					$image->resize($width, $height);
					$image->save(DIR_IMAGE . $image_new);
				} else {
					copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
				}
			}
		}
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return $this->config->get('config_ssl') . 'image/' . $image_new;
		} else {
			return $this->config->get('config_url') . 'image/' . $image_new;
		}
	}
	
	public function cropsize($filename, $width, $height = 0) {
	
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$old_image = $filename;
		
		if (!$height) {
			
			$size = getimagesize(DIR_IMAGE . $old_image);
			
			$height  = ceil($width / $size[0] * $size[1]);

		} 

		$extension = 'webp';
		$new_image =  'cache/' .substr($filename, 0, strrpos($filename, '.')) . '-cr-' . $width . 'x' . $height . '.' . $extension;
		
		if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
			$path = '';
			
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;
				
				if (!file_exists(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}		
			}
			
			$image = new Image(DIR_IMAGE . $old_image);
			$image->cropsize($width, $height);
			$image->save(DIR_IMAGE . $new_image);
		}
		
		$image_new = str_replace(' ', '%20', $new_image);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return HTTPS_SERVER . 'image/' . $image_new;
		} else {
			return HTTP_SERVER . 'image/' . $image_new;
		}
			
	}
	
	public function resizeCrop($filename, $width, $height = 0) {
	
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$old_image = $filename;
		
		if (!$height) {
			
			$size = getimagesize(DIR_IMAGE . $old_image);
			
			$height  = ceil($width / $size[0] * $size[1]);

		}
		
		$extension = 'webp';
		$new_image =  'cache/' .substr($filename, 0, strrpos($filename, '.')) . '-cr-' . $width . 'x' . $height . '.' . $extension;
		
		if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
			$path = '';
			
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;
				
				if (!file_exists(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}		
			}
			
			$image = new Image(DIR_IMAGE . $old_image);
			$image->cropsize($width, $height);
			$image->save(DIR_IMAGE . $new_image);
		}
		
		$image_new = str_replace(' ', '%20', $new_image);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return HTTPS_SERVER . 'image/' . $image_new;
		} else {
			return HTTP_SERVER . 'image/' . $image_new;
		}
			
	}
	
	public function resizeTransparentAuto($filename, $width) {
		
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}
		
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		
		list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
		
		$height = round(($width / $width_orig) * ($height_orig));
		
		$extension = 'webp';
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);				 
			
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF,18)))
			 { 
				return DIR_IMAGE . $image_old;
			}
						
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}
		
		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
		
		if ($this->request->server['HTTPS']) {
			return HTTPS_SERVER . 'image/' . $image_new;
		} else {
			return HTTP_SERVER . 'image/' . $image_new;
		}
	}	

  public function resizeRaw($filename) {
	  if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
		  return;
	  }


	  $extension = pathinfo($filename, PATHINFO_EXTENSION);

	  $image_old = $filename;
	   list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
		 $width = $width_orig;
		 $height = $height_orig;
		 
		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

	  if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
		  list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
			$width = $width_orig;
			$height = $height_orig;
		  if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
			  return DIR_IMAGE . $image_old;
		  }

		  $path = '';

		  $directories = explode('/', dirname($image_new));

		  foreach ($directories as $directory) {
			  $path = $path . '/' . $directory;

			  if (!is_dir(DIR_IMAGE . $path)) {
				  @mkdir(DIR_IMAGE . $path, 0777);
			  }
		  }

		  if ($width_orig != $width || $height_orig != $height) {
			  $image = new Image(DIR_IMAGE . $image_old);
			  $image->resize($width, $height);
			  $image->save(DIR_IMAGE . $image_new);
		  } else {
			  copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
		  }
	  }

	  $image_new = str_replace(' ', ' ', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +

	  if ($this->request->server['HTTPS']) {
		  return $this->config->get('config_ssl') . 'image/' . $image_new;
	  } else {
		  return $this->config->get('config_url') . 'image/' . $image_new;
	  }
  }
	
}
