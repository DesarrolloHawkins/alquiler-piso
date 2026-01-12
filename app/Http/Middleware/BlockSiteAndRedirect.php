<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockSiteAndRedirect
{
    /**
     * Handle an incoming request.
     * 
     * Bloquea el acceso a la web principal (apartamentosalgeciras.com)
     * y redirige a una URL configurada en .env
     * También bloquea el CRM si está activado
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $isMainDomain = $host === 'apartamentosalgeciras.com' || $host === 'www.apartamentosalgeciras.com';
        $isCrmDomain = str_starts_with($host, 'crm.');
        
        // Obtener la URL de redirección del .env
        $redirectUrl = env('SITE_BLOCK_REDIRECT_URL', null);
        
        // Si está activado el bloqueo global (bloquear también CRM)
        $blockGlobal = filter_var(env('SITE_BLOCK_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        
        // Si está activado el bloqueo solo de la web principal
        $blockMainSite = filter_var(env('SITE_BLOCK_MAIN_ONLY', false), FILTER_VALIDATE_BOOLEAN);
        
        // Bloquear si:
        // 1. Bloqueo global activado (bloquea todo: web y CRM)
        // 2. O bloqueo solo web principal activado Y es el dominio principal (no CRM)
        $shouldBlock = $blockGlobal || ($blockMainSite && $isMainDomain);
        
        if ($shouldBlock) {
            if (empty($redirectUrl)) {
                Log::warning('BlockSiteAndRedirect: URL de redirección no configurada en .env', [
                    'host' => $host,
                    'block_global' => $blockGlobal,
                    'block_main_only' => $blockMainSite,
                    'is_main_domain' => $isMainDomain,
                    'is_crm_domain' => $isCrmDomain,
                ]);
                
                // Si no hay URL configurada, devolver un error 503
                return response()->view('errors.503', [], 503);
            }
            
            Log::info('BlockSiteAndRedirect: Redirigiendo acceso bloqueado', [
                'host' => $host,
                'url' => $request->fullUrl(),
                'redirect_to' => $redirectUrl,
                'block_global' => $blockGlobal,
                'block_main_only' => $blockMainSite,
                'is_main_domain' => $isMainDomain,
                'is_crm_domain' => $isCrmDomain,
            ]);
            
            return redirect($redirectUrl, 302);
        }
        
        return $next($request);
    }
}
