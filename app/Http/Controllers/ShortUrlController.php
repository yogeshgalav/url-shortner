<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ShortUrlController extends Controller
{
    public function index()
    {
        return Inertia::render('ShortUrls/Index');
    }
}