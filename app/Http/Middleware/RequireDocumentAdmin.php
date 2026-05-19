<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDocumentAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('documents_admin_authenticated') === true) {
            return $next($request);
        }

        return redirect()->route('documents.login');
    }
}
