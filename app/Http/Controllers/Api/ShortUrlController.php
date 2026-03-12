<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortUrlController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ShortUrl::query()->with(['user', 'company']);

        if ($user->role->name === 'super_admin') {
            $shortUrls = $query->get();
        } elseif ($user->role->name === 'admin') {
            $shortUrls = $query
                ->where('company_id', '==', $user->company_id)
                ->get();
        } elseif ($user->role->name === 'member') {
            $shortUrls = $query
                ->where('user_id', '==', $user->id)
                ->get();
        } else {
            abort(403);
        }

        return response()->json([
            'data' => $shortUrls->map(function (ShortUrl $shortUrl) {
                return [
                    'id'           => $shortUrl->id,
                    'code'         => $shortUrl->code,
                    'short_url'    => url('/s/' . $shortUrl->code),
                    'original_url' => $shortUrl->original_url,
                    'company_name' => $shortUrl->company->name ?? null,
                    'created_by'   => $shortUrl->user->email ?? null,
                    'created_at'   => $shortUrl->created_at,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (in_array($user->role->name, ['super_admin'])) {
            return response()->json([
                'message' => 'You are not allowed to create short urls.',
            ], 403);
        }

        $validated = $request->validate([
            'original_url' => ['required', 'url', 'max:2048'],
        ]);

        $code = $this->generateUniqueCode();

        $shortUrl = ShortUrl::create([
            'code'        => $code,
            'original_url'=> $validated['original_url'],
            'user_id'     => $user->id,
            'company_id'  => $user->company_id,
        ]);

        return response()->json([
            'data' => [
                'id'           => $shortUrl->id,
                'code'         => $shortUrl->code,
                'short_url'    => url('/s/' . $shortUrl->code),
                'original_url' => $shortUrl->original_url,
            ],
        ], 201);
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = Str::random(6);
        } while (ShortUrl::where('code', $code)->exists());

        return $code;
    }
}
