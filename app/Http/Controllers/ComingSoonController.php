<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComingSoonRequest;
use App\Models\ComingSoonEmail;
use Illuminate\Http\RedirectResponse;

class ComingSoonController extends Controller
{
    public function index(ComingSoonRequest $request): RedirectResponse
    {
        ComingSoonEmail::create([
            'email' => $request->get('email'),
        ]);

        return redirect()->back()->banner(__('You are on the waitlist. We will keep you updated.'));
    }
}
