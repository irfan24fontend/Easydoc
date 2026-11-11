<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    /** Return the next active ad in creation order and count its impression. */
    public function next(Request $request): JsonResponse
    {
        $data = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);
        $afterId = (int) ($data['after_id'] ?? 0);

        $advertisement = Advertisement::query()
            ->where('is_active', true)
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->first();

        if (! $advertisement) {
            $advertisement = Advertisement::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
        }

        $settings = AdvertisementSetting::current();

        if (! $advertisement) {
            return response()->json([
                'advertisement' => null,
                'interval_minutes' => $settings->interval_minutes,
            ])->header('Cache-Control', 'no-store, private');
        }

        $advertisement->increment('impressions');

        return response()->json([
            'advertisement' => [
                'id' => $advertisement->id,
                'medicine_name' => $advertisement->medicine_name,
                'medicine_details' => $advertisement->medicine_details,
                // Use the current request host instead of APP_URL; local and staged
                // deployments often open the app through a different host name.
                'image_url' => url('storage/' . ltrim($advertisement->image_path, '/')),
            ],
            'interval_minutes' => $settings->interval_minutes,
        ])->header('Cache-Control', 'no-store, private');
    }
}
