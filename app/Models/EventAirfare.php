<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAirfare extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'airfare_id',
        'airline_id',
        'currency_id',
        'aircraft',
        'passengers_info',
        'baggage_info',
        'flight_time',
        'pax_first',
        'pax_executiva',
        'pax_premium',
        'pax_economica',
        'total_pax',
        'prazo_cia',
        'status_contrato',
        'prazo_proposta',
        'inc_taxa_embarque',
        'inc_servico_bordo',
        'inc_porao',
        'inc_bagagem_bordo',
        'inc_sala_vip',
        'inc_fbo_origem',
        'inc_fbo_destino',
        'inc_alteracao_nomes',
        'taxa_embarque_unit',
        'total_taxa_embarque',
        'total_net_sem_4bts',
        'total_venda_sem_4bts',
        'resultado_bruto',
        'exchange_rate_brl',
        'tt_net_brl',
        'tt_venda_brl',
        'photo_1',
        'photo_2',
        'photo_3',
        'photo_4',
        'observations',
        'notes',
        'iss_percent',
        'service_percent',
        'iva_percent',
        'iof',
        'taxa_4bts',
        'service_charge',
        'invoice',
        'internal_observation',
        'customer_observation',
        'sended_mail',
        'sended_mail_link',
        'token_budget',
        'deadline_date',
        'payment_method',
        'order'
    ];

    protected $table = 'event_airfare';

    protected $id = 'id';

    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function provider()
    {
        return $this->hasOne(AirfareAirline::class, 'id', 'airline_id');
    }

    public function airline()
    {
        return $this->hasOne(AirfareAirline::class, 'id', 'airline_id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function eventAirfareOpts()
    {
        return $this->hasMany(EventAirfareOpt::class, 'event_airfare_id', 'id');
    }

    public function passengers()
    {
        return $this->hasMany(EventAirfarePassenger::class, 'event_airfare_id', 'id');
    }

    public function status_his()
    {
        return $this->hasMany(StatusHistory::class, 'table_id', 'id')
            ->where('table', 'event_airfares')
            ->orderByDesc('created_at');
    }
}
