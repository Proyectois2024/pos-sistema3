<?php

ob_start();

ini_set("memory_limit", "512M");
set_time_limit(120);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set("display_errors", 0);
ini_set("log_errors", 1);

require_once "../../../controladores/cotizaciones.controlador.php";
require_once "../../../modelos/cotizaciones.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "tcpdf_include.php";

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

class MYPDF extends TCPDF {}

$pdf = new MYPDF("P", "mm", "LETTER", true, "UTF-8", false);

$pdf->SetCreator("POS");
$pdf->SetAuthor("Sistema POS");
$pdf->SetTitle("Pedido ".$codigo);
$pdf->SetSubject("Pedido");
$pdf->SetKeywords("pedido, Sistema, POS");

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(18, 10, 18);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

$logoEmpresa = __DIR__ . "/images/logopedido.png";

/* =============================
   ENCABEZADO ESTILO CAPTURA
============================= */

/* Banner izquierdo */
$pdf->SetFillColor(146, 205, 80);
$pdf->Rect(18, 22, 120, 16, "F");

$pdf->SetXY(18, 22);
$pdf->SetFont("helvetica", "BU", 20);
$pdf->SetTextColor(51, 102, 204);
$pdf->Cell(120, 8, "SISTEMA POS", 0, 2, "C");

$pdf->SetFont("helvetica", "I", 9);
$pdf->Cell(120, 4, "Los Mejores Productos", 0, 2, "C");

$pdf->SetFillColor(255, 255, 0);
$pdf->Rect(18, 38, 120, 5, "F");

$pdf->SetXY(18, 38);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont("helvetica", "BI", 9);
$pdf->Cell(120, 5, "Calidad y servicios", 0, 1, "C");

/* Logo derecha */
if(file_exists($logoEmpresa)){
    $pdf->Image($logoEmpresa, 141, 16, 38, 38);
}

/* Cuadro código / documento */
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.3);

$xBox = 132;
$yBox = 49;
$labelW = 28;
$valueW = 50;
$rowH = 8;

$pdf->SetXY($xBox, $yBox);
$pdf->SetFont("helvetica", "B", 10);
$pdf->Cell($labelW, $rowH, "CODIGO", 1, 0, "L");

$pdf->SetFont("helvetica", "", 9);
$pdf->Cell($valueW, $rowH, $codigo, 1, 1, "C");

$pdf->SetX($xBox);
$pdf->SetFont("helvetica", "B", 10);
$pdf->Cell($labelW, $rowH, "DOCUMENTO", 1, 0, "L");

$pdf->SetFont("helvetica", "", 10);
$pdf->Cell($valueW, $rowH, "PEDIDO", 1, 1, "C");

/* =============================
   DATOS CLIENTE
============================= */
$pdf->SetXY(18, 66);

$pdf->SetFont("helvetica", "B", 10);
$pdf->Cell(24, 8, "NOMBRE:", 1, 0, "L");
$pdf->SetFont("helvetica", "", 10);
$pdf->Cell(96, 8, $nombreCliente, 1, 0, "L");

$pdf->SetFont("helvetica", "B", 10);
$pdf->Cell(20, 8, "FECHA:", 1, 0, "L");
$pdf->Cell(40, 8, $fecha, 1, 1, "C");

$pdf->SetX(18);
$pdf->SetFont("helvetica", "B", 10);
$pdf->Cell(30, 8, "DIRECCION:", 1, 0, "L");
$pdf->SetFont("helvetica", "", 10);
$pdf->Cell(150, 8, $direccionCliente, 1, 1, "L");

/* =============================
   TABLA PRODUCTOS
============================= */
$pdf->Ln(10);

$pdf->SetFont("helvetica", "B", 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->SetTextColor(0, 0, 0);

$pdf->Cell(80, 10, "DESCRIPCION", 1, 0, "C", 1);
$pdf->Cell(20, 10, "CANTIDAD", 1, 0, "C", 1);
$pdf->Cell(25, 10, "UNIDAD MED.", 1, 0, "C", 1);
$pdf->Cell(25, 10, "PRECIO UNIT.", 1, 0, "C", 1);
$pdf->Cell(30, 10, "TOTAL", 1, 1, "C", 1);

$pdf->SetFont("helvetica", "", 10);

$totalCalculado = 0;

if(is_array($detalle) && count($detalle) > 0){

    foreach($detalle as $item){

        $descripcion = isset($item["descripcion_item"]) ? $item["descripcion_item"] : "";
        $cantidad = isset($item["cantidad"]) ? (float)$item["cantidad"] : 0;
        $unidad = isset($item["unidad_medida"]) && trim($item["unidad_medida"]) !== "" ? $item["unidad_medida"] : ".";
        $precioUnitario = isset($item["precio_unitario"]) ? (float)$item["precio_unitario"] : 0;
        $subtotal = isset($item["subtotal"]) ? (float)$item["subtotal"] : 0;

        $totalCalculado += $subtotal;

        $yAntes = $pdf->GetY();

        $pdf->MultiCell(80, 10, $descripcion, 1, "L", 0, 0);
$pdf->MultiCell(20, 10, number_format($cantidad, 0), 1, "C", 0, 0);
$pdf->MultiCell(25, 10, $unidad, 1, "C", 0, 0);
$pdf->MultiCell(25, 10, "Q ".number_format($precioUnitario, 2), 1, "C", 0, 0);
$pdf->MultiCell(30, 10, "Q ".number_format($subtotal, 2), 1, "C", 0, 1);

        $alturaFila = $pdf->GetY() - $yAntes;
        if($alturaFila < 10){
            $pdf->SetY($yAntes + 10);
        }
    }

} else {

    $pdf->Cell(84, 10, "", 1, 0, "L");
    $pdf->Cell(20, 10, "", 1, 0, "C");
    $pdf->Cell(20, 10, "", 1, 0, "C");
    $pdf->Cell(24, 10, "", 1, 0, "C");
    $pdf->Cell(22, 10, "", 1, 1, "C");
}

if($totalGeneral <= 0){
    $totalGeneral = $totalCalculado;
}

/* =============================
   TOTAL
============================= */
$pdf->SetFont("helvetica", "B", 11);
$pdf->Cell(150, 10, "Total:", 0, 0, "R");
$pdf->Cell(30, 10, "Q ".number_format($totalGeneral, 2), 1, 1, "C");

/* =============================
   FIRMA
============================= */
$pdf->Ln(28);

$yFirma = $pdf->GetY();
$pdf->Line(18, $yFirma, 100, $yFirma);

$pdf->Ln(3);
$pdf->SetFont("helvetica", "", 10);
$pdf->Cell(82, 6, "FIRMA DE RECIBIDO", 0, 1, "C");

while (ob_get_level() > 0) {
    ob_end_clean();
}

$pdf->Output("pedido_".$codigo.".pdf", "I");
