<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Departments
        $departments = [
            'BIBLIOTECA',
            'DEPARTAMENTO DE HUMANIDADES',
            'DEPARTAMENTO DE INVESTIGACIÓN',
            'ESPECIALIZACIÓN EN DERECHO LABORAL Y SEGURIDAD SOCIAL',
            'ESPECIALIZACIÓN EN CONTRATACIÓN ESTATAL',
            'ESPECIALIZACIÓN EN GERENCIA DE IMPUESTOS',
            'ESPECIALIZACIÓN EN GESTIÓN DEL TALENTO HUMANO',
            'ESPECIALIZACIÓN EN PROMOCIÓN PSICOSOCIAL',
            'ESPECIALIZACIÓN EN SEGURIDAD INFORMÁTICA',
            'OFICINA DE INFRAESTRUCTURA TECNOLÓGICA',
            'OFICINA DE ACTIVOS FIJOS',
            'OFICINA DE BIENESTAR UNIVERSITARIO',
            'OFICINA DE COMPRAS',
            'OFICINA DE COMUNICACIONES',
            'OFICINA DE CONTABILIDAD',
            'OFICINA DE GESTIÓN HUMANA',
            'OFICINA DE INFRAESTRUCTURA FÍSICA',
            'OFICINA DE INTERNACIONALIZACIÓN',
            'OFICINA DE MERCADEO',
            'OFICINA DE MULTILINGÜISMO',
            'OFICINA DE PLANEACIÓN',
            'OFICINA DE PROYECCIÓN SOCIAL',
            'OFICINA DE TESORERÍA',
            'OFICINA DEL CENTRO DE ATENCIÓN AL DOCENTE (CAD)',
            'OFICINA DEL DEPARTAMENTO DE ADMISIONES REGISTRO Y CONTROL (DARC)',
            'PROGRAMA DE ADMINISTRACIÓN DE EMPRESAS',
            'PROGRAMA DE CONTADURÍA PÚBLICA',
            'PROGRAMA DE DERECHO',
            'PROGRAMA DE INGENIERÍA DE SISTEMAS',
            'PROGRAMA DE PSICOLOGÍA',
        ];

        foreach ($departments as $department) {
            Department::create(['nombre' => $department]);
        }
    }
}
