<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

abstract class CrudBase extends BaseController
{
    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return (array) ($this->request->getJSON(true) ?? $this->request->getPost());
    }

    protected function falloValidacion(): ResponseInterface
    {
        return $this->response->setStatusCode(422)
            ->setJSON(['errors' => $this->validator->getErrors()]);
    }

    protected function noEncontrado(string $mensaje): ResponseInterface
    {
        return $this->response->setStatusCode(404)->setJSON(['error' => $mensaje]);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     */
    protected function valida(array $data, array $rules): bool
    {
        $mensajes = [];

        foreach (array_keys($rules) as $campo) {
            $mensajes[$campo] = array_merge($this->mensajesGenericos(), $this->mensajesEspecificos($campo));
        }

        return $this->validateData($data, $rules, $mensajes);
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesGenericos(): array
    {
        return [
            'required'              => 'El campo {field} es obligatorio.',
            'max_length'            => 'El campo {field} no puede superar {param} caracteres.',
            'in_list'               => 'El valor seleccionado en {field} es inválido.',
            'is_unique'             => 'El valor ingresado en {field} ya existe.',
            'is_not_unique'         => 'El valor seleccionado en {field} no existe.',
            'integer'               => 'El campo {field} debe ser un número entero.',
            'decimal'               => 'El campo {field} debe ser un número decimal.',
            'greater_than'          => 'El campo {field} debe ser mayor que {param}.',
            'greater_than_equal_to' => 'El campo {field} debe ser mayor o igual que {param}.',
            'less_than_equal_to'    => 'El campo {field} debe ser menor o igual que {param}.',
            'valid_email'           => 'El correo ingresado no es válido.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesEspecificos(string $campo): array
    {
        return [];
    }
}
