<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->loadCount('appointments');

        $totalSpent = Appointment::where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        $appointments = Appointment::with(['service', 'user'])
            ->where('customer_id', $customer->id)
            ->latest('start')
            ->paginate(10);

        $lastAppointment = $customer->appointments()
            ->with('service', 'user')
            ->latest('start')
            ->first();

        return view('admin.customers.show', compact(
            'customer', 'totalSpent', 'appointments', 'lastAppointment'
        ));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'cpf'        => 'required|string|max:14|unique:customers,cpf',
            'phone'      => 'required|string|max:20',
            'birth_date' => 'required|date',
            'email'      => 'nullable|email|max:100',
            'photo'      => 'nullable|string',
            'notes'      => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();

        Customer::create($data);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'cpf'        => 'required|string|max:14|unique:customers,cpf,' . $customer->id,
            'phone'      => 'required|string|max:20',
            'birth_date' => 'required|date',
            'email'      => 'nullable|email|max:100',
            'photo'      => 'nullable|string',
            'notes'      => 'nullable|string',
        ]);

        $customer->update($data);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente removido com sucesso!');
    }
}
