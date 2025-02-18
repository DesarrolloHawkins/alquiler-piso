<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Holidays;
use Carbon\Carbon;
use App\Mail\MailHoliday;
use App\Models\Alerts\Alert;
use App\Models\HolidaysPetitions;
use App\Models\Logs\LogsEmail;
use Illuminate\Support\Facades\Mail;


class HolidayController extends Controller
{

    public function index(Request $request)
{
    // Obtener los parámetros de filtro, orden y paginación
    $perPage = $request->input('perPage', 10); // Número de elementos por página
    $search = $request->input('buscar', ''); // Texto de búsqueda
    $estado = $request->input('estado', ''); // Filtro por estado
    $sortColumn = $request->input('sortColumn', 'created_at'); // Columna de ordenación
    $sortDirection = $request->input('sortDirection', 'asc'); // Dirección de ordenación

    // Validar columnas de ordenación permitidas para evitar problemas de inyección
    $validSortColumns = ['from', 'half_day', 'total_days', 'holidays_status_id', 'created_at'];
    if (!in_array($sortColumn, $validSortColumns)) {
        $sortColumn = 'created_at';
    }

    // Consultar las peticiones de vacaciones con filtros, orden y paginación
    $holidays = HolidaysPetitions::where('admin_user_id', Auth::user()->id)
        ->when($search, function ($query, $search) {
            $query->where('from', 'like', "%$search%")
                ->orWhere('to', 'like', "%$search%");
        })
        ->when($estado, function ($query, $estado) {
            $query->where('holidays_status_id', $estado);
        })
        ->orderBy($sortColumn, $sortDirection)
        ->paginate($perPage);

    // Obtener el número de peticiones pendientes
    $numberOfHolidayPetitions = HolidaysPetitions::where('admin_user_id', Auth::user()->id)
        ->where('holidays_status_id', 3)
        ->count();

    // Días disponibles de vacaciones del usuario
    $userHolidaysQuantity = Holidays::where('admin_user_id', Auth::user()->id)->first();

    // Devolver la vista con los datos
    return view('holidays.index', compact(
        'holidays',
        'userHolidaysQuantity',
        'numberOfHolidayPetitions',
        'perPage',
        'search',
        'estado',
        'sortColumn',
        'sortDirection'
    ));
}


    // public function index()
    // {
    //     // Peticiones
    //     $holidays = HolidaysPetitions::where('admin_user_id', Auth::user()->id )->orderBy('created_at', 'asc')->get();
    //     // Peticiones pendientes
    //     $numberOfHolidayPetitions = HolidaysPetitions::where('admin_user_id', Auth::user()->id )->where('holidays_status_id', 3)->count();
    //     // Días que tiene de vacaciones
    //     $userHolidaysQuantity = Holidays::where('admin_user_id', Auth::user()->id )->get()->first();

    //     return view('holidays.index', compact('holidays', 'userHolidaysQuantity', 'numberOfHolidayPetitions'));
    // }


    public function create()
    {
        // Peticiones
        $holidays = HolidaysPetitions::where('admin_user_id', Auth::user()->id )->where('holidays_status_id', 3)->orderBy('created_at', 'asc')->get();
        // Peticiones pendientes
        $numberOfHolidayPetitions = HolidaysPetitions::where('admin_user_id', Auth::user()->id )->where('holidays_status_id', 3)->count();
        // Días que tiene de vacaciones
        $userHolidaysQuantity = Holidays::where('admin_user_id', Auth::user()->id )->get()->first();

        return view('holidays.create', compact('holidays', 'userHolidaysQuantity', 'numberOfHolidayPetitions'));
    }


    public function store(Request $request)
    {


        // Validación
        $request->validate([
            'from_date' => 'required',
            'to_date' => 'required|after_or_equal:from_date',
        ]);


        // Formulario datos
        $data = $request->all();
        $data['admin_user_id'] = Auth::user()->id;
        $data['holidays_status_id'] =3; //Pending

        // Dates
        if(isset($data['from_date'])){
            if ($data['from_date'] != null){
                $data['from_date'] = date('Y-m-d', strtotime(str_replace('/', '-',  $data['from_date'])));
            }
        }
        if(isset($data['to_date'])){
            if ($data['to_date'] != null){
                $data['to_date'] = date('Y-m-d', strtotime(str_replace('/', '-',  $data['to_date'])));
            }
        }

        // Booleans
        if (!isset($data['half_day'])) {
            $data['half_day'] = 0;
        }else{
            //Si se marcó la casilla será medio día
            $data['half_day'] = 1;
        }

        // Fecha DESDE enviada desde formulario
        $dataFromToTime = strtotime($data['from_date']);
        $dataFromDateTime = new \DateTime();
        $dataFromDateTime->setTimestamp($dataFromToTime);
        // Fecha HASTA enviada desde formulario
        $dataToToTime = strtotime($data['to_date']);
        $dataToDateTime = new \DateTime();
        $dataToDateTime->setTimestamp($dataToToTime);

        if($data['half_day'] == 1){
            if($dataFromDateTime != $dataToDateTime){
                return redirect()->back()->with('toast', [
                      'icon' => 'error',
                      'mensaje' => 'No se pueden pedir medios días en intervalos'
                    ]
                );
            }
        }

        // Días que tiene de vacaciones
        $userHolidaysQuantity = Holidays::where('admin_user_id', Auth::user()->id )->get()->first();

        // Días que tiene pedidos
        $holidaysPetitions = HolidaysPetitions::where('admin_user_id', Auth::user()->id )->orderBy('created_at', 'asc')->get();

        // Calcular cuantos días está pidiendo
        $petitionQuantityDaysInterval =  $dataFromDateTime->diff($dataToDateTime);
        $petitionQuantityDays = $petitionQuantityDaysInterval->days;
        $petitionQuantityDays += 1;

        // Si la fecha es la misma se ha pedido 1 día.
        $petitionSingleDay = false;
        if ($dataFromDateTime == $dataToDateTime ){
            $petitionSingleDay = true;
        }
        if( $petitionSingleDay == true){
            if( $data['half_day'] == 1){
                $petitionQuantityDays -= 0.5;
            }
        }

        ////////////////////////////////////////
        //   Condiciones que deben cumplirse: //
        ////////////////////////////////////////

        // Usuario esté activo
        if(Auth::user()->inactive){
            return redirect()->back()->with('toast', [
                'icon' => 'error',
                'mensaje' => 'Usuario inactivo'
              ]
          );
        }

        // Si alguno de los días es sábado o domingo no puede pedirse vacaciones
        $signleDayisWeekend = false;
        if($dataFromDateTime->format('N') >= 6){
            $signleDayisWeekend = true;
        }

        // Si es fin de semana y un día solo
        if( $petitionSingleDay && $signleDayisWeekend){
            return redirect()->back()->with('toast', [
                'icon' => 'error',
                'mensaje' => 'La fecha no puede ser fin de semana'
              ]
          );
        }

        // Si es fin de semana y un intervalo
        $start = $dataFromDateTime;
        $end = $dataToDateTime;
        $end->modify('+1 day');
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
        foreach($period as $dt) {
            $curr = $dt->format('D');
            // Si es Sábado o Domingo
            if ($curr == 'Sat' || $curr == 'Sun' ) {
                return redirect()->back()->with('toast', [
                    'icon' => 'error',
                    'mensaje' => 'Las fechas no pueden ser fin de semana'
                  ]
              );
            }
        }
        //****** BUG *****
        // No deja por ejemplo si el usuario tiene el dia 25 y 27 el 26 estando libre


        // Que no tenga vacaciones ya pedidas ese día o periodo
        foreach($holidaysPetitions as $holidaysPetition ){
            // Desde
            $petitionFromToTime = strtotime($holidaysPetition->from);
            $petitionFromDateTime = new \DateTime();
            $petitionFromDateTime->setTimestamp($petitionFromToTime);
            // Hasta
            $petitionToToTime = strtotime($holidaysPetition->to);
            $petitionToDateTime = new \DateTime();
            $petitionToDateTime->setTimestamp($petitionToToTime);
            $dataToDateTime->modify('-1 day');
            // Comprobar si están en medio las fechas de ya disponibles
            $dateFromDateTimeIsBetween = $dataFromDateTime >= $petitionFromDateTime && $dataFromDateTime <= $petitionToDateTime ;
            $dateToDateTimeIsBetween = $dataToDateTime >= $petitionFromDateTime && $dataToDateTime <= $petitionToDateTime;

        }

        // Que la/s fecha/s introducidas sean mayor o igual que la actual
        $today =  strtotime(date("Y-m-d"));
        $todayTime = new \DateTime();
        $todayTime->setTimestamp($today);
        $dataToToTime = strtotime($data['to_date']);
        $dataToDateTime = new \DateTime();
        $dataToDateTime->setTimestamp($dataToToTime);

        if($dataFromDateTime < $todayTime || $dataToDateTime < $todayTime ){
            return redirect()->back()->with('toast', [
                'icon' => 'error',
                'mensaje' => 'Las fechas deben ser posterior a la actual'
              ]
          );
        }

        // Si dispone de los días necesarios
        if( $petitionQuantityDays > $userHolidaysQuantity->quantity   ){
            return redirect()->back()->with('toast', [
                'icon' => 'error',
                'mensaje' => 'No dispone de días de vacaciones suficientes'
              ]
          );
        }

        //formatear datos
        if(isset($data['from_date'])){
            if ($data['from_date'] != null){
                $data['from_date'] = date('Y-m-d', strtotime(str_replace('/', '-',  $data['from_date'])));
            }
        }
        if(isset($data['to_date'])){
            if ($data['to_date'] != null){
                $data['to_date'] = date('Y-m-d', strtotime(str_replace('/', '-',  $data['to_date'])));
            }
        }
        $from = $data['from_date'];
        $to = $data['to_date'];
        $data['from'] =$data['from_date'];
        $data['to'] =$data['to_date'];
        $data['total_days'] = $petitionQuantityDays;
        $petitionQuantityDaysNegative = -1 * abs($petitionQuantityDays);

        // Guardar
        $holidayPetition = HolidaysPetitions::create($data);
        $holidayPetitionSaved = $holidayPetition->save();

        // Resto las vacaciones
        if($holidayPetitionSaved){
            $updatedHolidaysQuantity =  $userHolidaysQuantity->quantity - $petitionQuantityDays;
            // Actualizo los días de vacaciones del usuario en la base de datos
            if($updatedHolidaysQuantity >= 0){
                $updateHolidaysDone = Holidays::where('admin_user_id', Auth::user()->id )->update(array('quantity' => $updatedHolidaysQuantity ));
            }
            // Añado un registro en holidays_additions guardando esta operación
            if($updateHolidaysDone){
                DB::table('holidays_additions')->insert([
                    [
                        'admin_user_id' => Auth::user()->id,
                        'quantity_before' => $userHolidaysQuantity->quantity,
                        'quantity_to_add' => $petitionQuantityDaysNegative,
                        'quantity_now' => $updatedHolidaysQuantity,
                        'manual' => 0,
                        'holiday_petition' => 1,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ],
                ]);

                $data = [
                    "admin_user_id" => 8,
                    "stage_id" => 16,
                    "activation_datetime" => Carbon::now()->format('Y-m-d H:i:s'),
                    "status_id" => 1,
                    "reference_id" => $holidayPetition->id,
                    "description" => "Tienes una nueva petición de vacaciones",
                ];

            // $alerta = Alert::create($data);
            // $alerta->save();

            }
        }

        $empleado = Auth::user();

        // Respuesta
        return redirect()->route('holiday.index',)->with('toast', [
            'icon' => 'success',
            'mensaje' => 'La petición de vacaciones se realizó correctamente'
          ]
      );

    }



    public function edit(HolidaysPetitions $holidayPetition)
    {

        return view('admin.user_holidays.edit', compact('holidayPetition'));
    }


    public function destroy(HolidaysPetitions $holidayPetition)
    {
        try {
            //Borrar registro

            // Si la petición de las vacaciones es diferente de pendiente no debe borrar
            if($holidayPetition->holidays_status_id != 3){
                 //Response
                 return redirect()->back()->with('toast', [
                    'icon' => 'error',
                    'mensaje' => 'No puede editar registros que no sean pendientes'
                  ]
              );

            }

            $deleted = $holidayPetition->delete();

            if($deleted){
                // Obtengo datos necesarios para actualizar registros
                $daysToAddToHolidaysDays =  $holidayPetition->total_days;
                // Actualizo los días de vacaciones del usuario en la base de datos
                if($daysToAddToHolidaysDays >= 0){
                    $userHolidaysDaysRecord = Holidays::where('admin_user_id', Auth::user()->id )->get()->first();
                    $quantityBeforeDelete = $userHolidaysDaysRecord->quantity;
                    $daysToAddToHolidaysDays += $userHolidaysDaysRecord->quantity;
                    $updateHolidaysDone = Holidays::where('id', $userHolidaysDaysRecord->id )->update(array('quantity' => $daysToAddToHolidaysDays ));
                }

                // Vuelvo a sumar las vacaciones que se le restaron al hacer la petición de las vacaciones
                if($updateHolidaysDone){
                    // Añado un registro en holidays_additions guardando esta operación
                    if($updateHolidaysDone){
                        DB::table('holidays_additions')->insert([
                            [
                                'admin_user_id' => Auth::user()->id,
                                'quantity_before' => $quantityBeforeDelete,
                                'quantity_to_add' => $holidayPetition->total_days,
                                'quantity_now' => $daysToAddToHolidaysDays,
                                'manual' => 0,
                                'holiday_petition' => 1,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            ],
                        ]);
                    }
                }

                //Response
                return redirect()->route('user_holidays.index')->with('toast', [
                    'icon' => 'success',
                    'mensaje' => 'El registro se borró correctamente'
                  ]
              );

            }else{
                return redirect()->back()->with('toast', [
                    'icon' => 'error',
                    'mensaje' => 'El registro no pudo ser eliminado.Pruebe más tarde.'
                  ]
              );
            }

        } catch (\Exception $e) {
             // Respuesta
             return redirect()->back()->with('toast', [
                'icon' => 'error',
                'mensaje' => 'El registro no pudo ser eliminado.Pruebe más tarde.'
              ]
          );
        }
    }

       // Función para enviar email, se solicita los datos a enviar y el usuario, luego serán mostrados en mailHoliday.blade.php
    public function sendEmail($empleado){

        $mail = "ivan@lchawkins.com";
        // $mailsCC[] = "";
        $mailsCC[] = "elena@lchawkins.com";

        // Si el estado es 1, es solicitud de vacaciones, el 2 es aceptada, el 3 es rechazada
        // $estado = 1;
        // $email = new MailHoliday($estado, $empleado);

        // Mail::to($mail)->cc($mailsCC)->send($email);
        //  //Mail::to($mail)->send($email);

        return 200;

    }

    // Función que guarda los Logs de los Emails enviados
    public function getLogEmails($logData){

        $mailEmisor = $logData['mailEmisor'];
        $mailReceptor = $logData['mailReceptor'];
        $status = $logData['status'];
        $mensaje = $logData['mensaje'];

        $data = [
            "mail_emisor" => $mailEmisor,
            "mail_receptor" => $mailReceptor,
            "status" => $status,
            "mensaje" => $mensaje,
        ];

        // $createLog = LogsEmail::create($data);
        // $logSaved = $createLog->save();

        return 200;


    }


}
