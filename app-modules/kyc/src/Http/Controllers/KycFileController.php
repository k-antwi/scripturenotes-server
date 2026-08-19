<?php

namespace Nucleus\Kyc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class KycFileController extends Controller
{
    /**
     * Stream a private KYC file from the configured disk.
     * Protected by the auth + kyc.reviewer middleware (registered in routes).
     */
    public function __invoke(Request $request, string $path): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $disk = config('kyc.storage_disk', 'kyc');

        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path);
    }
}
