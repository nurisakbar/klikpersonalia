<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Services\SalaryComponentService;
use App\Http\Resources\SalaryComponentResource;
use App\Http\Requests\SalaryComponentRequest;
use App\DataTables\SalaryComponentDataTable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SalaryComponentController extends Controller
{
    protected $salaryComponentService;

    public function __construct(SalaryComponentService $salaryComponentService)
    {
        $this->salaryComponentService = $salaryComponentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SalaryComponentDataTable $dataTable): View
    {
        return $dataTable->render('salary-components.index');
    }

    /**
     * Get data for DataTable.
     */
    public function data(SalaryComponentDataTable $dataTable)
    {
        return $dataTable->ajax();
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
    public function store(SalaryComponentRequest $request)
    {
        try {
            $data = $request->validated();
            $data['company_id'] = Auth::user()->company_id;
            
            $result = $this->salaryComponentService->createComponent($data);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return redirect()->route('salary-components.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            $errorMessage = 'Terjadi kesalahan saat menambahkan komponen gaji: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryComponent $salaryComponent)
    {
        try {
            // Check if component belongs to current company
            if ($salaryComponent->company_id !== Auth::user()->company_id) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Komponen gaji tidak ditemukan atau tidak memiliki akses.'
                    ], 404);
                }
                
                return redirect()->route('salary-components.index')
                    ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
            }
            
            // If AJAX request, return JSON
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => new SalaryComponentResource($salaryComponent)
                ]);
            }
            
            // If regular request, return view
            return view('salary-components.show', compact('salaryComponent'));
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('salary-components.index')
                ->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalaryComponent $salaryComponent)
    {
        // Check if component belongs to current company
        if ($salaryComponent->company_id !== Auth::user()->company_id) {
            return redirect()->route('salary-components.index')
                ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
        }
        
        return view('salary-components.edit', compact('salaryComponent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SalaryComponentRequest $request, SalaryComponent $salaryComponent)
    {
        // Check if component belongs to current company
        if ($salaryComponent->company_id !== Auth::user()->company_id) {
            $errorMessage = 'Komponen gaji tidak ditemukan atau tidak memiliki akses.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 404);
            }
            
            return redirect()->route('salary-components.index')
                ->with('error', $errorMessage);
        }

        try {
            $data = $request->validated();
            
            $result = $this->salaryComponentService->updateComponent($salaryComponent->id, $data);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return redirect()->route('salary-components.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            $errorMessage = 'Terjadi kesalahan saat memperbarui komponen gaji: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryComponent $salaryComponent)
    {
        try {
            // Check if component belongs to current company
            if ($salaryComponent->company_id !== Auth::user()->company_id) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Komponen gaji tidak ditemukan atau tidak memiliki akses.'
                    ], 404);
                }
                
                return redirect()->route('salary-components.index')
                    ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
            }

            // Check if component is being used in payrolls
            if ($salaryComponent->isUsedInPayrolls()) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Komponen gaji tidak dapat dihapus karena masih digunakan dalam penggajian.'
                    ], 400);
                }
                
                return redirect()->route('salary-components.index')
                    ->with('error', 'Komponen gaji tidak dapat dihapus karena masih digunakan dalam penggajian.');
            }

            $result = $this->salaryComponentService->deleteComponent($salaryComponent->id);

            if (request()->ajax()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return redirect()->route('salary-components.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('salary-components.index')
                    ->with('error', $result['message']);
            }
                
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('salary-components.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the component.
     */
    public function toggleStatus(SalaryComponent $salaryComponent)
    {
        // Check if component belongs to current company
        if ($salaryComponent->company_id !== Auth::user()->company_id) {
            return redirect()->route('salary-components.index')
                ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
        }

        try {
            $toggled = $this->salaryComponentService->toggleComponentStatus($salaryComponent);
            
            $status = $salaryComponent->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->route('salary-components.index')
                ->with('success', "Komponen gaji berhasil {$status}.");
        } catch (\Exception $e) {
            return redirect()->route('salary-components.index')
                ->with('error', 'Terjadi kesalahan saat mengubah status komponen: ' . $e->getMessage());
        }
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

        try {
            $companyId = Auth::user()->company_id;
            $updated = $this->salaryComponentService->updateComponentsSortOrder($request->components, $companyId);

            return response()->json(['message' => 'Urutan komponen berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat memperbarui urutan: ' . $e->getMessage()], 500);
        }
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
