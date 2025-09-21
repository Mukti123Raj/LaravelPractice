<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Jobs\SendSingleEmail;

class EmailController extends Controller
{
    public function compose()
    {
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
        }

        $classrooms = Classroom::whereHas('subjects', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();

        return view('teacher.email.compose', compact('classrooms'));
    }

    public function getStudentsByClass(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        
        $classroom = Classroom::with('students')->find($classroomId);
        
        if (!$classroom) {
            return response()->json(['error' => 'Classroom not found'], 404);
        }

        $studentEmails = $classroom->students->pluck('email')->toArray();
        
        return response()->json($studentEmails);
    }

    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required|array|min:1',
            'to.*' => 'required|email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        try {
            $toEmails = $request->input('to');
            $ccEmails = $request->input('cc', []);
            $subject = $request->input('subject');
            $body = $request->input('body');

            $allRecipients = array_merge($toEmails, $ccEmails);
            $totalRecipients = count($allRecipients);

            $mailDriver = config('mail.default');
            $delayMultiplier = $mailDriver === 'smtp' ? 15 : 1;

            foreach ($allRecipients as $index => $email) {
                $delay = $index * $delayMultiplier;
                
                SendSingleEmail::dispatch($email, $subject, $body)
                    ->delay(now()->addSeconds($delay));
            }

            $delayText = $delayMultiplier > 1 ? "{$delayMultiplier}-second intervals" : "1-second intervals";
            return redirect()->route('teacher.dashboard')->with('success', "Email queued for sending to {$totalRecipients} recipient(s). Emails will be sent with {$delayText} to respect rate limits.");
            
        } catch (\Exception $e) {
            \Log::error('Email queuing failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to queue emails. Please try again.']);
        }
    }
}
