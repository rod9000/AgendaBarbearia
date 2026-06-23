<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\WorkingHour;
use App\Services\AppointmentService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;
    protected $whatsapp;

    public function __construct(AppointmentService $appointmentService, WhatsAppService $whatsapp)
    {
        $this->appointmentService = $appointmentService;
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        $services = Service::where('active', true)->orderBy('name')->get();
        $products = Product::where('quantity', '>', 0)->orderBy('name')->get();

        if (auth()->user()->isAdmin()) {
            $users = \App\Models\User::where('active', true)->orderBy('name')->get();
        } else {
            $users = \App\Models\User::where('id', auth()->id())->orderBy('name')->get();
        }

        $workingHours = WorkingHour::where('active', true)->get()->groupBy('user_id');

        return view('admin.appointments.index', compact('customers', 'services', 'products', 'users', 'workingHours'));
    }

    public function calendarData(Request $request)
    {
        $appointments = $this->appointmentService->getCalendarData($request);
        return response()->json($appointments);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();

        if (!auth()->user()->isAdmin()) {
            $data['user_id'] = auth()->id();
        }

        $appointment = $this->appointmentService->create($data);

        return response()->json([
            'success' => true,
            'appointment' => $appointment->load(['customer', 'services', 'products', 'user']),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $data = $request->validated();

        if (!auth()->user()->isAdmin()) {
            $data['user_id'] = auth()->id();
        }

        $appointment = $this->appointmentService->update($appointment, $data);

        return response()->json([
            'success' => true,
            'appointment' => $appointment->fresh()->load(['customer', 'services', 'products', 'user', 'payment']),
        ]);
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $this->whatsapp->sendCancellation($appointment);

        $deleteAll = $request->boolean('delete_all_series');

        if ($deleteAll && $appointment->isRecurring()) {
            $appointment->children()->delete();
        }

        $appointment->delete();
        return response()->json(['success' => true]);
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after:start',
        ]);

        $appointment->update($data);

        return response()->json(['success' => true]);
    }
}
