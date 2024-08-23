<?php

namespace App\Http\Controllers;

use App\Models\CuentasContable;
use App\Models\DiarioCaja;
use App\Models\EstadosDiario;
use App\Models\FormasPago;
use App\Models\Gastos;
use App\Models\GrupoContable;
use App\Models\Ingresos;
use App\Models\SubCuentaContable;
use App\Models\SubCuentaHijo;
use App\Models\SubGrupoContable;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

use DataTables;
use Carbon\Carbon;
use Validator;

class DiarioCajaController extends Controller
{
    protected $sumatoria = 0;
    protected $saldoArray = [];
    
    /**
     * Mostrar la lista de contactos
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = DiarioCaja::all();
        return view('admin.contabilidad.diarioCaja.index', compact('response'));
    }
    /**
     *  Mostrar el formulario de creación
     *
     * @return \Illuminate\Http\Response
     */
    public function createIngreso()
    {
        $date = Carbon::now();
        $anio = $date->format('Y');

        $ingresos = Ingresos::whereYear('created_at', $anio)->get();
        $response = [];
        $data = [];
        $indice = 0;
        $dataSub = [];
        $grupos = GrupoContable::orderBy('numero', 'asc')->get();
        foreach($grupos as $grupo){
            array_push($dataSub, [
                'grupo' => $grupo,
                'subGrupo' => []
            ]) ;

            $subGrupos = SubGrupoContable::where('grupo_id', $grupo->id)->get();
            $i = 0;
            foreach ($subGrupos as $subGrupo) {
                array_push($dataSub[$indice]['subGrupo'], [
                    'item' => $subGrupo,
                    'cuentas' => []
                ]);

                $cuentas = CuentasContable::where('sub_grupo_id', $subGrupo->id)->get();
                $index = 0;
                foreach ($cuentas as $cuenta) {
                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'], [
                        'item' => $cuenta,
                        'subCuentas' => []
                    ]);

                    $subCuentas = SubCuentaContable::where('cuenta_id', $cuenta->id)->get();

                    if (count($subCuentas) > 0) {
                        $indices = 0;
                        foreach ($subCuentas as $subCuenta ) {

                            array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'],[
                                'item' => $subCuenta,
                                'subCuentasHija' => []
                            ]);

                            $sub_cuenta = SubCuentaHijo::where('sub_cuenta_id', $subCuenta->id)->get();
                            if (count($sub_cuenta) > 0) {
                                foreach ($sub_cuenta as $subCuenta) {
                                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'][$indices]['subCuentasHija'], $subCuenta );
                                }

                            }

                        }
                    }
                    $index++;
                }

                $i++;
            }
            $indice++;

        }
        array_push($response, $dataSub);
        $now = Carbon::now();
        $anio = $now->format('Y');
        $asiento = DiarioCaja::orderBy('id', 'desc')->first();
        $numeroAsiento;
        if ($asiento != null) {
            $asientoTemporal = explode("/", $asiento->asiento_contable);
            $numeroAsientos = $asientoTemporal[0] + 1;
            $numeroConCeros = str_pad($numeroAsientos, 4, "0", STR_PAD_LEFT);
            $numeroAsiento =  $numeroConCeros. '/' . $anio;
        }else{
            $numeroAsiento = '00001' . '/' . $anio;

        }
        $formasPago = FormasPago::all();
        $estados = EstadosDiario::all();
        return view('admin.contabilidad.diarioCaja.create', compact('ingresos','grupos','response','numeroAsiento','estados'));
    }
    public function createGasto()
    {
        $date = Carbon::now();
        $anio = $date->format('Y');

        $gastos = Gastos::whereYear('created_at', $anio)->get();
        $response = [];
        $data = [];
        $indice = 0;
        $dataSub = [];
        $grupos = GrupoContable::orderBy('numero', 'asc')->get();
        foreach($grupos as $grupo){
            array_push($dataSub, [
                'grupo' => $grupo,
                'subGrupo' => []
            ]) ;

            $subGrupos = SubGrupoContable::where('grupo_id', $grupo->id)->get();
            $i = 0;
            foreach ($subGrupos as $subGrupo) {
                array_push($dataSub[$indice]['subGrupo'], [
                    'item' => $subGrupo,
                    'cuentas' => []
                ]);

                $cuentas = CuentasContable::where('sub_grupo_id', $subGrupo->id)->get();
                $index = 0;
                foreach ($cuentas as $cuenta) {
                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'], [
                        'item' => $cuenta,
                        'subCuentas' => []
                    ]);

                    $subCuentas = SubCuentaContable::where('cuenta_id', $cuenta->id)->get();

                    if (count($subCuentas) > 0) {
                        $indices = 0;
                        foreach ($subCuentas as $subCuenta ) {

                            array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'],[
                                'item' => $subCuenta,
                                'subCuentasHija' => []
                            ]);

                            $sub_cuenta = SubCuentaHijo::where('sub_cuenta_id', $subCuenta->id)->get();
                            if (count($sub_cuenta) > 0) {
                                foreach ($sub_cuenta as $subCuenta) {
                                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'][$indices]['subCuentasHija'], $subCuenta );
                                }

                            }

                        }
                    }
                    $index++;
                }

                $i++;
            }
            $indice++;

        }
        array_push($response, $dataSub);
        $now = Carbon::now();
        $anio = $now->format('Y');
        $asiento = DiarioCaja::orderBy('id', 'desc')->first();
        $numeroAsiento;
        if ($asiento != null) {
            $asientoTemporal = explode("/", $asiento->asiento_contable);
            $numeroAsientos = $asientoTemporal[0] + 1;
            $numeroConCeros = str_pad($numeroAsientos, 4, "0", STR_PAD_LEFT);
            $numeroAsiento =  $numeroConCeros. '/' . $anio;
        }else{
            $numeroAsiento = '0001' . '/' . $anio;

        }
        $formasPago = FormasPago::all();
        $estados = EstadosDiario::all();

        return view('admin.contabilidad.diarioCaja.createGasto', compact('gastos','grupos','response','numeroAsiento', 'formasPago', 'estados'));
    }

    /**
     * Mostrar el formulario de edición
     *
     * @param  DiarioCaja  $contact
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $date = Carbon::now();
        $anio = $date->format('Y');

        $invoice = Ingresos::where('created_at', $anio)->get();
        $response = [];
        $data = [];
        $indice = 0;
        $dataSub = [];
        $grupos = GrupoContable::all();
        foreach($grupos as $grupo){

            array_push($dataSub, [
                'grupo' => $grupo,
                'subGrupo' => []
            ]) ;

            $subGrupos = SubGrupoContable::where('grupo_id', $grupo->id)->get();
            $i = 0;
            foreach ($subGrupos as $subGrupo) {

                array_push($dataSub[$indice]['subGrupo'], [
                    'item' => $subGrupo,
                    'cuentas' => []
                ]);

                $cuentas = CuentasContable::where('sub_grupo_id', $subGrupo->id)->get();

                $index = 0;
                foreach ($cuentas as $cuenta) {
                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'], [
                        'item' => $cuenta,
                        'subCuentas' => []
                    ]);

                    $subCuentas = SubCuentaContable::where('cuenta_id', $cuenta->id)->get();

                    if (count($subCuentas) > 0) {
                        $indices = 0;
                        foreach ($subCuentas as $subCuenta ) {

                            array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'],[
                                'item' => $subCuenta,
                                'subCuentasHija' => []
                            ]);

                            $sub_cuenta = SubcuentaHijo::where('sub_cuenta_id', $subCuenta->id)->get();
                            if (count($sub_cuenta) > 0) {
                                foreach ($sub_cuenta as $subCuenta) {
                                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'][$indices]['subCuentasHija'], $subCuenta );
                                }

                            }

                        }
                    }
                    $index++;
                }

                $i++;
            }
            $indice++;

        }
        array_push($response, $dataSub);

        $fila = DiarioCaja::where('id',$id)->first();

        return view('admin.contabilidad.diarioCaja.edit', compact('fila','invoice','grupos','response'));

    }


     /**
     * Guardar un nuevo contacto
     *
    * @param  Request  $request
    *
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {

        $messages = [
            'cuenta_id.required' => 'Debe seleccionar una cuenta contable.',
            'estado_id.required' => 'Debe seleccionar un estado.',
            'date.required' => 'La fecha es obligatoria.',
            'concepto.required' => 'El concepto es obligatorio.',
            'haber.required' => 'El campo importe es obligatorio.',
            'haber.numeric' => 'El importe debe ser un número.',
        ];
    
        $rules = [
            'cuenta_id' => 'required',
            'estado_id' => 'required',
            'date' => 'required|date',
            'concepto' => 'required|string|max:255',
            'haber' => 'required|numeric',
            'estado_id' => 'required'
        ];
        
    
        $validatedData = $request->validate($rules, $messages);

        $crearIngreso = DiarioCaja::create([
            'asiento_contable' => $request['asiento_contable'],
            'cuenta_id' => $validatedData['cuenta_id'],
            'ingreso_id' => $request['ingreso_id'] == null ? null : $request['ingreso_id'],
            'date' => Carbon::createFromDate($validatedData['date']),
            'concepto' => $validatedData['concepto'],
            'haber' => $validatedData['haber'],
            'estado_id' => $request['ingreso_id'] == null ? 1 : $validatedData['estado_id']
        ]);


        Alert::success('Guardado con Exito', 'Ingreso añadido correctamente');
        
        return redirect()->route('admin.diarioCaja.index')->with('status', 'Cliente creado con éxito!');

    }
    public function storeGasto(Request $request)
    {
        $messages = [
            'cuenta_id.required' => 'Debe seleccionar una cuenta contable.',
            'estado_id.required' => 'Debe seleccionar un estado.',
            'date.required' => 'La fecha es obligatoria.',
            'concepto.required' => 'El concepto es obligatorio.',
            'debe.required' => 'El campo importe es obligatorio.',
            'debe.numeric' => 'El importe debe ser un número.',
        ];
    
        $rules = [
            'cuenta_id' => 'required',
            'estado_id' => 'required',
            'date' => 'required|date',
            'concepto' => 'required|string|max:255',
            'debe' => 'required|numeric',
            'estado_id' => 'required'
        ];
        
    
        $validatedData = $request->validate($rules, $messages);

        $crearIngreso = DiarioCaja::create([
            'asiento_contable' => $request['asiento_contable'],
            'cuenta_id' => $validatedData['cuenta_id'],
            'gasto_id' => $request['gasto_id'] == null ? null : $request['gasto_id'],
            'date' => Carbon::createFromDate($validatedData['date']),
            'concepto' => $validatedData['concepto'],
            'debe' => $validatedData['debe'],
            'estado_id' => $request['gasto_id'] == null ? 1 : $validatedData['estado_id']
        ]);


        Alert::success('Guardado con Exito', 'Gasto añadido correctamente');
        
        return redirect()->route('admin.diarioCaja.index')->with('status', 'Cliente creado con éxito!');
    }


    /**
     * Actualizar contacto
     *
     * @param  Request  $request
     * @param  DiarioCaja  $contact
     *
     * @return \Illuminate\Http\Response
     */
    public function updated(Request $request, DiarioCaja $diarioCaja)
    {
        $validator = Validator::make($request->all(), [
            // 'invoice_id' => 'required',
            'asiento_contable' => 'required',
            'cuenta_id' => 'required',
            'date' => 'required',
            'concepto' => 'required',
            // 'debe' => 'required',
            // 'haber' => 'required',
            'formas_pago' => 'required',

        ]);
         $this->validate(request(), [
            'asiento_contable' => 'required',
            'cuenta_id' => 'required',
            'date' => 'required',
            'concepto' => 'required',
            // 'debe' => 'required',
            // 'haber' => 'required',
            'formas_pago' => 'required',

        ]);

        if ($validator->passes()) {

            $grupo = DiarioCaja::where('id', $request->id)->first();
            $grupo->cuenta_id = $request->cuenta_id;
            $grupo->date = $request->date;
            $grupo->concepto = $request->concepto;
            $grupo->debe = $request->debe;
            $grupo->haber = $request->haber;
            $grupo->formas_pago = $request->formas_pago;
            $grupo->save();


            return AjaxForm::custom([
                'message' => 'Asiento Creado.',
                'entryUrl' => route('admin.diarioCaja.edit', $request->id),
             ])->jsonResponse();
        }

         // Si la validacion no a sido pasada se muestra esta alerta.

         return AjaxForm::custom([
            'message' => $validator->errors()->all(),
         ])->jsonResponse();    

    }

    /**
     * Borrar contacto
     *
     * @param  DiarioCaja  $contact
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(DiarioCaja $diarioCaja)
    {

    }

    /**
     * Borrar contacto
     *
     * @param  DiarioCaja  $contact
     *
     * @return \Illuminate\Http\Response
     */
    public function mayorIndex()
    {
        $responses = 'Index Mayor';
        $date = Carbon::now();
        $anio = $date->format('Y');

        $invoice = Ingresos::where('created_at', $anio)->get();

        $response = [];
        $data = [];
        $indice = 0;
        $dataSub = [];

        $grupos = GrupoContable::all();

        foreach($grupos as $grupo){

            array_push($dataSub, [
                'grupo' => $grupo,
                'subGrupo' => []
            ]) ;

            $subGrupos = SubGrupoContable::where('grupo_id', $grupo->id)->get();
            $i = 0;
            foreach ($subGrupos as $subGrupo) {

                array_push($dataSub[$indice]['subGrupo'], [
                    'item' => $subGrupo,
                    'cuentas' => []
                ]);

                $cuentas = CuentasContable::where('sub_grupo_id', $subGrupo->id)->get();

                $index = 0;
                foreach ($cuentas as $cuenta) {
                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'], [
                        'item' => $cuenta,
                        'subCuentas' => []
                    ]);

                    $subCuentas = SubCuentaContable::where('cuenta_id', $cuenta->id)->get();

                    if (count($subCuentas) > 0) {
                        $indices = 0;
                        foreach ($subCuentas as $subCuenta ) {

                            array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'],[
                                'item' => $subCuenta,
                                'subCuentasHija' => []
                            ]);

                            $sub_cuenta = SubcuentaHijo::where('sub_cuenta_id', $subCuenta->id)->get();
                            if (count($sub_cuenta) > 0) {
                                foreach ($sub_cuenta as $subCuenta) {
                                    array_push($dataSub[$indice]['subGrupo'][$i]['cuentas'][$index]['subCuentas'][$indices]['subCuentasHija'], $subCuenta );
                                }

                            }

                        }
                    }
                    $index++;
                }

                $i++;
            }
            $indice++;

        }
        array_push($response, $dataSub);


        return view('admin.contabilidad.mayor.index', compact('response','invoice','grupos','responses'));

    }
    
    /**
     * Borrar contacto
     *
     * @param  DiarioCaja  $contact
     *
     * @return \Illuminate\Http\Response
     */
    public function mayorShow($id)
    {
        $response = 'Index Mayor: '.$id;
        $diarios = DiarioCaja::select('id',
        'invoice_id',
        'asiento_contable',
        'cuenta_id',
        'date',
        'concepto',
        'debe',
        'haber',
        'formas_pago')->where('cuenta_id', $id)->get();

        return Datatables::of($diarios)
                // ->addColumn('saldo', function ($diario) use ($saldo) {
                //     if($diario->debe != null){
                //         $saldo = ['operacion'=> 0, 'valor' => $diario->debe];
                //         array_push($this->saldoArray, $saldo);
                //         $valor = $diario->debe;

                //             $resultado = $this->sumatoria - $valor;

                //             $this->sumatoria = $resultado;
                //     }

                //     if($diario->haber != null){
                //         $saldo = ['operacion'=> 1, 'valor' => $diario->haber];
                //         array_push($this->saldoArray, $saldo);
                //         $valor = $diario->haber;
                //             $resultado = $this->sumatoria + $valor;

                //             $this->sumatoria = $resultado;

                //     }
                //     // if ($this->sumatoria < 10 || $this->sumatoria == 0) {
                //     //     $total = str_pad($this->sumatoria, 2, "0", STR_PAD_LEFT);
                //     //     return number_format($total, 2, '.', '') . ' €';
                //     // }
                //     // return $total = number_format(str_pad($this->sumatoria, 2, "0", STR_PAD_LEFT), 2, '.', ''). ' €';
                //     $total = str_pad($this->sumatoria, 2, "0", STR_PAD_LEFT);
                //     // return $total;
                //     return number_format($total,2,".",STR_PAD_LEFT) . ' €';
                // })
                // ->addColumn('saldo2', function ($diario){
                //     return $this->saldoArray;
                // })
                // ->editColumn('debe', function($diario){
                //         if ($diario->debe != null) {
                //             return number_format($diario->debe, 2, '.', '') . ' €';
                //         }
                //     } 
                // )
                // ->editColumn('haber', function($diario){
                //     if ($diario->haber != null) {
                //         return number_format($diario->haber, 2, '.', '') . ' €';
                //     }
                // } 
                // )  
                ->addColumn('action', function ($diario) {
                    return '<a href="/admin/caja-diaria/'.$diario->id.'/edit" class="btn btn-xs btn-primary"><i class="fas fa-pencil-alt"></i> Editar</a>';
                })
                ->escapeColumns(null)
                ->make();

        // return view('admin.contabilidad.mayor.show', compact('response'));

    }
}
