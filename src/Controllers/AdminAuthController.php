<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\AdminAuthService;
use App\Builders\ApiResponseBuilder;

class AdminAuthController extends Controller
{
    private AdminAuthService $service;

    public function __construct(AdminAuthService $service)
    {
        $this->service = $service;
    }

    public function login(): void
    {
        try {
            $input = $this->getJson();
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';

            if(empty($email) || empty($password)) {
                throw new \Exception("Email dan Password wajib diisi");
            }

            $result = $this->service->login($email, $password);
            ApiResponseBuilder::success($result, "Login Berhasil")->send();

        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 401)->send();
        }
    }
}