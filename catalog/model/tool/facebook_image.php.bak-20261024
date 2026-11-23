<?php
class ModelToolFacebookImage extends Model {

    public function resizeFacebook($filename) {

        $source = DIR_IMAGE . $filename;

        if (!is_file($source)) {
            return '';
        }

        $real = realpath($source);
        $root = realpath(DIR_IMAGE);

        if (!$real || !$root ||
            strpos($real, $root . DIRECTORY_SEPARATOR) !== 0) {
            return '';
        }

        $info = @getimagesize($real);

        if (!$info) {
            return '';
        }

        $width = $info[0];
        $height = $info[1];

        if ($width < 1 || $height < 1) {
            return '';
        }

        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $src = @imagecreatefromjpeg($real);
                break;
            case IMAGETYPE_PNG:
                $src = @imagecreatefrompng($real);
                break;
            case IMAGETYPE_WEBP:
                $src = @imagecreatefromwebp($real);
                break;
            case IMAGETYPE_GIF:
                $src = @imagecreatefromgif($real);
                break;
            default:
                return '';
        }

        if (!$src) {
            return '';
        }

        $targetWidth = 1200;
        $targetHeight = 630;

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        imagealphablending($canvas, true);

        $scale = min(
            $targetWidth / $width,
            $targetHeight / $height
        );

        $newWidth = max(1, (int)round($width * $scale));
        $newHeight = max(1, (int)round($height * $scale));

        $x = (int)(($targetWidth - $newWidth) / 2);
        $y = (int)(($targetHeight - $newHeight) / 2);

        imagecopyresampled(
            $canvas,
            $src,
            $x,
            $y,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        $relative = 'cache/facebook/' . sha1($filename) . '-1200x630.jpg';
        $destination = DIR_IMAGE . $relative;
        $directory = dirname($destination);

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            imagedestroy($src);
            imagedestroy($canvas);
            return '';
        }

        if (!is_file($destination) ||
            filemtime($real) > filemtime($destination)) {

            if (!imagejpeg($canvas, $destination, 90)) {
                imagedestroy($src);
                imagedestroy($canvas);
                return '';
            }
        }

        imagedestroy($src);
        imagedestroy($canvas);

        $base = $this->config->get('config_ssl')
            ?: $this->config->get('config_url');

        return rtrim($base, '/') . '/image/' . $relative;
    }
}
