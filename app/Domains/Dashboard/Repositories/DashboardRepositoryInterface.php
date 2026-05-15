<?php

namespace App\Domains\Dashboard\Repositories;

use Illuminate\Http\JsonResponse;

interface DashboardRepositoryInterface
{
    /**
     * Retorna a contagem de eventos com validações pendentes.
     *
     * @param int|null $userId Filtro por operador (opcional)
     * @param array $roles Papéis do usuário (admin, hotel_operator, etc)
     * @return int
     */
    public function getPendingValidateCount(?int $userId, array $roles): int;

    /**
     * Retorna a contagem de registros agrupados por status (último status de cada).
     *
     * @return array
     */
    public function getEventStatusCounts(): array;

    /**
     * Retorna contagens de itens aguardando aprovação (específicos para hotel/transporte).
     *
     * @return array
     */
    public function getWaitApprovalCounts(): array;

    /**
     * Retorna a taxa de aprovação de orçamentos (links).
     *
     * @return float
     */
    public function getBudgetApprovalRate(): float;

    /**
     * Retorna dados para o gráfico de evolução mensal (eventos vs registros).
     *
     * @param int|null $userId
     * @param array $roles
     * @return array
     */
    public function getMonthlyEvolutionData(?int $userId, array $roles): array;

    /**
     * Retorna a distribuição de usuários por grupo (role).
     *
     * @return array
     */
    public function getUserGroupDistribution(): array;
}
