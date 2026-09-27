<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\CurrentBusiness;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $logs = AuditLog::query()
            ->where('business_id', $businessId)
            ->with('user')
            ->latest('created_at')
            ->paginate(50);

        return view('audit.index', compact('logs'));
    }
}
