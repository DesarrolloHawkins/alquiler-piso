<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SecureFileController extends Controller
{
    public function photo(Request $request, Photo $photo)
    {
        $isAuthenticated = auth()->check();
        $token = (string) $request->query('token', '');
        $photoToken = null;
        if (!empty($photo->reserva_id)) {
            $photoToken = Reserva::where('id', $photo->reserva_id)->value('token');
        }
        $hasValidToken = !empty($token) && !empty($photoToken) && hash_equals($photoToken, $token);

        if (!$isAuthenticated && !$hasValidToken) {
            abort(403, 'No autorizado para acceder al archivo');
        }

        if (Storage::disk('private')->exists($photo->url)) {
            $stream = Storage::disk('private')->readStream($photo->url);

            if ($stream === false) {
                abort(404, 'Archivo no encontrado');
            }

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . basename($photo->url) . '"',
            ]);
        }

        $legacyPath = public_path($photo->url);
        if (file_exists($legacyPath)) {
            return response()->file($legacyPath);
        }

        abort(404, 'Archivo no encontrado');
    }

    public function incidencia(Request $request)
    {
        if (!auth()->check()) {
            abort(403, 'No autorizado para acceder al archivo');
        }

        $path = (string) $request->query('path', '');
        if (empty($path)) {
            abort(404, 'Archivo no encontrado');
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
        if (
            str_contains($normalizedPath, '..') ||
            str_contains($normalizedPath, "\0") ||
            !preg_match('/^[A-Za-z0-9_\\-\\/\\.]+$/', $normalizedPath)
        ) {
            abort(403, 'Ruta de archivo no permitida');
        }

        // Limitar esta ruta a contenido operativo (incidencias/limpieza), nunca documentos sensibles.
        $allowedPrefixes = ['incidencias/', 'images/', 'photos/'];
        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($normalizedPath, $prefix)) {
                $isAllowed = true;
                break;
            }
        }
        if (!$isAllowed) {
            abort(403, 'Ruta de archivo no permitida para este endpoint');
        }

        if (Storage::disk('private')->exists($normalizedPath)) {
            $stream = Storage::disk('private')->readStream($normalizedPath);
            if ($stream === false) {
                abort(404, 'Archivo no encontrado');
            }

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . basename($normalizedPath) . '"',
            ]);
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            $legacyStream = Storage::disk('public')->readStream($normalizedPath);
            if ($legacyStream === false) {
                abort(404, 'Archivo no encontrado');
            }

            return response()->stream(function () use ($legacyStream) {
                fpassthru($legacyStream);
                fclose($legacyStream);
            }, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . basename($normalizedPath) . '"',
            ]);
        }

        abort(404, 'Archivo no encontrado');
    }

    public function avatar(Request $request)
    {
        if (!auth()->check()) {
            abort(403, 'No autorizado para acceder al archivo');
        }

        $path = (string) $request->query('path', '');
        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        if (
            empty($normalizedPath) ||
            str_contains($normalizedPath, '..') ||
            str_contains($normalizedPath, "\0") ||
            !str_starts_with($normalizedPath, 'avatars/')
        ) {
            abort(403, 'Ruta de archivo no permitida');
        }

        if (!Storage::disk('private')->exists($normalizedPath)) {
            abort(404, 'Archivo no encontrado');
        }

        $stream = Storage::disk('private')->readStream($normalizedPath);
        if ($stream === false) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($normalizedPath) . '"',
        ]);
    }
}
