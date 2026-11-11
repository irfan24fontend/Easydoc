<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function index(): View
    {
        $advertisements = Advertisement::orderBy('id')->paginate(12);
        $settings = AdvertisementSetting::current();

        return view('admin.advertisements.index', compact('advertisements', 'settings'));
    }

    public function create(): View
    {
        return view('admin.advertisements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'medicine_name' => ['required', 'string', 'max:160'],
            'medicine_details' => ['required', 'string', 'min:5', 'max:4000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Advertisement::create([
            'medicine_name' => $data['medicine_name'],
            'medicine_details' => $data['medicine_details'],
            'image_path' => $request->file('image')->store('advertisements', 'public'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement added.');
    }

    public function edit(Advertisement $advertisement): View
    {
        return view('admin.advertisements.edit', compact('advertisement'));
    }

    public function update(Request $request, Advertisement $advertisement): RedirectResponse
    {
        $data = $request->validate([
            'medicine_name' => ['required', 'string', 'max:160'],
            'medicine_details' => ['required', 'string', 'min:5', 'max:4000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $oldImage = $advertisement->image_path;
        $newImage = $request->file('image')?->store('advertisements', 'public');

        $advertisement->update([
            'medicine_name' => $data['medicine_name'],
            'medicine_details' => $data['medicine_details'],
            'image_path' => $newImage ?: $oldImage,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($newImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement updated.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'interval_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);

        AdvertisementSetting::current()->update([
            'interval_minutes' => $data['interval_minutes'],
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.advertisements.index')->with('success', 'Ad interval updated.');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        Storage::disk('public')->delete($advertisement->image_path);
        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement deleted.');
    }
}
