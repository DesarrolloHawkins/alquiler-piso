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
            'pass' => 'H4Kins4p4rtamento2024'
        ); 

        // $response = PoliceHotelFacade::to($credentials)
        // ->getCountries();
        // $parse_cookies = array();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PostmanRuntime/7.16.3');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://webpol.policia.es/e-hotel/login');
        ob_start();
        $response = curl_exec($ch);

        dd($response);
        $response = $this->csr();
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

        dd($response);

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
        return array('_csrf' => $csr, 'cookie' => $parse_cookies);

    }

    private function login()
    {
        $data_csr = $this->csr();
        dd($data_csr);

        try {

            $data_csr = $this->csr();
            dd($data_csr);
            $credentials = array(
                'user' => 'H11070GEV04',
                'pass' => 'H4Kins4p4rtamento2024'
            ); 

            $this->user = $credentials['user'];
            $this->pass = $credentials['pass'];

            $data = [
                'username' => $this->user,
                'password' => $this->pass,
                '_csrf' => $data_csr['_csrf']
            ];

            $headers = $this->headers;
            $headers = array_merge(
                $headers,
                [
                    'Cookie: ' . $this->cookie,
                    'Content-Type: application/x-www-form-urlencoded',
                ]
            );

            $curl_response = $this->curl(
                $this->endpoint . '/execute_login',
                'POST',
                $data,
                $headers
            );

            if (strpos($curl_response['content'], '/e-hotel/inicio') !== false) {
                $this->cookie = $data_csr['cookie'];
                $response_home = $this->home();

                return $response_home;
            } else {
                throw new \Exception('error.login.police');
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function getCountries()
    {
        try {
            $this->login();
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
                $this->endpoint . '/hospederia/manual/vista/grabadorManual',
                'GET',
                [],
                $headers
            );

            dd($response);

            $pattern = '/<selectid="nacionalidad"(.*?)<\/select>/i';
            $pattern_options = '@<optionvalue=\"(.*)\">(.*)</option>@';

            preg_match($pattern, $response['content'], $matches);
            $raw_countries = $matches[1];

            preg_match($pattern_options, $raw_countries, $matches_options);

            $countries = explode('optionvalue=', $matches_options[1]);
            $new_countries = array();


            foreach ($countries as $country) {
                preg_match_all('/[A-Za-z0-9]+/i', $country, $tmp);
                if ($tmp && count($tmp) && (count($tmp[0]) > 1)) {
                    $new_countries[] = [
                        'id' => $tmp[0][0],
                        'name' => $tmp[0][1]
                    ];
                }
            }

            $this->pkgoptions['countries'] = $new_countries;
            return $this->pkgoptions['countries'];


        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function curl($url = null, $method = 'GET', $data = array(), $headers = array())
    {
        try {

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
            dd($response);

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
