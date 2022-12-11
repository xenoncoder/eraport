<?php

namespace App\Traits;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid as Generator;
trait Uuid
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            try {
                //dd($model);
                $model->{$model->getKeyName()} = Generator::uuid4()->toString();
                if (Schema::hasColumn($model->table, 'last_sync')) {
                    // The "users" table exists and has an "email" column...
                    $model->last_sync = now();
                }
            } catch (UnsatisfiedDependencyException $e) {
                abort(500, $e->getMessage());
            }
        });
    }
}