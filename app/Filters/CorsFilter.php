<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Disabled - CORS is now handled directly in the ApiController
        // header('Access-Control-Allow-Origin: *');
        // header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        // header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        // if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        //     exit(0);
        // }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu
    }
}
