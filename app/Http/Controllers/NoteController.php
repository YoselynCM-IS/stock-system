<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Payment;
use App\Register;
use App\Libro;
use App\Note;
use Carbon\Carbon;
use App\Exports\NotesExport;
use App\Exports\notes\NoteExport;
use Excel;
use PDF;

class NoteController extends Controller
{
    // BUSCAR NOTA POR FOLIO
    // Función utilizada en NewNoteComponent
    public function by_folio(Request $request){
        $folio = $request->folio;
        $note = Note::where('folio', $folio)->first();
        return response()->json($note);
    }

    // BUSCAR NOTA POR CLIENTE
    // Función utilizada en NewNoteComponent
    public function by_cliente(Request $request){
        $queryCliente = $request->queryCliente;
        $notes = Note::where('cliente','like','%'.$queryCliente.'%')
                    ->orderBy('folio','desc')->paginate(20);
        return response()->json($notes);
    }

    // MOSTRAR DETALLES DE NOTA
    // Función utilizada en NewNoteComponent
    public function detalles_nota(Request $request){
        $note_id = $request->note_id;
        $note = Note::whereId($note_id)->first();
        $registers = Register::where('note_id', $note->id)->with('libro')->with('payments')->get();
        return response()->json($registers);
    }

    // GUARDAR PAGO DE NOTA
    // Función utilizada en NewNoteComponent
    public function guardar_pago(Request $request){
        try{
            \DB::beginTransaction();
            
            $pagos = 0;
            foreach($request->registers as $register){
                $unidades = $register['unidades_base'];
                $total = $register['total_base'];

                if($unidades != 0){
                    $registro = Register::whereId($register['id'])->first();
                    Payment::create([
                        'register_id' => $registro->id,
                        'unidades' => $unidades,
                        'pago' => $total
                    ]);
                    
                    $unidades_pagado = $registro->unidades_pagado + $unidades;
                    $total_pagado = $registro->total_pagado + $total;
                    $unidades_pendiente = $registro->unidades_pendiente - $unidades;
                    $total_pendiente = $registro->total_pendiente - $total;

                    $registro->update([
                        'unidades_pagado' => $unidades_pagado,
                        'total_pagado' => $total_pagado,
                        'unidades_pendiente' => $unidades_pendiente,
                        'total_pendiente' => $total_pendiente
                    ]);
                }
                $pagos += $total;
            }

            $nota = Note::whereId($request->id)->first();
            $total_pagar = $nota->total_pagar - $pagos;
            $total_pagos = $nota->pagos + $pagos;
            $nota->update([
                'total_pagar' => $total_pagar,
                'pagos' => $total_pagos
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($nota);
    }

    // REGISTRAR DEVOLUCIÓN DE NOTA
    // Función utilizada en NewNoteComponent
    public function guardar_devolucion(Request $request){
        try{
            \DB::beginTransaction();
            
            $total_devolucion = 0;
            foreach($request->registers as $register){
                $unidades = $register['unidades_base'];
                $total = $register['total_base'];

                if($unidades !== 0){
                    $registro = Register::whereId($register['id'])->first();
                    
                    $unidades_devuelto = $registro->unidades_devuelto + $unidades;
                    $total_devuelto = $registro->total_devuelto + $total;
                    $unidades_pendiente = $registro->unidades_pendiente - $unidades;
                    $total_pendiente = $registro->total_pendiente - $total;
                    
                    $registro->update([
                        'unidades_devuelto' => $unidades_devuelto,
                        'total_devuelto' => $total_devuelto,
                        'unidades_pendiente' => $unidades_pendiente,
                        'total_pendiente' => $total_pendiente
                    ]);

                    $libro = Libro::whereId($registro->libro_id)->first();
                    $libro->update(['piezas' => $libro->piezas + $unidades]);
                }

                $total_devolucion += $total;
            }

            $nota = Note::whereId($request->id)->first();
            $total_pagar = $nota->total_pagar - $total_devolucion;
            $total_devolucion = $nota->total_devolucion + $total_devolucion;
            $nota->update([
                'total_pagar' => $total_pagar,
                'total_devolucion' => $total_devolucion
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($nota);
    }

    // CREAR UNA NOTA
    // Función utilizada en NewNoteComponent
    public function store(Request $request){
        try{
            \DB::beginTransaction();
            $num = Note::get()->count() + 1;
            if($num < 10){
                $folio = 'A000'.$num;
            }
            if($num >= 10 && $num < 100){
                $folio = 'A00'.$num;
            }
            if($num >= 100 && $num < 1000){
                $folio = 'A0'.$num;
            }
            if($num >= 1000 && $num < 10000){
                $folio = 'A'.$num;
            }
            $note = Note::create([
                'folio'     => $folio,
                'cliente'   => strtoupper($request->cliente),
                'entregado_por' => $request->entregado_por,
                'creado_por' => auth()->user()->name
            ]);
            $total = 0;
            foreach($request->registers as $register){
                Register::create([
                    'note_id' => $note->id,
                    'libro_id' => $register['id'],
                    'costo_unitario' => $register['costo_unitario'],
                    'unidades' => $register['unidades'],
                    'total' => $register['total'],
                    'unidades_pendiente' => $register['unidades'],
                    'total_pendiente' => $register['total']
                ]);
                
                $libro = Libro::whereId($register['id'])->first();
                $libro->update(['piezas' => $libro->piezas - $register['unidades']]);
                $total += $register['total'];
            }

            $note->update(['total_salida' => $total, 'total_pagar' => $total]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($note);
    }

    // ACTUALIZAR DATOS DE NOTA
    // Función utilizada en NewNoteComponent
    public function update(Request $request){
        $note = Note::whereId($request->id)->first();
        try{
            \DB::beginTransaction();
            //NUEVOS
            $total = 0;
            foreach($request->nuevos as $nuevo){
                Register::create([
                    'note_id' => $note->id,
                    'libro_id' => $nuevo['id'],
                    'costo_unitario' => $nuevo['costo_unitario'],
                    'unidades' => $nuevo['unidades'],
                    'total' => $nuevo['total'],
                    'unidades_pendiente' => $nuevo['unidades'],
                    'total_pendiente' => $nuevo['total']
                ]);
                $libro = Libro::whereId($nuevo['id'])->first();
                $libro->update(['piezas' => $libro->piezas - $nuevo['unidades']]);
                $total += $nuevo['total'];
            }
            $total_salida = $note->total_salida + $total;
            $total_pagar = $total_salida - ($note->pagos + $note->total_devolucion);
            $note->update([
                'cliente'   => strtoupper($request->cliente),
                'entregado_por' => $request->entregado_por,
                'total_salida' => $total_salida, 
                'total_pagar' => $total_pagar
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($note);
    }

    public function by_fecha(Request $request){
        $inicio = $request->inicio;
        $final = $request->final;
        $cliente = $request->cliente;

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        if($cliente === null){
            $notes = Note::whereBetween('created_at', [$fecha1, $fecha2])
                ->orderBy('id','desc')->paginate(20); 
        } else {
            $notes = Note::where('cliente','like','%'.$cliente.'%')
                ->whereBetween('created_at', [$fecha1, $fecha2])
                ->orderBy('id','desc')->paginate(20); 
        }
        
        return response()->json($notes);
    }

    public function format_date($fecha1, $fecha2){
        $inicio = new Carbon($fecha1);
        $final 	= new Carbon($fecha2);
        $inicio = $inicio->format('Y-m-d 00:00:00');
        $final 	= $final->format('Y-m-d 23:59:59');

        $fechas = [
            'inicio' => $inicio,
            'final' => $final
        ];

        return $fechas;
    }

    public function download_note($cliente, $inicio, $final, $tipo){
        return Excel::download(new NotesExport($cliente, $inicio, $final, $tipo), 'reporte-notas.xlsx');
    }

    public function download_nota($id){
        $note = Note::find($id);
        $nombre_archivo = 'nota_' . $note->folio . '.xlsx';
        return Excel::download(new NoteExport($id), $nombre_archivo);
        // $nota = Note::whereId($id)->with('registers.libro')->first();
        // $total_unidades = $this->acumular_unidades($nota->registers);
        // $data['nota'] = $nota;
        // $data['total_unidades'] = $total_unidades;
        // $pdf = PDF::loadView('download.pdf.notas.nota', $data); 
        // return $pdf->download('nota.pdf');
    }

    public function acumular_unidades($registers){
        $total_unidades = 0;
        foreach($registers as $register){
            $total_unidades += $register->unidades;
        }
        return $total_unidades;
    }

    public function index(){
        $notes = Note::orderBy('folio','desc')->paginate(20);
        return response()->json($notes);
    }
}
