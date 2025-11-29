<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Builders\ApiResponseBuilder;

class MemberController
{
    protected AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    protected function getJson(): array
    {
        $b = file_get_contents('php://input');
        return json_decode($b ?: '{}', true) ?: [];
    }

    public function register(): void
    {
        try {
            $payload = $this->getJson();
            $res = $this->auth->register($payload);
            ApiResponseBuilder::created($res, 'Registered')->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }

    public function login(): void
    {
        try {
            $p = $this->getJson();
            $res = $this->auth->login($p['email'] ?? '', $p['password'] ?? '');
            ApiResponseBuilder::ok($res)->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 401)->send();
        }
    }
}
