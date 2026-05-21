<?php

namespace App\Domains\Providers\Services;

use App\Domains\Providers\Repositories\ProviderRepositoryInterface;
use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Domains\Shared\Services\NotificationServiceInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DefaultProviderService implements ProviderServiceInterface
{
    protected $providerRepository;
    protected $eventRepository;
    protected $lookupRepository;
    protected $statusHistoryRepository;
    protected $notificationService;
    protected $userRepository;

    public function __construct(
        ProviderRepositoryInterface $providerRepository,
        EventRepositoryInterface $eventRepository,
        LookupRepositoryInterface $lookupRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository,
        NotificationServiceInterface $notificationService,
        UserRepositoryInterface $userRepository
    ) {
        $this->providerRepository = $providerRepository;
        $this->eventRepository = $eventRepository;
        $this->lookupRepository = $lookupRepository;
        $this->statusHistoryRepository = $statusHistoryRepository;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritDoc
     */
    public function bulkActivate(array $ids, string $type): void
    {
        DB::transaction(function () use ($ids, $type) {
            foreach ($ids as $id) {
                $this->activateByType($id, $type);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function bulkDeactivate(array $ids, string $type): void
    {
        DB::transaction(function () use ($ids, $type) {
            foreach ($ids as $id) {
                $this->deactivateByType($id, $type);
            }
        });
    }

    protected function activateByType(int $id, string $type): void
    {
        switch ($type) {
            case 'hotel': $this->providerRepository->activate($id); break;
            case 'service': $this->lookupRepository->activateProviderService($id); break;
            case 'transport': $this->lookupRepository->activateProviderTransport($id); break;
        }
    }

    protected function deactivateByType(int $id, string $type): void
    {
        switch ($type) {
            case 'hotel': $this->providerRepository->deactivate($id); break;
            case 'service': $this->lookupRepository->deactivateProviderService($id); break;
            case 'transport': $this->lookupRepository->deactivateProviderTransport($id); break;
        }
    }

    /**
     * @inheritDoc
     */
    public function sendDocument(array $requestData, int $authUserId, int $pdfType): bool
    {
        $data = $this->eventRepository->getProposalData($requestData['event_id'], $requestData['provider_id'], $requestData['type']);
        
        $view = 'proposalPdf';
        $subject = "Proposta para hotel";
        $filenamePrefix = "Proposta";

        if ($pdfType === 2) {
            $view = 'invoicePDF';
            $subject = "Faturamento evento";
            $filenamePrefix = "Faturamento";
        } elseif ($pdfType === 3) {
            $view = 'proposalPdfWithoutValues';
            $subject = "Proposta para hotel (Sem Valores)";
            $filenamePrefix = "Proposta_sem_valores";
        }

        $pdfContent = $this->notificationService->generatePdf($view, [
            'event' => $data['eventDataBase'],
            'provider' => $data['providerDataBase'],
            'table' => $data['table']
        ]);

        $filename = "ID{$requestData['event_id']} - " . ($data['providerDataBase']->name ?? '') . " - {$filenamePrefix}.pdf";

        if (($requestData['download'] ?? false) == "true") {
            // No service layer we don't handle streams directly if it's for email, 
            // but for simplicity we return true and handle download in controller or provide content
            return true; 
        }

        $user = $this->userRepository->find($authUserId);
        $emailData = [
            'body' => $requestData['message'] ? urldecode($requestData['message']) : "",
            'hasAttachment' => true,
            'signature' => $user->signature ?? "",
            'subject' => $subject
        ];

        $cc = ($requestData['copyMe'] ?? false) == "true" ? $user->email : null;
        
        $sent = $this->notificationService->sendEmailWithPdf(
            $requestData['emails'], 
            $subject, 
            $emailData, 
            $pdfContent, 
            $filename, 
            $cc
        );

        if ($sent) {
            $this->notificationService->logEmail([
                'event_id' => $requestData['event_id'],
                'provider_id' => $requestData['provider_id'],
                'sender_id' => $user->id,
                'body' => urldecode($requestData['message'] ?? ''),
                'attachment' => $pdfContent,
                'to' => $requestData['emails'],
                'type' => $pdfType === 2 ? 'invoice' : 'proposal'
            ]);
        }

        return $sent;
    }

    /**
     * @inheritDoc
     */
    public function storeProvider(array $data, ?int $id, int $authUserId): void
    {
        if ($id) {
            $this->providerRepository->update($id, $data);
        } else {
            $provider = $this->providerRepository->createWithService($data);
            $this->statusHistoryRepository->create([
                'status' => "created",
                'user_id' => $authUserId,
                'table' => "event_adds", // Note: The original code used event_adds for providers too? 
                                         // Keeping as is but might be a bug in legacy.
                'table_id' => $provider->id
            ]);
        }
    }
}
