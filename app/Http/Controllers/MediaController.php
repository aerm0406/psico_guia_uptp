<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    /**
     * Sirve imágenes de publicaciones sin depender de un symlink en el VPS
     */
    public function showPublicacionMedia($filename)
    {
        $path = storage_path('app/public/publicaciones/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $type = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $type
        ]);
    }

    /**
     * Sirve fotos de perfil sin depender de un symlink en el VPS
     */
    public function showProfilePhoto($filename)
    {
        $path = storage_path('app/public/profile-photos/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $type = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $type
        ]);
    }
}
