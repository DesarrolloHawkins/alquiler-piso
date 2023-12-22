<?php

namespace App\Http\Controllers;

use App\Models\MensajeAuto;
use App\Models\Reserva;
use App\Services\ClienteService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
// use leifermendez\police\PoliceHotelFacade;
// use Goutte\Client;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;


class HomeController extends Controller
{
    private $endpoint, $cookie, $user, $pass, $_csrf, $headers, $fpdi;

    protected $pkgoptions = array(
        'countries' => array(),
        'user' => array(),
        'pdf' => array(),
    );


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->headers = []; // Initialize as an empty array
        // try {

        //     $this->user = 'H11070GEV04';
        //     $this->pass =  'H4Kins4p4rtamento2024';
        //     $this->endpoint = 'https://webpol.policia.es/e-hotel';
        //     $this->headers = [
        //         'User-Agent: PostmanRuntime/7.16.3',
        //     ];

        //     // if (!$user or !$pass) {
        //     //     throw new \Exception('error.login.users');
        //     // }

        //     return $this;

        // } catch (\Exception $e) {
        //     return $e->getMessage();
        // }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function test(ClienteService $clienteService){
        // $credentials = array(
        //     'user' => 'H11070GEV04',
        //     'pass' => 'H4Kins4p4rtamento2023'
        // ); 
        // $data = [
        //     'username' => 'H11070GEV04',
        //     'password' => 'H4Kins4p4rtamento2023',
        //     '_csrf' => '49614a9a-efc7-4c36-9063-b1cd6824aa9a'
        // ];
        //https://webpol.policia.es/e-hotel/execute_login
        //https://webpol.policia.es/e-hotel/login
        //https://webpol.policia.es/hospederia/manual/vista/grabadorManual
        //https://webpol.policia.es/hospederia/manual/insertar/huesped

        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', 'https://webpol.policia.es/e-hotel/login');
        $csrfToken = $crawler->filter('meta[name="_csrf"]')->attr('content');

        $response1 = $browser->getResponse();
        $statusCode1 = $response1->getStatusCode();
        if ($statusCode1 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            echo '1 - Código de estado HTTP: ' . $statusCode1;
            return;
        }

        $cookiesArray = [];
        foreach ($browser->getCookieJar()->all() as $cookie) {
            $cookiesArray[$cookie->getName()] = $cookie->getValue();
        }
        
        $postData = [
            'username' => 'H11070GEV04',
            'password' => 'H4Kins4p4rtamento2023',
            '_csrf'    => $csrfToken
        ];

        $headers = [
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_COOKIE' => 'FRONTAL_JSESSIONID: ' . $cookiesArray['FRONTAL_JSESSIONID'] . ' UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_: ' . $cookiesArray['UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_'] . ' cookiesession1: ' . $cookiesArray['cookiesession1']
        ];

        $browser->setServerParameters($headers);
        $crawler = $browser->request(
            'POST',
            'https://webpol.policia.es/e-hotel/execute_login',
            $postData
        );

        $response2 = $browser->getResponse();
        $statusCode2 = $response2->getStatusCode();
        if ($statusCode2 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            echo '2 - Código de estado HTTP: ' . $statusCode2;
            return;
        }

        $crawler = $browser->request('GET', 'https://webpol.policia.es/e-hotel/hospederia/manual/vista/grabadorManual');
        $idHospederia = $crawler->filter('#idHospederia')->attr('value');

        $response3 = $browser->getResponse();
        $statusCode3 = $response3->getStatusCode();
        if ($statusCode3 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            echo '3 - Código de estado HTTP: ' . $statusCode3;
            return;
        }
        mb_internal_encoding("UTF-8");

        $apellido = mb_convert_encoding('CASTAÑOS', 'UTF-8');

        $data = [
            'jsonHiddenComunes'=> null, 
            'idHospederia' => $idHospederia,
            'nombre' => 'DANI',
            'apellido1' => $apellido,
            'apellido2' => 'MEFLE',
            'nacionalidad' => 'A9109AAAAA',
            'nacionalidadStr' => 'ESPAÑA',
            'tipoDocumento' => 'D',
            'tipoDocumentoStr' => 'DNI',
            'numIdentificacion' => '76586766D',
            'fechaExpedicionDoc' => '05/01/2022',
            'dia' => '23',
            'mes' => '11',
            'ano' => '2000',
            'fechaNacimiento' => '23/11/2000',
            'sexo' => 'M',
            'sexoStr' => 'MASCULINO',
            'fechaEntrada' => '21/12/2023',
            '_csrf' => $csrfToken
        ];
       
        $headers = [
            'Cookie' => 'FRONTAL_JSESSIONID: ' . $cookiesArray['FRONTAL_JSESSIONID'] . ' UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_: ' . $cookiesArray['UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_'] . ' cookiesession1: ' . $cookiesArray['cookiesession1'],
            'Accept' => 'text/html, */*; q=0.01',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Referer' => 'https://webpol.policia.es/e-hotel/inicio',
            'X-Csrf-Token' => $csrfToken,
            'X-Requested-With' => 'XMLHttpRequest',
            // Otros encabezados según sea necesario
        ];
        // $data['apellido1'] = mb_convert_encoding('CASTAÑOS', 'UTF-8');

        $browser->setServerParameters($headers);

        $crawler = $browser->request(
            'POST',
            'https://webpol.policia.es/e-hotel/hospederia/manual/insertar/huesped',
            $data
        );
        // Diagnóstico: Ver contenido de la respuesta
        $responseContent = $browser->getResponse()->getContent();
        echo $responseContent;

        $response4 = $browser->getResponse();
        $statusCode4 = $response4->getStatusCode();
        
        if ($browser->getResponse()->getStatusCode() == 302) {
            $crawler = $browser->followRedirect();
            // Sigue la redirección
        }

        if ($statusCode4 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            // echo '4 - Código de estado HTTP: ' . $statusCode4 . $csrfToken . ' id: '. $idHospederia;
            return;
        }
        return [
            $csrfToken,
            $cookiesArray,
            $responseContent
        ];
    }


}
