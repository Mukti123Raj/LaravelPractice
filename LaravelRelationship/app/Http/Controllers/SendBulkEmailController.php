<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendSingleEmail;

class SendBulkEmailController extends Controller
{
    public function __invoke(Request $request)
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


