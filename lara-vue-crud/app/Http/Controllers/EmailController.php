<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Email;
use App\Models\Customer;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends Controller
{
    public function create(Request $request): Response
    {
        $customer = Customer::findOrFail($request->customer_id);
        return Inertia::render('emails/Create', [
            'customer' => $customer,
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'to' => 'required|array|min:1',
            'to.*' => 'required|email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'body' => 'required|string',
        ]);

        $details = [
            'subject' => $request->subject,
            'body' => $request->body,
        ];

        Mail::send([], [], function ($message) use ($request, $details) {
            $message->to($request->to)
                ->cc($request->cc ?? [])
                ->subject($details['subject'])
                ->html($details['body']);
        });

        // Optionally store email in DB
        Email::create([
            'subject' => $request->subject,
            'to' => $request->to,
            'cc' => $request->cc,
            'body' => $request->body,
            'customer_id' => $request->customer_id ?? null,
        ]);

        return response()->json(['message' => 'Email sent successfully.']);
    }
}
