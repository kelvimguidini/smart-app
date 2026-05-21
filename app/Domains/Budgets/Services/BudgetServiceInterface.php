<?php

namespace App\Domains\Budgets\Services;

interface BudgetServiceInterface
{
    /**
     * Gera e envia o link de orçamento para um fornecedor.
     *
     * @param array $requestData
     * @param int $authUserId
     * @return bool
     */
    public function sendBudgetLink(array $requestData, int $authUserId): bool;

    /**
     * Avalia (aprova/reprova) um orçamento.
     *
     * @param int $budgetId
     * @param int $userId
     * @param string $decision
     * @return void
     */
    public function evaluateBudget(int $budgetId, int $userId, string $decision): void;

    /**
     * Salva os dados de um orçamento submetido pelo fornecedor.
     *
     * @param array $data
     * @return void
     */
    public function submitBudget(array $data): void;
}
