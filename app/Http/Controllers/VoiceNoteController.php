<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class VoiceNoteController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')
            ->whereNotNull('voice_note_path')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.voice_notes.index', compact('attendances'));
    }
}
