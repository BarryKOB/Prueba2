<?php

namespace App\Models;

use App\Enums\RolUser;
use App\Enums\RolUsuario;

class User
{
    public string $email;
    public string $nombre;
    public RolUser $rol;

    public function __construct(string $email, string $nombre, RolUser $rol)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->rol = $rol;
    }

    public static function verificarUsuario(string $email, string $password)
    {
        if ($email === 'user@email.com' && $password === '1234') {
            return new self($email, 'Usuario', RolUser::USUARIO);
        }

        if ($email === 'admin@email.com' && $password === '1234') {
            return new self($email, 'Admin', RolUser::ADMIN);
        }
        return null;
    }
}
