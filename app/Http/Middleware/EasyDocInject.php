<?php

namespace App\Http\Middleware;

use App\Models\Prescription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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

            $route  = $request->route();
            $name   = (string) optional($route)->getName();
            $path   = $request->path();
            $public = $name === 'prescription.public';
            $isDoctorArea = Str::startsWith($name, 'doctor.') || Str::startsWith($path, 'doctor');
            $isPrint = ! $public && Str::contains(strtolower($name.' '.$path), 'print') && $isDoctorArea;

            $head = ''; $bodyStart = view('easydoc::preloader')->render(); $bodyEnd = '';

            if ($isDoctorArea && ! $public) {
                $head   .= '<link rel="stylesheet" href="'.asset('css/doctor-mobile.css').'">';
                $bodyEnd .= '<script src="'.asset('js/doctor-mobile.js').'"></script>';
            }
            if ($public) {
                $head .= '<meta name="robots" content="noindex,nofollow">'
                    .'<style>@media screen{.rx-toolbar,.no-print,.d-print-none,.noprint,.print-hide,.print\:hidden,.toolbar,.print-toolbar,nav.navbar,.sidebar,aside{display:none!important}body{padding-bottom:90px}}</style>';
            }

            if ($public || $isPrint) {
                $p = $public ? null : $this->prescriptionFrom($route);
                if ($public || $p) {
                    $url = $public ? url()->current() : $this->shareUrl($p);
                    $bodyEnd .= view('easydoc::sharebar', [
                        'isPublic' => $public,
                        'shareUrl' => $url,
                        'rxId'     => $public ? 'rx' : $p->getKey(),
                        'revokeUrl' => $public ? null : route('prescription.share.revoke', $p->getKey()),
                    ])->render();
                }
            }

            $html = preg_replace('/<\/head>/i', $head.'</head>', $html, 1);
            $html = preg_replace('/(<body[^>]*>)/i', '$1'.$bodyStart, $html, 1);
            $html = preg_replace('/<\/body>/i', $bodyEnd.'</body>', $html, 1);
            $response->setContent($html);
            $response->headers->remove('Content-Length');
        } catch (\Throwable $e) {
            report($e); // kono error hole page ager moto-i cholbe
        }

        return $response;
    }

    private function prescriptionFrom($route): ?Prescription
    {
        if (! $route) return null;
        $param = $route->parameter('prescription') ?? $route->parameter('id');
        if ($param instanceof Prescription) return $param;
        return $param ? Prescription::find($param) : null;
    }

    private function shareUrl(Prescription $p): string
    {
        if (! $p->share_token) {
            do { $t = Str::random(40); } while (Prescription::where('share_token', $t)->exists());
            $p->forceFill(['share_token' => $t, 'shared_at' => now()])->save();
        }
        return route('prescription.public', $p->share_token);
    }
}
