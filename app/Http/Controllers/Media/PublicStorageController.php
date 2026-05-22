<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicStorageController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        $disk = (string) config('filesystems.public_uploads_disk', config('filesystems.default'));

        abort_unless($path !== '', 404);
        abort_if(str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\') || str_contains($path, '\\'), 404);
        abort_unless($this->diskPodeServirMidiaPublica($disk), 404);
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path);
    }

    private function diskPodeServirMidiaPublica(string $disk): bool
    {
        $config = config("filesystems.disks.{$disk}");

        if (!is_array($config)) {
            return false;
        }

        if (($config['driver'] ?? null) === 's3') {
            return filled($config['bucket'] ?? null);
        }

        return $disk === 'public' || (($config['visibility'] ?? null) === 'public');
    }
}
