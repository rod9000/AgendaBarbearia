<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySettingsController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;

        if (!$company) {
            $company = Company::firstOrCreate(
                ['slug' => 'barbearia-andre'],
                ['name' => 'Barbearia Andrê', 'active' => true]
            );
            Auth::user()->update(['company_id' => $company->id]);
        }

        return view('admin.settings.company', compact('company'));
    }

    public function store(Request $request)
    {
        $company = Auth::user()->company;

        if (!$company) {
            return back()->with('error', 'Nenhuma empresa configurada.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'cep' => 'nullable|string|max:10',
            'uf' => 'nullable|string|max:2',
            'complemento' => 'nullable|string|max:255',
        ]);

        $company->update($data);

        return redirect()->route('admin.settings.company')
            ->with('success', 'Dados da empresa salvos!');
    }
}
