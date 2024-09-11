<?php

namespace App\Http\Controllers;

use App\Models\Ajustes;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AjustesController extends Controller
{
    public function index()
    {
        $preinscripcionHabilitada = false;
        $inscripcionHabilitada = false;
        $diferenciaDiasPreinscripcion = null;
        $diferenciaDiasInscripcion = null;
        try {
            $ajustes = Ajustes::find(1);
            if (!$ajustes) {
                throw new Exception('No se encontraron los ajustes.');
            }

            $inicioPreinscripcion = $ajustes->inicio_fecha_preinscripcion . ' ' . $ajustes->inicio_hora_preinscripcion;
            $finPreinscripcion = $ajustes->fin_fecha_preinscripcion . ' ' . $ajustes->fin_hora_preinscripcion;
            $inicioInscripcion = $ajustes->inicio_fecha_inscripcion . ' ' . $ajustes->inicio_hora_inscripcion;
            $finInscripcion = $ajustes->fin_fecha_inscripcion . ' ' . $ajustes->fin_hora_inscripcion;
            $now = Carbon::now();

            /**
             **Casos:
             **inscripciones habilitadas pero falta para la fecha y horario -> 'faltan tantos dias y horas para ...'
             **inscripciones habilitadas dentro de la fecha y horario -> permite realizar el proceso
             **inscripciones habilitadas pasadas de la fecha y horario -> 'el proceso de... ha finalizado. nos vemos en la prox. convocatoria
             **inscripciones deshabilitadas -> 'No se encuentra habilitada la convocatoria...'

             ** Muestro o no los botones einfo en la pantalla principal,
             ** o muestro los botones y dentro de cada página me dice si estan o no habilitadas las inscripciones y pre..
             **/
            if ($ajustes->habilitar_preinscripcion) {
                if ($now->between($inicioPreinscripcion, $finPreinscripcion)) {
                    $preinscripcionHabilitada = true;
                } elseif ($now < $inicioPreinscripcion) {
                    $diferenciaDiasPreinscripcion = $now->locale('es')->diffForHumans($inicioPreinscripcion, CarbonInterface::DIFF_ABSOLUTE, false);
                } else {
                    $diferenciaDiasPreinscripcion = -$now->locale('es')->diffForHumans($finPreinscripcion, CarbonInterface::DIFF_ABSOLUTE, false);
                }
                $preinscripcionHabilitada = true;
            }

            if ($ajustes->habilitar_inscripcion) {
                if ($now->between($inicioInscripcion, $finInscripcion)) {
                    $inscripcionHabilitada = true;
                } elseif ($now < $inicioInscripcion) {
                    $diferenciaDiasInscripcion = $now->locale('es')->diffForHumans($inicioInscripcion, CarbonInterface::DIFF_ABSOLUTE, false);
                } else {
                    $diferenciaDiasInscripcion = -$now->locale('es')->diffForHumans($finInscripcion, CarbonInterface::DIFF_ABSOLUTE, false);
                }
                $inscripcionHabilitada = true;
            }

            return view('inicio', compact('preinscripcionHabilitada', 'inscripcionHabilitada', 'diferenciaDiasPreinscripcion', 'diferenciaDiasInscripcion'));
        } catch (Exception $e) {
            Log::error('Error al procesar los ajustes: ' . $e->getMessage());
            return view('inicio', ['preinscripcionHabilitada' => false, 'inscripcionHabilitada' => false]);
        }
    }
}
