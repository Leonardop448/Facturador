<?php

if (!isset($_SESSION['usuario'])) {
    echo "<script>window.location = '" . RUTA_URL . "/usuarios/login';</script>";
    exit;
}
