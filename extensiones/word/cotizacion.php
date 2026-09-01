<?php

require_once "../../controladores/cotizaciones.controlador.php";
require_once "../../modelos/cotizaciones.modelo.php";

require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";

if(!isset($_GET["idDocto"]) || empty($_GET["idDocto"])){
    die("Documento no válido");
}

$idDocto = (int) $_GET["idDocto"];

$cotizacion = ControladorCotizaciones::ctrMostrarCotizacion("id", $idDocto);
$detalle = ControladorCotizaciones::ctrMostrarDetalleCotizacion($idDocto);

if(!$cotizacion || !is_array($cotizacion)){
    die("Cotización no encontrada");
}

$cliente = ControladorClientes::ctrMostrarClientes("id", $cotizacion["id_cliente"]);

$nombreCliente = (is_array($cliente) && isset($cliente["nombre"])) ? $cliente["nombre"] : "";
$nitCliente = (is_array($cliente) && isset($cliente["documento"])) ? $cliente["documento"] : "";
$direccionCliente = (is_array($cliente) && isset($cliente["direccion"])) ? $cliente["direccion"] : "";

$fecha = !empty($cotizacion["fecha"]) ? date("d/m/Y", strtotime($cotizacion["fecha"])) : "";
$codigo = isset($cotizacion["codigo_docto"]) ? $cotizacion["codigo_docto"] : "";
$totalGeneral = isset($cotizacion["total"]) ? (float)$cotizacion["total"] : 0;

$logoPath = "http://localhost/pos/extensiones/word/logo_chonay.png";

/* nombre del archivo */
$nombreArchivo = "cotizacion_".$codigo.".doc";

/* headers para Word */
header("Content-Type: application/vnd.ms-word; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"".$nombreArchivo."\"");
header("Pragma: no-cache");
header("Expires: 0");

$totalCalculado = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cotización <?php echo htmlspecialchars($codigo, ENT_QUOTES, "UTF-8"); ?></title>
<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #000;
    margin: 0;
    padding: 0;
}
.page{
    width: 96%;
    margin: 18px auto;
}
.top-line{
    border-top: 4px solid #6ea834;
    margin-bottom: 18px;
}
.header-table,
.info-table,
.items-table,
.total-table,
.sign-table{
    width: 100%;
    border-collapse: collapse;
}
.header-table td{
    vertical-align: top;
}
.logo{
    width: 120px;
    max-width: 120px;
    height: auto;
    display: block;
}
.empresa{
    text-align: center;
}
.empresa-titulo{
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 8px;
}
.empresa-linea{
    font-size: 12px;
    margin-bottom: 6px;
}
.doc-box{
    width: 100%;
    border-collapse: collapse;
}
.doc-box td{
    border: 1px solid #6ea834;
    padding: 5px 8px;
}
.doc-head{
    text-align: center;
    font-weight: bold;
}
.doc-title{
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    color: #fff;
    background: #6ea834;
}
.info-table{
    margin-top: 14px;
}
.info-table td{
    border: 1px solid #bdbdbd;
    padding: 6px 8px;
}
.info-label{
    width: 22%;
    font-weight: bold;
    background: #f5f5f5;
}
.items-table{
    margin-top: 22px;
}
.items-table th{
    background: #6ea834;
    color: #fff;
    border: 1px solid #6ea834;
    padding: 8px;
    text-align: center;
    font-size: 12px;
}
.items-table td{
    border: 1px solid #bdbdbd;
    padding: 8px;
    font-size: 12px;
}
.items-table td.c{
    text-align: center;
}
.total-table{
    margin-top: 24px;
}
.total-table td{
    border: 2px solid #6ea834;
    padding: 10px;
    font-weight: bold;
    font-size: 16px;
    text-align: center;
}
.obs{
    margin-top: 18px;
    font-size: 13px;
}
.sign-table{
    margin-top: 70px;
}
.sign-line{
    border-top: 1px solid #444;
    width: 70%;
    margin: 0 auto 6px auto;
}
.sign-name{
    text-align: center;
    font-weight: bold;
}
.note{
    margin-top: 18px;
    text-align: center;
    font-style: italic;
    color: #555;
}
</style>
</head>
<body>
<div class="page">

    <div class="top-line"></div>

    <table class="header-table">
    <tr>
        <td style="width:16%; text-align:left;">
            <img src="<?php echo $logoPath; ?>" style="width:120px;height:auto;">        </td>

        <td style="width:56%; padding-top:8px;">
            <div class="empresa">
                <div class="empresa-titulo">SISTEMA POS</div>
                <div class="empresa-linea">Barrio el Centro, Dolores, Peten</div>
                <div class="empresa-linea">Tel: </div>
                <div class="empresa-linea">@gmail.com</div>
            </div>
        </td>

        <td style="width:28%; vertical-align:top;">
            <table class="doc-box">
                <tr>
                    <td class="doc-head">DOCUMENTO</td>
                </tr>
                <tr>
                    <td class="doc-title">COTIZACION</td>
                </tr>
                <tr>
                    <td><strong>No.</strong> <?php echo htmlspecialchars($codigo, ENT_QUOTES, "UTF-8"); ?></td>
                </tr>
                <tr>
                    <td><strong>Fecha:</strong> <?php echo htmlspecialchars($fecha, ENT_QUOTES, "UTF-8"); ?></td>
                </tr>
                <tr>
                    <td><strong>NIT:</strong> <?php echo htmlspecialchars((string)$nitCliente, ENT_QUOTES, "UTF-8"); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

    <table class="info-table">
        <tr>
            <td class="info-label">Nombre:</td>
            <td><?php echo htmlspecialchars($nombreCliente, ENT_QUOTES, "UTF-8"); ?></td>
        </tr>
        <tr>
            <td class="info-label">NIT / DPI:</td>
            <td><?php echo htmlspecialchars((string)$nitCliente, ENT_QUOTES, "UTF-8"); ?></td>
        </tr>
        <tr>
            <td class="info-label">Direccion / referencia:</td>
            <td><?php echo htmlspecialchars($direccionCliente, ENT_QUOTES, "UTF-8"); ?></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:14%;">CANTIDAD</th>
                <th style="width:46%;">DESCRIPCION</th>
                <th style="width:20%;">PRECIO UNIT.</th>
                <th style="width:20%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if(is_array($detalle) && count($detalle) > 0): ?>
                <?php foreach($detalle as $item): ?>
                    <?php
                        $cantidad = isset($item["cantidad"]) ? (float)$item["cantidad"] : 0;
                        $descripcion = isset($item["descripcion_item"]) ? $item["descripcion_item"] : "";
                        $precioUnitario = isset($item["precio_unitario"]) ? (float)$item["precio_unitario"] : 0;
                        $subtotal = isset($item["subtotal"]) ? (float)$item["subtotal"] : 0;
                        $totalCalculado += $subtotal;
                    ?>
                    <tr>
                        <td class="c"><?php echo number_format($cantidad, 0); ?></td>
                        <td><?php echo htmlspecialchars($descripcion, ENT_QUOTES, "UTF-8"); ?></td>
                        <td class="c">Q <?php echo number_format($precioUnitario, 2); ?></td>
                        <td class="c">Q <?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($totalGeneral <= 0){ $totalGeneral = $totalCalculado; } ?>

    <table class="total-table">
        <tr>
            <td style="width:60%;">TOTAL</td>
            <td style="width:40%;">Q <?php echo number_format($totalGeneral, 2); ?></td>
        </tr>
    </table>

    <div class="obs">
        <strong>Observaciones:</strong>
    </div>

    <table class="sign-table">
        <tr>
            <td style="width:50%; text-align:center;">
                <div class="sign-line"></div>
                <div class="sign-name">SISTEMA POS</div>
            </td>
            <td style="width:50%; text-align:center;">
                <div class="sign-line"></div>
                <div class="sign-name">RECIBIDO POR CLIENTE</div>
            </td>
        </tr>
    </table>

    <div class="note">
        , SOMOS ASESORIA TECNICA RESPONSABLE...
    </div>

</div>
</body>
</html>
