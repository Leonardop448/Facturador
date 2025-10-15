<?php

namespace App\controladores;

require_once BASE_PATH . '/vendor/autoload.php';


use App\nucleo\Controlador;
use App\modelos\Productos;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\nucleo\Sesion;

class InventarioControlador extends Controlador
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            Sesion::iniciarSesionLarga();
        }
    }

    public function index()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        $conexion = \App\configuracion\BaseDatos::conectar();
        $usuario = $_SESSION['usuario'];
        $clienteId = null;

        if ($usuario['rol'] === 'SuperU') {
            // Permitir filtro por cliente
            $clienteId = isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;
            $usuarioModelo = new \App\modelos\Usuario($conexion);
            $clientes = $usuarioModelo->obtenerTodosLosClientes();
        } elseif ($usuario['rol'] === 'cliente') {
            $clienteId = $usuario['id'];
            $clientes = []; // No se usa
        } else {
            $clienteId = $usuario['cliente_id'] ?? null;
            $clientes = []; // No se usa
        }

        $modelo = new Productos($conexion);
        $productos = $modelo->obtenerTodosPorCliente($clienteId);

        $this->vista('inventario/index', [
            'productos' => $productos,
            'clientes' => $clientes,
            'clienteSeleccionado' => $clienteId,
            'usuario' => $usuario
        ]);
    }



    public function imprimir()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        $conexion = \App\configuracion\BaseDatos::conectar();
        $usuario = $_SESSION['usuario'];

        if ($usuario['rol'] === 'SuperU') {
            // Si se pasó un cliente_id válido por GET, se usa, si no, se muestra todo
            $clienteId = (isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id'])) ? (int)$_GET['cliente_id'] : null;
        } elseif ($usuario['rol'] === 'cliente') {
            $clienteId = $usuario['id'];
        } else {
            $clienteId = $usuario['cliente_id'] ?? null;
        }

        $modelo = new Productos($conexion);
        $productos = $modelo->obtenerTodosPorCliente($clienteId);

        $this->vista('inventario/imprimir', [
            'productos' => $productos,
            'usuario' => $usuario
        ]);
    }


    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        require_once BASE_PATH . '/vendor/autoload.php';

        $conexion = \App\configuracion\BaseDatos::conectar();
        $usuario = $_SESSION['usuario'];

        if ($usuario['rol'] === 'SuperU') {
            // Aplicar filtro solo si viene por GET
            $clienteId = (isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id'])) ? (int)$_GET['cliente_id'] : null;
        } elseif ($usuario['rol'] === 'cliente') {
            $clienteId = $usuario['id'];
        } else {
            $clienteId = $usuario['cliente_id'] ?? null;
        }

        $modelo = new Productos($conexion);
        $productos = $modelo->obtenerTodosPorCliente($clienteId);

        // Calcular totales
        $valorCosto = 0;
        $valorVenta = 0;

        foreach ($productos as $p) {
            $valorCosto += (int)$p['cantidad_en_stock'] * (int)$p['precio_compra'];
            $valorVenta += (int)$p['cantidad_en_stock'] * (int)$p['precio_venta'];
        }

        $ganancia = $valorVenta - $valorCosto;

        // Renderizar HTML
        ob_start();
        $this->vista('inventario/pdf', [
            'productos' => $productos,
            'usuario' => $usuario,
            'valorCosto' => $valorCosto,
            'valorVenta' => $valorVenta,
            'ganancia' => $ganancia
        ]);
        $html = ob_get_clean();

        // Crear y generar el PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("inventario.pdf", ["Attachment" => false]);
    }
}
