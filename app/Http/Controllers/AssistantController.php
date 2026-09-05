<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(private readonly ChatbotService $chatbot) {}

    /**
     * Endpoint SAPA AI: terima pertanyaan warga, balas jawaban + saran.
     */
    public function ask(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        return response()->json(
            $this->chatbot->ask($data['message'], $request->user())
        );
    }
}
