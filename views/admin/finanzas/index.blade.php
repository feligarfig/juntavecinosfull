@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Gestión de Ingresos y Gastos</h1>
        <p class="text-gray-600 mb-4">Registra y controla las finanzas de la Junta de Vecinos La Flecha</p>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <button onclick="document.getElementById('modal').classList.remove('hidden')" class="btn primary mb-4">Agregar Transacción</button>

        <table class="w-full table-auto border-collapse text-sm">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2">Descripción</th>
                    <th class="p-2">Tipo</th>
                    <th class="p-2">Monto (CLP)</th>
                    <th class="p-2">Fecha</th>
                    <th class="p-2">Categoría</th>
                    <th class="p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientos as $m)
                    <tr class="border-b">
                        <td class="p-2">{{ $m->descripcion }}</td>
                        <td class="p-2">{{ $m->tipo }}</td>
                        <td class="p-2">{{ number_format($m->monto, 0, ',', '.') }}</td>
                        <td class="p-2">{{ \Carbon\Carbon::parse($m->fecha)->format('d-m-Y') }}</td>
                        <td class="p-2">{{ $m->categoria }}</td>
                        <td class="p-2 text-sm text-gray-500">—</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-center text-gray-500">No hay transacciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-xl">
            <h2 class="text-xl font-bold mb-4">Agregar Transacción</h2>
            <form method="POST" action="{{ route('finanzas.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium">Descripción</label>
                    <input type="text" name="descripcion" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Tipo</label>
                    <select name="tipo" required class="w-full border rounded px-3 py-2">
                        <option value="Ingreso">Ingreso</option>
                        <option value="Gasto">Gasto</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Monto (CLP)</label>
                    <input type="number" name="monto" min="0" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Fecha</label>
                    <input type="date" name="fecha" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium">Categoría</label>
                    <select name="categoria" required class="w-full border rounded px-3 py-2">
                        <option value="Membresía">Membresía</option>
                        <option value="Eventos">Eventos</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Otros">Otros</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" class="btn delete">Cancelar</button>
                    <button type="submit" class="btn primary">Guardar Transacción</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection