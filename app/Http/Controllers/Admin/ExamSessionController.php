<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use Illuminate\Http\Request;

class ExamSessionController extends Controller
{
    /**
     * Get all exam sessions with filters
     */
    public function index(Request $request)
    {
        $query = ExamSession::with(['student', 'classroom'])
            ->orderBy('created_at', 'desc');

        // Apply filters if provided
        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->get();

        return response()->json([
            'sessions' => $sessions
        ]);
    }

    /**
     * Get single exam session with detailed answers
     */
    public function show($id)
    {
        $session = ExamSession::with([
            'student',
            'classroom',
            'answers.question'
        ])->findOrFail($id);

        return response()->json($session);
    }

    /**
     * Get exam statistics
     */
    public function statistics()
    {
        $totalSessions = ExamSession::count();
        $completedSessions = ExamSession::whereNotNull('completed_at')->count();
        $averageScore = ExamSession::whereNotNull('score')->avg('score');
        
        $passRate = ExamSession::whereNotNull('score')
            ->where('score', '>=', 50)
            ->count();
        
        $totalGraded = ExamSession::whereNotNull('score')->count();
        $passPercentage = $totalGraded > 0 ? ($passRate / $totalGraded) * 100 : 0;

        return response()->json([
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'average_score' => round($averageScore, 2),
            'pass_rate' => round($passPercentage, 2)
        ]);
    }
}
