<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    // Register student for exam
    public function registerSubmit(Request $request, $code)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'group_id' => 'nullable|exists:classroom_groups,id',
            'name' => 'required|string|max:255',
            'matric_number' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // Check if classroom is active
        $classroom = Classroom::where('id', $validated['classroom_id'])
            ->where('is_active', true)
            ->firstOrFail();

        // Create or update student
        $student = Student::updateOrCreate(
            [
                'email' => $validated['email'],
                'classroom_id' => $classroom->id
            ],
            [
                'group_id' => $validated['group_id'] ?? null,
                'name' => $validated['name'],
                'matric_number' => $validated['matric_number'],
                'phone' => $validated['phone'] ?? null,
            ]
        );

        // Store student ID in session
        session(['student_id' => $student->id]);
        session(['classroom_id' => $classroom->id]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'redirect' => route('exam.instructions', ['code' => $code])
        ]);
    }

    // Show exam instructions
    public function instructions($code)
    {
        $classroom = Classroom::where('code', $code)
            ->where('is_active', true)
            ->with('groups')
            ->firstOrFail();

        $studentId = session('student_id');
        if (!$studentId) {
            return redirect()->route('exam.register', ['code' => $code]);
        }

        $student = Student::findOrFail($studentId);

        return view('exam.instructions', compact('classroom', 'student'));
    }

    // Start exam
    public function start(Request $request, $code)
    {
        $classroom = Classroom::where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        $studentId = session('student_id');
        if (!$studentId) {
            return redirect()->route('exam.register', ['code' => $code]);
        }

        // Check if student already has an active session
        $existingSession = ExamSession::where('student_id', $studentId)
            ->where('classroom_id', $classroom->id)
            ->whereNull('completed_at')
            ->first();

        if ($existingSession) {
            return redirect()->route('exam.take', [
                'code' => $code,
                'session' => $existingSession->id
            ]);
        }

        // Get questions for this classroom
        $questions = $classroom->questions()
            ->inRandomOrder()
            ->limit($classroom->questions_per_exam)
            ->get();

        if ($questions->count() < $classroom->questions_per_exam) {
            return back()->with('error', 'Not enough questions available for this exam.');
        }

        // Create exam session
        $session = ExamSession::create([
            'student_id' => $studentId,
            'classroom_id' => $classroom->id,
            'started_at' => now(),
            'expires_at' => $classroom->timer_minutes ? now()->addMinutes($classroom->timer_minutes) : null,
        ]);

        // Store question order
        $questionOrder = $questions->pluck('id')->toArray();
        if ($classroom->shuffle_questions) {
            shuffle($questionOrder);
        }
        
        session(['question_order_' . $session->id => $questionOrder]);

        return redirect()->route('exam.take', [
            'code' => $code,
            'session' => $session->id
        ]);
    }
}
