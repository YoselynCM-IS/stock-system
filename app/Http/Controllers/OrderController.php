<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Element;
use App\Reporte;
use App\Cliente;
use App\Order;

class OrderController extends Controller
{
    // VISTA PARA LOS PEDIDOS DE LOS CLIENTES
    public function proveedor(){
        return view('information.orders.lista-proveedor');
    }

    // OBTENER TODOS LOS PEDIDOS
    public function index(){
        $os = Order::orderBy('created_at', 'desc');
        $orders = $this->rol_oficina_almacen($os);
        return response()->json($orders);
    }

    public function by_cliente(Request $request){
        $os = Order::where('cliente_id', $request->cliente_id)
                    ->orderBy('created_at', 'desc');
        $orders = $this->rol_oficina_almacen($os);
        return response()->json($orders);
    }

    public function by_provider(Request $request){
        $os = Order::where('provider', $request->provider)
                    ->orderBy('created_at', 'desc');
        $orders = $this->rol_oficina_almacen($os);
        return response()->json($orders);
    }

    public function rol_oficina_almacen($orders){
        if(auth()->user()->role_id == 3){
            return $orders->where('status', 'espera')
                            ->where('almacen', 'SI')->paginate(20);
        } else {
            return $orders->paginate(20);
        }
    }

    // DETALLES DEL PEDIDO
    public function show($order_id){
        $order = Order::whereId($order_id)->with('elements.libro', 'remisiones.cliente')
                    ->withCount('remisiones')->first();
        return view('information.orders.details-order', compact('order'));
    }

    // ACTUALIZAR ESTADO
    public function change_status(Request $request){
        $order = Order::whereId($request->pedido_id)->first();
        \DB::beginTransaction();
        try{
            $status = $request->status;
            if($status == 'espera'){
                $order->update(['status' => $status]);
            } else {
                $actual_total_bill = 0;
                if($status == 'incompleto'){
                    $elements = collect($request->elements);
                    $elements->map(function($e) use (&$actual_total_bill){
                        $element = Element::find($e['id']);
                        $aq = (int) $e['actual_quantity'];
                        $at = $aq * $element->unit_price;
                        $element->update([
                            'actual_quantity' => $aq,
                            'actual_total' => $at
                        ]);
                        $actual_total_bill += $at;
                    });
                }
                $order->update([
                    'status' => $status,
                    'observations' => $order->observations.'<br>'.$request->observations,
                    'actual_total_bill' => $actual_total_bill
                ]);
            }

            $reporte = 'actualizo el estado de un pedido al proveedor '.$order->provider.' PEDIDO: '.$order->identifier.' / '.$status;
            $this->create_report($order->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($order);
    }

    public function cancelar(Request $request){
        $order = Order::find($request->id);
        \DB::beginTransaction();
        try{
            $order->update(['status' => 'cancelado']);

            $reporte = 'cancelo un pedido al proveedor '.$order->provider.' PEDIDO: '.$order->identifier;
            $this->create_report($order->id, $reporte);
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($order);
    }

    public function add_costo(Request $request){
        \DB::beginTransaction();
        try{
            $elements = collect($request->elements);
            $elements->map(function($element){
                Element::whereId($element['id'])->update([
                    'unit_price' => (float) $element['unit_price'],
                    'total' => (float) $element['total']
                ]);
            });

            $order = Order::find($request->id);
            $order->update([
                'almacen' => $request->almacen,
                'total_bill' => $request->total_bill,
                'status' => 'espera'
            ]);

            $reporte = 'registro los costos del pedido al proveedor '.$order->provider.' PEDIDO: '.$order->identifier;
            $this->create_report($order->id, $reporte);
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function create_report($order_id, $reporte){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => 'orders', 
            'id_table' => $order_id
        ]);
    }

    public function store(Request $request){
        \DB::beginTransaction();
        try{
            $fecha_actual = Carbon::now();

            $libros = collect($request->libros);
            $editorial = $request->editorial;
            $provider_count = Order::where('provider', $editorial)->count();
            $identifier = 'PED '.($provider_count + 1).'-'.$fecha_actual->format('Y');
            
            $order = Order::create([
                'pedido_id' => null,
                'cliente_id' => $request->cliente_id,
                'destination' => $request->cliente_name,
                'identifier' => $identifier,
                'date' => $fecha_actual->format('Y-m-d'),
                'provider' => $editorial,
                'total_bill' => (double) $request->total_bill,
                'creado_por' => auth()->user()->name
            ]); 

            $tipo_order = 'fisicos';
            $almacen = 'SI';
            $libros->map(function($libro) use (&$order, &$tipo_order, &$almacen){
                $tipo = $libro['tipo'];
                $element = Element::create([
                    'order_id' => $order->id,
                    'libro_id' => $libro['libro']['id'],
                    'tipo' => $tipo, 
                    'quantity' => (int) $libro['quantity'],
                    'unit_price' => (float) $libro['price'],
                    'total' => (double) $libro['total']
                ]);

                if($tipo != null) {
                    $tipo_order = 'digitales';
                    $almacen = 'NO';
                }
            });

            $order->update([
                'tipo'  => $tipo_order,
                'almacen' => $almacen,
            ]);

            $reporte = 'creo un pedido al proveedor '.$editorial.' PEDIDO: '.$order->identifier;
            $this->create_report($order->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function relacionar(Request $request){
        \DB::beginTransaction();
        try{
        $order = Order::find($request->order_id);

        $remisiones = collect($request->selected);
        $remisiones->map(function($remision) use(&$order){
            $folio = (int)$remision['id'];
            $order->remisiones()->attach($folio);
            $reporte = 'relaciono una remisión al pedido al proveedor '.$order->provider.' / '.$order->identifier.' REMISIÓN: '.$folio;
            $this->create_report($order->id, $reporte);
        });
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }
}
