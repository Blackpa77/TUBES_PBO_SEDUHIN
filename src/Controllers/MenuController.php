<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\MenuService;
use App\Builders\ApiResponseBuilder;

class MenuController extends Controller
{
    private MenuService $service;
    public function __construct(MenuService $s) { $this->service = $s; }

    public function index(): void
    {
        $filters = $_GET;
        $data = $this->service->list($filters);
        $this->send($data);
    }

    public function show(int $id): void
    {
        try {
            $data = $this->service->get($id);
            $this->send($data);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 404)->send();
        }
    }

    public function store(): void
    {
        $payload = $this->getJson();
        try {
            $menu = $this->service->create($payload);
            ApiResponseBuilder::created($menu, 'Menu created')->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400, $e instanceof \App\Exceptions\ValidationException ? $e->getErrors() : [])->send();
        }
    }

    public function update(int $id): void
    {
        $data = $this->getJson();
        try {
            $menu = $this->service->update($id, $data);
            $this->send($menu);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->service->delete($id);
            $this->send(['deleted' => true]);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }
}
