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
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path);
    }
}
