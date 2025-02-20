<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Activatable
{
    /**
     * Boot the trait.
     */
    protected static function bootActivatable()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', true);
        });
    }

    /**
     * Activate the model.
     */
    public function activate()
    {
        $this->active = true;
        $this->save();
    }

    /**
     * Deactivate the model.
     */
    public function deactivate()
    {
        $this->active = false;
        $this->save();
    }

    /**
     * Scope a query to only include active models.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to include all models, including inactive.
     */
    public function scopeWithInactive($query)
    {
        return $query->withoutGlobalScope('active');
    }
}
