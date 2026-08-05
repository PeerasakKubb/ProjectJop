<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCurriculumService
{
    private ?string $lastUsedProvider = null;

    public function isConfigured(): bool
    {
        return config('services.ai.demo_mode') || ! empty($this->availableProviders());
    }

    public function currentProvider(): string
    {
        if ($this->lastUsedProvider) {
            return $this->lastUsedProvider;
        }

        if (config('services.ai.demo_mode') && empty($this->availableProviders())) {
            return 'demo (ออฟไลน์)';
        }

        $providers = $this->availableProviders();

        if (config('services.ai.demo_mode') && ! empty($providers)) {
            return implode(' → ', $providers) . ' (มี demo สำรอง)';
        }

        return empty($providers) ? 'none' : implode(' → ', $providers);
    }

    public function generateLesson(string $subject, string $gradeLevel, string $topic): array
    {
        try {
            return $this->chatJson($this->lessonPrompt($subject, $gradeLevel, $topic), 'lesson');
        } catch (\RuntimeException $e) {
            if (config('services.ai.demo_mode')) {
                $this->lastUsedProvider = 'demo';

                return $this->demoLesson($subject, $gradeLevel, $topic);
            }
            throw $e;
        }
    }

    public function generateExam(string $subject, string $gradeLevel, string $topic, int $count = 5): array
    {
        try {
            return $this->chatJson($this->examPrompt($subject, $gradeLevel, $topic, $count), 'exam');
        } catch (\RuntimeException $e) {
            if (config('services.ai.demo_mode')) {
                $this->lastUsedProvider = 'demo';

                return $this->demoExam($subject, $gradeLevel, $topic, $count);
            }
            throw $e;
        }
    }

    public function generateMoreQuestions(
        string $subject,
        string $gradeLevel,
        string $topic,
        int $count,
        array $existingQuestions = [],
    ): array {
        $existingList = empty($existingQuestions)
            ? 'ไม่มี'
            : implode("\n- ", $existingQuestions);

        $prompt = <<<PROMPT
สร้างข้อสอบเพิ่ม {$count} ข้อ (ห้ามซ้ำกับข้อเดิม)

วิชา: {$subject} | ระดับ: {$gradeLevel} | หัวข้อ: {$topic}

ข้อสอบที่มีอยู่แล้ว:
- {$existingList}

ข้อกำหนด: ปรนัย 4 ตัวเลือก ถูก 1 ข้อ, มีข้อวิเคราะห์อย่างน้อย 1 ข้อ, ทุกข้อมีเฉลย

ตอบเป็น JSON: {"questions": [...]}
PROMPT;

        try {
            return $this->chatJson($prompt, 'exam');
        } catch (\RuntimeException $e) {
            if (config('services.ai.demo_mode')) {
                $this->lastUsedProvider = 'demo';

                return $this->demoExam($subject, $gradeLevel, $topic . ' (เพิ่มเติม)', $count);
            }
            throw $e;
        }
    }

    private function demoLesson(string $subject, string $gradeLevel, string $topic): array
    {
        return [
            'title' => "บทเรียน: {$topic}",
            'content' => "วิชา {$subject} ระดับ {$gradeLevel}\n\n"
                . "1. ทฤษฎี\n{$topic} เป็นหัวข้อสำคัญในวิชา{$subject} นักเรียนควรเข้าใจแนวคิดหลัก นิยาม และหลักการที่เกี่ยวข้อง\n\n"
                . "2. ตัวอย่าง\nตัวอย่างในชีวิตประจำวัน: การประยุกต์ใช้ความรู้เรื่อง{$topic} ในสถานการณ์จริง เช่น ในห้องเรียน ห้องปฏิบัติการ หรือสภาพแวดล้อมรอบตัว\n\n"
                . "3. สรุป\n{$topic} ช่วยให้นักเรียน{$gradeLevel} มีพื้นฐานเพียงพอสำหรับการทำข้อสอบและการประยุกต์ใช้ต่อไป\n\n"
                . "[โหมด Demo — ใส่ GROQ_API_KEY ใน .env เพื่อให้ AI สร้างเนื้อหาเต็มรูปแบบ]",
        ];
    }

    private function demoExam(string $subject, string $gradeLevel, string $topic, int $count): array
    {
        $questions = [];

        for ($i = 1; $i <= $count; $i++) {
            if ($i === $count || $i % 4 === 0) {
                $questions[] = [
                    'question_text' => "ข้อ {$i}: อธิบายความสำคัญของ{$topic} ในวิชา{$subject} ({$gradeLevel})",
                    'type' => 'analytical',
                    'correct_answer' => $topic,
                    'explanation' => "{$topic} เป็นหัวข้อหลักที่นักเรียนต้องเข้าใจเพื่อนำไปประยุกต์ใช้",
                ];
            } else {
                $questions[] = [
                    'question_text' => "ข้อ {$i}: ข้อใดถูกต้องเกี่ยวกับ{$topic}?",
                    'type' => 'multiple_choice',
                    'explanation' => "เฉลยข้อ {$i}: ตัวเลือก B ถูกต้องตามหลักการของ{$topic}",
                    'options' => [
                        ['text' => 'ไม่เกี่ยวข้องกับ' . $subject, 'is_correct' => false],
                        ['text' => "เป็นหัวข้อสำคัญใน{$subject}", 'is_correct' => true],
                        ['text' => 'ใช้ได้เฉพาะมหาวิทยาลัย', 'is_correct' => false],
                        ['text' => 'ไม่ต้องทบทวนก่อนสอบ', 'is_correct' => false],
                    ],
                ];
            }
        }

        return [
            'title' => "แบบทดสอบ: {$topic}",
            'description' => "วิชา{$subject} {$gradeLevel} [โหมด Demo — ใส่ GROQ_API_KEY เพื่อ AI จริง]",
            'questions' => $questions,
        ];
    }

    public function gradeAnalyticalAnswer(string $question, string $correctAnswer, string $studentAnswer): array
    {
        if (! $this->isConfigured()) {
            $isCorrect = stripos($studentAnswer, $correctAnswer) !== false;

            return [
                'is_correct' => $isCorrect,
                'feedback' => $isCorrect ? 'ถูกต้อง' : 'ไม่ถูกต้อง คำตอบที่ถูก: ' . $correctAnswer,
            ];
        }

        $prompt = <<<PROMPT
ตรวจข้อสอบวิเคราะห์ ยอมรับคำตอบที่ใกล้เคียง

คำถาม: {$question}
แนวคำตอบ: {$correctAnswer}
คำตอบนักเรียน: {$studentAnswer}

ตอบ JSON: {"is_correct": true, "feedback": "อธิบายสั้นๆ"}
PROMPT;

        $result = $this->chatJson($prompt, 'grade');

        return [
            'is_correct' => (bool) ($result['is_correct'] ?? false),
            'feedback' => $result['feedback'] ?? '',
        ];
    }

    private function lessonPrompt(string $subject, string $gradeLevel, string $topic): string
    {
        return <<<PROMPT
สร้างเนื้อหาบทเรียนภาษาไทยคุณภาพสูง
วิชา: {$subject} | ระดับ: {$gradeLevel} | หัวข้อ: {$topic}
มี 3 ส่วน: ทฤษฎี, ตัวอย่าง, สรุป | 400-800 คำ

ตอบ JSON: {"title": "...", "content": "..."}
PROMPT;
    }

    private function examPrompt(string $subject, string $gradeLevel, string $topic, int $count): string
    {
        $analyticalCount = max(1, (int) round($count * 0.2));
        $mcCount = $count - $analyticalCount;

        return <<<PROMPT
สร้างข้อสอบภาษาไทย {$count} ข้อ คุณภาพสูง
วิชา: {$subject} | ระดับ: {$gradeLevel} | หัวข้อ: {$topic}

ข้อกำหนด:
- ปรนัย {$mcCount} ข้อ (4 ตัวเลือก ถูก 1 ข้อ) + วิเคราะห์ {$analyticalCount} ข้อ
- ระดับความยากผสม ง่าย/กลาง/ยาก
- ทุกข้อมีเฉลยอธิบายละเอียด

ตอบ JSON:
{
  "title": "ชื่อข้อสอบ",
  "description": "คำอธิบาย",
  "questions": [
    {
      "question_text": "...",
      "type": "multiple_choice",
      "explanation": "...",
      "options": [
        {"text": "A", "is_correct": false},
        {"text": "B", "is_correct": true},
        {"text": "C", "is_correct": false},
        {"text": "D", "is_correct": false}
      ]
    },
    {
      "question_text": "...",
      "type": "analytical",
      "correct_answer": "...",
      "explanation": "..."
    }
  ]
}
PROMPT;
    }

    private function chatJson(string $prompt, string $task = 'default'): array
    {
        $providers = $this->availableProviders();

        if (empty($providers)) {
            if (config('services.ai.demo_mode')) {
                throw new \RuntimeException('demo_fallback');
            }
            throw new \RuntimeException('ยังไม่ได้ตั้งค่า AI — เปิด AI_DEMO_MODE=true หรือใส่ GROQ_API_KEY');
        }

        $errors = [];

        foreach ($providers as $provider) {
            try {
                $content = match ($provider) {
                    'gemini' => $this->callGemini($prompt, $task),
                    'groq' => $this->callOpenAiCompatible($prompt, $task, 'groq'),
                    'openai' => $this->callOpenAiCompatible($prompt, $task, 'openai'),
                    default => throw new \RuntimeException("ไม่รองรับ {$provider}"),
                };

                $decoded = json_decode($content, true);

                if (! is_array($decoded)) {
                    throw new \RuntimeException('รูปแบบ JSON ไม่ถูกต้อง');
                }

                $this->lastUsedProvider = $provider;

                return $decoded;
            } catch (\RuntimeException $e) {
                $errors[] = "{$provider}: {$e->getMessage()}";
                Log::warning("AI provider {$provider} failed, trying next...", ['error' => $e->getMessage()]);

                if (! $this->isRetryableError($e)) {
                    throw $e;
                }
            }
        }

        if (config('services.ai.demo_mode')) {
            Log::info('AI providers failed, using demo mode', ['errors' => $errors]);
            $this->lastUsedProvider = 'demo';

            throw new \RuntimeException('demo_fallback');
        }

        throw new \RuntimeException(
            "AI ใช้ไม่ได้ — เปิด AI_DEMO_MODE=true ใน .env หรือใส่ GROQ_API_KEY (ฟรี)\n" . implode("\n", $errors)
        );
    }

    private function isRetryableError(\RuntimeException $e): bool
    {
        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'quota')
            || str_contains($msg, 'rate limit')
            || str_contains($msg, '429')
            || str_contains($msg, 'exceeded')
            || str_contains($msg, 'overloaded')
            || str_contains($msg, 'capacity');
    }

    /** @return list<string> */
    private function availableProviders(): array
    {
        $forced = config('services.ai.provider');

        if ($forced && $forced !== 'auto') {
            return $this->providerHasKey($forced) ? [$forced] : [];
        }

        return array_values(array_filter(
            ['groq', 'gemini', 'openai'],
            fn (string $p) => $this->providerHasKey($p),
        ));
    }

    private function providerHasKey(string $provider): bool
    {
        return match ($provider) {
            'gemini' => ! empty(config('services.gemini.api_key')),
            'groq' => ! empty(config('services.groq.api_key')),
            'openai' => ! empty(config('services.openai.api_key')),
            default => false,
        };
    }

    private function callGemini(string $prompt, string $task): string
    {
        $apiKey = config('services.gemini.api_key');
        $timeout = (int) config('services.ai.timeout', 120);
        $models = array_unique([
            config('services.gemini.model', 'gemini-1.5-flash'),
            'gemini-1.5-flash',
            'gemini-2.0-flash-lite',
        ]);

        $lastError = 'Unknown error';

        foreach ($models as $model) {
            $response = Http::timeout($timeout)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'systemInstruction' => [
                        'parts' => [['text' => 'คุณเป็นผู้เชี่ยวชาญด้านการศึกษาไทย ตอบเป็น JSON ที่ถูกต้องเท่านั้น']],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => $task === 'exam' ? 0.8 : 0.7,
                    ],
                ]
            );

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text', '{}');
            }

            $lastError = $response->json('error.message', $response->body());
            Log::warning("Gemini model {$model} failed", ['error' => $lastError]);

            if (! $this->isRetryableError(new \RuntimeException($lastError))) {
                break;
            }
        }

        throw new \RuntimeException('Gemini error: ' . $lastError);
    }

    private function callOpenAiCompatible(string $prompt, string $task, string $provider): string
    {
        $config = config("services.{$provider}");
        $model = match ($task) {
            'grade' => $config['grade_model'] ?? $config['model'],
            default => $config['model'],
        };

        $response = Http::withToken($config['api_key'])
            ->timeout((int) config('services.ai.timeout', 120))
            ->post(rtrim($config['base_url'], '/') . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'คุณเป็นผู้เชี่ยวชาญด้านการศึกษาไทย ตอบเป็น JSON ที่ถูกต้องเท่านั้น'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => $task === 'exam' ? 0.8 : 0.7,
            ]);

        if (! $response->successful()) {
            $error = $response->json('error.message', 'Unknown');
            throw new \RuntimeException(ucfirst($provider) . ' error: ' . $error);
        }

        return $response->json('choices.0.message.content', '{}');
    }
}
