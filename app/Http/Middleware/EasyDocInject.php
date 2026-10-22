<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shob HTML page e stethoscope preloader, ar doctor area te (print page chhara) mobile CSS/JS boshay.
 */
class EasyDocInject
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if (! method_exists($response, 'getContent') || $request->ajax() || $request->expectsJson()
                || $request->isMethod('POST') || $response->getStatusCode() !== 200) {
                return $response;
            }
            $ct = (string) $response->headers->get('Content-Type');
            if ($ct !== '' && ! Str::contains($ct, 'text/html')) {
                return $response;
            }
            $html = $response->getContent();
            if (! is_string($html) || ! preg_match('/<body[^>]*>/i', $html) || str_contains($html, 'id="ed-preloader"')) {
                return $response;
            }

            $name = (string) optional($request->route())->getName();
            $path = $request->path();
            $isDoctorArea = Str::startsWith($name, 'doctor.') || Str::startsWith($path, 'doctor');
            $isPrint = Str::contains(strtolower($name . ' ' . $path), 'print');

            $head = '';
            $bodyEnd = '';
            if ($isDoctorArea && ! $isPrint) {
                $head   .= '<link rel="stylesheet" href="' . asset('css/doctor-mobile.css') . '">';
                $bodyEnd .= '<script src="' . asset('js/doctor-mobile.js') . '"></script>';
            }

            $bodyStart = view('easydoc::preloader')->render();

            $html = preg_replace('/<\/head>/i', $head . '</head>', $html, 1);
            $html = preg_replace('/(<body[^>]*>)/i', '$1' . $bodyStart, $html, 1);
            $html = preg_replace('/<\/body>/i', $bodyEnd . '</body>', $html, 1);
            $response->setContent($html);
            $response->headers->remove('Content-Length');
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }
}
