<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SalaryComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('salary-components.index');
    }

    /**
     * Get data for DataTable.
     */
    public function data()
    {
        try {
            $components = SalaryComponent::where('company_id', Auth::user()->company_id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return DataTables::of($components)
                ->addColumn('checkbox', function ($component) {
                    return view('salary-components.partials.checkbox', compact('component'))->render();
                })
                ->addColumn('type_badge', function ($component) {
                    return view('salary-components.partials.type-badge', compact('component'))->render();
                })
                ->addColumn('status_badge', function ($component) {
                    return view('salary-components.partials.status-badge', compact('component'))->render();
                })
                ->addColumn('formatted_value', function ($component) {
                    return 'Rp ' . number_format($component->default_value, 0, ',', '.');
                })
                ->addColumn('action', function ($component) {
                    return view('salary-components.partials.actions', compact('component'))->render();
                })
                ->addColumn('is_taxable', function ($component) {
                    return $component->is_taxable ? 
                        '<span class="badge badge-success">Ya</span>' : 
                        '<span class="badge badge-secondary">Tidak</span>';
                })
                ->addColumn('is_bpjs_calculated', function ($component) {
                    return $component->is_bpjs_calculated ? 
                        '<span class="badge badge-info">Ya</span>' : 
                        '<span class="badge badge-secondary">Tidak</span>';
                })
                ->rawColumns(['checkbox', 'type_badge', 'status_badge', 'action', 'is_taxable', 'is_bpjs_calculated'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('Error in SalaryComponent data method: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('salary-components.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_value' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'is_bpjs_calculated' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $component = SalaryComponent::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'description' => $request->description,
            'default_value' => $request->default_value,
            'type' => $request->type,
            'is_active' => $request->boolean('is_active'),
            'is_taxable' => $request->boolean('is_taxable'),
            'is_bpjs_calculated' => $request->boolean('is_bpjs_calculated'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('salary-components.index')
            ->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryComponent $salaryComponent)
    {
        $this->authorize('view', $salaryComponent);
        return view('salary-components.show', compact('salaryComponent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalaryComponent $salaryComponent)
    {
        $this->authorize('update', $salaryComponent);
        return view('salary-components.edit', compact('salaryComponent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $this->authorize('update', $salaryComponent);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_value' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'is_bpjs_calculated' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $salaryComponent->update([
            'name' => $request->name,
            'description' => $request->description,
            'default_value' => $request->default_value,
            'type' => $request->type,
            'is_active' => $request->boolean('is_active'),
            'is_taxable' => $request->boolean('is_taxable'),
            'is_bpjs_calculated' => $request->boolean('is_bpjs_calculated'),
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('salary-components.index')
            ->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryComponent $salaryComponent)
    {
        $this->authorize('delete', $salaryComponent);

        // Check if component is being used in payrolls
        if ($salaryComponent->isUsedInPayrolls()) {
            return redirect()->route('salary-components.index')
                ->with('error', 'Komponen gaji tidak dapat dihapus karena masih digunakan dalam penggajian.');
        }

        $salaryComponent->delete();

        return redirect()->route('salary-components.index')
            ->with('success', 'Komponen gaji berhasil dihapus.');
    }

    /**
     * Toggle the active status of the component.
     */
    public function toggleStatus(SalaryComponent $salaryComponent)
    {
        $this->authorize('update', $salaryComponent);

        $salaryComponent->update([
            'is_active' => !$salaryComponent->is_active
        ]);

        $status = $salaryComponent->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('salary-components.index')
            ->with('success', "Komponen gaji berhasil {$status}.");
    }

    /**
     * Update the sort order of components.
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'components' => 'required|array',
            'components.*.id' => 'required|uuid|exists:salary_components,id',
            'components.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->components as $component) {
            SalaryComponent::where('id', $component['id'])
                ->where('company_id', Auth::user()->company_id)
                ->update(['sort_order' => $component['sort_order']]);
        }

        return response()->json(['message' => 'Urutan komponen berhasil diperbarui.']);
    }

    /**
     * Bulk toggle status of components.
     */
    public function bulkToggleStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|uuid|exists:salary_components,id',
            'status' => 'required|boolean'
        ]);

        $updated = SalaryComponent::whereIn('id', $request->ids)
            ->where('company_id', Auth::user()->company_id)
            ->update(['is_active' => $request->status]);

        $statusText = $request->status ? 'diaktifkan' : 'dinonaktifkan';
        
        return response()->json([
            'success' => true,
            'message' => "{$updated} komponen gaji berhasil {$statusText}."
        ]);
    }

    /**
     * Bulk delete components.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|uuid|exists:salary_components,id'
        ]);

        $deleted = 0;
        $errors = [];

        foreach ($request->ids as $id) {
            $component = SalaryComponent::where('id', $id)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            if ($component) {
                // Check if component can be deleted
                if ($component->isUsedInPayrolls()) {
                    $errors[] = "Komponen '{$component->name}' tidak dapat dihapus karena masih digunakan dalam penggajian.";
                    continue;
                }

                if ($component->delete()) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $message = "{$deleted} komponen gaji berhasil dihapus.";
            if (!empty($errors)) {
                $message .= " Beberapa komponen tidak dapat dihapus: " . implode(', ', $errors);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada komponen yang berhasil dihapus. ' . implode(', ', $errors)
            ]);
        }
    }
}
