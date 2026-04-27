<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Update the user's push subscription.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $subscription = $request->all();

        // Simpan data subscription ke kolom JSON
        $user->update([
            'push_subscription' => $subscription
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Push subscription updated successfully'
        ]);
    }

    /**
     * Remove the user's push subscription.
     */
    public function destroy()
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['push_subscription' => null]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Push subscription removed'
        ]);
    }
}
