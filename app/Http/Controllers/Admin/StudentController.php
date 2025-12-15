<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['classroom', 'group']);
        
        // Filter by classroom
        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('matric_number', 'like', "%{$search}%");
            });
        }
        
        $students = $query->latest()->get();
        
        return response()->json([
            'students' => $students
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'group_id' => 'nullable|exists:classroom_groups,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'matric_number' => 'nullable|string|max:50',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student added successfully',
            'student' => $student->load(['classroom', 'group'])
        ], 201);
    }

    public function show(Student $student)
    {
        $student->load(['classroom', 'group', 'examSessions']);
        
        return response()->json([
            'student' => $student
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'group_id' => 'nullable|exists:classroom_groups,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'matric_number' => 'nullable|string|max:50',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'student' => $student->load(['classroom', 'group'])
        ]);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully'
        ]);
    }
}
