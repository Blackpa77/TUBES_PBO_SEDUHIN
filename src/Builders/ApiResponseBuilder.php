<?php
namespace App\Builders;

/**
 * Builder Pattern - Membangun objek kompleks step by step
 * Membuat API response yang konsisten
 */
class ApiResponseBuilder
{
    private int $status = 200;
    private ?array $data = null;
    private string $message = '';
    private array $meta = [];
    private array $errors = [];

    public static function from($data = null, int $status = 200): self
    {
        $b = new self();
        $b->status = $status;
        $b->data = is_array($data) ? $data : ($data ? ['data' => $data] : null);
        return $b;
    }

    public static function success($data = null, string $message = 'OK'): self
    {
        return self::from($data, 200)->setMessage($message);
    }

    public static function created($data = null, string $message = 'Created'): self
    {
        return self::from($data, 201)->setMessage($message);
    }

    public static function error(string $message, int $status = 400, array $errors = []): self
    {
        $b = new self();
        $b->status = $status;
        $b->message = $message;
        $b->errors = $errors;
        return $b;
    }

    public function setMessage(string $m): self { $this->message = $m; return $this; }
    public function addMeta(string $k, $v): self { $this->meta[$k] = $v; return $this; }
    public function setErrors(array $e): self { $this->errors = $e; return $this; }

    public function build(): array
    {
        $resp = [
            'success' => $this->status >=200 && $this->status < 300,
            'status_code' => $this->status
        ];
        if ($this->message !== '') $resp['message'] = $this->message;
        if ($this->data !== null) $resp['data'] = $this->data;
        if (!empty($this->errors)) $resp['errors'] = $this->errors;
        if (!empty($this->meta)) $resp['meta'] = $this->meta;
        return $resp;
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->build(), JSON_PRETTY_PRINT);
        exit;
    }
}
