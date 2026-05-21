<?php

namespace App\Domains\Budgets\Repositories;

use App\Models\ProviderBudget;

interface ProviderBudgetRepositoryInterface
{
    public function find(int $id): ?ProviderBudget;
    public function findWithItems(int $id): ?ProviderBudget;
    public function findByToken(string $token): ?ProviderBudget;
    
    /**
     * Busca os dados necessários para o formulário de orçamento via token.
     *
     * @param string $token
     * @return array
     */
    public function getBudgetDataByToken(string $token): array;

    /**
     * Salva ou atualiza um orçamento e seus itens.
     *
     * @param array $data
     * @return ProviderBudget
     */
    public function saveBudget(array $data): ProviderBudget;

    /**
     * Aprova ou reprova um orçamento, atualizando os registros de serviço vinculados.
     *
     * @param int $id
     * @param int $userId
     * @param int $decision (1 para aprovar, 0 para reprovar)
     * @return bool
     */
    public function evaluateBudget(int $id, int $userId, int $decision): bool;
}
