<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Usuarios extends Component
{
    public string $busqueda = '';
    public array $usuarios = [];

    // Modal editar
    public bool $modalEditar = false;
    public ?int $editarId = null;
    public string $editNombre = '';
    public string $editApellido = '';
    public string $editDni = '';
    public string $editEmail = '';
    public string $editTelefono = '';
    public string $editRol = 'usuario';
    public bool $editEsSocio = false;
    public string $editPassword = '';

    // Modal eliminar
    public bool $modalEliminar = false;
    public ?int $eliminarId = null;

    public function mount(): void
    {
        $this->cargarUsuarios();
    }

    public function updatedBusqueda(): void
    {
        $this->cargarUsuarios();
    }

    public function cargarUsuarios(): void
    {
        $query = User::where('rol', '!=', 'admin');

        if (!empty($this->busqueda)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('apellido', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('email', 'like', '%' . $this->busqueda . '%');
            });
        }

        $this->usuarios = $query->orderBy('apellido')->get()->toArray();
    }

    public function abrirEditar(int $id): void
    {
        $user = User::find($id);
        if (!$user) return;

        $this->editarId     = $id;
        $this->editNombre   = $user->nombre;
        $this->editApellido = $user->apellido;
        $this->editDni      = $user->dni ?? '';
        $this->editEmail    = $user->email;
        $this->editTelefono = $user->telefono ?? '';
        $this->editRol      = $user->rol;
        $this->editEsSocio  = $user->es_socio;
        $this->editPassword = '';
        $this->modalEditar  = true;
    }

    public function guardarEdicion(): void
    {
        $rules = [
            'editNombre'   => 'required|string|min:2|max:100',
            'editApellido' => 'required|string|min:2|max:100',
            'editDni'      => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')->ignore($this->editarId)],
            'editEmail'    => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editarId)],
            'editTelefono' => 'nullable|string|max:20',
            'editRol'      => 'required|in:admin,control,usuario',
        ];

        if (!empty($this->editPassword)) {
            $rules['editPassword'] = 'string|min:6';
        }

        $this->validate($rules, [
            'editNombre.required'   => 'El nombre es obligatorio.',
            'editApellido.required' => 'El apellido es obligatorio.',
            'editEmail.required'    => 'El email es obligatorio.',
            'editEmail.unique'      => 'Ese email ya está en uso.',
            'editPassword.min'      => 'Mínimo 6 caracteres.',
        ]);

        $user = User::find($this->editarId);
        if (!$user) return;

        $data = [
            'nombre'   => ucfirst(strtolower(trim($this->editNombre))),
            'apellido' => ucfirst(strtolower(trim($this->editApellido))),
            'dni'      => trim($this->editDni) ?: null,
            'email'    => strtolower(trim($this->editEmail)),
            'telefono' => trim($this->editTelefono),
            'rol'      => $this->editRol,
            'es_socio' => $this->editEsSocio,
        ];

        if (!empty($this->editPassword)) {
            $data['password'] = Hash::make($this->editPassword);
        }

        $user->update($data);

        $this->modalEditar = false;
        $this->cargarUsuarios();
        $this->dispatch('toast', message: 'Usuario actualizado correctamente.', type: 'success');
    }

    public function confirmarEliminar(int $id): void
    {
        if ($id === Auth::id()) {
            $this->dispatch('toast', message: 'No podés eliminarte a vos mismo.', type: 'error');
            return;
        }
        $this->eliminarId    = $id;
        $this->modalEliminar = true;
    }

    public function eliminarUsuario(): void
    {
        if ($this->eliminarId === Auth::id()) return;

        User::destroy($this->eliminarId);
        $this->modalEliminar = false;
        $this->cargarUsuarios();
        $this->dispatch('toast', message: 'Usuario eliminado.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.usuarios')->layout('layouts.app');
    }
}
