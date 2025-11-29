<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\MenuService;
use App\Builders\ApiResponseBuilder;

class MenuController extends Controller
{
    public function __construct(private MenuService $service) {}

    public function index(): void
    {
        $data = $this->service->list($_GET);
        $this->send($data);
    }

    public function show(int $id): void
    {
        try {
            $this->send($this->service->get($id));
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 404)->send();
        }
    }

    public function store(): void
    {
        try {
            $payload = $this->getJson();
            $menu = $this->service->create($payload);
            ApiResponseBuilder::created($menu, 'Menu created')->send();
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        }
    }

    public function update(int $id): void
    {
        try {
            $payload = $this->getJson();
            $menu = $this->service->update($id, $payload);
            $this->send($menu);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->service->delete($id);
            $this->send(['deleted' => true]);
        } catch (\Exception $e) {
            ApiResponseBuilder::error($e->getMessage(), 400)->send();
        }
    }
}
