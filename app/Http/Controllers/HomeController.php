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

       // Obtiene el HTML de la página y lo convierte en una cadena
        $csrfToken = $crawler->filter('meta[name="_csrf"]')->attr('content');
        $form = $crawler->filter('#loginForm')->form();

        // Rellena los campos del formulario con tus credenciales y el token CSRF
        $form['username'] = 'H11070GEV04';
        $form['password'] = 'H4Kins4p4rtamento2023';
        $form['_csrf'] = $csrfToken; // Asegúrate de que el nombre del campo sea correcto
        
        // Envía el formulario
        $crawler = $browser->submit($form);

        // $crawler = $browser->request('GET', 'https://webpol.policia.es/hospederia/manual/insertar/huesped');

        $htmlContent = $crawler->html();
        
        // Ahora $crawler contiene la respuesta después de enviar el formulario

        // Devuelve el HTML como respuesta
        return response($htmlContent);
    }


}
