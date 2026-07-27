<?php

namespace App\Controllers\Api;

class ImagenesApi extends BaseApiController
{
    private string $dir    = 'uploads/productos/';
    private array  $mimes  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private array  $exts   = ['jpg','jpeg','png','gif','webp'];

    public function get_all()
    {
        $ruta = FCPATH . $this->dir;
        if (!is_dir($ruta)) {
            mkdir($ruta, 0755, true);
        }

        $archivos = glob($ruta . '*.{' . implode(',', $this->exts) . '}', GLOB_BRACE) ?: [];

        usort($archivos, fn($a, $b) => filemtime($b) - filemtime($a));

        $imagenes = array_map(fn($f) => [
            'nombre' => basename($f),
            'url'    => base_url($this->dir . basename($f)),
            'bytes'  => filesize($f),
        ], $archivos);

        return $this->respond(['success' => true, 'imagenes' => $imagenes]);
    }

    public function upload()
    {
        $file = $this->request->getFile('imagen');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->fail('Archivo inválido o no recibido');
        }

        if (!in_array($file->getMimeType(), $this->mimes, true)) {
            return $this->fail('Solo se permiten imágenes JPG, PNG, GIF o WEBP');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->fail('La imagen no debe superar 5 MB');
        }

        $ruta  = FCPATH . $this->dir;
        $nombre = $this->nombreUnico($ruta, $file->getClientName());

        $file->move($ruta, $nombre);

        $this->redimensionar($ruta . $nombre, 400);

        return $this->respond([
            'success' => true,
            'nombre'  => $nombre,
            'url'     => base_url($this->dir . $nombre),
        ]);
    }

    private function redimensionar(string $ruta, int $maxAlto): void
    {
        [$w, $h, $tipo] = getimagesize($ruta);

        if ($h <= $maxAlto) return;

        $nuevoAlto  = $maxAlto;
        $nuevoAncho = (int) round($w * $maxAlto / $h);

        $src = match ($tipo) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($ruta),
            IMAGETYPE_PNG  => imagecreatefrompng($ruta),
            IMAGETYPE_GIF  => imagecreatefromgif($ruta),
            IMAGETYPE_WEBP => imagecreatefromwebp($ruta),
            default        => null,
        };

        if (!$src) return;

        $dst = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        // Preservar transparencia para PNG y GIF
        if ($tipo === IMAGETYPE_PNG || $tipo === IMAGETYPE_GIF) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparente = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $nuevoAncho, $nuevoAlto, $transparente);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $w, $h);

        match ($tipo) {
            IMAGETYPE_JPEG => imagejpeg($dst, $ruta, 88),
            IMAGETYPE_PNG  => imagepng($dst, $ruta, 8),
            IMAGETYPE_GIF  => imagegif($dst, $ruta),
            IMAGETYPE_WEBP => imagewebp($dst, $ruta, 88),
            default        => null,
        };

        imagedestroy($src);
        imagedestroy($dst);
    }

    private function nombreUnico(string $dir, string $original): string
    {
        // Sanitizar: minúsculas, solo alfanumérico, guión y punto
        $base = strtolower(pathinfo($original, PATHINFO_FILENAME));
        $base = preg_replace('/[^a-z0-9_-]/', '_', $base);
        $base = trim($base, '_') ?: 'imagen';
        $ext  = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        $nombre = $base . '.' . $ext;
        $i = 1;
        while (file_exists($dir . $nombre)) {
            $nombre = $base . '_' . $i . '.' . $ext;
            $i++;
        }
        return $nombre;
    }
}
