<?php

namespace App\Http\Controllers;

use App\Notifications\NewPedClienteNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Peticione;
use App\Element;
use App\Reporte;
use App\Pedido;
use App\Order;
use App\User;
use App\Code;

class PedidoController extends Controller
{
    // VISTA PARA LOS PEDIDOS DE LOS CLIENTES
    public function cliente(){
        return view('information.pedidos.lista-cliente');
    }

    // OBTENER TODOS LOS PEDIDOS
    public function index(){
        $pedidos = Pedido::orderBy('created_at', 'desc')
                    ->with('user', 'cliente')->paginate(20);
        return response()->json($pedidos);
    }

    // DETALLES DEL PEDIDO
    public function show($pedido_id, $notification_id = null){
        $pedido = Pedido::whereId($pedido_id)->with('user', 'cliente', 'peticiones.libro', 'orders.remisiones.cliente')->first();
        $notification = auth()->user()->unreadNotifications->where('id', $notification_id);
        $notification->map(function($n){
            $n->markAsRead();
            $n->delete();
        });
        return view('information.pedidos.details-pedido', compact('pedido'));
    }

    public function get_pedido($pedido_id){
        return Pedido::whereId($pedido_id)->with('user', 'cliente', 'peticiones.libro')->first();
    }

    // GUARDAR PEDIDO
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'user_id' => auth()->user()->id,
                'cliente_id' => $request->cliente_id, 
                'total_quantity' => (int) $request->total_quantity,
                'total' => (double) $request->total
            ]);
            
            $peticiones = collect($request->libros);
            $peticiones->map(function($peticione) use ($pedido){
                Peticione::create([
                    'pedido_id' => $pedido->id,
                    'libro_id' => $peticione['libro']['id'], 
                    'tipo' => $peticione['tipo'], 
                    'quantity' => (int) $peticione['quantity'],
                    'price' => (float) $peticione['price'],
                    'total' => (double) $peticione['total']
                ]);
            });

            $reporte = 'creo un pedido del cliente '.$pedido->cliente->name;
            $this->create_report($pedido->id, $reporte);

            $users = User::whereIn('role_id', [1,2,6])
                            ->whereNotIn('id', [auth()->user()->id])->get();
            foreach($users as $user){
                //$user->notify(new NewPedClienteNotification($pedido, $pedido->user));
            }

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($pedido);
    }

    // PREPRAR PEDIDO
    public function preparar($pedido_id){
        $p = $this->get_pedido($pedido_id);

        $tipo = 'fisicos';
        $peticiones = collect();
        $p->peticiones->map(function($peticione) use (&$peticiones, &$tipo){
            $quantity = $peticione->quantity;
            if($peticione->tipo == NULL || $peticione->tipo == 'alumno'){
                $piezas = $peticione->libro->piezas;
            } 
            if($peticione->tipo == 'profesor' || $peticione->tipo == 'demo') {
                $piezas = Code::where('libro_id', $peticione->libro_id)
                    ->where('estado', 'inventario')
                    ->where('tipo', $peticione->tipo)
                    ->groupBy('libro_id')
                    ->count();
            }

            if($peticione->tipo != NULL) $tipo = 'digitales';

            $faltante = 0;
            if($quantity > $piezas) $faltante = $quantity - $piezas;
            
            $peticiones->push([
                'id' => $peticione->id,
                'tipo' => $peticione->tipo,
                'libro_id' => $peticione->libro_id, 
                'editorial' => $peticione->libro->editorial,
                'ISBN' => $peticione->libro->ISBN,
                'titulo' => $peticione->libro->titulo,
                'quantity' => $quantity,
                'existencia' => $piezas,
                'faltante' => $faltante,
                'solicitar' => 0
            ]);
        });
        
        $pedido = collect([
            'id' => $p->id,
            'tipo' => $tipo,
            'user_name' => $p->user->name,
            'cliente_name' => $p->cliente->name, 
            'total_quantity' => $p->total_quantity,
            'total_solicitar' => 0,
            'created_at' => $p->created_at,
            'peticiones' => $peticiones
        ]);
        return view('information.pedidos.preparar-pedido', compact('pedido'));
    }

    // GUARDAR PEDIDO YA PREPARADO PARA PROVEEDOR
    public function preparado(Request $request){
        \DB::beginTransaction();
        try {
            $e = $this->create_peticiones($request->peticiones);

            $pedido = Pedido::find($request->id);
            $pedido->update([
                'total_solicitar' => $request->total_solicitar,
                'estado' => 'en orden'
            ]);

            // CREAR ORDEN POR SEPARADO
            $es = $e->unique();
            $editoriales = collect($es->values()->all());

            $fecha_actual = Carbon::now();
            $order_ids = collect();
            $tipo = $request->tipo;
            $editoriales->map(function($editorial) use(&$order_ids, $fecha_actual, $pedido, $tipo){
                $provider_count = Order::where('provider', $editorial)->count();
                $identifier = 'PED '.($provider_count + 1).'-'.$fecha_actual->format('Y');
                
                $almacen = 'SI';
                if($tipo == 'digitales') $almacen = 'NO';

                $order = Order::create([
                    'pedido_id' => $pedido->id,
                    'cliente_id' => $pedido->cliente_id,
                    'tipo'  => $tipo,
                    'almacen' => $almacen,
                    'destination' => $pedido->cliente->name,
                    'identifier' => $identifier,
                    'date' => $fecha_actual->format('Y-m-d'),
                    'provider' => $editorial,
                    'creado_por' => auth()->user()->name
                ]);

                $reporte = 'preparo un pedido del cliente '.$pedido->cliente->name.' PEDIDO AL PROVEEDOR: '.$identifier;
                $this->create_report($pedido->id, $reporte);

                $order_ids->push([
                    'order_id' => $order->id,
                    'editorial' => $order->provider
                ]);
            });

            $peticiones = $pedido->peticiones;
            $peticiones->map(function($peticione) use($order_ids){
                if($peticione->solicitar > 0){
                    $order_ids->map(function($oi) use($peticione){
                        $editorial = $peticione->libro->editorial;
                        if($oi['editorial'] == $editorial) {
                            $element = Element::create([
                                'order_id' => $oi['order_id'],
                                'libro_id' => $peticione->libro_id,
                                'tipo'  => $peticione->tipo,
                                'quantity' => $peticione->solicitar
                            ]);
                        }
                    });
                }
            });
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json(true);
    }

    public function create_peticiones($ps){
        $editoriales = collect();
        $peticiones = collect($ps);
        $peticiones->map(function($peticione) use(&$editoriales){
            Peticione::whereId($peticione['id'])->update([
                'existencia' => $peticione['existencia'],
                'faltante' => $peticione['faltante'],
                'solicitar' => (int) $peticione['solicitar']
            ]);

            $editoriales->push($peticione['editorial']);
        });
        return $editoriales;
    }

    public function despachar(Request $request){
        \DB::beginTransaction();
        try {
            $pedido = Pedido::find($request->id);
            $pedido->update([
                'estado' => 'de inventario',
                'comentarios' => $pedido->comentarios.'<p>El pedido se tomará de lo disponible en inventario.</p>'
            ]);

            $this->create_peticiones($request->peticiones);

            $reporte = 'preparo un pedido del cliente '.$pedido->cliente->name.' DISPONIBLE EN INVENTARIO';
            $this->create_report($pedido->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($pedido);
    }

    public function cancelar(Request $request){
        \DB::beginTransaction();
        try {
            $pedido = Pedido::find($request->pedido_id);
            $pedido->update([
                'estado' => 'cancelado',
            ]);

            $reporte = 'cancelo un pedido del cliente '.$pedido->cliente->name;
            $this->create_report($pedido->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function create_report($pedido_id, $reporte){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => 'pedidos', 
            'id_table' => $pedido_id
        ]);
    }

    public function by_cliente(Request $request){
        $pedidos = Pedido::where('cliente_id', $request->cliente_id)->orderBy('created_at', 'desc')
                    ->with('user', 'cliente')->paginate(20);
        return response()->json($pedidos);
    }
}
