<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;

class RedirectShortUrlController extends Controller
{
    public function show(Request $request, string $code)
    {
        $shortUrl = ShortUrl::where('code', $code)->firstOrFail();

        return redirect()->away($shortUrl->original_url);
    }
}
