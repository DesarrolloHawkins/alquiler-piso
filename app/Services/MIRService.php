<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Reserva;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class MIRService
{
    /**
     * Obtener la configuración de MIR desde settings
     */
    private function getConfig()
    {
        return [
            'codigo_arrendador' => Setting::get('mir_codigo_arrendador', Setting::get('mir_arrendador', '0000004735')), // Mantener compatibilidad
            'codigo_establecimiento' => Setting::get('mir_codigo_establecimiento', '0000003984'), // Valor por defecto para sandbox
            'usuario' => Setting::get('mir_usuario', 'B56927809WS'), // Valor por defecto para sandbox
            'password' => Setting::get('mir_password', 'Temporal1'), // Valor por defecto para sandbox
            'entorno' => Setting::get('mir_entorno', 'sandbox'), // sandbox o production
            'aplicacion' => Setting::get('mir_aplicacion', 'Hawkins Suite'),
        ];
    }

    /**
     * Obtener la URL del endpoint según el entorno
     */
    private function getEndpointUrl($entorno)
    {
        if ($entorno === 'production') {
            return 'https://hospedajes.ses.mir.es/hospedajes-web/ws/v1/comunicacion';
        }
        return 'https://hospedajes.pre-ses.mir.es/hospedajes-web/ws/v1/comunicacion';
    }

    /**
     * Generar el XML para un parte de viajeros (PV)
     * Estructura correcta según documentación MIR:
     * <solicitud> -> <codigoEstablecimiento> + <comunicacion> -> <contrato> + <persona> (para cada viajero)
     */
    private function generarXMLReserva(Reserva $reserva, $codigoEstablecimiento)
    {
        $cliente = $reserva->cliente;
        $apartamento = $reserva->apartamento;
        
        // Formatear fechas con horas realistas (entrada 14:00, salida 12:00)
        $fechaEntrada = \Carbon\Carbon::parse($reserva->fecha_entrada)->setTime(14, 0, 0);
        $fechaSalida = \Carbon\Carbon::parse($reserva->fecha_salida)->setTime(12, 0, 0);
        $fechaEntradaStr = $fechaEntrada->format('Y-m-d\TH:i:s');
        $fechaSalidaStr = $fechaSalida->format('Y-m-d\TH:i:s');
        
        // Normalizar nacionalidad a código ISO de 3 letras (ESP, FRA, etc.)
        $normalizarNacionalidad = function($nacionalidad) {
            if (empty($nacionalidad)) {
                return 'ESP';
            }
            // Si ya es un código ISO de 3 letras, devolverlo
            if (preg_match('/^[A-Z]{3}$/i', $nacionalidad)) {
                return strtoupper($nacionalidad);
            }
            // Si es código de 2 letras, convertir a 3
            $mapa2a3 = [
                'ES' => 'ESP', 'FR' => 'FRA', 'GB' => 'GBR', 'DE' => 'DEU', 'IT' => 'ITA',
                'PT' => 'PRT', 'NL' => 'NLD', 'BE' => 'BEL', 'CH' => 'CHE', 'AT' => 'AUT',
            ];
            $nacionalidadUpper = strtoupper($nacionalidad);
            if (isset($mapa2a3[$nacionalidadUpper])) {
                return $mapa2a3[$nacionalidadUpper];
            }
            // Si es "España", convertir a "ESP"
            if (stripos($nacionalidad, 'españa') !== false || stripos($nacionalidad, 'spain') !== false) {
                return 'ESP';
            }
            // Por defecto, devolver ESP
            return 'ESP';
        };
        
        // Normalizar código de provincia (ej: "Cádiz" -> "CA")
        $normalizarProvincia = function($provincia) {
            if (empty($provincia)) {
                return '';
            }
            // Mapa de provincias españolas a códigos
            $mapaProvincias = [
                'Cádiz' => 'CA', 'Cordoba' => 'CO', 'Córdoba' => 'CO', 'Sevilla' => 'SE',
                'Málaga' => 'MA', 'Granada' => 'GR', 'Almería' => 'AL', 'Jaén' => 'JA',
                'Huelva' => 'H', 'Madrid' => 'M', 'Barcelona' => 'B', 'Valencia' => 'V',
            ];
            $provinciaUpper = ucfirst($provincia);
            return $mapaProvincias[$provinciaUpper] ?? strtoupper(substr($provincia, 0, 2));
        };
        
        // Obtener sexo del cliente (H/M)
        $obtenerSexo = function($sexo, $sexoStr = null) {
            // Priorizar sexo_str si está disponible
            if (!empty($sexoStr)) {
                $sexoStrUpper = strtoupper($sexoStr);
                if ($sexoStrUpper === 'M' || $sexoStrUpper === 'F') {
                    return $sexoStrUpper === 'F' ? 'M' : 'H'; // F = Mujer = M, M = Hombre = H
                }
            }
            
            if (empty($sexo)) {
                return 'H'; // Por defecto
            }
            
            $sexoUpper = strtoupper($sexo);
            
            // Mapeo de valores comunes
            if (in_array($sexoUpper, ['H', 'M', 'HOMBRE', 'MUJER', 'MALE', 'FEMALE', 'FEMENINO', 'MASCULINO'])) {
                if (in_array($sexoUpper, ['HOMBRE', 'MALE', 'MASCULINO', 'H'])) return 'H';
                if (in_array($sexoUpper, ['MUJER', 'FEMALE', 'FEMENINO', 'F', 'M'])) return 'M';
            }
            
            // Si empieza con F, es Femenino = M
            if (substr($sexoUpper, 0, 1) === 'F') {
                return 'M';
            }
            
            return 'H'; // Por defecto
        };
        
        // Construir XML con la estructura correcta según documentación oficial MIR
        // Formato correcto: <loteReservas> -> <reserva> -> <contrato> + <viajero>
        // IMPORTANTE: 
        // - Raíz: <loteReservas xmlns="http://www.mir.es/hospedajes/esquema">
        // - NO usar <comunicacion> ni <persona>, usar <reserva> y <viajero>
        // - El archivo dentro del ZIP debe llamarse loteReservas.xml
        // - Usar indentación de 2 espacios como en el ejemplo oficial
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<loteReservas xmlns="http://www.mir.es/hospedajes/esquema">' . "\n";
        $xml .= '  <codigoEstablecimiento>' . htmlspecialchars($codigoEstablecimiento) . '</codigoEstablecimiento>' . "\n";
        $xml .= '  <reserva>' . "\n";
        
        // Sección contrato (dentro de <reserva>)
        $xml .= '    <contrato>' . "\n";
        $xml .= '      <referencia>' . htmlspecialchars($reserva->codigo_reserva) . '</referencia>' . "\n";
        $xml .= '      <fechaContrato>' . $fechaEntrada->format('Y-m-d') . '</fechaContrato>' . "\n";
        $xml .= '      <fechaEntrada>' . $fechaEntradaStr . '</fechaEntrada>' . "\n";
        $xml .= '      <fechaSalida>' . $fechaSalidaStr . '</fechaSalida>' . "\n";
        $xml .= '      <numPersonas>' . $reserva->numero_personas . '</numPersonas>' . "\n";
        $xml .= '      <pago>' . "\n";
        $xml .= '        <importe>' . number_format($reserva->precio ?? 0, 2, '.', '') . '</importe>' . "\n";
        $xml .= '        <moneda>EUR</moneda>' . "\n";
        $xml .= '        <metodo>' . htmlspecialchars($reserva->tipo_pago ?? 'Efectivo') . '</metodo>' . "\n";
        $xml .= '      </pago>' . "\n";
        $xml .= '    </contrato>' . "\n";
        
        // Cliente principal (persona 1)
        $dniCliente = $cliente->num_identificacion ?? null;
        if (empty($dniCliente)) {
            throw new \Exception('El cliente principal no tiene DNI configurado (num_identificacion). Es obligatorio para el envío a MIR.');
        }
        
        // Cliente principal como <viajero> (NO <persona>)
        $xml .= '    <viajero>' . "\n";
        $xml .= '      <rol>VI</rol>' . "\n"; // VI = Viajero
        $xml .= '      <nombre>' . htmlspecialchars($cliente->nombre ?? '') . '</nombre>' . "\n";
        $xml .= '      <apellido1>' . htmlspecialchars($cliente->apellido1 ?? '') . '</apellido1>' . "\n";
        if (!empty($cliente->apellido2)) {
            $xml .= '      <apellido2>' . htmlspecialchars($cliente->apellido2) . '</apellido2>' . "\n";
        }
        $xml .= '      <tipoDocumento>' . $this->getTipoDocumentoMIR($dniCliente) . '</tipoDocumento>' . "\n";
        $xml .= '      <numeroDocumento>' . htmlspecialchars($dniCliente) . '</numeroDocumento>' . "\n";
        if ($cliente->fecha_nacimiento) {
            $xml .= '      <fechaNacimiento>' . \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('Y-m-d') . '</fechaNacimiento>' . "\n";
        }
        $xml .= '      <nacionalidad>' . $normalizarNacionalidad($cliente->nacionalidad ?? 'ES') . '</nacionalidad>' . "\n";
        $xml .= '      <sexo>' . $obtenerSexo($cliente->sexo ?? null, $cliente->sexo_str ?? null) . '</sexo>' . "\n";
        
        // NOTA: Según el ejemplo de MIR, <viajero> NO incluye dirección, teléfono ni correo
        // Solo incluye: rol, nombre, apellido1, apellido2 (opcional), tipoDocumento, numeroDocumento, fechaNacimiento, nacionalidad, sexo
        
        $xml .= '    </viajero>' . "\n";
        
        // Huéspedes adicionales (personas 2, 3, ...)
        $huespedes = \App\Models\Huesped::where('reserva_id', $reserva->id)->get();
        foreach ($huespedes as $huesped) {
            $apellido1 = $huesped->primer_apellido ?? $huesped->apellido1 ?? '';
            $apellido2 = $huesped->segundo_apellido ?? $huesped->apellido2 ?? '';
            
            // Validar que el huésped tenga al menos nombre y apellido1
            if (empty($huesped->nombre) || empty($apellido1)) {
                Log::warning('Huésped con datos incompletos omitido del XML MIR', [
                    'huesped_id' => $huesped->id,
                    'nombre' => $huesped->nombre,
                    'apellido1' => $apellido1,
                ]);
                continue;
            }
            
            $dniHuesped = $huesped->numero_identificacion ?? null;
            if (empty($dniHuesped)) {
                throw new \Exception("El huésped {$huesped->nombre} {$apellido1} no tiene DNI configurado (numero_identificacion). Es obligatorio para el envío a MIR.");
            }
            
            // Huéspedes adicionales como <viajero> (NO <persona>)
            $xml .= '    <viajero>' . "\n";
            $xml .= '      <rol>VI</rol>' . "\n";
            $xml .= '      <nombre>' . htmlspecialchars($huesped->nombre) . '</nombre>' . "\n";
            $xml .= '      <apellido1>' . htmlspecialchars($apellido1) . '</apellido1>' . "\n";
            if (!empty($apellido2)) {
                $xml .= '      <apellido2>' . htmlspecialchars($apellido2) . '</apellido2>' . "\n";
            }
            $xml .= '      <tipoDocumento>' . $this->getTipoDocumentoMIR($dniHuesped) . '</tipoDocumento>' . "\n";
            $xml .= '      <numeroDocumento>' . htmlspecialchars($dniHuesped) . '</numeroDocumento>' . "\n";
            if ($huesped->fecha_nacimiento) {
                $xml .= '      <fechaNacimiento>' . \Carbon\Carbon::parse($huesped->fecha_nacimiento)->format('Y-m-d') . '</fechaNacimiento>' . "\n";
            }
            $xml .= '      <nacionalidad>' . $normalizarNacionalidad($huesped->nacionalidad ?? 'ES') . '</nacionalidad>' . "\n";
            $xml .= '      <sexo>' . $obtenerSexo($huesped->sexo ?? null, $huesped->sexo_str ?? null) . '</sexo>' . "\n";
            
            // NOTA: Según el ejemplo de MIR, <viajero> NO incluye dirección, teléfono ni correo
            // Solo incluye: rol, nombre, apellido1, apellido2 (opcional), tipoDocumento, numeroDocumento, fechaNacimiento, nacionalidad, sexo
            
            $xml .= '    </viajero>' . "\n";
        }
        
        $xml .= '    </reserva>' . "\n";
        $xml .= '</loteReservas>';
        
        return $xml;
    }
    
    /**
     * Determinar el tipo de documento según el formato del DNI para MIR
     * MIR usa: DNI, NIE, PAS (no PASAPORTE)
     */
    private function getTipoDocumentoMIR($dni)
    {
        if (empty($dni)) {
            return 'DNI';
        }
        
        // Si empieza con letra y tiene 8 dígitos, es NIE
        if (preg_match('/^[XYZ][0-9]{7}[A-Z]$/i', $dni)) {
            return 'NIE';
        }
        
        // Si tiene 9 caracteres (8 dígitos + letra), es DNI español
        if (preg_match('/^[0-9]{8}[A-Z]$/i', $dni)) {
            return 'DNI';
        }
        
        // Por defecto, asumimos pasaporte (PAS en MIR)
        return 'PAS';
    }

    /**
     * Determinar el tipo de documento según el formato del DNI
     */
    private function getTipoDocumento($dni)
    {
        if (empty($dni)) {
            return 'DNI';
        }
        
        // Si empieza con letra y tiene 8 dígitos, es NIE
        if (preg_match('/^[XYZ][0-9]{7}[A-Z]$/i', $dni)) {
            return 'NIE';
        }
        
        // Si tiene 9 caracteres (8 dígitos + letra), es DNI español
        if (preg_match('/^[0-9]{8}[A-Z]$/i', $dni)) {
            return 'DNI';
        }
        
        // Por defecto, asumimos pasaporte
        return 'PASAPORTE';
    }

    /**
     * Comprimir XML en ZIP y codificar en Base64
     */
    private function comprimirYCodificar($xml, $nombreArchivo = 'reserva.xml')
    {
        $tempZip = tempnam(sys_get_temp_dir(), 'mir_');
        $zip = new ZipArchive();
        
        if ($zip->open($tempZip, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('No se pudo crear el archivo ZIP');
        }
        
        $zip->addFromString($nombreArchivo, $xml);
        $zip->close();
        
        $zipContent = file_get_contents($tempZip);
        $base64 = base64_encode($zipContent);
        
        unlink($tempZip);
        
        return $base64;
    }

    /**
     * Enviar reserva a MIR
     */
    public function enviarReserva(Reserva $reserva)
    {
        try {
            $config = $this->getConfig();
            
            // Validar configuración
            if (empty($config['codigo_arrendador']) || empty($config['usuario']) || empty($config['password'])) {
                throw new \Exception('La configuración de MIR no está completa. Por favor, configure los datos en Configuración > MIR.');
            }
            
            // Cargar las relaciones necesarias de forma explícita
            $reserva->load('apartamento');
            $apartamento = $reserva->apartamento;
            
            if (!$apartamento) {
                throw new \Exception('La reserva no tiene un apartamento asociado.');
            }
            
            // Cargar explícitamente la relación edificio del apartamento
            $apartamento->load('edificio');
            
            // Obtener código de establecimiento
            // NOTA: En sandbox, puede ser necesario usar el código de las credenciales en lugar del de la reserva
            // si el código de la reserva no está asociado al arrendador en el sistema MIR
            $codigoEstablecimiento = null;
            
            // En sandbox, priorizar el código de la configuración (de las credenciales)
            // porque puede que el código de la reserva no esté asociado al arrendador
            if ($config['entorno'] === 'sandbox' && !empty($config['codigo_establecimiento'])) {
                $codigoEstablecimiento = $config['codigo_establecimiento'];
                Log::info('Usando código de establecimiento de configuración (sandbox)', [
                    'codigo_establecimiento' => $codigoEstablecimiento,
                    'razon' => 'En sandbox, usar código de credenciales para evitar errores de asociación',
                ]);
            } else {
                // En producción, usar el código de la reserva
                // Primero intentar desde el apartamento directamente
                if (!empty($apartamento->codigo_establecimiento)) {
                    $codigoEstablecimiento = $apartamento->codigo_establecimiento;
                }
                // Si no, intentar desde el edificio (cargar explícitamente si no está cargado)
                elseif ($apartamento->edificio_id) {
                    // Si la relación no está cargada o está vacía, cargarla explícitamente
                    if (!$apartamento->relationLoaded('edificio') || !$apartamento->edificio) {
                        $edificio = \App\Models\Edificio::find($apartamento->edificio_id);
                    } else {
                        $edificio = $apartamento->edificio;
                    }
                    
                    if ($edificio && !empty($edificio->codigo_establecimiento)) {
                        $codigoEstablecimiento = $edificio->codigo_establecimiento;
                    }
                    
                    // Si aún no tenemos el código, intentar consultar directamente desde DB
                    if (empty($codigoEstablecimiento) && $apartamento->edificio_id) {
                        $codigoEstablecimiento = \DB::table('edificios')
                            ->where('id', $apartamento->edificio_id)
                            ->value('codigo_establecimiento');
                    }
                }
                
                // Si no se encontró en la reserva, usar el de la configuración
                if (empty($codigoEstablecimiento)) {
                    $codigoEstablecimiento = $config['codigo_establecimiento'];
                    Log::info('Usando código de establecimiento de configuración (no encontrado en reserva)', [
                        'codigo_establecimiento' => $codigoEstablecimiento,
                    ]);
                }
            }
            
            if (empty($codigoEstablecimiento)) {
                throw new \Exception('No se pudo obtener el código de establecimiento. Verifica que el apartamento, su edificio o la configuración MIR tengan el código configurado.');
            }
            
            Log::info('Código de establecimiento obtenido para MIR', [
                'reserva_id' => $reserva->id,
                'codigo_establecimiento' => $codigoEstablecimiento,
                'apartamento_id' => $apartamento->id,
                'edificio_id' => $edificio->id ?? null,
            ]);
            
            // Generar XML (pasar codigoEstablecimiento para incluirlo en el XML interno)
            $xml = $this->generarXMLReserva($reserva, $codigoEstablecimiento);
            
            // Validar que el XML esté bien formado
            libxml_use_internal_errors(true);
            $xmlDoc = simplexml_load_string($xml);
            if ($xmlDoc === false) {
                $errors = libxml_get_errors();
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = trim($error->message);
                }
                libxml_clear_errors();
                throw new \Exception('El XML generado no está bien formado: ' . implode('; ', $errorMessages));
            }
            
            // Comprimir y codificar
            // IMPORTANTE: El nombre del archivo dentro del ZIP debe ser EXACTAMENTE "loteReservas.xml"
            // según la especificación MIR para partes de viajeros (PV)
            $solicitudBase64 = $this->comprimirYCodificar($xml, 'loteReservas.xml');
            
            // Construir XML con formato SOAP según especificación MIR
            // Formato correcto según respuesta del soporte MIR:
            // - SOAP Envelope con namespaces
            // - <codigoArrendador> (no <arrendador>)
            // - Todo envuelto en <peticion>
            $requestXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $requestXml .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:com="http://www.soap.servicios.hospedajes.mir.es/comunicacion">' . "\n";
            $requestXml .= '   <soapenv:Header/>' . "\n";
            $requestXml .= '   <soapenv:Body>' . "\n";
            $requestXml .= '      <com:comunicacionRequest>' . "\n";
            $requestXml .= '         <peticion>' . "\n";
            $requestXml .= '            <cabecera>' . "\n";
            $requestXml .= '               <codigoArrendador>' . htmlspecialchars($config['codigo_arrendador']) . '</codigoArrendador>' . "\n";
            $requestXml .= '               <aplicacion>' . htmlspecialchars($config['aplicacion']) . '</aplicacion>' . "\n";
            $requestXml .= '               <tipoOperacion>A</tipoOperacion>' . "\n";
            $requestXml .= '               <tipoComunicacion>PV</tipoComunicacion>' . "\n";
            $requestXml .= '            </cabecera>' . "\n";
            $requestXml .= '            <solicitud>' . $solicitudBase64 . '</solicitud>' . "\n";
            $requestXml .= '         </peticion>' . "\n";
            $requestXml .= '      </com:comunicacionRequest>' . "\n";
            $requestXml .= '   </soapenv:Body>' . "\n";
            $requestXml .= '</soapenv:Envelope>';
            
            // Log del XML generado para debugging (solo en sandbox)
            if ($config['entorno'] === 'sandbox') {
                Log::info('XML generado para MIR (sandbox)', [
                    'xml_interno' => $xml,
                    'xml_interno_length' => strlen($xml),
                    'request_xml_preview' => substr($requestXml, 0, 1000), // Primeros 1000 caracteres
                    'request_xml_length' => strlen($requestXml),
                    'codigo_establecimiento' => $codigoEstablecimiento,
                    'codigo_arrendador' => $config['codigo_arrendador'],
                    'base64_length' => strlen($solicitudBase64),
                ]);
                
                // Guardar XML completo en archivo temporal para debugging (sobrescribir si existe)
                $tempFile = storage_path('logs/mir_request_' . $reserva->id . '.xml');
                file_put_contents($tempFile, $requestXml);
                Log::info('XML completo guardado en', ['file' => $tempFile]);
            }
            
            // Preparar autenticación
            $credentials = base64_encode($config['usuario'] . ':' . $config['password']);
            
            // Realizar petición
            $endpoint = $this->getEndpointUrl($config['entorno']);
            
            Log::info('Enviando reserva a MIR', [
                'reserva_id' => $reserva->id,
                'codigo_reserva' => $reserva->codigo_reserva,
                'endpoint' => $endpoint,
                'entorno' => $config['entorno'],
            ]);
            
            // Configurar la petición HTTP usando cURL directamente
            // Headers para SOAP según especificación MIR
            $headers = [
                'Authorization: Basic ' . $credentials,
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: ""',
                'Accept: text/xml',
                'User-Agent: PHP-cURL/8.2'
            ];
            
            Log::info('Headers HTTP configurados', [
                'headers_count' => count($headers),
                'has_auth' => !empty($credentials),
                'endpoint' => $endpoint,
            ]);
            
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $requestXml,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_VERBOSE => $config['entorno'] === 'sandbox', // Log detallado en sandbox
                CURLOPT_POSTFIELDS => $requestXml, // Asegurar que se envíe el XML
            ]);
            
            // En sandbox, deshabilitar verificación SSL (solo para desarrollo)
            if ($config['entorno'] === 'sandbox') {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
            
            // Ejecutar petición
            try {
                $responseBody = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($curlError) {
                    throw new \Exception('Error cURL: ' . $curlError);
                }
                
            } catch (\Exception $e) {
                if (isset($ch) && is_resource($ch)) {
                    curl_close($ch);
                }
                Log::error('Error al enviar a MIR', [
                    'reserva_id' => $reserva->id,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
            
            $statusCode = $httpCode;
            
            // Log detallado de la respuesta
            Log::info('Respuesta de MIR', [
                'reserva_id' => $reserva->id,
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'response_length' => strlen($responseBody),
            ]);
            
            // Si hay errores en la respuesta, loguearlos con más detalle
            if ($statusCode >= 400) {
                // Intentar parsear la respuesta JSON si es posible
                $responseData = null;
                if (!empty($responseBody)) {
                    $jsonResponse = json_decode($responseBody, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $responseData = $jsonResponse;
                    }
                }
                
                Log::error('Error en respuesta MIR', [
                    'reserva_id' => $reserva->id,
                    'status_code' => $statusCode,
                    'response' => $responseBody,
                    'response_parsed' => $responseData,
                    'request_xml_preview' => substr($requestXml, 0, 500), // Primeros 500 caracteres
                    'codigo_establecimiento_usado' => $codigoEstablecimiento,
                    'codigo_arrendador' => $config['codigo_arrendador'],
                ]);
            }
            
            // Procesar respuesta
            if ($statusCode === 200) {
                // Intentar parsear la respuesta XML
                $xmlResponse = simplexml_load_string($responseBody);
                
                if ($xmlResponse !== false) {
                    $codigoReferencia = (string) ($xmlResponse->codigoReferencia ?? '');
                    $estado = (string) ($xmlResponse->estado ?? 'enviado');
                    $mensaje = (string) ($xmlResponse->mensaje ?? '');
                    
                    return [
                        'success' => true,
                        'estado' => $estado,
                        'codigo_referencia' => $codigoReferencia,
                        'mensaje' => $mensaje,
                        'respuesta_completa' => $responseBody,
                    ];
                } else {
                    // Si no se puede parsear, asumir éxito si el código es 200
                    return [
                        'success' => true,
                        'estado' => 'enviado',
                        'codigo_referencia' => null,
                        'mensaje' => 'Reserva enviada correctamente',
                        'respuesta_completa' => $responseBody,
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'estado' => 'error',
                    'codigo_referencia' => null,
                    'mensaje' => 'Error al enviar: ' . $statusCode,
                    'respuesta_completa' => $responseBody,
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Error al enviar reserva a MIR', [
                'reserva_id' => $reserva->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'estado' => 'error',
                'codigo_referencia' => null,
                'mensaje' => $e->getMessage(),
                'respuesta_completa' => null,
            ];
        }
    }
}

