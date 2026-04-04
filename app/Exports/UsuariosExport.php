<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        private string $busqueda    = '',
        private string $filtroSocio = 'todos'
    ) {}

    public function query()
    {
        $query = User::where('rol', '!=', 'admin');

        if (!empty($this->busqueda)) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('apellido', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('email', 'like', '%' . $this->busqueda . '%');
            });
        }

        if ($this->filtroSocio === 'socio') {
            $query->where('es_socio', true);
        } elseif ($this->filtroSocio === 'no_socio') {
            $query->where('es_socio', false);
        }

        return $query->orderBy('apellido');
    }

    public function headings(): array
    {
        return ['Apellido', 'Nombre', 'DNI', 'Email', 'Teléfono', 'Socio', 'Rol'];
    }

    public function map($user): array
    {
        return [
            $user->apellido,
            $user->nombre,
            $user->dni ?? '',
            $user->email,
            $user->telefono ?? '',
            $user->es_socio ? 'Sí' : 'No',
            $user->rol,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
