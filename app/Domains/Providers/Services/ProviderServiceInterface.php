<?php

namespace App\Domains\Providers\Services;

interface ProviderServiceInterface
{
    /**
     * Ativa fornecedores em massa.
     *
     * @param array $ids
     * @param string $type (hotel, service, transport)
     * @return void
     */
    public function bulkActivate(array $ids, string $type): void;

    /**
     * Inativa fornecedores em massa.
     *
     * @param array $ids
     * @param string $type (hotel, service, transport)
     * @return void
     */
    public function bulkDeactivate(array $ids, string $type): void;

    /**
     * Gera e envia documentos PDF (Proposta/Faturamento) para fornecedores.
     *
     * @param array $requestData
     * @param int $authUserId
     * @param int $pdfType (1: Proposta, 2: Faturamento, 3: Proposta Sem Valores)
     * @return bool
     */
    public function sendDocument(array $requestData, int $authUserId, int $pdfType): bool;

    /**
     * Salva um fornecedor base (Hotel).
     *
     * @param array $data
     * @param int|null $id
     * @param int $authUserId
     * @return void
     */
    public function storeProvider(array $data, ?int $id, int $authUserId): void;
}
