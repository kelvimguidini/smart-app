<?php

namespace App\Domains\Shared\Repositories;

interface ProviderTransportRepositoryInterface
{
    public function list(array $filters = []): array;
    public function findById(int $id): ?object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function activate(int $id): void;
    public function deactivate(int $id): void;
    public function delete(int $id): void;
}
