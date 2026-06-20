<?php

namespace vendor\isenselabs\watermark;

class iWatermark extends Library {
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT = 'text';
    const OPACITY_IMAGE = 'image';
    const OPACITY_GENERAL = 'general';
    const POSITION_TL = 'top_left';
    const POSITION_TC = 'top_center';
    const POSITION_TR = 'top_right';
    const POSITION_BL = 'bottom_left';
    const POSITION_BC = 'bottom_center';
    const POSITION_BR = 'bottom_right';
    const POSITION_RC = 'right_center';
    const POSITION_LC = 'left_center';
    const POSITION_C = 'center';

    private $base_image;
    private $type;
    private $font_size;
    private $font;
    private $text;
    private $color;
    private $image_file;
    private $opacity;
    private $rotation;
    private $position;

    public function watermark(&$base_image, $settings) {
        $this->base_image = &$base_image;

        $this->applySettings($settings);

        return $this->applyWatermark($this->generateWatermark());
    }

    private function applyWatermark($watermark) {
        if (empty($watermark)) {
            return $this->base_image->getImage();
        }
        
        $watermark_width = imagesx($watermark);
        $watermark_height = imagesy($watermark);

        $image_width = $this->base_image->getWidth();
        $image_height = $this->base_image->getHeight();
        
        if (empty($image_width) || empty($image_height)) {
            return $this->base_image->getImage();
        }

        // Resize watermark
        if ($image_width < $watermark_width || $image_height < $watermark_height) {
            if ($image_width < $watermark_width && $image_height < $watermark_height) {
                if ($watermark_width > $watermark_height) {
                    $watermark_proportion = $watermark_width / $watermark_height;
                    $watermark_new_width = $image_width;
                    $watermark_new_height = $watermark_new_width / $watermark_proportion;
                } else {
                    $watermark_proportion = $watermark_height / $watermark_width;
                    $watermark_new_height = $image_height;
                    $watermark_new_width = $watermark_new_height/$watermark_proportion;
                }
            } else if ($image_width < $watermark_width) {
                $watermark_proportion = $watermark_width / $watermark_height;
                $watermark_new_width = $image_width;
                $watermark_new_height = $watermark_new_width / $watermark_proportion;
            } else if ($image_height < $watermark_height) {
                $watermark_proportion = $watermark_height / $watermark_width;
                $watermark_new_height = $image_height;
                $watermark_new_width = $watermark_new_height/$watermark_proportion;
            }
            
            $watermark_new = imagecreatetruecolor($watermark_new_width, $watermark_new_height);
            $coloralpha = imagecolorallocatealpha($watermark_new, 0x00, 0x00, 0x00, 127); 
            imagefill($watermark_new, 0, 0, $coloralpha);
            imagecopyresampled($watermark_new, $watermark, 0, 0, 0, 0, $watermark_new_width, $watermark_new_height, $watermark_width, $watermark_height);

            $watermark_width = $watermark_new_width;
            $watermark_height = $watermark_new_height;
            $watermark = $watermark_new;
        }

        switch($this->position) {
            case self::POSITION_TL :
                $watermark_pos_x = 0;
                $watermark_pos_y = 0;
                break;
            case self::POSITION_TC :
                $watermark_pos_x = floor($image_width / 2 - $watermark_width / 2);
                $watermark_pos_y = 0;
                break;
            case self::POSITION_TR :
                $watermark_pos_x = $image_width - $watermark_width;
                $watermark_pos_y = 0;
                break;
            case self::POSITION_RC :
                $watermark_pos_x = $image_width - $watermark_width;
                $watermark_pos_y = floor($image_height / 2 - $watermark_height / 2);
                break;
            case self::POSITION_C :
                $watermark_pos_x = floor($image_width / 2 - $watermark_width / 2);
                $watermark_pos_y = floor($image_height / 2 - $watermark_height / 2);
                break;
            case self::POSITION_LC :
                $watermark_pos_x = 0;
                $watermark_pos_y = floor($image_height / 2 - $watermark_height / 2);
                break;
            case self::POSITION_BL :
                $watermark_pos_x = 0;
                $watermark_pos_y = $image_height - $watermark_height;
                break;
            case self::POSITION_BC :
                $watermark_pos_x = floor($image_width / 2 - $watermark_width / 2);
                $watermark_pos_y = $image_height - $watermark_height;
                break;
            case self::POSITION_BR :
                $watermark_pos_x = $image_width - $watermark_width;
                $watermark_pos_y = $image_height - $watermark_height;
                break;
        }
        
        $image = $this->base_image->getImage();

        if ($this->base_image->getMime() == 'image/png') {
            // Create a white background, the same size as the original.
            $background = imagecreatetruecolor($this->base_image->getWidth(), $this->base_image->getHeight());
            $white = imagecolorallocate($background, 255, 255, 255);
            imagefill($background, 0, 0, $white);

            // Merge the two images.
            imagecopyresampled(
                $background, $image,
                0, 0, 0, 0,
                $this->base_image->getWidth(), $this->base_image->getHeight(),
                $this->base_image->getWidth(), $this->base_image->getHeight());

            imagedestroy($image);
            $image = $background;
        }

        imagealphablending($image, true);
        imagealphablending($watermark, true);

        if ($this->use_image_opacity) {
            imagecopy($image, $watermark, $watermark_pos_x, $watermark_pos_y, 0, 0, $watermark_width, $watermark_height);
        } else {
            imagecopymerge($image, $watermark, $watermark_pos_x, $watermark_pos_y, 0, 0, $watermark_width, $watermark_height, $this->opacity);
        }

        imagedestroy($watermark);

        return $image;
    }

    private function generateWatermark() {
        if ($this->type == self::TYPE_IMAGE) {
            switch ($this->detectMime()) {
                case 'image/jpeg' : 
                    $watermark = imagecreatefromjpeg($this->image_file);
                    break;
                case 'image/png' :
                    $watermark = imagecreatefrompng($this->image_file);
                    break;
                default : 
                    return null;
            }

            $watermark_width = imagesx($watermark);
            $watermark_height = imagesy($watermark);
        
            if (!$this->use_image_opacity) {
                $background = imagecreatetruecolor($watermark_width, $watermark_height);
                $white = imagecolorallocatealpha($background, 0xFF, 0xFF, 0xFF, 127);
                imagefill($background, 0, 0, $white);
                imagecopy($background, $watermark, 0, 0, 0, 0, $watermark_width, $watermark_height);
                $watermark = $background;
            }
        } else {
            $font_box_size = imagettfbbox($this->font_size, 0, $this->font, $this->text);

            $watermark_width = ($font_box_size[4] - $font_box_size[6]) + 10;
            $watermark_height = 1.2 * ($font_box_size[1] - $font_box_size[7]) + 20;
            
            $watermark = imagecreatetruecolor($watermark_width, $watermark_height);
            $backgroundalpha = imagecolorallocatealpha($watermark, 0xFF, 0xFF, 0xFF, 127);
            $coloralpha = imagecolorallocatealpha($watermark, $this->color['r'], $this->color['g'], $this->color['b'], round(127 * (100 - $this->opacity) / 100));

            imagefill($watermark, 0, 0, $backgroundalpha);
            
            if (function_exists('imagettftext')) {
                imagettftext($watermark, $this->font_size, 0, 0, $this->font_size + 15, $coloralpha, $this->font, $this->text);
            } else {
                imagestring($watermark, 5, 5, 5, $this->text, $coloralpha);
            }
        }

        // Rotate watermark
        $watermark = imagerotate($watermark, $this->rotation, imagecolorallocatealpha($watermark, 0xFF, 0xFF, 0xFF, 127));

        return $watermark;
    }

    private function detectMime() {
        return mime_content_type($this->image_file);
    }

    private function applySettings($settings) {
        $this->type = $settings['type'];
        $this->font_size = (int)$settings['font_size'];
        $this->font = DIR_SYSTEM . 'library/vendor/isenselabs/watermark/fonts/' . $settings['font'];
        $this->text = $settings['text'];
        $this->color = $this->HexToRgb($settings['color']);
        $this->image_file = DIR_UPLOAD . $settings['image_file'];
        $this->use_image_opacity = $settings['type'] == self::TYPE_TEXT || $settings['opacity_type'] == self::OPACITY_IMAGE;
        $this->opacity = (int)$settings['opacity'];
        $this->rotation = (int)$settings['rotation'];
        $this->position = $settings['position'];
    }

    // Based on http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
    private function HexToRgb($hex) { 
        $hex = str_replace("#", "", $hex);
        
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }

        return array('r' => $r, 'g' => $g, 'b' => $b);
    }
}