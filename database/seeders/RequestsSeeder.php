<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Request;
use App\Models\RequestLog;
use App\Models\ProductUnit;
use App\Models\RequestProductUnit;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class RequestsSeeder extends Seeder
{
    public function run()
    {
        // Obtener unidades de producto disponibles
        $productUnits = ProductUnit::where('estado', 'disponible')->pluck('id')->toArray();

        if (empty($productUnits)) {
            $this->command->warn("No hay unidades de producto disponibles para prestar.");
            return;
        }

        $usersId = [2, 3, 4, 5, 6]; //

        // Crear 20 solicitudes
        for ($i = 0; $i < 1013; $i++) {

            $userId = Arr::random($usersId);

            // Fecha aleatoria en los últimos 6 meses
            $fechaSolicitud = Carbon::now()->subMonths(rand(0, 6))->subDays(rand(0, 30));

            // Cantidad de unidades a solicitar (máximo 3 para asegurarnos de tener disponibilidad)
            $cantidadSolicitada = rand(1, 3);

            // Seleccionar unidades de productos disponibles aleatorias
            $selectedUnits = Arr::random($productUnits, min($cantidadSolicitada, count($productUnits)));

            $request = Request::withoutEvents(function () use ($userId, $cantidadSolicitada, $fechaSolicitud) {
                // Crear la solicitud
                $request = Request::create([
                    'user_id' => $userId,
                    'cantidad_solicitada' => $cantidadSolicitada,
                    'estado' => 'pendiente',
                    'created_at' => $fechaSolicitud,
                    'updated_at' => $fechaSolicitud,
                ]);

                return $request; // Retornar la instancia del modelo
            });


            // Registrar el estado inicial en request_logs
            RequestLog::create([
                'request_id' => $request->id,
                'estado' => 'pendiente',
                'fecha_cambio' => $fechaSolicitud,
                'created_at' => $fechaSolicitud,
                'updated_at' => $fechaSolicitud,
            ]);

            // Insertar las unidades de productos en la tabla pivote request_product_units
            foreach ($selectedUnits as $unitId) {
                RequestProductUnit::create([
                    'request_id' => $request->id,
                    'product_unit_id' => $unitId,
                    'created_at' => $fechaSolicitud,
                    'updated_at' => $fechaSolicitud,
                ]);

                // Marcar la unidad como "reservado"
                ProductUnit::where('id', $unitId)->update(['estado' => 'reservado']);
            }

            // Decidir si se cambia el estado
            $estadoFinal = Arr::random(['aceptado', 'rechazado']);

            if ($estadoFinal === 'rechazado') {

                $motivosRechazo = [
                    'Falta de documentos requeridos',
                    'Información incompleta o incorrecta',
                    'No cumple con los criterios de elegibilidad',
                    'Cupo limitado',
                    'Otros motivos',
                ];

                $motivoRechazo = Arr::random($motivosRechazo);

                $fechaCambioEstado = $fechaSolicitud->copy()->addHours(rand(1, 6));

                Request::withoutEvents(function () use ($request, $estadoFinal, $motivoRechazo) {
                    $request->update([
                        'estado' => $estadoFinal,
                        'motivo_rechazo' => $motivoRechazo
                    ]);
                });

                RequestLog::create([
                    'request_id' => $request->id,
                    'estado' => $estadoFinal,
                    'fecha_cambio' => $fechaCambioEstado,
                    'created_at' => $fechaCambioEstado,
                    'updated_at' => $fechaCambioEstado,
                ]);
            }

            if ($estadoFinal === 'aceptado') {
                // $fechaCambioEstado = $fechaSolicitud->copy()->addDays(rand(1, 15));
                $fechaCambioEstado = $fechaSolicitud->copy()->addHours(rand(1, 6));

                Request::withoutEvents(function () use ($request, $estadoFinal) {
                    $request->update(['estado' => $estadoFinal]);
                });

                RequestLog::create([
                    'request_id' => $request->id,
                    'estado' => $estadoFinal,
                    'fecha_cambio' => $fechaCambioEstado,
                    'created_at' => $fechaCambioEstado,
                    'updated_at' => $fechaCambioEstado,
                ]);


                $fechaCompletado = $fechaCambioEstado->copy()->addHours(rand(1, 5));

                Request::withoutEvents(function () use ($request) {
                    $request->update(['estado' => 'completado']);
                });


                RequestLog::create([
                    'request_id' => $request->id,
                    'estado' => 'completado',
                    'fecha_cambio' => $fechaCompletado,
                    'created_at' => $fechaCompletado,
                    'updated_at' => $fechaCompletado,
                ]);

                // Marcar las unidades como "disponible" nuevamente
                ProductUnit::whereIn('id', $selectedUnits)->update(['estado' => 'disponible']);
            }
        }

        $this->command->info('solicitudes generadas correctamente.');
    }
}
