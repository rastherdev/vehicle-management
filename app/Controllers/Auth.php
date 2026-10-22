<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    public function index(): string|RedirectResponse
    {
        if (session()->get('user_id') !== null) {
            return redirect()->to('admin');
        }

        return view('auth/login', [
            'title'  => 'Iniciar sesión',
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function login(): RedirectResponse
    {
        $rules = [
            'email'    => ['label' => 'Correo electrónico', 'rules' => 'required|valid_email'],
            'password' => ['label' => 'Contraseña', 'rules' => 'required'],
        ];

        $mensajes = [
            'email' => [
                'required'    => 'El correo electrónico es obligatorio.',
                'valid_email' => 'El correo ingresado no es válido.',
            ],
            'password' => [
                'required' => 'La contraseña es obligatoria.',
            ],
        ];

        if (! $this->validate($rules, $mensajes)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = model(UsuarioModel::class)->where('email', $email)->first();

        if ($user === null || ! password_verify($password, $user['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['login' => 'Credenciales inválidas.']);
        }

        if ((int) $user['activo'] !== 1) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['login' => 'El usuario está inactivo.']);
        }

        session()->set([
            'user_id'      => (int) $user['id'],
            'user_email'   => $user['email'],
            'user_nombre'  => $user['nombre'],
            'user_apellido'=> $user['apellido'],
        ]);

        return redirect()->to('admin');
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to('/');
    }
}
