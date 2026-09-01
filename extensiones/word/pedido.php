<?php

require_once "../../controladores/cotizaciones.controlador.php";
require_once "../../modelos/cotizaciones.modelo.php";

require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";

if(!isset($_GET["idDocto"]) || empty($_GET["idDocto"])){
    die("Documento no válido");
}

$idDocto = (int) $_GET["idDocto"];

$pedido = ControladorCotizaciones::ctrMostrarCotizacion("id", $idDocto);
$detalle = ControladorCotizaciones::ctrMostrarDetalleCotizacion($idDocto);

if(!$pedido || !is_array($pedido)){
    die("Pedido no encontrado");
}

$cliente = ControladorClientes::ctrMostrarClientes("id", $pedido["id_cliente"]);

$nombreCliente = (is_array($cliente) && isset($cliente["nombre"])) ? $cliente["nombre"] : "";
$direccionCliente = (is_array($cliente) && isset($cliente["direccion"])) ? $cliente["direccion"] : "";
$fecha = !empty($pedido["fecha"]) ? date("d/m/Y", strtotime($pedido["fecha"])) : "";
$codigo = isset($pedido["codigo_docto"]) ? $pedido["codigo_docto"] : "";
$totalGeneral = isset($pedido["total"]) ? (float)$pedido["total"] : 0;

$logoPath = "http://localhost/pos/extensiones/word/logopedido.png";

$nombreArchivo = "pedido_".$codigo.".doc";

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
<title>Pedido <?php echo htmlspecialchars($codigo, ENT_QUOTES, "UTF-8"); ?></title>
<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #000;
}
.page{
    width: 95%;
    margin: 0 auto;
}
.header-main{
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
.header-main td{
    vertical-align: top;
}
.banner{
    width: 74%;
}
.banner-top{
    background: #92cd50;
    padding: 8px 10px 4px 10px;
    text-align: center;
}
.banner-title{
    color: #3366cc;
    font-size: 26px;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 4px;
}
.banner-sub{
    color: #0066cc;
    font-style: italic;
    font-size: 12px;
}
.banner-bottom{
    background: #ffff00;
    text-align: center;
    font-size: 12px;
    font-style: italic;
    font-weight: bold;
    padding: 3px 0;
}
.logo-box{
    width: 26%;
    text-align: center;
}
.logo-box img{
    width: 120px;
    height: auto;
}
.doc-box{
    width: 180px;
    border-collapse: collapse;
    margin-top: 15px;
    margin-left: auto;
}
.doc-box td{
    border: 1px solid #000;
    padding: 5px 6px;
    font-size: 12px;
}
.doc-label{
    font-weight: bold;
    width: 90px;
}
.info-table{
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
}
.info-table td{
    border: 1px solid #000;
    padding: 6px 8px;
    font-size: 12px;
}
.info-label{
    font-weight: bold;
}
.items-table{
    width: 100%;
    border-collapse: collapse;
    margin-top: 18px;
}
.items-table th,
.items-table td{
    border: 1px solid #000;
    padding: 6px 8px;
}
.items-table th{
    background: #d9d9d9;
    font-weight: bold;
    text-align: center;
}
.c{
    text-align: center;
}
.r{
    text-align: right;
}
.total-box{
    width: 100%;
    margin-top: 6px;
}
.total-right{
    width: 220px;
    margin-left: auto;
    border-collapse: collapse;
}
.total-right td{
    border: 1px solid #000;
    padding: 6px 8px;
}
.firma{
    margin-top: 80px;
    width: 50%;
    text-align: center;
}
.linea{
    border-top: 1px solid #000;
    width: 80%;
    margin: 0 auto 8px auto;
}
</style>
</head>
<body>
<div class="page">

    <table class="header-main">
        <tr>
            <td class="banner">
                <div class="banner-top">
                    <div class="banner-title">SISTEMA POS</div>
                    <div class="banner-sub"> GUATEMALA</div>
                </div>
                <div class="banner-bottom">Calidad y servicios</div>
            </td>
            <td class="logo-box">
                <img src="<?php echo $logoPath; ?>" alt="Logo">
            </td>
        </tr>
    </table>

    <table class="doc-box">
        <tr>
            <td class="doc-label">CODIGO</td>
            <td class="c"><?php echo htmlspecialchars($codigo, ENT_QUOTES, "UTF-8"); ?></td>
        </tr>
        <tr>
            <td class="doc-label">DOCUMENTO</td>
            <td class="c">PEDIDO</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-label" style="width:62%;">NOMBRE:</td>
            <td class="info-label" style="width:11%;">FECHA:</td>
            <td class="c" style="width:27%; font-weight:bold;"><?php echo htmlspecialchars($fecha, ENT_QUOTES, "UTF-8"); ?></td>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars($nombreCliente, ENT_QUOTES, "UTF-8"); ?></td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="info-label">DIRECCION:</td>
            <td colspan="2"><?php echo htmlspecialchars($direccionCliente, ENT_QUOTES, "UTF-8"); ?></td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%;">DESCRIPCION</th>
                <th style="width:12%;">CANTIDAD</th>
                <th style="width:12%;">UNIDAD DE MEDIDA</th>
                <th style="width:13%;">PRECIO UNITARIO</th>
                <th style="width:13%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
        <?php if(is_array($detalle) && count($detalle) > 0): ?>
            <?php foreach($detalle as $item): ?>
                <?php
                    $descripcion = isset($item["descripcion_item"]) ? $item["descripcion_item"] : "";
                    $cantidad = isset($item["cantidad"]) ? (float)$item["cantidad"] : 0;
                    $unidad = (isset($item["unidad_medida"]) && trim($item["unidad_medida"]) !== "") ? $item["unidad_medida"] : ".";
                    $precioUnitario = isset($item["precio_unitario"]) ? (float)$item["precio_unitario"] : 0;
                    $subtotal = isset($item["subtotal"]) ? (float)$item["subtotal"] : 0;
                    $totalCalculado += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($descripcion, ENT_QUOTES, "UTF-8"); ?></td>
                    <td class="c"><?php echo number_format($cantidad, 0); ?></td>
                    <td class="c"><?php echo htmlspecialchars($unidad, ENT_QUOTES, "UTF-8"); ?></td>
                    <td class="c">Q <?php echo number_format($precioUnitario, 2); ?></td>
                    <td class="c">Q. <?php echo number_format($subtotal, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if($totalGeneral <= 0){ $totalGeneral = $totalCalculado; } ?>

    <table class="total-box">
        <tr>
            <td>
                <table class="total-right">
                    <tr>
                        <td class="r" style="font-weight:bold;">Total:</td>
                        <td class="c">Q <?php echo number_format($totalGeneral, 2); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="firma">
        <div class="linea"></div>
        <div>FIRMA DE RECIBIDO</div>
    </div>

</div>
</body>
</html>
