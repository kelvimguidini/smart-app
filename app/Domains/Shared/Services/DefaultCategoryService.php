<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DefaultCategoryService implements CategoryServiceInterface
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->categoryRepository->list($filters, $perPage);
    }

    public function create(array $data): Category
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->categoryRepository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        if (isset($data['name'])) {
            $data['name'] = mb_strtoupper($data['name']);
        }
        return $this->categoryRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }

    public function activate(int $id): bool
    {
        return $this->categoryRepository->activate($id);
    }

    public function deactivate(int $id): bool
    {
        return $this->categoryRepository->deactivate($id);
    }
}
