<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentAuthController extends Controller
{
    public function show()
    {
        return view('documents.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (
            ! hash_equals((string) config('knowledge.document_admin_username'), $data['username'])
            || ! hash_equals((string) config('knowledge.document_admin_password'), $data['password'])
        ) {
            return back()
                ->withInput()
                ->withErrors(['login' => 'Usuário ou senha inválidos.']);
        }

        $request->session()->put('documents_admin_authenticated', true);

        return redirect()->route('documents.index');
    }

    public function destroy(Request $request)
    {
        $request->session()->forget('documents_admin_authenticated');

        return redirect()->route('documents.login');
    }
}
