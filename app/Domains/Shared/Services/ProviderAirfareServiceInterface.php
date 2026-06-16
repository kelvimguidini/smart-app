<?php

namespace App\Domains\Shared\Services;

interface ProviderAirfareServiceInterface
{
    public function list(array $filters = []): array;
    public function store(array $data): object;
    public function update(int $id, array $data): object;
    public function activate(int $id): void;
    public function deactivate(int $id): void;
    public function delete(int $id): void;
}
