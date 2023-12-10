<?php

namespace App\Http\Controllers;

use App\Notifications\NewActNotification;
use App\Exports\actividades\ActividadExport;
use Illuminate\Http\Request;
// use App\Events\NewActividad;
use App\Actividade;
use Carbon\Carbon;
use App\Cliente;
use App\Reporte;
use App\User;
use Excel;

class ActividadeController extends Controller
{
    // OBTENER LA LISTA DE ACTIVIDADES
    // public function index(){
    //     return view('information.actividades.lista');
    // }

    // OBTENER ACTIVIDADES POR TIPO DE CLIENTE
    public function lista(){
        return view('information.actividades.lista');
    }

    public function simple(){
        return view('information.actividades.simple');
    }

    // OBTENER LAS ACTIVIDADES POR STATUS
    public function get_status($status){
        return view('information.actividades.status-lista', compact('status'));
    }

    // GUARDAR ACTIVIDAD
    public function store(Request $request){

        \DB::beginTransaction();
        try {
            $tipo = $request->tipo;
            $descripcion = $request->descripcion;
            $fecha_hora = $request->fecha.' '.$request->hora;
            $fecha = new Carbon($fecha_hora);
            $estado = $this->set_tiempo_estado($fecha);
            
            $actividad = Actividade::create([
                'user_id' => auth()->user()->id,
                'nombre' => strtoupper($request->nombre),
                'tipo' => $tipo, 
                'descripcion' => $descripcion, 
                'estado' => $estado, 
                'fecha' => $fecha,
                'lugar' => $request->lugar,
                'recordatorio' => $this->set_recordatorio($fecha_hora, $request->recordatorio),
                'marcar_antesde' => $this->set_marcar_antesde($fecha_hora)
            ]);

            $clientes = collect($request->clientes);
            $clientes->map(function($cliente) use(&$actividad){
                $actividad->clientes()->attach($cliente['id']);
            });

            $reporte = 'creo la actividad '.$actividad->tipo.': '.$actividad->nombre.' / '.$actividad->descripcion;
            $this->create_report($actividad->id, $reporte, 'actividades');

            // broadcast(new NewActividad($actividad))->toOthers();
            $users = User::whereIn('role_id', [5,6,7])
                            ->whereNotIn('id', [auth()->user()->id])->get();
            foreach($users as $user){
                $user->notify(new NewActNotification($actividad, $actividad->user));
            }
            
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($actividad);
    }

    public function set_recordatorio($fecha, $minutos){
        $recordatorio = new Carbon($fecha);
        return $recordatorio->subMinutes((int)$minutos);
    }

    public function set_marcar_antesde($fecha){
        $marcar_antesde = new Carbon($fecha);
        return $marcar_antesde->addMinutes(60);
    }

    // MARCAR ACTIVIDADES COMO TEMRINADAS
    public function update_estado(Request $request){
        \DB::beginTransaction();
        try {
            $hoy = Carbon::now();
            $actividad = Actividade::find($request->id);
            $estado = $request->estado;
            $fecha_obs = $hoy.' :</b> '.$request->observaciones.'</p>';
            
            if($estado == 'cancelado') $observaciones = $actividad->observaciones.'<p><b>ACTIVIDAD CANCELADA - '.$fecha_obs;
            if($estado == 'completado') $observaciones = $actividad->observaciones.'<p><b>ACTIVIDAD COMPLETADA - '.$fecha_obs;
            
            $actividad->update([
                'estado' => $estado,
                'exitosa' => $request->exitosa, 
                'observaciones' => $observaciones
            ]);

            $reporte = 'marco como '.$actividad->estado.' la actividad '.$actividad->tipo.': '.$actividad->nombre.' / '.$actividad->descripcion;
            $this->create_report($actividad->id, $reporte, 'actividades');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($actividad);
    }

    public function create_report($id_table, $reporte, $name_table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => $name_table,
            'id_table' => $id_table
        ]);
    }

    public function set_tiempo_estado($fecha){
        $ahora = Carbon::now();
        $mañana = Carbon::tomorrow();

        $estado = 'pendiente';
        if($fecha < $ahora) $estado = 'vencido';
        if($fecha->format('Y-m-d') >= $mañana->format('Y-m-d')) $estado = 'proximo';
        return $estado;
    }

    public function update(Request $request){
        \DB::beginTransaction();
        try {
            $actividad = Actividade::find($request->id);

            $f1 = new Carbon($actividad->fecha);
            $f2 = new Carbon($actividad->recordatorio);
            $recordatorio = $f1->diffInMinutes($f2);

            $hoy = Carbon::now();
            $descripcion = $actividad->descripcion.'<p><b>ACTUALIZACIÓN ('.$hoy.'):</b> '.$request->observaciones.'</p>';
            
            $fecha_hora = $request->fecha.' '.$request->hora;
            $fecha = new Carbon($fecha_hora);

            $estado = $this->set_tiempo_estado($fecha);

            $actividad->update([
                'estado' => $estado,
                'fecha' => $fecha,
                'descripcion' => $descripcion,
                'recordatorio' => $this->set_recordatorio($fecha_hora, $recordatorio),
                'marcar_antesde' => $this->set_marcar_antesde($fecha_hora)
            ]);

            $reporte = 'edito la actividad '.$actividad->tipo.': '.$actividad->nombre.' / '.$actividad->descripcion;
            $this->create_report($actividad->id, $reporte, 'actividades');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($actividad);
    }

    public function by_user_fecha_actual(){
        $hoy = Carbon::now();
        $actividades = $this->get_user_actividades()
                        ->where('fecha', 'like', '%'.$hoy->format('Y-m-d').'%')
                        ->get();
        return response()->json($actividades);
    }

    public function by_user_estado(Request $request){
        $actividades = $this->get_user_actividades()
                        ->where('estado', $request->estado)
                        ->get();
        return response()->json($actividades);
    }

    public function get_user_actividades(){
        return Actividade::with('clientes')->orderBy('fecha', 'desc');
        // where('user_id', auth()->user()->id)
    }

    public function download($id){
        return Excel::download(new ActividadExport($id), 'actividad.xlsx');
    }

    public function view_notification(Request $request){
        $actividad = Actividade::whereId($request->actividad_id)->with('clientes')->first();
        $notification = auth()->user()->unreadNotifications->where('id', $request->notification_id);
        $notification->map(function($n){
            $n->markAsRead();
            $n->delete();
        });
        return response()->json($actividad);
    }

    // *** FUNCIONES PENDIENTES POR REVISAR

    // OBTENER TODAS LAS ACTIVIDADES POR ESTADO Y TIPO
    public function by_tipo_estado(Request $request){
        $actividades = $this->get_tipo_estado($request)
                            ->where('clientes.tipo', $request->clientetipo)
                            ->get();
        return response()->json($actividades);
    }

    // OBTENER TODAS LAS ACTIVIDADES POR CLIENTE, ESTADO Y TIPO
    public function by_cliente_tipo_estado(Request $request){
        $actividades = $this->get_tipo_estado($request)
                            ->where('actividades.cliente_id', $request->cliente_id)
                            ->get();
        return response()->json($actividades);
    }

    // OBTENER ACTIVIDADES DEL USUARIO EN SESION
    public function by_userid_tipo_estado(Request $request){
        $actividades = $this->get_tipo_estado($request)
                        ->where('clientes.tipo', $request->clientetipo)
                        ->where('actividades.user_id', auth()->user()->id)->get();
        return response()->json($actividades);
    }

    public function get_tipo_estado($request){
        return \DB::table('actividades')
                    ->select('actividades.*', 
                        'clientes.name as cliente_name','users.name as user_name')
                    ->join('clientes', 'actividades.cliente_id', '=', 'clientes.id')
                    ->join('users', 'actividades.user_id', '=', 'users.id')
                    ->where('actividades.tipo', $request->tipo)
                    ->where('actividades.estado', $request->estado)
                    ->orderBy('actividades.created_at', 'desc');
    }

    // *** FUNCIONES PENDIENTES POR REVISAR
}
