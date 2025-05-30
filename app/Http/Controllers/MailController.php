<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MailController extends Controller
{
    public function sendMail()
    {
        $apiKey = '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';
        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [  
            'sender' => [
                'name' => 'SIDRPPC',
                'email' => 'sidrppc8@gmail.com',
            ],
            'to' => [
                [
                    'email' => 'mikeprime99@gmail.com',
                ],
            ],
            'subject' => 'test',
            'htmlContent' => '<html><body><h1>Test</h1></body></html>', 
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Email sent']);
        } else {
            return response()->json(['message' => 'Email not sent', 'error' => $response->body()]);
        }
    }
}
