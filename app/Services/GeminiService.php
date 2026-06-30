<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * Get response from Gemini API or fallback to mock response.
     *
     * @param string $userMessage
     * @return string
     */
    public static function getResponse(string $userMessage): string
    {
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            return self::getFallbackResponse($userMessage);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "You are a helpful customer support chatbot for our app. Keep your answer friendly and concise (under 2 sentences). Reply in the same language and script (English, Urdu, or Roman Urdu) that the user used. The user says: \"{$userMessage}\""
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($data['candidates'][0]['content']['parts'][0]['text']);
                }
            }

            Log::error('Gemini API Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
        }

        return self::getFallbackResponse($userMessage);
    }

    /**
     * Basic mock responses in case Gemini API is not configured or fails.
     */
    private static function getFallbackResponse(string $userMessage): string
    {
        $normalized = strtolower(trim($userMessage));

        $responses = [
            'hello' => 'Hello! How can I help you? You can select from the quick reply options below.',
            'hi' => 'Hi there! How can I assist you? Please select an option below.',
            'help' => 'I can help you! If you want to talk to a live agent, type "i need to talk to a person".',
            'thanks' => 'You are welcome! If you have any more questions, feel free to ask.',
            'thank you' => 'You are welcome! If you have any more questions, feel free to ask.',
        ];

        foreach ($responses as $key => $reply) {
            if (str_contains($normalized, $key)) {
                return $reply;
            }
        }

        return 'I didn\'t quite understand that. Please select from the options below or type "i need to talk to a person" to connect with a live agent.';
    }
}
