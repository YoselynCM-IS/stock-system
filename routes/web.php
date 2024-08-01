<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

// ADMINISTRADOR
Route::name('administrador.')->prefix('administrador')->middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/remisiones', 'AdministradorController@remisiones')->name('remisiones');
    Route::get('/notas', 'AdministradorController@notas')->name('notas');
    Route::get('/entradas', 'AdministradorController@entradas')->name('entradas');
    Route::get('/libros', 'AdministradorController@libros')->name('libros');
    Route::get('/clientes', 'AdministradorController@clientes')->name('clientes');
    Route::get('/pedidos', 'AdministradorController@pedidos')->name('pedidos');
    Route::get('/donaciones', 'AdministradorController@donaciones')->name('donaciones');
    Route::get('/movimientos', 'AdministradorController@movimientos')->name('movimientos');
    Route::get('/movimientos_monto', 'AdministradorController@movimientos_monto')->name('movimientos_monto');
    Route::get('/pagos', 'AdministradorController@pagos')->name('pagos');
    Route::get('/registrar_pago', 'AdministradorController@registrar_pago')->name('registrar_pago');
    Route::get('/fecha-adeudo', 'AdministradorController@fecha_adeudo')->name('fecha-adeudo');

    Route::name('entradas.')->prefix('entradas')->group(function () {
        Route::get('/pagos', 'AdministradorController@entradas_pagos')->name('pagos');
    });
    
    Route::get('/unidades', 'AdministradorController@unidades')->name('unidades');
    Route::get('/getUnidades', 'AdministradorController@getUnidades')->name('getUnidades');
    Route::get('/detallesUnidades', 'AdministradorController@detallesUnidades')->name('detallesUnidades');
    Route::get('/download_unidades', 'AdministradorController@download_unidades')->name('download_unidades');

    Route::get('/unidades_libro', 'AdministradorController@unidades_libro')->name('unidades_libro');
    Route::get('/getULibros', 'AdministradorController@getULibros')->name('getULibros');
    Route::get('/detallesULibro', 'AdministradorController@detallesULibro')->name('detallesULibro');
    Route::get('/download_ulibros', 'AdministradorController@download_ulibros')->name('download_ulibros');

    Route::get('/comparativa', 'AdministradorController@comparativa')->name('comparativa');

    Route::get('/majestic', 'AdministradorController@majestic')->name('majestic');

    Route::get('/entradas-salidas', 'AdministradorController@entradas_salidas')->name('entradas-salidas');
    Route::get('/salidas', 'AdministradorController@salidas')->name('salidas');
    Route::get('/cerrar', 'AdministradorController@cerrar')->name('cerrar');

    Route::get('/codes', 'AdministradorController@codes')->name('codes');
});

// CONTADOR
Route::name('contador.')->prefix('contador')->middleware(['auth', 'role:Contador'])->group(function () {
    Route::get('/remisiones', 'ContadorController@remisiones')->name('remisiones');
    Route::get('/pagos', 'ContadorController@pagos')->name('pagos');
    Route::get('/obtenerPagos', 'ContadorController@obtenerPagos')->name('obtenerPagos');
    Route::get('/pagosFecha', 'ContadorController@pagosFecha')->name('pagosFecha');
    Route::get('/movimientos_monto', 'ContadorController@movimientos_monto')->name('movimientos_monto');
});

// OFICINA
Route::name('oficina.')->prefix('oficina')->middleware(['auth', 'role:Oficina'])->group(function () {
    Route::get('/remisiones', 'OficinaController@remisiones')->name('remisiones');
    Route::get('/pedidos', 'OficinaController@pedidos')->name('pedidos');
    Route::get('/pagos', 'OficinaController@pagos')->name('pagos');
    Route::get('/clientes', 'OficinaController@clientes')->name('clientes');
    Route::get('/libros', 'OficinaController@libros')->name('libros');
    Route::get('/codes', 'OficinaController@codes')->name('codes');
    Route::get('/entradas', 'OficinaController@entradas')->name('entradas');
    Route::get('/donaciones', 'OficinaController@donaciones')->name('donaciones');
    Route::get('/fecha-adeudo', 'OficinaController@fecha_adeudo')->name('fecha-adeudo');
    Route::get('/cerrar', 'OficinaController@cerrar')->name('cerrar');

    Route::name('entradas.')->prefix('entradas')->group(function () {
        Route::get('/pagos', 'OficinaController@entradas_pagos')->name('pagos');
    });

    Route::get('/salidas', 'OficinaController@salidas')->name('salidas');
    Route::get('/entradas-salidas', 'OficinaController@entradas_salidas')->name('entradas-salidas');
});

// CAPTURA
Route::name('captura.')->prefix('captura')->middleware(['auth', 'role:Captura'])->group(function () {
    Route::get('/remisiones', 'CapturaController@remisiones')->name('remisiones');
});

// ALMACEN
Route::name('almacen.')->prefix('almacen')->middleware(['auth', 'role:Almacen'])->group(function () {
    Route::get('/remisiones', 'AlmacenController@remisiones')->name('remisiones');
    Route::get('/devoluciones', 'AlmacenController@devoluciones')->name('devoluciones');
    Route::get('/notas', 'AlmacenController@notas')->name('notas');
    Route::get('/entradas', 'AlmacenController@entradas')->name('entradas');
    Route::get('/libros', 'AlmacenController@libros')->name('libros');
    Route::get('/pedidos', 'AlmacenController@pedidos')->name('pedidos');
    Route::get('/donaciones', 'AlmacenController@donaciones')->name('donaciones');
    Route::get('/movimientos', 'AlmacenController@movimientos')->name('movimientos');
    Route::get('/entradas-salidas', 'AlmacenController@entradas_salidas')->name('entradas-salidas');
    Route::get('/codes', 'AlmacenController@codes')->name('codes');
});

//CLIENTES
//Buscar cliente
Route::get('/mostrarClientes', 'ClienteController@mostrarClientes')->name('mostrarClientes');
// DESCARGAR LISTA DE CLIENTES
Route::get('/descargar_clientes', 'ClienteController@descargar_clientes')->name('descargar_clientes');


//REMISIONES
// OBTENER TODAS AS REMISIONES
Route::name('remisiones.')->prefix('remisiones')
    ->middleware(['auth'])->group(function () {
    Route::get('/index', 'RemisionController@index')->name('index');
    Route::get('/pay_remisiones', 'RemisionController@pay_remisiones')->name('pay_remisiones');
    // Crear remision
    Route::post('/store', 'RemisionController@store')->name('store');
    // Actualizar remision
    Route::put('/update', 'RemisionController@update')->name('update');
    //Cancelar remision
    Route::put('/cancel', 'RemisionController@cancel')->name('cancel');
    //Cerrar remision
    Route::put('/close', 'RemisionController@close')->name('close');
    // Obtener remisiones pendientes
    Route::get('/obtener_pendientes', 'RemisionController@obtener_pendientes')->name('obtener_pendientes');
    // Obtener remisiones pendientes
    Route::get('/by_cliente_pendientes', 'RemisionController@by_cliente_pendientes')->name('by_cliente_pendientes');
    // ABRIR PAGINA PARA CREAR REMSION
    Route::get('/ce_remision/{remisione_id}/{editar}', 'RemisionController@ce_remision')->name('ce_remision');
    // ABRIR PAGINA PARA OBTENER DETALLES DE REMSION
    Route::get('/details/{id}', 'RemisionController@get_details')->name('details');
    // OBTENER LOS RESPONSABLES
    Route::get('/get_responsables', 'RemisionController@get_responsables')->name('get_responsables');
    //Buscar remision
    Route::get('obtener_devoluciones', 'RemisionController@obtener_devoluciones')->name('obtener_devoluciones');
    // Asignar responsable de la remision
    Route::post('save_responsable', 'RemisionController@save_responsable')->name('save_responsable');
    // Guardar informacion de envio de la remision
    Route::post('save_envio', 'RemisionController@save_envio')->name('save_envio');
    // ABRIR VENTANA PARA ELIMINAR DEVOLUCIÓN
    Route::get('/delete_dev/{remisione_id}', 'RemisionController@delete_dev')->name('delete_dev');
    // Surtir remisión desde un pedido
    Route::post('surtir', 'RemisionController@surtir')->name('surtir');
    
    // HISTORIAL
    // Verificar que no exista el folio
    Route::get('check_folio', 'RemisionController@check_folio')->name('check_folio');
    // Crear remision
    Route::post('/historial_store', 'RemisionController@historial_store')->name('historial_store');

    // // ENVIAR LA REMISION A PROVEEDOR
    // Route::put('/enviar', 'RemisionController@enviar')->name('enviar');
});


// OBTENER REMISION POR ID
Route::get('get_remision_id', 'RemisionController@get_remision_id')->name('get_remision_id');


// REMISIONES BUSQUEDA
// Buscar remisiones por cliente
Route::get('buscar_por_cliente', 'RemisionController@buscar_por_cliente')->name('buscar_por_cliente');
// Buscar remisiones por estado
Route::get('buscar_por_estado', 'RemisionController@buscar_por_estado')->name('buscar_por_estado');
// Buscar remisiones por fecha y cliente / estado
Route::get('buscar_por_fecha', 'RemisionController@buscar_por_fecha')->name('buscar_por_fecha');
// Obtener totales por fecha
Route::get('get_totales_fecha', 'RemisionController@get_totales_fecha')->name('get_totales_fecha');

//REMISIONES -Listado
Route::get('todos_los_clientes', 'RemisionController@todos')->name('todos_los_clientes');
Route::get('buscar_por_numero', 'RemisionController@por_numero')->name('buscar_por_numero');


//REMISIONES - Descargar
Route::get('/imprimirSalida/{id}', 'RemisionController@imprimirSalida')->name('imprimirSalida');

Route::get('/download_remision/{id}', 'RemisionController@download_remision')->name('download_remision');

// DESCARGAR TODO EN EXCEL DETALLADO
Route::get('/down_remisiones_excel/{cliente_id}/{inicio}/{final}/{estado}', 'RemisionController@down_remisiones_excel')->name('down_remisiones_excel');
// DESCARGAR TODO EN EXCEL GENERAL
Route::get('/down_gral_excel/{cliente_id}/{inicio}/{final}/{estado}', 'RemisionController@down_gral_excel')->name('down_gral_excel');
// DESCARGAR TODO EN PDF
Route::get('/down_remisiones_pdf/{cliente_id}/{inicio}/{final}/{estado}', 'RemisionController@down_remisiones_pdf')->name('down_remisiones_pdf');
// DESCARGAR LA CUENTA GENERAL DEL CLIENTE
Route::get('/descargar_gralClientes', 'RemisionController@descargar_gralClientes')->name('descargar_gralClientes');


//LIBROS
//Agregar libro
Route::post('new_libro', 'LibroController@store')->name('new_libro');
//Actualizar libro
Route::put('actualizar_libro', 'LibroController@update')->name('actualizar_libro');
//Eliminar libro
Route::delete('eliminar_libro', 'LibroController@delete')->name('eliminar_libro');
//Buscar libro
Route::get('/mostrarLibros', 'LibroController@buscar')->name('mostrarLibros');
// Mostrar libros por editorial
Route::get('/libros_por_editorial', 'LibroController@libros_por_editorial')->name('libros_por_editorial');
//Datos del libro
Route::get('/buscarISBN', 'LibroController@show')->name('buscarISBN'); 
// Buscar libro por ISBN y editorial
Route::get('/isbn_por_editorial', 'LibroController@isbn_por_editorial')->name('isbn_por_editorial'); 
//Obtener todos los libros
Route::get('allLibros', 'LibroController@allLibros')->name('allLibros');
// Descargar en formato excel todos los libros
Route::get('/downloadExcel/{editorial}', 'LibroController@downloadExcel')->name('downloadExcel');
// Mostrar entradas por libro
Route::get('movimientos_todos', 'LibroController@movimientos_todos')->name('movimientos_todos');
// Mostrar entradas por libro
Route::get('movimientos_por_edit', 'LibroController@movimientos_por_edit')->name('movimientos_por_edit');
// Mostrar libros por tipo
Route::get('obtener_movimientos', 'LibroController@obtener_movimientos')->name('obtener_movimientos');
// Detalles del movimiento
Route::get('detalles_movimientos', 'LibroController@detalles_movimientos')->name('detalles_movimientos');
// Obtener movimientos por fecha
Route::get('movimientos_por_fecha', 'LibroController@movimientos_por_fecha')->name('movimientos_por_fecha');
// Descargar movimientos por fecha y categoria
Route::get('/down_fechaCategoria/{incio}/{final}/{categoria}', 'LibroController@down_fechaCategoria')->name('down_fechaCategoria');

// OBTENER TODOS LOS MOVIMIENTOS POR MONTO
Route::get('all_movmonto', 'LibroController@all_movmonto')->name('all_movmonto');
// OBTENER LOS MOVIMIENTOS POR EDITORIAL
Route::get('editorial_movmonto', 'LibroController@editorial_movmonto')->name('editorial_movmonto');
// OBTENER LOS MOVIMIENTOS POR FECHA
Route::get('fecha_movmonto', 'LibroController@fecha_movmonto')->name('fecha_movmonto');
// OBTENER DETALLES DE MONTO POR LIBRO
Route::get('detalles_monto', 'LibroController@detalles_monto')->name('detalles_monto');
// DESCARGAR EXCEL DE LOS MOVIMIENTOS 
Route::get('download_movmonto/{editorial}/{mes}', 'LibroController@download_movmonto')->name('download_movmonto');

//ENTRADAS
//Buscar folio
Route::get('/buscarFolio', 'EntradaController@buscarFolio')->name('buscarFolio'); 
//Mostrar todas las entradas
Route::get('detalles_entrada', 'EntradaController@detalles_entrada')->name('detalles_entrada');
//Imprimir entrada
Route::get('/downloadEntrada/{id}', 'EntradaController@downloadEntrada')->name('downloadEntrada');
//Mostrarentradas por fecha
Route::put('pago_entrada', 'EntradaController@pago_entrada')->name('pago_entrada');
// Descargar reporte de entradas en PDF
Route::get('/downEntradas/{inicio}/{final}/{editorial}', 'EntradaController@downEntradas')->name('downEntradas');
// Descargar reporte de entradas en EXCEL
Route::get('/downEntradasEXC/{inicio}/{final}/{editorial}/{tipo}', 'EntradaController@downEntradasEXC')->name('downEntradasEXC');
// Obtener todos los pagos de las entradas por editorial
Route::get('pagos_entrada', 'EntradaController@pagos_entrada')->name('pagos_entrada');

Route::name('entradas.')->prefix('entradas')->group(function () {
    // Guardar deposito de entrada
    Route::post('save_pago', 'EntradaController@save_pago')->name('save_pago');
    // ACTUALIZAR DEPOSITO
    Route::put('update_pago', 'EntradaController@update_pago')->name('update_pago');
    // ELIMINAR PAGO
    Route::delete('delete_pago', 'EntradaController@delete_pago')->name('delete_pago');
    // ENVIAR UNIDADES A ME
    Route::put('send_me', 'EntradaController@send_me')->name('send_me');
    // ENVIAR UNIDADES A ME
    Route::put('send_salida', 'EntradaController@send_salida')->name('send_salida');
    // OBTENER LAS ENTRADAS
    Route::get('index', 'EntradaController@index')->name('index');
    //Buscar editorial
    Route::get('/by_editorial', 'EntradaController@by_editorial')->name('by_editorial');
    //Buscar entradas por imprenta
    Route::get('/by_imprenta', 'EntradaController@by_imprenta')->name('by_imprenta');
    //Mostrarentradas por fecha
    Route::get('by_fecha', 'EntradaController@by_fecha')->name('by_fecha');
    // Mostrar los depositos por editorial
    Route::get('enteditoriale_pagos', 'EntradaController@enteditoriale_pagos')->name('enteditoriale_pagos');

    ///Crear entrada
    Route::post('store', 'EntradaController@store')->name('store');
    ///Crear entrada de codigos
    Route::post('store_codes', 'EntradaController@store_codes')->name('store_codes');
    //Actualizar entrada
    Route::put('update', 'EntradaController@update')->name('update');
    //Actualizar costos unitarios
    Route::put('update_costos', 'EntradaController@update_costos')->name('update_costos');
    ///Guardar devolución de la entrada
    Route::post('devolucion', 'EntradaController@devolucion')->name('devolucion');

    // GUARDAR EDITORIAL
    Route::post('save_editorial', 'EntradaController@save_editorial')->name('save_editorial');
    // GUARDAR IMPRENTA
    Route::post('save_imprenta', 'EntradaController@save_imprenta')->name('save_imprenta');
    // OBTENER CORTES DE LA EDITORIAL
    Route::get('get_cortes', 'EntradaController@get_cortes')->name('get_cortes');
    // OBTENER DETALLES DEL CORTE DE UNA EDITORIAL
    Route::get('cortes_details', 'EntradaController@cortes_details')->name('cortes_details');
    // OBTENER LISTA DE IMPRENTAS
    Route::get('/get_imprentas/{tipo}', 'EntradaController@get_imprentas')->name('get_imprentas');

    // OBTENER COMPROBANTE
    Route::get('comprobante_url', 'EntradaController@comprobante_url')->name('comprobante_url');
});

//PAGOS
//Guardar pago por unidades
Route::post('registrar_pago', 'PagoController@store')->name('registrar_pago');
// Guardar pago de remision por monto
Route::post('deposito_remision', 'PagoController@deposito_remision')->name('deposito_remision');
//Obtener registros de vendidos
Route::get('datos_vendidos', 'PagoController@datos_vendidos')->name('datos_vendidos');
//Buscar pagos por cliente por geneeral
Route::get('/all_pagos', 'PagoController@all_pagos')->name('all_pagos');
//Buscar pagos por cliente por remision
Route::get('/pagos_remision_cliente', 'PagoController@pagos_remision_cliente')->name('pagos_remision_cliente');
// Guardar la revisión del deposito
Route::put('guardar_revision', 'PagoController@guardar_revision')->name('guardar_revision');

//NOTA
//Guardar nota
Route::post('guardar_nota', 'NoteController@store')->name('guardar_nota');
//Actualizar nota
Route::post('actualizar_nota', 'NoteController@update')->name('actualizar_nota');
//Mostrar detalles de nota
Route::get('detalles_nota', 'NoteController@detalles_nota')->name('detalles_nota');
//Guardar pago de la nota
Route::post('guardar_pago', 'NoteController@guardar_pago')->name('guardar_pago');
//Guardar devolucion
Route::post('guardar_devolucion', 'NoteController@guardar_devolucion')->name('guardar_devolucion');
// Descargar reporte de notas
Route::get('/download_note/{cliente}/{inicio}/{final}/{tipo}', 'NoteController@download_note')->name('download_note');
// Descargar nota
Route::get('/download_nota/{id}', 'NoteController@download_nota')->name('download_nota');

Route::name('notes.')->prefix('notes')->group(function () {
    Route::get('index', 'NoteController@index')->name('index');
    // Buscar nota por folio
    Route::get('by_folio', 'NoteController@by_folio')->name('by_folio');
    // Buscar por cliente
    Route::get('by_cliente', 'NoteController@by_cliente')->name('by_cliente');
    // Buscar notas por fecha
    Route::get('by_fecha', 'NoteController@by_fecha')->name('by_fecha');
});

//PROMOCION
//Guardar promocion
Route::post('guardar_promocion', 'PromotionController@store')->name('guardar_promocion');
//Mostrar departures
Route::get('obtener_departures', 'PromotionController@obtener_departures')->name('obtener_departures');
// Buscar promocion por folio
Route::get('buscar_folio_promo', 'PromotionController@buscar_folio')->name('buscar_folio_promo');
// Buscar promocion por plantel
Route::get('buscar_plantel', 'PromotionController@buscar_plantel')->name('buscar_plantel');
// Buscar promociones por fecha
Route::get('buscar_fecha_promo', 'PromotionController@buscar_fecha_promo')->name('buscar_fecha_promo');
// Descargar el reporte de promoción
Route::get('download_promotion/{plantel}/{inicio}/{final}/{tipo}', 'PromotionController@download_promotion')->name('download_promotion');
// Descargar nota de la promocion
Route::get('/download_promocion/{id}', 'PromotionController@download_promocion')->name('download_promocion');

Route::name('promotions.')->prefix('promotions')->group(function () {
    Route::put('cancel', 'PromotionController@cancel')->name('cancel');
    Route::post('devolucion', 'PromotionController@devolucion')->name('devolucion');
    Route::get('index', 'PromotionController@index')->name('index');
    Route::post('save_envio', 'PromotionController@save_envio')->name('save_envio');
    // Route::put('enviar', 'PromotionController@enviar')->name('enviar');
});

// DONACIONE
// Obtener detalles de la donación
Route::get('detalles_donacion', 'DonacioneController@detalles_donacion')->name('detalles_donacion');
// Descargar el reporte de promoción
Route::get('download_donacion/{plantel}/{inicio}/{final}/{tipo}', 'DonacioneController@download_donacion')->name('download_donacion');
// Marcar como entregada la donación
Route::put('entrega_donacion', 'DonacioneController@entrega_donacion')->name('entrega_donacion');
// Descargar nota de la donacion
Route::get('/download_regalo/{id}', 'DonacioneController@download_regalo')->name('download_regalo');

// Guardar comentario de la remisión
Route::post('guardar_comentario', 'RemisionController@guardar_comentario')->name('guardar_comentario');

// // VENDIDO
// // Obtener todas los libros vendidos
// Route::get('obtener_vendidos', 'VendidoController@obtener_vendidos')->name('obtener_vendidos');
// // Obtener por fecha
// Route::get('obtener_por_fecha', 'VendidoController@obtener_por_fecha')->name('obtener_por_fecha');
// // Obtener unidades vendidas por libro
// Route::get('obtener_libro', 'VendidoController@obtener_libro')->name('obtener_libro');
// //Obtener por libros y fecha
// Route::get('libro_por_fecha', 'VendidoController@libro_por_fecha')->name('libro_por_fecha');
// // Obtener libros vendidos por cliente
// Route::get('obtener_cliente', 'VendidoController@obtener_cliente')->name('obtener_cliente');
// // Obtener libros vendidos por cliente y fecha
// Route::get('cliente_por_fecha', 'VendidoController@cliente_por_fecha')->name('cliente_por_fecha');
// // Obtener libros vendidos por editorial
// Route::get('obtener_editorial', 'VendidoController@obtener_editorial')->name('obtener_editorial');
// // Obtener por editorial y fecha
// Route::get('editorial_por_fecha', 'VendidoController@editorial_por_fecha')->name('editorial_por_fecha');
// // Obtener detalles de vendidos
// Route::get('detalles_vendidos', 'VendidoController@detalles_vendidos')->name('detalles_vendidos');

// DESCARGAR REPORTES
// Descargar reporte de libros vendidos por cliente
Route::get('/downClienteEX/{cliente_id}/{fecha1}/{fecha2}', 'VendidoController@downClienteEX')->name('downClienteEX');
// Descargar reporte por libro
Route::get('/downLibroEX/{libro_id}/{fecha1}/{fecha2}', 'VendidoController@downLibroEX')->name('downLibroEX');
// Descargar reporte de libros vendidos por editorial
Route::get('/downEditorialEX/{editorial}/{fecha1}/{fecha2}', 'VendidoController@downEditorialEX')->name('downEditorialEX');
// Descargar reporte detallado de libros vendidos
Route::get('/downDetalladoEX/{fecha1}/{fecha2}', 'VendidoController@downDetalladoEX')->name('downDetalladoEX');

// DESCARGAR LA CUENTA GENERAL DE LAS EDITORIALES
Route::get('/descargar_gralEdit', 'EntradaController@descargar_gralEdit')->name('descargar_gralEdit'); 


// OBTENER REMCLIENTE PARA REALIZAR PAGO
Route::get('get_remcliente', 'RemisionController@get_remcliente')->name('get_remcliente');



// MOSTRAR EDITORIALES
Route::get('show_editoriales', 'OficinaController@show_editoriales')->name('show_editoriales');

Route::name('pedido.')->prefix('pedido')->middleware(['auth'])->group(function () {
    Route::get('index', 'PedidoController@index')->name('index');
    Route::post('store', 'PedidoController@store')->name('store');
    Route::get('/show/{pedido_id}/{notification_id?}', 'PedidoController@show')->name('show');
    Route::get('/preparar/{pedido_id}', 'PedidoController@preparar')->name('preparar');
    Route::put('/preparado', 'PedidoController@preparado')->name('preparado');
    Route::put('/despachar', 'PedidoController@despachar')->name('despachar');
    Route::put('/cancelar', 'PedidoController@cancelar')->name('cancelar');
    Route::get('/by_cliente', 'PedidoController@by_cliente')->name('by_cliente');
    Route::get('/create_edit/{tipo}/{pedido_id}', 'PedidoController@create_edit')->name('create_edit');
    Route::put('/update', 'PedidoController@update')->name('update');
    Route::put('/cerrar', 'PedidoController@cerrar')->name('cerrar');
    Route::get('/check_scratch', 'PedidoController@check_scratch')->name('check_scratch');
    Route::put('/save_scratch', 'PedidoController@save_scratch')->name('save_scratch');
});

Route::name('order.')->prefix('order')->group(function () {
    Route::get('index', 'OrderController@index')->name('index');
    Route::post('store', 'OrderController@store')->name('store');
    Route::get('/show/{order_id}', 'OrderController@show')->name('show');
    Route::put('change_status', 'OrderController@change_status')->name('change_status');
    Route::put('cancelar', 'OrderController@cancelar')->name('cancelar');
    Route::put('add_costo', 'OrderController@add_costo')->name('add_costo');
    Route::post('relacionar', 'OrderController@relacionar')->name('relacionar');
    Route::get('/by_provider', 'OrderController@by_provider')->name('by_provider');
    Route::get('by_cliente', 'OrderController@by_cliente')->name('by_cliente');
});

//REMCLIENTE
Route::name('remcliente.')->prefix('remcliente')->group(function () {
    // OBTENER TODAS LAS REMCLIENTE
    Route::get('/index', 'RemclienteController@index')->name('index');
    Route::get('/by_cliente', 'RemclienteController@by_cliente')->name('by_cliente');
    Route::get('/get_totales', 'RemclienteController@get_totales')->name('get_totales');
    Route::get('/get_gralcortes', 'RemclienteController@get_gralcortes')->name('get_gralcortes');
    Route::get('/gc_bycliente', 'RemclienteController@gc_bycliente')->name('gc_bycliente');
});

//LIBROS
Route::name('libro.')->prefix('libro')->group(function () {
    Route::get('/index', 'LibroController@index')->name('index');
    Route::get('/by_titulo', 'LibroController@by_titulo')->name('by_titulo');
    Route::get('/by_isbn', 'LibroController@by_isbn')->name('by_isbn');
    //Buscar libro por editorial
    Route::get('/by_editorial', 'LibroController@by_editorial')->name('by_editorial');
    // OBTENER MOVIMIENTOS DEL LIBRO
    Route::get('/movimientos_libro', 'LibroController@movimientos_libro')->name('movimientos_libro');
    // MARCAR COMO INACTIVO EL LIBRO
    Route::put('/inactivar', 'LibroController@inactivar')->name('inactivar');
    // GUARDAR DEFECTUOSOS
    Route::put('/save_defectuosos', 'LibroController@save_defectuosos')->name('save_defectuosos');
    // OBTENER ENTRADAS Y SALIDAS
    Route::get('/entradas_salidas', 'LibroController@entradas_salidas')->name('entradas_salidas');
    // OBTENER DETALLES DE ENTRADAS Y SALIDAS, DE UN LIBRO
    Route::get('/details_entsal', 'LibroController@details_entsal')->name('details_entsal');
    // OBTENER TODAS LAS EDITORIALES
    Route::get('get_editoriales', 'LibroController@get_editoriales')->name('get_editoriales');
    // DESCARGAR LAS ENTRADAS SALIDAS
    Route::get('download_entsal/{editorial}/{de}/{a}', 'LibroController@download_entsal')->name('download_entsal');
    // Descargar movimientos por libro
    Route::get('/down_movgral/{editorial}/{type}', 'LibroController@down_movgral')->name('down_movgral');
    // Enviar movimientos por dia
    Route::get('/send_movday/{de}/{a}', 'LibroController@send_movday')->name('send_movday');
    // Buscar libros por isbn y editorial
    Route::get('/by_isbn_editorial', 'LibroController@by_isbn_editorial')->name('by_isbn_editorial');
    // Buscar libros por titulo y editorial
    Route::get('/by_titulo_editorial', 'LibroController@by_titulo_editorial')->name('by_titulo_editorial');
    // Buscar libros digitales por editorial
    Route::get('/by_editorial_digital', 'LibroController@by_editorial_digital')->name('by_editorial_digital');
    // Buscar por editorial, tipo y titulo
    Route::get('/by_editorial_type_titulo', 'LibroController@by_editorial_type_titulo')->name('by_editorial_type_titulo');
    // Buscar por editorial, tipo e isbn
    Route::get('/by_editorial_type_isbn', 'LibroController@by_editorial_type_isbn')->name('by_editorial_type_isbn');
    // Buscar libros por tipo
    Route::get('/by_type', 'LibroController@by_type')->name('by_type');
    // Buscar por titulo, no utilizados en la lista de los clientes
    Route::get('/by_titulo_nu', 'LibroController@by_titulo_nu')->name('by_titulo_nu');
    // OBTENER TODOS LOS LIBROS DE LOS 2 SISTEMAS
    Route::get('/all_list', 'LibroController@all_list')->name('all_list');
    // BUSCAR POR TITULO EN AMBOS SISTEMAS
    Route::get('/all_libro', 'LibroController@all_libro')->name('all_libro');
    // OBTENER VISTA DEL LISTADO DE LIBROS DE AMBOS SISTEMAS
    Route::get('/all_sistemas', 'LibroController@all_sistemas')->name('all_sistemas');
    // OBTENER LOS LIBROS QUE ESTAN EN SCRTACH
    Route::get('/all_scratch', 'LibroController@all_scratch')->name('all_scratch');
    // OBTENER LOS LIBROS QUE ESTAN EN PACK
    Route::get('/scratch_libros', 'LibroController@scratch_libros')->name('scratch_libros');
    // OBTENER LIBRO, PARA VERIFICAR SCRATCH
    Route::get('/get_scratch', 'LibroController@get_scratch')->name('get_scratch');
    // GUARDAR PACK
    Route::post('/save_pack', 'LibroController@save_pack')->name('save_pack');
});

// PAGOS
Route::name('pagos.')->prefix('pagos')->group(function () {
    // Guardar pago
    //Guardar deposito de cuenta general
    Route::post('store_gral', 'PagoController@store_gral')->name('store_gral');
    // Mostrar los depositos por cliente
    Route::get('/depositos_cliente', 'PagoController@depositos_cliente')->name('depositos_cliente');
    // Descargar el estado de cuenta del cliente
    Route::get('/download_edocuenta/{cliente_id}', 'PagoController@download_edocuenta')->name('download_edocuenta');
});

//DEVOLUCIONES
Route::name('devoluciones.')->prefix('devoluciones')->group(function () {
    //REGISTRAR DEVOLUCION
    Route::put('update', 'DevolucioneController@update')->name('update');
    // BORRAR DEVOLUCIÓN
    Route::delete('delete', 'DevolucioneController@delete')->name('delete');

    // HISTORIAL
    // Registrar devolucion
    Route::put('historial_update', 'DevolucioneController@historial_update')->name('historial_update');
});

//DONACIONES
Route::name('donaciones.')->prefix('donaciones')->group(function () {
    //Obtener todas las donaciones
    Route::get('/index', 'DonacioneController@index')->name('index');
    // GUARDAR DONACIONES
    Route::post('store', 'DonacioneController@store')->name('store');
    // Obtener donaciones por fecha
    Route::get('by_fecha', 'DonacioneController@by_fecha')->name('by_fecha');
    // Obtener donaciones por plantel
    Route::get('by_plantel', 'DonacioneController@by_plantel')->name('by_plantel');
    // Obtener donaciones por destino
    Route::get('by_destino', 'DonacioneController@by_destino')->name('by_destino');
});

//Obtener todos los cliente
Route::get('/getTodo', 'ClienteController@getTodo')->name('getTodo');
//CLIENTES
Route::name('clientes.')->prefix('clientes')->group(function () {
    //Agregar cliente
    Route::post('/store', 'ClienteController@store')->name('store');
    //Agregar cliente prospecto
    Route::post('/store_prospecto', 'ClienteController@store_prospecto')->name('store_prospecto');
    //Editar informacion de cliente
    Route::put('/update', 'ClienteController@update')->name('update');
    // ACTUALIZAR TIPO DE CLIENTE
    Route::put('/update_tipo', 'ClienteController@update_tipo')->name('update_tipo');
    //Obtener todos los cliente
    Route::get('/index', 'ClienteController@index')->name('index');
    //Obtener los clientes por coincidencia de nombre
    Route::get('/by_name', 'ClienteController@by_name')->name('by_name');
    // Detalles del cliente
    Route::get('/show', 'ClienteController@show')->name('show'); 
    // Obtener estados de la republica mexicana
    Route::get('/get_estados', 'ClienteController@get_estados')->name('get_estados'); 
    // Obtener los usuarios registrados en sistema
    Route::get('/get_usuarios', 'ClienteController@get_usuarios')->name('get_usuarios');
    // Guardar libro relacionado al cliente
    Route::post('/save_libro', 'ClienteController@save_libro')->name('save_libro'); 
    // Actualizar libro relacionado al cliente
    Route::put('/update_libro', 'ClienteController@update_libro')->name('update_libro');
    // Borrar libro relacionado al cliente
    Route::delete('/delete_libro', 'ClienteController@delete_libro')->name('delete_libro');
    // Obtener los libros registrados en el cliente
    Route::get('/get_libros', 'ClienteController@get_libros')->name('get_libros');
    //Obtener los clientes por responsables
    Route::get('/by_userid', 'ClienteController@by_userid')->name('by_userid');
    Route::get('/by_name_userid', 'ClienteController@by_name_userid')->name('by_name_userid');
    
    // Obtener clientes por tipo
    Route::get('/by_tipo', 'ClienteController@by_tipo')->name('by_tipo');

    // BUSCAR DESTINATARIOS
    Route::get('/get_destinatarios', 'ClienteController@get_destinatarios')->name('get_destinatarios');// Guardar libro relacionado al cliente
    
    Route::post('/save_seguimiento', 'ClienteController@save_seguimiento')->name('save_seguimiento'); 
    // Obtener seguimiento de clientes
    Route::get('/get_seguimiento', 'ClienteController@get_seguimiento')->name('get_seguimiento');
    // Borrar cliente
    Route::put('/change_status', 'ClienteController@change_status')->name('change_status');
    // OBTENER CLIENTES POR COINCIDENCIA DE NOMBRE Y STATUS
    Route::get('/by_namestatus', 'ClienteController@by_namestatus')->name('by_namestatus');
});

// MANAGER
Route::name('manager.')->prefix('manager')
    ->middleware(['auth', 'role:Manager'])->group(function () {
    Route::name('cortes.')->prefix('cortes')->group(function () {
        Route::get('/lista', 'ManagerController@lista_cortes')->name('lista');
        Route::get('/pagos', 'ManagerController@cortes_pagos')->name('pagos');
    });
    Route::name('movimientos.')->prefix('movimientos')->group(function () {
        Route::get('/clientes', 'ManagerController@movimientos_clientes')->name('clientes');
        Route::get('/libros', 'ManagerController@movimientos_libros')->name('libros');
        Route::get('/entradas-salidas', 'ManagerController@entradas_salidas')->name('entradas-salidas');
    });
    Route::name('remisiones.')->prefix('remisiones')->group(function () {
        Route::get('/lista', 'ManagerController@lista_remisiones')->name('lista');
        Route::get('/pago_devolucion', 'ManagerController@pago_devolucion')->name('pago_devolucion');
        Route::get('/fecha_adeudo', 'ManagerController@fecha_adeudo')->name('fecha_adeudo');
    });
    Route::name('otros.')->prefix('otros')->group(function () {
        Route::get('/notas', 'ManagerController@notas')->name('notas');
        Route::get('/donaciones', 'ManagerController@donaciones')->name('donaciones');
    });
    Route::name('entradas.')->prefix('entradas')->group(function () {
        Route::get('/lista', 'ManagerController@lista_entradas')->name('lista');
        Route::get('/pagos', 'ManagerController@entradas_pagos')->name('pagos');
    });
    Route::get('/libros', 'ManagerController@libros')->name('libros');
    Route::get('/codes', 'ManagerController@codes')->name('codes');
    Route::get('/clientes', 'ManagerController@clientes')->name('clientes');
    Route::get('/salidas', 'ManagerController@salidas')->name('salidas');

    Route::name('users.')->prefix('users')->group(function () {
        Route::get('/lista', 'ManagerController@lista_users')->name('lista');
    });
});

Route::name('users.')->prefix('users')
    ->middleware(['auth', 'role:Manager'])->group(function () {
    Route::get('/index', 'UserController@index')->name('index');
    Route::put('/update', 'UserController@update')->name('update');
    Route::post('/store', 'UserController@store')->name('store');
    Route::delete('/delete', 'UserController@delete')->name('delete');
    Route::put('/restore', 'UserController@restore')->name('restore');
    Route::get('/get_roles', 'UserController@get_roles')->name('get_roles');
    Route::get('/set_user', 'UserController@set_user')->name('set_user');
});

Route::name('cortes.')->prefix('cortes')->group(function () {
    Route::get('/index', 'CorteController@index')->name('index');
    Route::get('/get_all', 'CorteController@get_all')->name('get_all');
    Route::post('/store', 'CorteController@store')->name('store');
    Route::put('/update', 'CorteController@update')->name('update');
    Route::get('/show', 'CorteController@show')->name('show');
    Route::get('/show_one', 'CorteController@show_one')->name('show_one');
    Route::get('/show_bycliente', 'CorteController@show_bycliente')->name('show_bycliente');
    Route::get('/get_remisiones', 'CorteController@get_remisiones')->name('get_remisiones');
    Route::get('/rems_bycliente', 'CorteController@rems_bycliente')->name('rems_bycliente');
    Route::put('/clasificar_rems', 'CorteController@clasificar_rems')->name('clasificar_rems');
    Route::put('/move_rem', 'CorteController@move_rem')->name('move_rem');
    Route::get('/get_pagos', 'CorteController@get_pagos')->name('get_pagos');
    Route::get('/pagos_bycliente', 'CorteController@pagos_bycliente')->name('pagos_bycliente');
    Route::put('/clasificar_pagos', 'CorteController@clasificar_pagos')->name('clasificar_pagos');
    Route::put('/move_pago', 'CorteController@move_pago')->name('move_pago');
    Route::put('/verify_totales', 'CorteController@verify_totales')->name('verify_totales');
    Route::post('/save_payment', 'CorteController@save_payment')->name('save_payment');
    Route::get('/by_cliente', 'CorteController@by_cliente')->name('by_cliente');
    Route::put('/edit_payment', 'CorteController@edit_payment')->name('edit_payment');
    Route::delete('/delete_payment', 'CorteController@delete_payment')->name('delete_payment');
    Route::get('/list_bycliente', 'CorteController@list_bycliente')->name('list_bycliente');
    Route::get('/by_ficticios', 'CorteController@by_ficticios')->name('by_ficticios');

    // ABRIR EN PAGINA NUEVA
    Route::get('/details_cliente/{cliente_id}', 'CorteController@details_cliente')->name('details_cliente');

    Route::post('/upload_payment', 'CorteController@upload_payment')->name('upload_payment');
    Route::get('/image_url', 'CorteController@image_url')->name('image_url');

    // ADEUDOS
    Route::name('adeudos.')->prefix('adeudos')->group(function () {
        Route::post('/save', 'CorteController@save_adeudo')->name('save');
        // ABONOS
        Route::name('abonos.')->prefix('abonos')->group(function () {
            Route::post('/save', 'CorteController@save_abono')->name('save');
            Route::get('/get', 'CorteController@get_abonos')->name('get');
        });
    });
});

Route::get('/information/majestic', function () {
    return view('information.majesticeducation.index');
})->middleware(['auth'])->name('information.majestic');

Route::name('historial.')->prefix('historial')->middleware(['auth'])->group(function () {
    // OBTENER LISTADO DE REMISIONES POR PERIODO
    Route::get('/remisiones_byperiodo', 'RemisionController@remisiones_byperiodo')->name('remisiones_byperiodo');
    
    Route::name('remisiones.')->prefix('remisiones')->group(function () {
        // VISTA DE REMISIONES
        Route::get('/lista/{corte_id}', 'RemisionController@lista_remisiones')->name('lista');
        // OBTENER LISTADO DE REMISIONES POR PERIODO Y CLIENTE
        Route::get('/byperiodo_cliente', 'RemisionController@byperiodo_cliente')->name('byperiodo_cliente');
    });
    Route::name('pagos.')->prefix('pagos')->group(function () {
        // REGISTRAR PAGO
        Route::get('/registrar/{cliente_id}/{corte_id}', 'RemclienteController@h_registrar_pago')->name('registrar');
    });

    // PARA HISTORIAL
    // CREAR REMISION
    Route::get('/crear_remision', 'RemisionController@h_crear_remision')->name('crear_remision');
    // REGISTRAR DEVOLUCIÓN
    Route::get('/registrar_devolucion/{remisione_id}', 'RemisionController@h_registrar_devolucion')->name('registrar_devolucion');
    // GUARDAR PAGO
    Route::post('/save_payment', 'CorteController@h_save_payment')->name('save_payment');
});

Route::name('information.')->prefix('information')->middleware(['auth'])->group(function () {
    Route::name('actividades.')->prefix('actividades')->group(function () {
        Route::get('/lista', 'ActividadeController@lista')->name('lista');
        Route::get('/simple', 'ActividadeController@simple')->name('simple');
        Route::get('/get_status/{status}', 'ActividadeController@get_status')->name('get_status');
        Route::get('/download/{id}', 'ActividadeController@download')->name('download');
    });

    Route::name('pedidos.')->prefix('pedidos')->group(function () {
        Route::get('/cliente', 'PedidoController@cliente')->name('cliente');
        Route::get('/proveedor', 'OrderController@proveedor')->name('proveedor');
    });
    
    Route::name('clientes.')->prefix('clientes')->group(function () {
        Route::get('/lista', 'ClienteController@lista')->name('lista');
        Route::get('/cortes', 'ClienteController@cortes')->name('cortes');
    });

    Route::name('remisiones.')->prefix('remisiones')->group(function () {
        Route::get('/lista', 'RemisionController@lista')->name('lista');
    });

    Route::name('reportes.')->prefix('reportes')->group(function () {
        Route::get('/lista', 'ReporteController@lista')->name('lista');
        Route::name('ventas.')->prefix('ventas')->group(function () {
            Route::get('/lista/{fi}/{ff}', 'ReporteController@ventas_lista')->name('lista');
            Route::get('/by_fecha', 'ReporteController@ventas_by_fecha')->name('by_fecha');
            Route::get('/download/{fi}/{ff}', 'ReporteController@down_ventas_by_fecha')->name('download');
        });
    });

    Route::name('entradas.')->prefix('entradas')->group(function () {
        Route::get('/lista', 'EntradaController@lista')->name('lista');
        Route::get('/cortes/{editorial}', 'EntradaController@cortes')->name('cortes');
    });

    Route::name('promotions.')->prefix('promotions')->group(function () {
        Route::get('/lista', 'PromotionController@lista')->name('lista');
    });

    Route::name('donations.')->prefix('donations')->group(function () {
        Route::get('/lista', 'DonacioneController@lista')->name('lista');
    });
}); 

Route::name('actividades.')->prefix('actividades')->group(function () {
    // GUARDAR ACTIVIDAD
    Route::post('/store', 'ActividadeController@store')->name('store');
    // OBTENER ACTIVIDADES POR FECHA ACTUAL
    Route::get('/by_user_fecha_actual', 'ActividadeController@by_user_fecha_actual')->name('by_user_fecha_actual');
    // MARCAR ACTIVIDADES COMO COMPLETADAS
    Route::put('/update_estado', 'ActividadeController@update_estado')->name('update_estado');
    // OBTENER ACTIVIDADES POR FECHA ACTUAL
    Route::get('/by_user_estado', 'ActividadeController@by_user_estado')->name('by_user_estado');
    // OBTENER ACTIVIDADES POR FECHA ACTUAL
    Route::put('/update', 'ActividadeController@update')->name('update');
    // OBTENER ACTIVIDADES POR FECHA ACTUAL
    Route::get('/view_notification', 'ActividadeController@view_notification')->name('view_notification');
    
    // **** POR REVISAR
    // OBTENER ACTIVIDADES POR TIPO Y ESTADO
    Route::get('/by_tipo_estado', 'ActividadeController@by_tipo_estado')->name('by_tipo_estado');
    // OBTENER ACTIVIDADES POR CLIENTE, ESTADO Y TIPO
    Route::get('/by_cliente_tipo_estado', 'ActividadeController@by_cliente_tipo_estado')->name('by_cliente_tipo_estado');
    // OBTENER ACTIVIDADES CREADAS POR EL USUARIO EN SESION
    Route::get('/by_userid_tipo_estado', 'ActividadeController@by_userid_tipo_estado')->name('by_userid_tipo_estado');
    // *** POR REVISAR
});

Route::name('salidas.')->prefix('salidas')->group(function () {
    Route::get('/index', 'SalidaController@index')->name('index');
    Route::post('/store', 'SalidaController@store')->name('store');
    Route::get('/show', 'SalidaController@show')->name('show');
    Route::get('/download/{id}', 'SalidaController@download')->name('download');
});

Route::name('reportes.')->prefix('reportes')->group(function () {
    Route::get('/by_type_estado', 'ReporteController@by_type_estado')->name('by_type_estado');
    Route::get('/by_type_estado_fecha', 'ReporteController@by_type_estado_fecha')->name('by_type_estado_fecha');
    Route::get('/by_type_estado_usuario', 'ReporteController@by_type_estado_usuario')->name('by_type_estado_usuario');
});

Route::name('codes.')->prefix('codes')->group(function () {
    // Lista de codigos
    Route::get('/index', 'CodeController@index')->name('index');
    // Obtener codigos por libro
    Route::get('/by_libro', 'CodeController@by_libro')->name('by_libro');
    // Obtener codigos por editorial
    Route::get('/by_editorial', 'CodeController@by_editorial')->name('by_editorial');
    // Obtener codigo por coincidencia
    Route::get('/by_code', 'CodeController@by_code')->name('by_code');
    // Subir codigos
    Route::post('/upload', 'CodeController@upload')->name('upload');
    // Descargar codigos por remision
    Route::get('/download_byremision/{remisione_id}', 'CodeController@download_byremision')->name('download_byremision');
    // Obtener codigos por cliente
    Route::get('/show_remisiones', 'CodeController@show_remisiones')->name('show_remisiones');
    // Obtener codigos disponibles por libro
    Route::get('/by_libro_count', 'CodeController@by_libro_count')->name('by_libro_count');
    // Obtener inventario de licencias y demos
    Route::get('/licencias_demos', 'CodeController@licencias_demos')->name('licencias_demos');
    // Obtener los libros en scratch
    Route::get('/scratch', 'CodeController@scratch')->name('scratch');
});

// ********** NO UTILIZADO **********
//Llenar tabla de vendidos
Route::put('vendidos_remision', 'RemisionController@registrar_vendidos')->name('vendidos_remision');
// CATEGORIES
Route::name('categories.')->prefix('categories')->group(function () {
    Route::post('/store', 'CategorieController@store')->name('store');
});