<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Services\SalaryComponentService;
use App\Http\Resources\SalaryComponentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

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
    public function index()
    {
        return view('salary-components.index');
    }

    /**
     * Get data for DataTables.
     */
    public function data()
    {
        try {
            $components = $this->salaryComponentService->getPaginatedComponents();
            
            return response()->json([
                'draw' => request()->get('draw'),
                'recordsTotal' => $components->total(),
                'recordsFiltered' => $components->total(),
                'data' => SalaryComponentResource::collection($components)
            ]);
        } catch (Exception $e) {
            Log::error('DataTable error for salary components', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'draw' => request()->get('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Gagal memuat data komponen gaji'
            ], 500);
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
        $result = $this->salaryComponentService->createComponent($request->all());
        
        if ($request->expectsJson()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->route('salary-components.index')
                ->with('success', $result['message']);
        } else {
            return back()->withInput()
                ->with('error', $result['message']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryComponent $salaryComponent)
    {
        // Check if component belongs to current company
        if ($salaryComponent->company_id !== Auth::user()->company_id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komponen gaji tidak ditemukan atau tidak memiliki akses.'
                ], 404);
            }
            return redirect()->route('salary-components.index')
                ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
        }

        // Get component with additional data from service
        $componentData = $this->salaryComponentService->getComponentDetails($salaryComponent->id);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => new SalaryComponentResource($salaryComponent),
                'usage_stats' => $componentData['usage_stats'] ?? []
            ]);
        }

        return view('salary-components.show', compact('salaryComponent'));
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
    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        // Check if component belongs to current company
        if ($salaryComponent->company_id !== Auth::user()->company_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komponen gaji tidak ditemukan atau tidak memiliki akses.'
                ], 404);
            }
            return redirect()->route('salary-components.index')
                ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
        }

        $result = $this->salaryComponentService->updateComponent($salaryComponent->id, $request->all());
        
        if ($request->expectsJson()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->route('salary-components.index')
                ->with('success', $result['message']);
        } else {
            return back()->withInput()
                ->with('error', $result['message']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryComponent $salaryComponent)
    {
        // Check if component belongs to current company
        if ($salaryComponent->company_id !== Auth::user()->company_id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komponen gaji tidak ditemukan atau tidak memiliki akses.'
                ], 404);
            }
            return redirect()->route('salary-components.index')
                ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
        }

        $result = $this->salaryComponentService->deleteComponent($salaryComponent->id);
        
        if (request()->expectsJson()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->route('salary-components.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('salary-components.index')
                ->with('error', $result['message']);
        }
    }

    /**
     * Toggle component status.
     */
    public function toggleStatus(SalaryComponent $salaryComponent)
    {
        // Check if component belongs to current company
        if ($salaryComponent->company_id !== Auth::user()->company_id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komponen gaji tidak ditemukan atau tidak memiliki akses.'
                ], 404);
            }
            return redirect()->route('salary-components.index')
                ->with('error', 'Komponen gaji tidak ditemukan atau tidak memiliki akses.');
        }

        $result = $this->salaryComponentService->toggleComponentStatus($salaryComponent);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Status komponen berhasil diubah!' : 'Gagal mengubah status komponen.',
                'data' => new SalaryComponentResource($salaryComponent->fresh())
            ]);
        }
        
        return redirect()->route('salary-components.index')
            ->with($result ? 'success' : 'error', 
                $result ? 'Status komponen berhasil diubah!' : 'Gagal mengubah status komponen.');
    }


}
