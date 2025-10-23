<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use Illuminate\Http\Request;
use App\Events\ViolationCreated;

class ViolationController extends Controller
{

       public function store(Request $request)
    {
         \Log::info('🚀 Controller ViolationController@store() DIPANGGIL');

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $violation = Violation::create([
            'student_id' => $request->student_id,
            'teacher_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        \Log::info('📤 Event ViolationCreated akan DIKIRIM untuk ID: ' . $violation->id);

        // Panggil event
        event(new ViolationCreated($violation));

        return back()->with('success', 'Pelanggaran berhasil dilaporkan.');
    }
}
