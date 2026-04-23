<?php

namespace App\Http\Controllers;

use App\Models\TrueLayerCredential;
use App\Services\TrueLayerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrueLayerCredentialController extends Controller
{
    public function __construct(private readonly TrueLayerService $trueLayerService)
    {
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $isValid = $this->trueLayerService->verifyCredentials(
            $request->input('client_id'),
            $request->input('client_secret')
        );

        if (!$isValid) {
            return back()->withErrors(['truelayer' => 'Invalid TrueLayer credentials. Please check your Client ID and Secret.']);
        }

        TrueLayerCredential::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'client_id' => $request->input('client_id'),
                'client_secret' => $request->input('client_secret'),
                'is_fully_setup' => false,
            ]
        );

        return back()->with('truelayer_status', 'verified');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $credential = Auth::user()->trueLayerCredential;

        if (!$credential) {
            return back()->withErrors(['truelayer' => 'Please verify your credentials first.']);
        }

        $credential->update(['is_fully_setup' => true]);

        return back()->with('truelayer_status', 'confirmed');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        Auth::user()->trueLayerCredential?->delete();
        return back()->with('truelayer_status', 'disconnected');
    }
}
