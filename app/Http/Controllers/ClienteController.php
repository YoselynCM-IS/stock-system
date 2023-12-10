<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cliente;
use App\Remisione;
use App\Dato;
use App\Exports\ClientesExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Remcliente;
use App\Cctotale;
use App\Reporte;
use App\Corte;
use App\Destinatario;
use App\Seguimiento;

class ClienteController extends Controller
{
    // VISTA PARA LOS CLIENTES
    public function lista(){
        return view('information.clientes.lista');
    }

    public function cortes(){
        return view('information.cortes.clientes.lista');
    }

    // OBTENER TODOS LOS CLIENTES
    public function index(){
        $clientes = $this->get_all_clientes()->paginate(20);
        return response()->json($clientes);
    }

    // OBTENER TODOS LOS CLIENTES RESPONSABLES DEL USUARIO EN SESION
    public function by_userid(){
        $clientes = $this->get_all_clientes()->where('user_id', auth()->user()->id)->paginate(20);
        return response()->json($clientes);
    }

    public function get_all_clientes(){
        return Cliente::with('user', 'estado')->orderBy('name', 'asc');
    }

    // MOSTRAR LOS CLIENTES POR COINCIDENCIA DE NOMBRE PAGINADO
    public function by_name(Request $request){
        $clientes = $this->get_all_clientes()->where('name','like','%'.$request->cliente.'%')->paginate(20);
        return response()->json($clientes);
    }

    public function by_name_userid(Request $request){
        $clientes = $this->get_all_clientes()->where('user_id', auth()->user()->id)
                        ->where('name','like','%'.$request->cliente.'%')->paginate(20);
        return response()->json($clientes);
    }

    // OBTENER UN CLIENTE POR ID
    public function show(Request $request){
        $cliente_id = $request->cliente_id;
        $cliente = Cliente::whereId($cliente_id)->with('user', 'estado')->first();
        return response()->json($cliente);
    }
    
    // MOSTRAR TODOS LOS CLIENTES
    // Función utilizada en los componentes
    // - AdeudosComponent - ClientesComponent - DevolucionAdeudosComponent
    // - DevolucionComponent - ListadoComponent - PagosComponent - RemisionComponent - RemisionesComponent
    public function mostrarClientes(Request $request){
        $queryCliente = $request->queryCliente;
        $clientes = $this->get_likename($queryCliente)->get();
        return response()->json($clientes);
    }

    public function get_likename($queryCliente){
        return Cliente::where('name','like','%'.$queryCliente.'%')->orderBy('name', 'asc');
    }

    // EDITAR DATOS DE CLIENTE
    // Función utilizada en ClientesComponent
    public function update(Request $request){
        $cliente_id = $request->id;
        $cliente = Cliente::whereId($cliente_id)->first();
        $cliente->name = 'CLIENTE-'.$cliente->name;
        $cliente->save();
        $this->validacion($request);
        \DB::beginTransaction();
        try {
            $cliente->update([
                'name' => strtoupper($request->name),
                'contacto' => strtoupper($request->contacto),
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => strtoupper($request->direccion),
                'condiciones_pago' => strtoupper($request->condiciones_pago),
                'rfc' => strtoupper($request->rfc),
                'fiscal' => strtoupper($request->fiscal),
                'tipo' => $request->tipo, 
                'user_id' => $request->user_id, 
                'estado_id' => $request->estado_id, 
                'tel_oficina' => $request->tel_oficina
            ]);

            $reporte = 'edito al '.$cliente->tipo.' '.$cliente->name;
            $this->create_report($cliente->id, $reporte, 'clientes');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($cliente);
    }

    // GUARDAR NUEVO CLIENTE
    // Función utilizada en NewClienteComponent
    public function store(Request $request){
        $this->validacion($request);
        \DB::beginTransaction();
        try {
            $cliente = Cliente::create([
                'name' => strtoupper($request->name),
                'contacto' => strtoupper($request->contacto),
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => strtoupper($request->direccion),
                'condiciones_pago' => strtoupper($request->condiciones_pago),
                'rfc' => strtoupper($request->rfc),
                'fiscal' => strtoupper($request->fiscal),
                'tipo' => $request->tipo, 
                'user_id' => $request->user_id, 
                'estado_id' => $request->estado_id, 
                'tel_oficina' => $request->tel_oficina
            ]);

            $reporte = 'creo al '.$cliente->tipo.' '.$cliente->name;
            $this->create_report($cliente->id, $reporte, 'clientes');

            Remcliente::create([
                'cliente_id' => $cliente->id,
                'total' => 0,
                'total_pagar' => 0
            ]);

            $hoy = Carbon::now();
            $corte = Corte::where('inicio', '<', $hoy)
                        ->where('final', '>', $hoy)
                        ->first();

            Cctotale::create([
                'corte_id' => $corte->id, 
                'cliente_id' => $cliente->id
            ]);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($cliente);
    }

    public function store_prospecto(Request $request){
        $this->validate($request, [
            'name' => 'min:3|max:100|required|string|unique:clientes',
            'email' => 'min:8|max:50|required|email',
            'telefono' => 'required|numeric|max:999999999999999|min:1000000',
            'tel_oficina' => 'required|numeric|max:999999999999999|min:1000000'
        ]);
        \DB::beginTransaction();
        try {
            $cliente = Cliente::create([
                'tipo' => 'PROSPECTO',
                'name' => strtoupper($request->name),
                'contacto' => strtoupper($request->contacto),
                'email' => $request->email,
                'telefono' => $request->telefono,
                'tel_oficina' => $request->tel_oficina,
                'estado_id' => $request->estado_id,
                'user_id' => 0
            ]);

            $reporte = 'creo al '.$cliente->tipo.' '.$cliente->name;
            $this->create_report($cliente->id, $reporte, 'clientes');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($cliente);
    }

    public function validacion($request){
        $this->validate($request, [
            'name' => 'min:3|max:100|required|string|unique:clientes',
            'email' => 'min:8|max:50|required|email',
            'telefono' => 'required|numeric|max:999999999999999|min:1000000',
            'direccion' => 'min:3|max:250|required|string',
            'condiciones_pago' => 'min:3|max:150|required|string',
            'rfc' => 'min:3|max:50|required|string',
            'fiscal' => 'min:3|max:250|required|string',
        ]);
    }

    public function descargar_clientes(){
        return Excel::download(new ClientesExport, 'clientes.xlsx');
    }

    public function getTodo(){
        $clientes = Cliente::orderBy('name', 'asc')->get();
        return response()->json($clientes);
    }

    public function get_estados(){
        $estados = \DB::table('estados')->orderBy('estado', 'asc')->get();
        return response()->json($estados);
    }

    public function get_usuarios(Request $request){
        $users = \DB::table('users')->whereNotIn('role_id', [$request->role_id])
                        ->orderBy('name', 'asc')->get();
        return response()->json($users);
    }

    public function save_libro(Request $request){
        \DB::beginTransaction();
        try {
            $costo_unitario = (float) $request->costo_unitario;

            $cliente = Cliente::find($request->cliente_id);
            $cliente->libros()->attach($request->libro_id, ['costo_unitario' => $costo_unitario]);

            $reporte = 'agrego un libro al '.$cliente->tipo.' '.$cliente->name.' LIBRO: '.$request->libro_titulo.' / $'.$costo_unitario;
            $this->create_report($cliente->id, $reporte, 'cliente_libro');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($cliente);
    }

    public function update_libro(Request $request){
        \DB::beginTransaction();
        try {
            $cliente = Cliente::find($request->cliente_id);
            $costo_unitario = (float) $request->costo_unitario;
            $cliente->libros()->updateExistingPivot($request->libro_id, [
                'costo_unitario' => $costo_unitario
            ]);

            $reporte = 'edito el costo de un libro al '.$cliente->tipo.' '.$cliente->name.' LIBRO: '.$request->libro_titulo.' / $'.$costo_unitario;
            $this->create_report($cliente->id, $reporte, 'cliente_libro');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($costo_unitario);
    }

    public function get_libros(Request $request){
        $cliente = Cliente::find($request->cliente_id);
        return response()->json($cliente->libros);
    }

    public function delete_libro(Request $request){
        \DB::beginTransaction();
        try {
            $cliente = Cliente::find($request->cliente_id);
            $cliente->libros()->detach($request->libro_id);

            $reporte = 'elimino un libro al '.$cliente->tipo.' '.$cliente->name.' LIBRO: '.$request->libro_titulo.' / $'.$request->costo_unitario;
            $this->create_report($cliente->id, $reporte, 'cliente_libro');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($cliente);
    }

    public function by_tipo(Request $request){
        $clientes = $this->get_likename($request->queryCliente)
                        ->where('tipo', $request->tipo)->get();
        return response()->json($clientes);
    }

    public function create_report($cliente_id, $reporte, $name_table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => $name_table, 
            'id_table' => $cliente_id
        ]);
    }

    public function get_destinatarios(Request $request){
        $destinatarios = Destinatario::where('destinatario','like','%'.$request->queryDestinatario.'%')
                            ->orderBy('destinatario', 'asc')->get();
        return response()->json($destinatarios);
    }

    public function save_seguimiento(Request $request){
        \DB::beginTransaction();
        try {
            $fecha = new Carbon($request->fecha.' '.$request->hora);
            $duracion = $request->duracion['horas'].' horas '.$request->duracion['minutos'].' minutos '.$request->duracion['segundos'].' segundos';
            $registro = $request->registro;
            $tipo = $request->tipo;
            $respuesta = $request->respuesta;
            if($registro == 'nota') {
                $tipo = null;
                $respuesta = null;
            }

            $seguimiento = Seguimiento::create([
                'user_id' => auth()->user()->id, 
                'cliente_id' => $request->cliente_id, 
                'tipo' => $registro,
                'situacion' => $tipo, 
                'respuesta' => $respuesta, 
                'fecha_hora' => $fecha, 
                'duracion' => $duracion, 
                'comentario' => $request->comentario
            ]);
            $reporte = 'registro un(a) '.$seguimiento->tipo.' para '.$seguimiento->cliente->name;
            $this->create_report($seguimiento->id, $reporte, 'seguimientos');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function get_seguimiento(Request $request){
        $seguimientos = Seguimiento::where('cliente_id', $request->cliente_id)
                            ->with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($seguimientos);
    }
}
