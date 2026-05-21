<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Generate an AI summary and determine recommended priority for a task.
     */
    public function generateSummary(Task $task): array
    {
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "Analyze the following task details:\n" .
                                              "Title: {$task->title}\n" .
                                              "Description: {$task->description}\n" .
                                              "User Priority: {$task->priority}\n" .
                                              "Status: {$task->status}\n\n" .
                                              "Please provide a JSON object containing exactly two keys:\n" .
                                              "1. 'ai_summary': A concise summary of the task (1-2 sentences) explaining its main goal.\n" .
                                              "2. 'ai_priority': An independent priority recommendation ('low', 'medium', or 'high') based on the complexity and urgency described.\n\n" .
                                              "Return ONLY the raw JSON block, no markdown formatting, no backticks, no comments."
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    // Clean up any markdown code block wraps if present
                    $text = trim($text);
                    if (str_starts_with($text, '```json')) {
                        $text = substr($text, 7);
                    }
                    if (str_starts_with($text, '```')) {
                        $text = substr($text, 3);
                    }
                    if (str_ends_with($text, '```')) {
                        $text = substr($text, 0, -3);
                    }
                    $text = trim($text);

                    $parsed = json_decode($text, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($parsed['ai_summary'], $parsed['ai_priority'])) {
                        return [
                            'ai_summary' => $parsed['ai_summary'],
                            'ai_priority' => strtolower($parsed['ai_priority']),
                        ];
                    }
                }
                
                Log::warning('AI Service: API call returned unexpected response or failed parsing. Body: ' . $response->body());
            } catch (\Exception $e) {
                Log::error('AI Service: Exception during API call: ' . $e->getMessage());
            }
        }

        // Fallback to mocked response
        return $this->getMockedResponse($task);
    }

    /**
     * Mock fallback response if API is unavailable or config is missing.
     */
    protected function getMockedResponse(Task $task): array
    {
        $titleLower = strtolower($task->title);
        $descLower = strtolower($task->description ?? '');
        
        // Dynamic mock priority based on content keywords
        $aiPriority = 'medium';
        if (str_contains($titleLower, 'urgent') || str_contains($descLower, 'critical') || str_contains($titleLower, 'launch') || str_contains($titleLower, 'deploy')) {
            $aiPriority = 'high';
        } elseif (str_contains($titleLower, 'minor') || str_contains($descLower, 'backlog') || str_contains($titleLower, 'low')) {
            $aiPriority = 'low';
        }

        // Dynamic mock summary based on input
        $descText = $task->description ? " aiming to " . rtrim(lcfirst($task->description), '.') : "";
        $aiSummary = "AI Summary: This task focuses on '{$task->title}'{$descText}. Evaluated as recommended priority: '{$aiPriority}' based on key objectives and urgency parameters.";

        return [
            'ai_summary' => $aiSummary,
            'ai_priority' => $aiPriority
        ];
    }
}
