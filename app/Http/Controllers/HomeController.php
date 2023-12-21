<?php

namespace App\Http\Controllers;

use App\Models\MensajeAuto;
use App\Models\Reserva;
use App\Services\ClienteService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
// use leifermendez\police\PoliceHotelFacade;

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
        $credentials = array(
            'user' => 'H11070GEV04',
            'pass' => 'H4Kins4p4rtamento2023'
        ); 

        // $response = PoliceHotelFacade::to($credentials)
        // ->getCountries();
        // $parse_cookies = array();

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // curl_setopt($ch, CURLOPT_USERAGENT, 'PostmanRuntime/7.16.3');
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        // curl_setopt($ch, CURLOPT_URL, 'https://webpol.policia.es/e-hotel/login');
        // ob_start();
        // $response = curl_exec($ch);

        // dd($response);
        $response = $this->getCountries();
        // $response = '$this->csr()';
        return $response;
    }


    private function csr()
    {

        $parse_cookies = array();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PostmanRuntime/7.16.3');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://webpol.policia.es/e-hotel/login');
        ob_start();
        $response = curl_exec($ch);

        // dd($response);

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }

        $cookies = array_reverse($cookies);
        foreach ($cookies as $key_cookie => $value_cookie) {
            $parse_cookies[] = $key_cookie . '=' . $value_cookie;
        }
        $parse_cookies = implode('; ', $parse_cookies);
        ob_end_clean();
        curl_close($ch);

        $response = str_replace(["\r\n", "\n", " "], "", $response);

        preg_match('/<metaname="_csrf"content="(.{36})"\/>/',
            $response, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches)) {
            $matches = array_reverse($matches);
        }
        $matches = array_shift($matches);
        $csr = $matches[0];
        $this->_csrf = $csr;
        $this->cookie = $parse_cookies;

        // dd($parse_cookies, $this->_csrf);
        return array('_csrf' => $csr, 'cookie' => $parse_cookies);

    }

    private function login()
    {
        // $data_csr = $this->csr();
        // dd($data_csr);

        try {

            $data_csr = $this->csr();
            // dd($data_csr);
            // $credentials = array(
            //     'user' => 'H11070GEV04',
            //     'pass' => 'H4Kins4p4rtamento2023'
            // ); 

            // $this->user = $credentials['user'];
            // $this->pass = $credentials['pass'];

            $data = [
                'username' => 'H11070GEV04',
                'password' => 'H4Kins4p4rtamento2023',
                '_csrf' => '49614a9a-efc7-4c36-9063-b1cd6824aa9a'
            ];

            // $headers = $this->headers;
            // $headers = array_merge(
            //     $headers,
            //     [
            //         'Cookie: ' . $data_csr['cookie'],
            //         'Content-Type: application/x-www-form-urlencoded',
            //     ]
            // );

            // $curl_response = $this->curl(
            //     'https://webpol.policia.es/e-hotel/execute_login',
            //     'POST',
            //     $data,
            //     $headers
            // );

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://webpol.policia.es/e-hotel/execute_login',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'username=H11070GEV04&password=H4Kins4p4rtamento2023&_csrf='.$data_csr['_csrf'],
                CURLOPT_HTTPHEADER => array(
                    'Cookie: '.$data_csr['cookie'],
                    'Content-Type: application/x-www-form-urlencoded'
                  ),
                ));

            $response434 = curl_exec($curl);
            // dd($data,$data_csr['cookie'], $response434);
            if ($response434 === false) {
                $error = curl_error($curl);
                // Puedes registrar o imprimir este error para depuración
                // dd($error);
            }
            curl_close($curl);

            // if (strpos($curl_response['content'], '/e-hotel/inicio') !== false) {
            //     $this->cookie = $data_csr['cookie'];
            //     $response_home = $this->home();

            //     return $response_home;
            // } else {
            //     throw new \Exception('error.login.police');
            // }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function getCountries()
    {
        try {
            $this->login();
            // dd($te);
            // dd($login2);

            $headers = array_merge(
                is_array($this->headers) ? $this->headers : [],
                [
                    'Cookie: ' . $this->cookie,
                    'X-CSRF-TOKEN: ' . $this->_csrf,
                    'X-Requested-With: XMLHttpRequest'
                ]
            );

            $response = $this->curl(
                'https://webpol.policia.es/e-hotel/hospederia/manual/vista/grabadorManual',
                'GET',
                [],
                $headers
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_URL, 'https://webpol.policia.es/e-hotel/hospederia/manual/vista/grabadorManual');

            ob_start();

            $response = curl_exec($ch);
            $raw_response = $response;

            // dd($response);

            $pattern = '/<select id="nacionalidad".*?>(.*?)<\/select>/is';
            $pattern_options = '/<option value="(.*?)">(.*?)<\/option>/is';

            preg_match($pattern, $response, $matches);
            if (!empty($matches)) {
                $raw_countries = $matches[1];

                preg_match_all($pattern_options, $raw_countries, $matches_options, PREG_SET_ORDER);

                $new_countries = array();
                foreach ($matches_options as $match) {
                    $id = $match[1];
                    $name = $match[2]; // Considera usar html_entity_decode($name) si es necesario

                    if (!empty($id)) { // Ignorar opciones vacías
                        $new_countries[] = [
                            'id' => $id,
                            'name' => $name
                        ];
                    }
                }

                $this->pkgoptions['countries'] = $new_countries;
                return $this->pkgoptions['countries'];
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function curl($url = null, $method = 'GET', $data = array(), $headers = array())
    {
        try {
            // dd($headers, $url, $data, $method);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($method === 'POST') curl_setopt($ch, CURLOPT_POST, TRUE);
            if ($method === 'POST') curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            ob_start();

            $response = curl_exec($ch);
            $raw_response = $response;

            /** cookies ** */
            // dd($raw_response);

            preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
            $cookies = array();
            foreach ($matches[1] as $item) {
                parse_str($item, $cookie);
                $cookies = array_merge($cookies, $cookie);
            }
            $parse_cookies = array();

            $cookies = array_reverse($cookies);
            foreach ($cookies as $key_cookie => $value_cookie) {
                $parse_cookies[] = $key_cookie . '=' . $value_cookie;
            }
            $parse_cookies = implode('; ', $parse_cookies);

            $raw_response = str_replace(["\r\n", "\n", " "], "", $raw_response);

            ob_end_clean();
            curl_close($ch);

            preg_match('/<metaname="_csrf"content="(.{36})"\/>/',
                $raw_response, $matches, PREG_OFFSET_CAPTURE);
            if (count($matches)) {
                $matches = array_reverse($matches);
            }
            $matches = array_shift($matches);
            $csr = $matches[0];

            return array(
                'content' => $raw_response,
                'cookies' => $parse_cookies,
                '_csrf' => $csr
            );

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
