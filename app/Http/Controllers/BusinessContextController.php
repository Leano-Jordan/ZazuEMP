<?php

namespace App\Http\Controllers;

use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BusinessContextController extends Controller
{
    public function change(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id' => ['required', 'integer'],
        ]);

        app(CurrentBusiness::class)->switchTo((int) $validated['business_id'], $request->user());

        return back()->with('success', 'Workspace changed.');
    }
}
