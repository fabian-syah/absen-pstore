<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * CONTROLLER DINONAKTIFKAN
 * 
 * Controller ini sebelumnya memiliki bug keamanan kritis:
 * - Hardcode User ID 47, sehingga siapa pun yang mengakses endpoint ini
 *   akan login sebagai user tersebut (session hijacking).
 * - Tidak ada session regeneration setelah Auth::login().
 * 
 * Route sudah dinonaktifkan di web.php.
 * Jangan aktifkan kembali tanpa implementasi WebAuthn/FIDO2 yang benar.
 */
class FingerprintAuthController extends Controller
{
    public function index()
    {
        abort(403, 'Fitur fingerprint login dinonaktifkan.');
    }

    public function authenticate(Request $request)
    {
        abort(403, 'Fitur fingerprint login dinonaktifkan.');
    }
}
