<?php

namespace App\Domains\Budgets\Services;

use App\Domains\Budgets\Repositories\ProviderBudgetRepositoryInterface;
use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Services\NotificationServiceInterface;
use App\Domains\Hotels\Repositories\EventHotelRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EventABRepositoryInterface;
use App\Domains\Halls\Repositories\EventHallRepositoryInterface;
use App\Domains\Additions\Repositories\EventAddRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DefaultBudgetService implements BudgetServiceInterface
{
    protected $budgetRepository;
    protected $eventRepository;
    protected $userRepository;
    protected $notificationService;
    protected $eventHotelRepository;
    protected $eventABRepository;
    protected $eventHallRepository;
    protected $eventAddRepository;

    public function __construct(
        ProviderBudgetRepositoryInterface $budgetRepository,
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        NotificationServiceInterface $notificationService,
        EventHotelRepositoryInterface $eventHotelRepository,
        EventABRepositoryInterface $eventABRepository,
        EventHallRepositoryInterface $eventHallRepository,
        EventAddRepositoryInterface $eventAddRepository
    ) {
        $this->budgetRepository = $budgetRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
        $this->eventHotelRepository = $eventHotelRepository;
        $this->eventABRepository = $eventABRepository;
        $this->eventHallRepository = $eventHallRepository;
        $this->eventAddRepository = $eventAddRepository;
    }

    /**
     * @inheritDoc
     */
    public function sendBudgetLink(array $requestData, int $authUserId): bool
    {
        $providerId = $requestData['provider_id'];
        $eventId = $requestData['event_id'];
        $type = $requestData['type'];

        $pdfContent = null;
        if (($requestData['download'] ?? false) == "true" || ($requestData['attachment'] ?? false) == "true") {
            $proposalData = $this->eventRepository->getProposalData($eventId, $providerId, $type);
            $pdfContent = $this->notificationService->generatePdf('budgetPdf', [
                'event' => $proposalData['eventDataBase'],
                'provider' => $proposalData['providerDataBase']
            ]);
        }

        $user = $this->userRepository->find($authUserId);
        $msg = $requestData['message'] ? urldecode($requestData['message']) : "";
        
        if (($requestData['linkEmail'] ?? false) == "true" || ($requestData['linkEmail'] ?? false) == "1") {
            $link = route('budget', ['token' => $requestData['link']]);
            $msg .= "<div style='text-align: center;'>
                        <a href='{$link}' style='display: inline-block; padding: 10px 20px; border-radius: 5px; color: #fff; background-color: #007bff; text-decoration: none;'>Abrir o formulário</a>
                        <p style='font-size: 12px; margin-top: 10px;'>URL: {$link}</p>
                    </div>";
        }

        $emailData = [
            'body' => $msg,
            'hasAttachment' => ($requestData['attachment'] ?? false) == "true",
            'signature' => $user->signature ?? "",
            'subject' => "Orçamento de fornecedor"
        ];

        $cc = ($requestData['copyMe'] ?? false) == "true" ? $user->email : null;
        
        $sent = $this->notificationService->sendEmailWithPdf(
            $requestData['emails'], 
            "Orçamento de fornecedor", 
            $emailData, 
            $pdfContent, 
            'Orçamento.pdf', 
            $cc
        );

        if ($sent) {
            $this->notificationService->logEmail([
                'event_id' => $eventId,
                'provider_id' => $providerId,
                'sender_id' => $user->id,
                'body' => $msg,
                'attachment' => $pdfContent ?? '',
                'type' => 'budget',
                'to' => $requestData['emails'],
            ]);

            $updateData = ['token_budget' => $requestData['link'], 'sended_mail_link' => true];
            $this->updateProviderStatus($eventId, $providerId, $type, $updateData);
        }

        return $sent;
    }

    /**
     * @inheritDoc
     */
    public function evaluateBudget(int $budgetId, int $userId, string $decision): void
    {
        $this->budgetRepository->evaluateBudget($budgetId, $userId, $decision);
    }

    /**
     * @inheritDoc
     */
    public function submitBudget(array $data): void
    {
        $this->budgetRepository->saveBudget($data);
    }

    /**
     * Atualiza o status/token no repositório correspondente.
     */
    protected function updateProviderStatus(int $eventId, int $providerId, string $type, array $data): void
    {
        switch ($type) {
            case 'event_hotels': $this->eventHotelRepository->updateByEventAndProvider($eventId, $providerId, $data); break;
            case 'event_abs': $this->eventABRepository->updateByEventAndProvider($eventId, $providerId, $data); break;
            case 'event_halls': $this->eventHallRepository->updateByEventAndProvider($eventId, $providerId, $data); break;
            case 'event_adds': $this->eventAddRepository->updateByEventAndProvider($eventId, $providerId, $data); break;
        }
    }
}
