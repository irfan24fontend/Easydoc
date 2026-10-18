<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Medicine extends Model
{
    protected $fillable = [
    'brand_name',
    'generic_name',
    'strength',
    'company',
    'dosage_form',
    'category',
    'shortcut',
    'usage_count',
    'status',
    'is_sponsored',
    'sponsor_company',
    'sponsor_priority',
];

protected $casts = [
    'status' => 'boolean',
    'is_sponsored' => 'boolean',
];

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('brand_name', 'like', "{$term}%")
            ->orWhere('generic_name', 'like', "{$term}%")
            ->orWhere('shortcut', $term);
    }

    public function doctorUsages()
{
    return $this->hasMany(DoctorMedicine::class);
}

    /**
     * এই মেডিসিনের generic এর মধ্যে sponsored ব্র্যান্ড(গুলো) খুঁজে বের করে।
     * ব্যবহার হবে suggestion API তে — ডাক্তার "seclo" লিখলে এটা দিয়ে "Maxpro" (sponsored) বের হবে।
     */
    public static function sponsoredForGeneric(string $genericName)
    {
        return static::where('generic_name', $genericName)
            ->where('is_sponsored', true)
            ->where('status', true)
            ->orderBy('sponsor_priority', 'desc')
            ->get();
    }
}