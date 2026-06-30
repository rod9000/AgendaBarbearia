@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-brand-800 leading-tight">{{ isset($customer) ? 'Editar Cliente' : 'Novo Cliente' }}</h2>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="card-pastel">
            <form method="POST" action="{{ isset($customer) ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
                @csrf
                @if(isset($customer)) @method('PUT') @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Nome Completo</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required class="input-pastel">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf', $customer->cpf ?? '') }}" required maxlength="14" class="input-pastel">
                    @error('cpf') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" required maxlength="20" class="input-pastel">
                    @error('phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Data de Nascimento</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', isset($customer) && $customer->birth_date ? $customer->birth_date->format('Y-m-d') : '') }}" class="input-pastel">
                    @error('birth_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" class="input-pastel">
                    @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700 mb-2">Foto</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-full overflow-hidden bg-brand-100 flex items-center justify-center flex-shrink-0">
                            <img id="photoPreview" src="" class="w-full h-full object-cover hidden">
                            <svg id="photoPlaceholder" class="w-8 h-8 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="space-y-2">
                            <label class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-brand-700 bg-brand-100 rounded-lg hover:bg-brand-200 cursor-pointer transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Escolher Arquivo
                                <input type="file" accept="image/*" onchange="handleFileUpload(this)" class="hidden">
                            </label>
                            <button type="button" onclick="openWebcam()" class="block text-sm text-brand-600 hover:text-brand-800 font-medium">📷 Tirar Foto</button>
                            <button type="button" onclick="removePhoto()" class="block text-sm text-rose-500 hover:text-rose-700 font-medium hidden" id="removePhotoBtn">Remover foto</button>
                        </div>
                    </div>
                    <input type="hidden" name="photo" id="photoInput" value="">
                    @error('photo') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-brand-700">Observações</label>
                    <textarea name="notes" rows="3" class="input-pastel">{{ old('notes', $customer->notes ?? '') }}</textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.customers.index') }}" class="btn-pastel-secondary">Cancelar</a>
                    <button type="submit" class="btn-pastel-primary">
                        {{ isset($customer) ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="webcamModal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-stone-800 rounded-2xl p-5 max-w-md w-full shadow-2xl">
        <div class="relative bg-black rounded-xl overflow-hidden mb-4">
            <video id="webcam" autoplay playsinline class="w-full aspect-[4/3] object-cover"></video>
            <canvas id="webcamCanvas" class="hidden"></canvas>
        </div>
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="capturePhoto()" class="btn-pastel-primary">Capturar</button>
            <button type="button" onclick="closeWebcam()" class="btn-pastel-secondary">Cancelar</button>
        </div>
    </div>
</div>

<script>
function handleFileUpload(input) {
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        setPhoto(e.target.result);
    };
    reader.readAsDataURL(file);
}

function openWebcam() {
    document.getElementById('webcamModal').classList.remove('hidden');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } })
        .then(function(stream) {
            window.webcamStream = stream;
            document.getElementById('webcam').srcObject = stream;
        })
        .catch(function() {
            alert('Não foi possível acessar a câmera. Verifique as permissões.');
            closeWebcam();
        });
}

function capturePhoto() {
    var video = document.getElementById('webcam');
    var canvas = document.getElementById('webcamCanvas');
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    canvas.getContext('2d').drawImage(video, 0, 0);
    setPhoto(canvas.toDataURL('image/jpeg', 0.8));
    closeWebcam();
}

function closeWebcam() {
    if (window.webcamStream) {
        window.webcamStream.getTracks().forEach(function(t) { t.stop(); });
        window.webcamStream = null;
    }
    document.getElementById('webcamModal').classList.add('hidden');
}

function setPhoto(dataUrl) {
    document.getElementById('photoInput').value = dataUrl;
    var preview = document.getElementById('photoPreview');
    preview.src = dataUrl;
    preview.classList.remove('hidden');
    document.getElementById('photoPlaceholder').classList.add('hidden');
    document.getElementById('removePhotoBtn').classList.remove('hidden');
}

function removePhoto() {
    document.getElementById('photoInput').value = '';
    document.getElementById('photoPreview').src = '';
    document.getElementById('photoPreview').classList.add('hidden');
    document.getElementById('photoPlaceholder').classList.remove('hidden');
    document.getElementById('removePhotoBtn').classList.add('hidden');
}
</script>
@endsection
