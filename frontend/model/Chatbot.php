<?php
declare(strict_types=1);

class Chatbot
{
    private string $defaultApiKey = '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';

    public function ask(string $userMessage, array $chatHistory = []): array
    {
        $envKey = getenv('OPENROUTER_API_KEY');
        $apiKey = !empty($envKey) ? $envKey : $this->defaultApiKey;

        if (empty(trim($apiKey))) {
            return [
                'success' => false,
                'message' => 'OpenRouter API key is not configured.'
            ];
        }

        $url = 'https://openrouter.ai/api/v1/chat/completions';

$systemPrompt = <<<EOT
You are an expert, warm, and highly accurate AI travel assistant for Bhutan.

STRICT ACCURACY & TRUTH DIRECTIVES:
1. ONLY state verified historical, geographical, and cultural facts about Bhutan.
2. ABSOLUTELY NO GUESSING or inventing details. If you are not 100% certain about a specific historical event, name, year, or policy, state that clearly.
3. BHUTAN SOVEREIGNTY RULE: Bhutan was NEVER colonized by any foreign power. NEVER use terms like "freedom fighter", "independence struggle", or "liberation leader" for any Bhutanese figure.
4. MONARCHY RESPECT & LINEAGE:
   * 1st King: His Majesty Ugyen Wangchuck
   * 2nd King: His Majesty Jigme Wangchuck
   * 3rd King: His Majesty Jigme Dorji Wangchuck (Father of Modern Bhutan; Jigme Dorji National Park is named in his honor)
   * 4th King: His Majesty Jigme Singye Wangchuck (Father of Gross National Happiness)
   * 5th King (Current): His Majesty Jigme Khesar Namgyel Wangchuck
   * Dasho Jigme Palden Dorji was Bhutan's 1st Prime Minister (Lyonchen), NOT Home Minister or a freedom fighter.

MANDATORY OUTPUT FORMATTING RULES:
1. Use bullet points (*) ONLY when returning a list, breakdown, or multiple items.
2. For greetings, single-sentence answers, or conversational replies, DO NOT start the sentence with an asterisk (*). Respond in plain, clean text.
3. When returning a list, every bullet item MUST start on a brand-new line with an asterisk and space (e.g., "* Item").
4. ALWAYS place two line breaks (\n\n) before starting a new list or section title.

ACRONYM DIRECTIVES:
* "SDF" = Sustainable Development Fee. Always provide the full fee breakdown when asked about SDF.
* "MDPR" = Minimum Daily Package Rate. Explain that it is abolished.

VERIFIED BHUTAN KNOWLEDGE BASE:

**Sustainable Development Fee (SDF)**
* Standard International Tourists: $100 USD per person per night (valid until Aug 31, 2027)
* Children (Ages 6 to 11): $50 USD per night (50% discount)
* Children (Under 6): Exempt ($0 USD)
* Indian Nationals: Nu. 1,200 (or INR 1,200) per person per night
* Bangladeshi Nationals: $15 USD per person per night

**Visa & Entry Details**
* Visa Processing Fee: $40 USD (one-time fee)
* MDPR Status: Minimum Daily Package Rate is ABOLISHED
* Entry Ports: Paro International Airport (Air); Phuntsholing, Gelephu, Samdrup Jongkhar (Land borders)

**Jigme Dorji National Park Facts**
* Naming: Named in honor of His Majesty King Jigme Dorji Wangchuck (the 3rd Druk Gyalpo of Bhutan).
* Location: Spans across Thimphu, Paro, Punakha, Gasa, and Wangdue Phodrang districts.
* Highlights: Second-largest protected area in Bhutan; sanctuary for snow leopards, takins, Bengal tigers, and red pandas; home to Mount Jomolhari.
EOT;

        $messages = [
            [
                'role'    => 'system',
                'content' => $systemPrompt
            ]
        ];

        foreach ($chatHistory as $msg) {
            if (isset($msg['role'], $msg['text'])) {
                $role = ($msg['role'] === 'model') ? 'assistant' : 'user';
                $messages[] = [
                    'role'    => $role,
                    'content' => (string)$msg['text']
                ];
            }
        }

        $messages[] = [
            'role'    => 'user',
            'content' => $userMessage
        ];

        $payload = [
            'model' => 'openrouter/free',
            'models' => [
                'openrouter/free',
                'google/gemma-3-27b-it:free',
                'qwen/qwen-2.5-72b-instruct:free'
            ],
            'messages'    => $messages,
            'temperature' => 0.0,
            'max_tokens'  => 1000
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . trim($apiKey),
                'HTTP-Referer: http://localhost:3000',
                'X-Title: Bhutan Travel Assistant'
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $curlError
            ];
        }

        $data = json_decode((string)$response, true);

        if (!is_array($data)) {
            return [
                'success' => false,
                'message' => 'Invalid response received from OpenRouter.'
            ];
        }

        if (!empty($data['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'message' => trim((string)$data['choices'][0]['message']['content'])
            ];
        }

        $errorMessage = $data['error']['message'] ?? 'OpenRouter API request failed.';

        return [
            'success'   => false,
            'message'   => $errorMessage,
            'http_code' => $httpCode
        ];
    }
}