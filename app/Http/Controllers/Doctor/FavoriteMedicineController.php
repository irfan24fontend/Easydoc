<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\FavoriteMedicineRequest;
use App\Models\Doctor;
use App\Models\DoctorFavoriteCompany;
use App\Models\DoctorMedicine;
use App\Models\Medicine;
use App\Services\FavoriteMedicineService;
use App\Services\MedicineSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteMedicineController extends Controller
{
    public function __construct(
        protected FavoriteMedicineService $favorites,
        protected MedicineSuggestionService $suggestions,
    ) {
    }

    protected function doctor(): Doctor
    {
        return auth()->user()->doctor;
    }

    public function index(): View
    {
        $doctor = $this->doctor();

        $rows = DoctorMedicine::where('doctor_id', $doctor->id)
            ->where(fn ($q) => $q->where('is_favorite', true)->orWhere('is_default', true))
            ->with('medicine')
            ->orderByDesc('is_default')
            ->orderBy('default_sort')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (DoctorMedicine $dm) => $dm->medicine)
            ->values();

        $companies = DoctorFavoriteCompany::where('doctor_id', $doctor->id)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        return view('doctor.favorites.index', compact('doctor', 'rows', 'companies'));
    }

    /** Medicine autocomplete (same ranking as the prescription form). */
    public function searchMedicines(Request $request): JsonResponse
    {
        return response()->json(
            $this->suggestions->suggest($this->doctor()->id, (string) $request->query('term', ''), 10)
        );
    }

    /** Company autocomplete, from companies that exist in the medicine list. */
    public function searchCompanies(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('term', ''));

        if (mb_strlen($term) < 1) {
            return response()->json([]);
        }

        return response()->json($this->favorites->searchCompanies($term));
    }

    public function storeMedicine(FavoriteMedicineRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->favorites->save($this->doctor(), (int) $data['medicine_id'], $data);

        return redirect()
            ->route('doctor.favorites.index')
            ->with('success', 'Medicine saved to your favorites.');
    }

    public function updateMedicine(FavoriteMedicineRequest $request, DoctorMedicine $doctorMedicine): RedirectResponse
    {
        $this->authorizeRow($doctorMedicine);

        $this->favorites->save($this->doctor(), (int) $doctorMedicine->medicine_id, $request->validated());

        return redirect()
            ->route('doctor.favorites.index')
            ->with('success', 'Favorite medicine updated.');
    }

    /** Remove from favorites/defaults (the row stays so usage ranking is not lost). */
    public function destroyMedicine(DoctorMedicine $doctorMedicine): RedirectResponse
    {
        $this->authorizeRow($doctorMedicine);

        $doctorMedicine->update([
            'is_favorite'         => false,
            'is_default'          => false,
            'default_dose'        => null,
            'default_dose_text'   => null,
            'default_duration'    => null,
            'default_instruction' => null,
        ]);

        return redirect()
            ->route('doctor.favorites.index')
            ->with('success', 'Removed from favorites.');
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:190'],
        ]);

        // Only companies that really exist in the medicine list can be chosen
        $exists = Medicine::where('company', $data['company'])->exists();

        if (! $exists) {
            return back()
                ->withErrors(['company' => 'Choose a company from the list that appears while you type.'])
                ->withInput();
        }

        $this->favorites->addCompany($this->doctor(), $data['company']);

        return redirect()
            ->route('doctor.favorites.index')
            ->with('success', 'Favorite brand added. Its medicines now appear first in your search.');
    }

    public function destroyCompany(DoctorFavoriteCompany $favoriteCompany): RedirectResponse
    {
        abort_unless($favoriteCompany->doctor_id === $this->doctor()->id, 404);

        $favoriteCompany->delete();

        return redirect()
            ->route('doctor.favorites.index')
            ->with('success', 'Favorite brand removed.');
    }

    protected function authorizeRow(DoctorMedicine $row): void
    {
        abort_unless($row->doctor_id === $this->doctor()->id, 404);
    }
}
