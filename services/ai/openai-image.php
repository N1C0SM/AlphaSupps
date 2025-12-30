<?php
class OpenAIImageGenerator {
    private string $apiKey;
    private string $apiBase;
    private string $defaultModel;
    private string $defaultSize;
    private string $promptPrefix;
    private bool $autoVariation;

    private array $allowedSizes = [
        '1024x1024',
        '1792x1024',
        '1024x1792',
        '512x512',
        '256x256'
    ];

    public function __construct() {
        $this->apiKey = getenv('GEMINI_API_KEY')
            ?: getenv('gemini_API_KEY')
            ?: getenv('gemin_API_KEY')
            ?: '';

        // Gemini imagen endpoint (Google AI Studio)
        $this->apiBase = $this->normalizeGeminiBase(
            getenv('GEMINI_API_BASE')
            ?: getenv('gemini_API_BASE')
            ?: 'https://generativelanguage.googleapis.com/v1beta'
        );
        $this->defaultModel = $this->normalizeGeminiModel(
            getenv('GEMINI_IMAGE_MODEL')
            ?: getenv('gemini_IMAGE_MODEL')
            ?: 'imagen-3.0'
        );
        $this->defaultSize = getenv('GEMINI_IMAGE_SIZE')
            ?: getenv('gemini_IMAGE_SIZE')
            ?: '1024x1024';

        $this->promptPrefix = getenv('AI_IMAGE_PROMPT_PREFIX')
            ?: 'Imagen lifestyle premium para e-commerce de suplementos deportivos, iluminación realista, detalle alto, estética limpia:';
        $this->autoVariation = strtolower((string)getenv('AI_IMAGE_AUTO_VARIATION')) !== 'false';
    }

    /**
     * Construye el prompt final combinando prefijo (ajustable) + input del usuario.
     */
    public function buildPrompt(string $userPrompt, ?string $customPrefix = null, ?string $suffix = null): string {
        $userPrompt = trim($userPrompt);
        if ($userPrompt === '') {
            throw new InvalidArgumentException('El prompt no puede estar vacío');
        }

        $prefix = trim($customPrefix ?? $this->promptPrefix);
        $final = $prefix !== '' ? $prefix . ' ' . $userPrompt : $userPrompt;

        if ($suffix) {
            $final .= ' ' . trim($suffix);
        }

        return $final;
    }

    /**
     * Genera imágenes y devuelve URLs y metadata usada.
     */
    public function generate(string $userPrompt, array $options = []): array {
        if ($this->apiKey === '') {
            throw new RuntimeException('Falta la API key (GEMINI_API_KEY).');
        }

        $model = $options['model'] ?? $this->defaultModel;
        $promptSuffix = $options['prompt_suffix'] ?? null;
        $prompt = $this->buildPrompt($userPrompt, $options['prompt_prefix'] ?? null, $promptSuffix);
        $size = $this->sanitizeSize($options['size'] ?? $this->defaultSize);

        // Variación automática para evitar imágenes repetidas con el mismo prompt
        $randomize = array_key_exists('randomize', $options)
            ? filter_var($options['randomize'], FILTER_VALIDATE_BOOLEAN)
            : $this->autoVariation;

        $seed = $options['seed'] ?? null;
        if ($randomize && !$seed) {
            $seed = substr(sha1(uniqid((string)mt_rand(), true) . microtime()), 0, 12);
        }
        if ($seed) {
            $prompt .= ' | variation:' . preg_replace('/[^A-Za-z0-9_-]/', '', (string)$seed);
        }

        return $this->generateGemini($prompt, $model, $size, $options, $seed ?? '');
    }

    private function generateGemini(string $prompt, string $model, string $size, array $options, string $seed = ''): array {
        // Google AI Imagen endpoint
        $endpoint = $this->apiBase . '/models/imagegeneration:generate?key=' . urlencode($this->apiKey);
        $n = max(1, min((int)($options['n'] ?? 1), 4));

        $payload = [
            'model' => $model,
            'prompt' => [
                'text' => $prompt
            ],
            'numberOfImages' => $n,
            'image' => [
                'format' => 'PNG'
            ]
        ];

        // Tamaños: usamos el valor en prompt para guiar relación de aspecto, ya que la API no soporta size estándar
        $payload['prompt']['text'] .= " | size_hint:{$size}";
        if ($seed !== '') {
            $payload['prompt']['text'] .= " | random_detail:{$seed}";
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);

        $rawResponse = curl_exec($ch);
        if ($rawResponse === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Error de conexión con Gemini: ' . $err);
        }

        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($rawResponse, true);
        if ($httpCode >= 300) {
            $message = $decoded['error']['message'] ?? ('Respuesta inesperada (' . $httpCode . ')');
            throw new RuntimeException('Gemini devolvió un error: ' . $message);
        }

        $images = [];
        $items = $decoded['generatedImages'] ?? $decoded['data'] ?? [];
        foreach ($items as $item) {
            if (isset($item['image']['bytesBase64Encoded'])) {
                $images[] = 'data:image/png;base64,' . $item['image']['bytesBase64Encoded'];
            } elseif (isset($item['b64_json'])) {
                $images[] = 'data:image/png;base64,' . $item['b64_json'];
            }
        }

        if (!$images && isset($decoded['error']['message'])) {
            throw new RuntimeException('Gemini devolvió un error: ' . $decoded['error']['message']);
        }

        return [
            'provider' => 'gemini',
            'prompt' => $prompt,
            'model' => $model,
            'size' => $size,
            'count' => count($images),
            'images' => $images
        ];
    }

    private function normalizeGeminiBase(string $base): string {
        $base = trim($base);
        if ($base === '' || stripos($base, 'openai.com') !== false || stripos($base, 'openai.azure.com') !== false) {
            // Config errónea: usar base oficial de Gemini
            return 'https://generativelanguage.googleapis.com/v1beta';
        }

        return rtrim($base, '/');
    }

    private function normalizeGeminiModel(string $model): string {
        $model = trim($model);
        if ($model === '' || stripos($model, 'gpt-image') !== false || stripos($model, 'dall-e') !== false) {
            // Ajuste automático si el modelo configurado es de OpenAI
            return 'imagen-3.0';
        }

        return $model;
    }

    private function sanitizeSize(string $size): string {
        $size = trim($size) ?: $this->defaultSize;

        return in_array($size, $this->allowedSizes, true) ? $size : $this->defaultSize;
    }
}
