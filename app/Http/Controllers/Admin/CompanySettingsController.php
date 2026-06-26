<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySettingsController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;
        return view('admin.settings.company', compact('company'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'razao_social'  => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'cnpj'          => 'nullable|string|max:20',
            'phone'         => 'nullable|string|max:20',
            'whatsapp'      => 'nullable|string|max:20',
            'endereco'      => 'nullable|string|max:255',
            'numero'        => 'nullable|string|max:20',
            'bairro'        => 'nullable|string|max:255',
            'cidade'        => 'nullable|string|max:255',
            'cep'           => 'nullable|string|max:10',
            'uf'            => 'nullable|string|max:2',
            'complemento'   => 'nullable|string|max:255',
        ]);

        $company = Auth::user()->company;
        $company->update($data);

        return redirect()->route('admin.settings.company')
            ->with('success', 'Dados da empresa atualizados com sucesso!');
    }
}
