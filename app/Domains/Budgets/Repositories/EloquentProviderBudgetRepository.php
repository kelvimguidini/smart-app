<?php

namespace App\Domains\Budgets\Repositories;

use App\Models\ProviderBudget;
use App\Models\ProviderBudgetItem;
use App\Models\EventHotel;
use App\Models\EventAB;
use App\Models\EventHall;
use App\Models\EventAdd;
use App\Models\EventHotelOpt;
use App\Models\EventABOpt;
use App\Models\EventHallOpt;
use App\Models\EventAddOpt;
use Illuminate\Support\Facades\DB;
use DateTime;

class EloquentProviderBudgetRepository implements ProviderBudgetRepositoryInterface
{
    public function find(int $id): ?ProviderBudget
    {
        return ProviderBudget::find($id);
    }

    public function findWithItems(int $id): ?ProviderBudget
    {
        return ProviderBudget::with('providerBudgetItems')->find($id);
    }

    public function findByToken(string $token): ?ProviderBudget
    {
        return ProviderBudget::where('token', $token)->first();
    }

    /**
     * @inheritDoc
     */
    public function getBudgetDataByToken(string $token): array
    {
        $eventHotel = EventHotel::with(['hotel', 'eventHotelsOpt.regime', 'eventHotelsOpt.purpose', 'eventHotelsOpt.apto_hotel', 'eventHotelsOpt.category_hotel', 'currency'])
            ->where('token_budget', $token)->first();
            
        $eventAb = EventAB::with(['ab', 'eventAbOpts.Local', 'eventAbOpts.service', 'eventAbOpts.service_type', 'currency'])
            ->where('token_budget', $token)->first();
            
        $eventHall = EventHall::with(['hall', 'eventHallOpts.purpose', 'currency'])
            ->where('token_budget', $token)->first();
            
        $eventAdd = EventAdd::with(['add', 'eventAddOpts.service', 'eventAddOpts.measure', 'eventAddOpts.frequency', 'currency'])
            ->where('token_budget', $token)->first();

        return [
            'eventHotel' => $eventHotel,
            'eventAb' => $eventAb,
            'eventHall' => $eventHall,
            'eventAdd' => $eventAdd
        ];
    }

    /**
     * @inheritDoc
     */
    public function saveBudget(array $data): ProviderBudget
    {
        return DB::transaction(function () use ($data) {
            $budget = isset($data['id']) && $data['id'] > 0 
                ? ProviderBudget::findOrFail($data['id']) 
                : new ProviderBudget();

            $budget->fill($data);
            $budget->save();

            // Salvar itens (Lógica simplificada para o repositório)
            if (isset($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    if (isset($itemData['id'])) {
                        $item = ProviderBudgetItem::findOrFail($itemData['id']);
                        $item->update($itemData);
                    } else {
                        $itemData['provider_budget_id'] = $budget->id;
                        ProviderBudgetItem::create($itemData);
                    }
                }
            }

            return $budget;
        });
    }

    /**
     * @inheritDoc
     */
    public function evaluateBudget(int $id, int $userId, int $decision): bool
    {
        return DB::transaction(function () use ($id, $userId, $decision) {
            $budget = ProviderBudget::with(['providerBudgetItems'])->findOrFail($id);

            if ($decision == 1) {
                // Atualizar cabeçalhos de serviços
                if ($budget->event_hotel_id) {
                    EventHotel::where('id', $budget->event_hotel_id)->update([
                        'iss_percent' => $budget->iss_fee_hotel,
                        'iva_percent' => $budget->iva_fee_hotel,
                        'service_percent' => $budget->hosting_fee_hotel,
                    ]);
                }
                if ($budget->event_ab_id) {
                    EventAB::where('id', $budget->event_ab_id)->update([
                        'iss_percent' => $budget->iss_fee_ab,
                        'iva_percent' => $budget->iva_fee_ab,
                        'service_percent' => $budget->hosting_fee_ab,
                    ]);
                }
                if ($budget->event_add_id) {
                    EventAdd::where('id', $budget->event_add_id)->update([
                        'iss_percent' => $budget->iss_fee_add,
                        'iva_percent' => $budget->iva_fee_add,
                        'service_percent' => $budget->hosting_fee_add,
                    ]);
                }
                if ($budget->event_hall_id) {
                    EventHall::where('id', $budget->event_hall_id)->update([
                        'iss_percent' => $budget->iss_fee_hall,
                        'iva_percent' => $budget->iva_fee_hall,
                        'service_percent' => $budget->hosting_fee_hall,
                    ]);
                }

                // Atualizar itens (Opções)
                foreach ($budget->providerBudgetItems as $item) {
                    if ($item->event_hotel_opt_id) {
                        EventHotelOpt::where('id', $item->event_hotel_opt_id)->update([
                            'kickback' => $item->comission,
                            'received_proposal' => $item->value
                        ]);
                    }
                    if ($item->event_ab_opt_id) {
                        EventABOpt::where('id', $item->event_ab_opt_id)->update([
                            'kickback' => $item->comission,
                            'received_proposal' => $item->value
                        ]);
                    }
                    if ($item->event_hall_opt_id) {
                        EventHallOpt::where('id', $item->event_hall_opt_id)->update([
                            'kickback' => $item->comission,
                            'received_proposal' => $item->value
                        ]);
                    }
                    if ($item->event_add_opt_id) {
                        EventAddOpt::where('id', $item->event_add_opt_id)->update([
                            'kickback' => $item->comission,
                            'received_proposal' => $item->value
                        ]);
                    }
                }
            }

            $budget->evaluated = true;
            $budget->approved = ($decision == 1);
            $budget->user_id = $userId;
            $budget->approval_date = new DateTime('now');
            
            return $budget->save();
        });
    }
}
