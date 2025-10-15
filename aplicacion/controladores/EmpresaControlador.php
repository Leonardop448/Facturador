<?php

namespace App\controladores;

use App\modelos\Empresa;
use App\configuracion\BaseDatos;
use App\nucleo\Controlador;
use App\nucleo\Sesion;

class EmpresaControlador extends Controlador
{
    private $empresaModelo;

    public function __construct()
    {
        $this->empresaModelo = new Empresa(BaseDatos::conectar());
    }

    public function index()
    {
        $this->mostrar();
    }

    public function mostrar()
    {

        if (session_status() === PHP_SESSION_NONE) {
            Sesion::iniciarSesionLarga();
        }


        if (!isset($_SESSION['usuario'])) {
            echo "<script>window.location = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $rol = $_SESSION['usuario']['rol'] ?? '';
        if (!in_array($rol, ['SuperU', 'cliente', 'admin'])) {
            echo "<script>alert('Acceso no autorizado'); window.location = '" . RUTA_URL . "/modulos';</script>";
            exit;
        }

        $clienteId = $_SESSION['usuario']['id'];
        $empresa = $this->empresaModelo->obtenerPorCliente($clienteId);

        $this->vista('empresa/datos', ['empresa' => $empresa]);
    }

    public function guardar()
    {

        if (session_status() === PHP_SESSION_NONE) {
            Sesion::iniciarSesionLarga();
        }

        if (!isset($_SESSION['usuario'])) {
            echo "<script>window.location = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $clienteId = $_SESSION['usuario']['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteId = $_SESSION['usuario']['id'];

            $nombre = trim($_POST['nombre']);
            $nit = trim($_POST['nit']);
            $direccion = trim($_POST['direccion']);
            $telefono = trim($_POST['telefono']);
            $correo = trim($_POST['correo']);
            $logo = null;

            // Validar duplicidad de NIT
            $existeNIT = $this->empresaModelo->existeNit($nit, $clienteId);
            if ($existeNIT) {
                echo "<script>alert('El NIT ya se encuentra registrado para otra empresa.'); window.history.back();</script>";
                exit;
            }

            // Validar tipo y tamaño de archivo del logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
                $tipoArchivo = $_FILES['logo']['type'];
                $tamanoArchivo = $_FILES['logo']['size'];

                if (!in_array($tipoArchivo, $permitidos)) {
                    echo "<script>alert('Solo se permiten archivos JPG, PNG o WEBP.'); window.history.back();</script>";
                    exit;
                }

                if ($tamanoArchivo > 10 * 1024 * 1024) { // 10MB
                    echo "<script>alert('El logo no debe superar los 10MB.'); window.history.back();</script>";
                    exit;
                }

                $nombreArchivo = time() . '_' . basename($_FILES['logo']['name']);
                $rutaDestino = BASE_PATH . "/publico/logos/" . $nombreArchivo;

                if (!file_exists(dirname($rutaDestino))) {
                    mkdir(dirname($rutaDestino), 0777, true);
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $rutaDestino)) {
                    $logo = 'logos/' . $nombreArchivo;
                }
            }

            $empresaExistente = $this->empresaModelo->obtenerPorCliente($clienteId);

            $datos = [
                'nombre' => $nombre,
                'nit' => $nit,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'correo' => $correo,
                'logo' => $logo ?? ($empresaExistente['logo'] ?? null),
                'cliente_id' => $clienteId
            ];



            if ($empresaExistente) {
                $this->empresaModelo->actualizar($empresaExistente['id'], $datos);
            } else {
                $this->empresaModelo->guardar($datos);
            }

            echo "<script>window.location = '" . RUTA_URL . "/empresa';</script>";
        }
    }
}
