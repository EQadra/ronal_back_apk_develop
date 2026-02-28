<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        // 🔒 Scope global por empresa
        static::addGlobalScope('company', function (Builder $builder) {
            if (
                !App::runningInConsole() &&
                auth()->check() &&
                auth()->user()->company_id
            ) {
                $builder->where(
                    $builder->getModel()->getTable() . '.company_id',
                    auth()->user()->company_id
                );
            }
        });

        // 🧠 Asignar company_id automáticamente
        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
