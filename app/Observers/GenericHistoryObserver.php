<?php

namespace App\Observers;

use App\Models\ProposalHistory;
use Illuminate\Support\Facades\Auth;

class GenericHistoryObserver
{
    public function created($model)
    {
        ProposalHistory::create([
            'table_name' => $model->getTable(),
            'record_id'  => $model->getKey(),
            'new_data'   => json_encode($model->attributesToArray()),
            'action'     => 'created',
            'user_id'    => Auth::id(),
        ]);
    }

    public function updated($model)
    {
        $oldData = $model->getOriginal();
        $newData = $model->getChanges();

        $table = $model->getTable();

        // Mapeie os campos relacionais e substitua os IDs pelos nomes
        $relationFields = [
            // Campos únicos
            'customer_id' => [
                'model' => \App\Models\Customer::class,
                'field' => 'name'
            ],
            'provider_id' => [
                'model' => \App\Models\Provider::class,
                'field' => 'name'
            ],
            'crd_id' => [
                'model' => \App\Models\CRD::class,
                'field' => 'name'
            ],
            'user_id' => [
                'model' => \App\Models\User::class,
                'field' => 'name'
            ],
            'hotel_operator' => [
                'model' => \App\Models\User::class,
                'field' => 'name'
            ],
            'air_operator' => [
                'model' => \App\Models\User::class,
                'field' => 'name'
            ],
            'land_operator' => [
                'model' => \App\Models\User::class,
                'field' => 'name'
            ],
            'event_id' => [
                'model' => \App\Models\Event::class,
                'field' => 'name'
            ],
            'ab_id' => [
                'model' => \App\Models\Provider::class,
                'field' => 'name'
            ],
            'currency_id' => [
                'model' => \App\Models\Currency::class,
                'field' => 'name'
            ],
            'vehicle_id' => [
                'model' => \App\Models\Vehicle::class,
                'field' => 'name'
            ],
            'model_id' => [
                'model' => \App\Models\CarModel::class,
                'field' => 'name'
            ],
            'brand_id' => [
                'model' => \App\Models\Brand::class,
                'field' => 'name'
            ],
            'transport_id' => [
                'model' => \App\Models\ProviderTransport::class,
                'field' => 'name'
            ],
            'apto_hotel_id' => [
                'model' => \App\Models\Apto::class,
                'field' => 'name'
            ],
            'category_hotel_id' => [
                'model' => \App\Models\Category::class,
                'field' => 'name'
            ],
            'regime_id' => [
                'model' => \App\Models\Regime::class,
                'field' => 'name'
            ],
            'purpose_id' => [
                'model' => \App\Models\Purpose::class,
                'field' => 'name'
            ],
            'hotel_id' => [
                'model' => \App\Models\Provider::class,
                'field' => 'name'
            ],
            'purpose_id' => [
                'model' => \App\Models\PurposeHall::class,
                'field' => 'name'
            ],
            'hall_id' => [
                'model' => \App\Models\Provider::class,
                'field' => 'name'
            ],
            'measure_id' => [
                'model' => \App\Models\Measure::class,
                'field' => 'name'
            ],
            'frequency_id' => [
                'model' => \App\Models\Frequency::class,
                'field' => 'name'
            ],
            'add_id' => [
                'model' => \App\Models\ProviderServices::class,
                'field' => 'name'
            ],
            'add_id' => [
                'model' => \App\Models\ProviderServices::class,
                'field' => 'name'
            ],
            'local_id' => [
                'model' => \App\Models\Local::class,
                'field' => 'name'
            ],
            'service_type_id' => [
                'model' => \App\Models\ServiceType::class,
                'field' => 'name'
            ],

        ];

        // Campos que dependem do contexto (tabela/modelo)
        if ($table === 'event_ab_opt') {
            $relationFields['service_id'] = [
                'model' => \App\Models\Service::class,
                'field' => 'name'
            ];
        }
        if ($table === 'event_add_opt') {
            $relationFields['service_id'] = [
                'model' => \App\Models\ServiceAdd::class,
                'field' => 'name'
            ];
        }
        if ($table === 'event_hall_opt') {
            $relationFields['service_id'] = [
                'model' => \App\Models\ServiceHall::class,
                'field' => 'name'
            ];
        }

        if ($table === 'broker_transports') {
            $relationFields['broker_id'] = [
                'model' => \App\Models\BrokerTransport::class,
                'field' => 'name'
            ];
            $relationFields['service_id'] = [
                'model' => \App\Models\TransportService::class,
                'field' => 'name'
            ];
        } else {
            $relationFields['broker_id'] = [
                'model' => \App\Models\Broker::class,
                'field' => 'name'
            ];
        }

        foreach ($relationFields as $field => $config) {
            if (isset($oldData[$field])) {
                $oldModel = $config['model']::find($oldData[$field]);
                $oldData[$field] = $oldModel ? $oldModel->{$config['field']} : $oldData[$field];
            }
            if (isset($newData[$field])) {
                $newModel = $config['model']::find($newData[$field]);
                $newData[$field] = $newModel ? $newModel->{$config['field']} : $newData[$field];
            }
        }

        ProposalHistory::create([
            'table_name' => $model->getTable(),
            'record_id'  => $model->getKey(),
            'old_data'   => json_encode($oldData),
            'new_data'   => json_encode($newData),
            'action'     => 'updated',
            'user_id'    => Auth::id(),
        ]);
    }

    public function deleted($model)
    {
        ProposalHistory::create([
            'table_name' => $model->getTable(),
            'record_id'  => $model->getKey(),
            'old_data'   => json_encode($model->getOriginal()),
            'action'     => 'deleted',
            'user_id'    => Auth::id(),
        ]);
    }
}
