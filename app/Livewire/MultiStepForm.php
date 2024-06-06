<?php

namespace App\Livewire;


use App\Models\DatoEstudiante;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Tutor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

use function Laravel\Prompts\alert;

class MultiStepForm extends Component
{
    public $currentStep = 1;
    public $total_steps = 5;
    /* STEP 1 */
    public $nombre;
    public $apellido;
    public $genero = "";
    public $fecha_nac;
    public $email;
    public $telefono;
    public $cuil;
    /* STEP 2 */
    public $calle;
    public $numeracion;
    public $piso;
    public $localidad;
    public $ciudad;
    public $provincia;
    public $transporte = [];
    public $convive = [];
    public $obraSocial;
    public $nombreObraSocial;
    /* STEP 3 */
    public $nombreTutor;
    public $apellidoTutor;
    public $cuilTutor;
    public $emailTutor;
    public $telefonoTutor;
    public $ocupacion;
    public $parentezco;
    /* STEP 4 */
    public $curso = "";
    public $modalidad = "";
    public $escuelaProviene = "";
    public $turno;
    public $condicionAlumno = '';
    public $adeudaMaterias;
    public $nombreMaterias;
    /* STEP 5 */
    public $reconocimientos = [];
    public $terminos;

    public function render()
    {
        return view('livewire.multi-step-form');
    }
    public function mount()
    {
        $preinscripto = Session::get('preinscripto');
        $inscripto = Session::get('inscripto');
        $data = $inscripto ? $inscripto : $preinscripto;
        $this->nombre = $data['nombre'];
        $this->apellido = $data['apellido'];
        $this->genero = $data['genero'];
        $this->fecha_nac = $data['fecha_nac'];
        $this->email = $data['email'];
        $this->telefono = $data['telefono'];
        $this->cuil = $data['cuil'];

        //Relleno con los datos del estudiante que hay en la bd
        if ($inscripto) {
            $tutor = Tutor::where('id', $inscripto['tutor_id'])->firstOrFail();
            $this->nombreTutor = $tutor['nombre'];
            $this->apellidoTutor = $tutor['apellido'];
            $this->cuilTutor = $tutor['cuil'];
            $this->emailTutor = $tutor['email'];
            $this->telefonoTutor = $tutor['telefono'];
            $this->ocupacion = $tutor['ocupacion'];
            $this->parentezco = $tutor['parentezco'];

            $datoEstudiante = DatoEstudiante::where('estudiante_id', $inscripto['id'])->firstOrFail();
            $this->provincia = $datoEstudiante['provincia'];
            $this->ciudad = $datoEstudiante['ciudad'];
            $this->localidad = $datoEstudiante['localidad'];
            $this->calle = $datoEstudiante['calle'];
            $this->numeracion = $datoEstudiante['numeracion'];
            $this->piso = $datoEstudiante['piso'];
            //$this->lugar_nacimiento=$datoEstudiante['lugar_nacimiento'];
            $this->nombreObraSocial = $datoEstudiante['nombre_obra_social'];
            $this->obraSocial = $datoEstudiante['obra_social'];
            $this->transporte = json_decode($datoEstudiante['medio_transporte']);
            $this->convive = json_decode($datoEstudiante['convivencia']);

            $inscripcion = Inscripcion::where('estudiante_id', $inscripto['id'])->firstOrFail();
            $this->turno = $inscripcion['turno'];
            $this->curso = $inscripcion['curso_inscripto'];
            $this->modalidad = $inscripcion['modalidad'];
            $this->escuelaProviene = $inscripcion['escuela_proviene'];
            $this->condicionAlumno = $inscripcion['condicion_alumno'];
            $this->adeudaMaterias = $inscripcion['adeuda_materias'];
            $this->nombreMaterias = json_decode($inscripcion['nombre_materias']);
            $this->reconocimientos = json_decode($inscripcion['reconocimientos']);
        }
    }

    public function incrementSteps()
    {
        $this->validateForm();
        if ($this->currentStep < $this->total_steps) {
            $this->currentStep++;
        }
    }

    public function decrementSteps()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function submit()
    {
        $this->validateForm();
        if ($inscripto = Session::get('inscripto')) {
            try {
                DB::beginTransaction();
                $tutor = Tutor::where('id', $inscripto['tutor_id']);
                $tutor->update([
                    'nombre' => $this->nombreTutor,
                    'apellido' => $this->apellidoTutor,
                    'cuil' => $this->cuilTutor,
                    'email' => $this->emailTutor,
                    'telefono' => $this->telefonoTutor,
                    'ocupacion' => $this->ocupacion,
                    'parentezco' => $this->parentezco,
                ]);

                $estudiante = Estudiante::where('id', $inscripto['id']);
                $estudiante->update([
                    'nombre' => $this->nombre,
                    'apellido' => $this->apellido,
                    'genero' => $this->genero,
                    'cuil' => $this->cuil,
                    'telefono' => $this->telefono,
                    'email' => $this->email,
                    'fecha_nac' => $this->fecha_nac,
                ]);

                $datoEstudiante = DatoEstudiante::where('estudiante_id', $inscripto['id']);
                $datoEstudiante->update([
                    'provincia' => $this->provincia,
                    'ciudad' => $this->ciudad,
                    'localidad' => $this->localidad,
                    'calle' => $this->calle,
                    'numeracion' => $this->numeracion,
                    'piso' => $this->piso,
                    'lugar_nacimiento' => 'lugar_nacimiento',
                    'nombre_obra_social' => $this->nombreObraSocial,
                    'obra_social' => $this->obraSocial,
                    'medio_transporte' => json_encode($this->transporte),
                    'convivencia' => json_encode($this->convive),
                ]);

                $inscripcion = Inscripcion::where('estudiante_id', $inscripto['id']);
                $inscripcion->update([
                    'turno' => $this->turno,
                    'curso_inscripto' => $this->curso,
                    'modalidad' => $this->modalidad,
                    'escuela_proviene' => $this->escuelaProviene,
                    //'fecha_inscripcion' => now(),
                    'condicion_alumno' => $this->condicionAlumno,
                    'adeuda_materias' => $this->adeudaMaterias,
                    'nombre_materias' => json_encode($this->nombreMaterias),
                    'reconocimientos' => json_encode($this->reconocimientos),
                ]);

                DB::commit();

                return redirect()->route('confirmacion-inscripcion');
            } catch (QueryException $e) {
                DB::rollBack();
                abort(500, 'Error en la base de datos: ' . $e->getMessage());
            } catch (\Exception $e) {
                DB::rollBack();
                abort(500, 'Error al guadar de datos: ' . $e->getMessage());
            } finally {
                Session::forget('preinscripto');
                Session::forget('cuilCheck');
                Session::forget('inscripto');
            }
        }

        if ($preinscripto = Session::get('preinscripto')) {
            try {
                DB::beginTransaction();
                $tutor = Tutor::create([
                    'nombre' => $this->nombreTutor,
                    'apellido' => $this->apellidoTutor,
                    'cuil' => $this->cuilTutor,
                    'email' => $this->emailTutor,
                    'telefono' => $this->telefonoTutor,
                    'ocupacion' => $this->ocupacion,
                    'parentezco' => $this->parentezco,
                ]);

                $estudiante = Estudiante::create([
                    'nombre' => $this->nombre,
                    'apellido' => $this->apellido,
                    'genero' => $this->genero,
                    'cuil' => $this->cuil,
                    'telefono' => $this->telefono,
                    'email' => $this->email,
                    'fecha_nac' => $this->fecha_nac,
                    'tutor_id' => $tutor->id,
                ]);

                DatoEstudiante::create([
                    'provincia' => $this->provincia,
                    'ciudad' => $this->ciudad,
                    'localidad' => $this->localidad,
                    'calle' => $this->calle,
                    'numeracion' => $this->numeracion,
                    'piso' => $this->piso,
                    'lugar_nacimiento' => '$this->lugar_nacimiento',
                    'nombre_obra_social' => $this->nombreObraSocial,
                    'obra_social' => $this->obraSocial,
                    'fecha_ingreso' => now(),
                    'medio_transporte' => json_encode($this->transporte),
                    'convivencia' => json_encode($this->convive),
                    'estudiante_id' => $estudiante->id,
                ]);

                Inscripcion::create([
                    'turno' => $this->turno,
                    'curso_inscripto' => $this->curso,
                    'modalidad' => $this->modalidad,
                    'escuela_proviene' => $this->escuelaProviene,
                    'fecha_inscripcion' => now(),
                    'condicion_alumno' => $this->condicionAlumno,
                    'adeuda_materias' => $this->adeudaMaterias,
                    'nombre_materias' => $this->nombreMaterias,
                    'reconocimientos' => json_encode($this->reconocimientos),
                    'estudiante_id' => $estudiante->id,
                ]);
                DB::commit();

                return redirect()->route('confirmacion-inscripcion');
            } catch (QueryException $e) {
                DB::rollBack();
                abort(500, 'Error en la base de datos: ' . $e->getMessage());
            } catch (\Exception $e) {
                DB::rollBack();
                abort(500, 'Error al guadar de datos: ' . $e->getMessage());
            } finally {
                Session::forget('preinscripto');
                Session::forget('cuilCheck');
                Session::forget('inscripto');
            }
        }

        $this->reset();
    }

    public function validateForm()
    {
        if ($this->currentStep === 1) {
            $validated = $this->validate([
                'nombre' => 'required|string|max:255', // Campo nombre es requerido, debe ser una cadena de caracteres y tener como máximo 255 caracteres
                'apellido' => 'required|string|max:255', // Campo apellido es requerido, debe ser una cadena de caracteres y tener como máximo 255 caracteres
                'genero' => 'required|in:Femenino,Masculino,Otro', // Campo género es requerido y debe ser uno de los valores especificados
                'fecha_nac' => 'required|date', // Campo fecha de nacimiento es requerido y debe ser una fecha válida
                'email' => 'required|email|max:255', // Campo email es requerido, debe ser un email válido y tener como máximo 255 caracteres
                'telefono' => 'required|string|max:20', // Campo teléfono es requerido, debe ser una cadena de caracteres y tener como máximo 20 caracteres
            ], [
                'nombre.required' => 'El campo nombre es obligatorio.', //Estos son los mensajes que apareceran en caso de no cumplir
                'apellido.required' => 'El campo apellido es obligatorio.',
                'genero.required' => 'Debe seleccionar un género.',
                'genero.in' => 'El género seleccionado no es válido.',
                'fecha_nac.required' => 'El campo fecha de nacimiento es obligatorio.',
                'fecha_nac.date' => 'El campo fecha de nacimiento debe ser una fecha válida.',
                'email.required' => 'El campo email es obligatorio.',
                'email.email' => 'El campo email debe ser una dirección de correo electrónico válida.',
                'telefono.required' => 'El campo teléfono es obligatorio.',
            ]);
        } elseif ($this->currentStep === 2) {
            $validated = $this->validate([
                'calle' => 'required|string',
                'provincia' => 'required|string',
                'ciudad' => 'required|string',
                'localidad' => 'required|string',
                'numeracion' => 'required|numeric',
                'transporte' => 'required',
                'convive' => 'required',
                'obraSocial' => 'required',
            ], [
                'calle.required' => 'El campo calle es obligatorio.',
                'provincia.required' => 'El campo provincia es obligatorio.',
                'ciudad.required' => 'El campo ciudad es obligatorio.',
                'transporte.required' => 'Debe seleccionar una opción.',
                'numeracion.required' => 'El campo numeración es obligatorio.',
                'localidad.required' => 'El campo localidad es obligatorio.',
                'convive.required' => 'Debe seleccionar una opción.',
                'obraSocial.required' => 'Debe seleccionar una opción.'
            ]);
        } elseif ($this->currentStep === 3) {
            $validated = $this->validate([
                'nombreTutor' => 'required|string',
                'apellidoTutor' => 'required|string',
                'cuilTutor' => 'required|numeric',
                'emailTutor' => 'required|email',
                'telefonoTutor' => 'required|numeric',
                'ocupacion' => 'required|string',
                'parentezco' => 'required',
            ], [
                'nombreTutor.required' => 'El campo nombre es obligatorio.',
                'apellidoTutor.required' => 'El campo apellido es obligatorio.',
                'cuilTutor.required' => 'El campo cuil es obligatorio.',
                'cuilTutor.numeric' => 'El cuil debe ser numérico, sin puntos ni guiones.',
                'emailTutor.required' => 'El campo email es obligatorio.',
                'emailTutor.email' => 'El email debe ser una dirección de correo electrónico válida.',
                'telefonoTutor.required' => 'El campo telefono es obligatorio',
                'telefonoTutor.numeric' => 'El telefono debe contener números.',
                'ocupacion.required' => 'El campo ocupación es obligatorio',
                'parentezco.required' => 'Debe seleccionar una opción'
            ]);
        } elseif ($this->currentStep === 4) {
            $validated = $this->validate([
                'curso' => 'required|in:"Primer año","Segundo año","Tercer año","Cuarto año","Quinto año","Sexto año"',
                'modalidad' => 'in:"Informática","Economía","Industria"',
                'condicionAlumno' => 'required',
                'turno' => 'required',
                //'escuelaProviene' => 'required',
                'adeudaMaterias' => 'required',
                //'nombreMaterias' => 'required'
            ], [
                'curso.required' => 'Debe seleccionar una opción.',
                'curso.in' => 'El curso ingresado no es válido.',
                //'modalidad.required' => 'Debe seleccionar una opción.',
                'modalidad.in' => 'La modalidad ingresada no es válida.',
                'turno.required' => 'Debe seleccionar una opción.',
                'adeudaMaterias.required' => 'Debe seleccionar una opción.',
                'condicionAlumno.required' => 'Debe seleccionar una opción.',
                //'escuelaProviene.required' => 'Debe indicar una institución',
                //'nombreMaterias.required' => 'Debe indicar las materias que adeuda',
            ]);
        } elseif ($this->currentStep === 5) {
            $validated = $this->validate([
                'reconocimientos' => 'required',
                'terminos' => 'required',
            ], [
                'reconocimientos.required' => 'Debe seleccionar al menos una opción',
                'terminos.required' => 'Debe seleccionar que leyó y está de acuerdo'
            ]);
        }
    }
}
