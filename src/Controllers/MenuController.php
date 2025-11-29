<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\MenuService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\ValidationException;

class MenuController extends Controller
{
    private MenuService $service;
    public function __construct(MenuService $s) { $this->service = $s; }

    public function index(): void
    {
        $filters = $_GET ?? [];
        $data = $this->service->list($filters);
        $this->send($data);
    }

    public function show($id): void
    {
        try {
            $data = $this->service->get((int)$id);
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
        } catch (ValidationException $ve) {
            ApiResponseBuilder::error($ve->getMessage(), $ve->getCode() ?: 422, $ve->getErrors())->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }

    public function update($id): void
    {
        $data = $this->getJson();
        try {
            $menu = $this->service->update((int)$id, $data);
            $this->send($menu);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }

    public function destroy($id): void
    {
        try {
            $this->service->delete((int)$id);
            $this->send(['deleted' => true]);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();
        }
    }
}
