<?php

namespace App\Enums;

enum RolUser: string
{
    case ADMIN = 'admin';
    case USUARIO = 'usuario';
    case GESTOR = 'gestor';

    public function descripcion(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador del sistema',
            self::USUARIO => 'Usuario estándar',
            self::GESTOR => 'Gestor del sistema',
        };
    }
}