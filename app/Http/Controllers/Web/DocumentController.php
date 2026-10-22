<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ListingDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Temporary signed-link download. The `signed` middleware validates the
 * signature + expiry before this runs — no session/token needed, and the
 * link is useless once tampered with or expired. Private paths never leak.
 */
class DocumentController extends Controller
{
    public function signedDownload(ListingDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk(config('aqar.document_disk'))->exists($document->path), 404);

        return Storage::disk(config('aqar.document_disk'))->download($document->path, $document->original_name);
    }
}
