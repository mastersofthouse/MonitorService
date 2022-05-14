<?php
namespace SoftHouse\MonitoringService\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Monitoring extends Model
{
    use HasFactory;

    protected $table = "monitoring";

    protected $fillable = [
        "batch_uuid",
        "uuid",
        "authentication",
        "tenant",
        "hostname",
        "type",
        "context",
    ];

    protected $casts = [
        "authentication" => "array",
        "tenant" => "array",
        "context" => "array",
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string)Str::uuid(4);
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
