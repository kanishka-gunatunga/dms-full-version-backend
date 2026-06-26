<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LicenseChecker;

class LicenseController extends Controller
{
    public function index()
    {
        // For now returning a simple status, can be expanded to a view if needed
        return response()->json([
            'valid' => app(LicenseChecker::class)->isValid()
        ]);
    }

    public function apply(Request $request)
    {
        $request->validate([
            'key' => 'required|string'
        ]);

        try {
            app(LicenseChecker::class)->applyKey($request->key);

            return response()->json([
                'success' => true,
                'message' => 'License applied successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
