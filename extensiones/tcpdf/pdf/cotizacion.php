<?php

ob_start();

ini_set("memory_limit", "512M");
set_time_limit(120);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set("display_errors", 1);

require_once "../../../controladores/cotizaciones.controlador.php";
require_once "../../../modelos/cotizaciones.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "tcpdf_include.php";

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

class MYPDF extends TCPDF {}

$pdf = new MYPDF("P", "mm", "LETTER", true, "UTF-8", false);

$pdf->SetCreator("POS");
$pdf->SetAuthor("SISTEMA POS");
$pdf->SetTitle("Cotización ".$codigo);
$pdf->SetSubject("Cotización");
$pdf->SetKeywords("cotizacion, sistema, pos");

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(12, 10, 12);
$pdf->SetAutoPageBreak(true, 12);
$pdf->AddPage();

$logoPath = __DIR__ . "/images/logo_chonay.png";

/* =============================
   COLORES
============================= */
$verdeR = 110;
$verdeG = 168;
$verdeB = 52;

/* =============================
   LINEA SUPERIOR
============================= */
$pdf->SetDrawColor($verdeR, $verdeG, $verdeB);
$pdf->SetLineWidth(0.8);
$pdf->Line(18, 14, 198, 14);

/* =============================
   ENCABEZADO
============================= */

/* LOGO IZQUIERDA */
if(file_exists($logoPath)){
    $pdf->Image($logoPath, 18, 18, 35, 0); 
}

/* TITULO */
$pdf->SetXY(60, 18);
$pdf->SetFont("helvetica", "B", 16);
$pdf->Cell(90, 8, "SISTEMA POS", 0, 1, "C");

/* TEXTO DEBAJO DEL TITULO */
$pdf->SetX(60);
$pdf->SetFont("helvetica", "", 11);
$pdf->Cell(90, 6, "Barrio el Centro, Dolores, Peten", 0, 1, "C");

$pdf->SetX(60);
$pdf->Cell(90, 6, "Tel: ", 0, 1, "C");

$pdf->SetX(60);
$pdf->Cell(90, 6, "CORREO", 0, 1, "C");


/* =============================
   CAJA DOCUMENTO DERECHA
============================= */

$pdf->SetDrawColor($verdeR, $verdeG, $verdeB);
$pdf->SetLineWidth(0.6);

$pdf->SetXY(154, 18);

$pdf->SetFont("helvetica", "B", 8);
$pdf->SetFillColor(255,255,255);
$pdf->Cell(36,6,"DOCUMENTO",1,2,"C");

$pdf->SetFont("helvetica","B",13);
$pdf->SetFillColor($verdeR,$verdeG,$verdeB);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(36,9,"COTIZACION",1,2,"C",1);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont("helvetica","",8);
$pdf->Cell(36,6,"No. ".$codigo,1,2,"L");
$pdf->Cell(36,6,"Fecha: ".$fecha,1,2,"L");
$pdf->Cell(36,6,"NIT: ".$nitCliente,1,2,"L");
/* =============================
   DATOS CLIENTE
============================= */
$pdf->SetDrawColor(190, 190, 190);
$pdf->SetLineWidth(0.5);
$pdf->SetFillColor(245, 245, 245);

/* Fila 1 */
$pdf->SetXY(18, 72);
$pdf->SetFont("helvetica", "B", 9);
$pdf->Cell(45, 7, "Nombre:", 1, 0, "L", 1);

$pdf->SetFont("helvetica", "", 9);
$pdf->Cell(135, 7, $nombreCliente, 1, 1, "L", 0);

/* Fila 2 */
$pdf->SetX(18);
$pdf->SetFont("helvetica", "B", 9);
$pdf->Cell(45, 7, "NIT / DPI:", 1, 0, "L", 1);

$pdf->SetFont("helvetica", "", 9);
$pdf->Cell(135, 7, $nitCliente, 1, 1, "L", 0);

/* Fila 3 */
$pdf->SetX(18);
$pdf->SetFont("helvetica", "B", 9);
$pdf->Cell(45, 7, "Direccion / referencia:", 1, 0, "L", 1);

$pdf->SetFont("helvetica", "", 9);
$pdf->Cell(135, 7, $direccionCliente, 1, 1, "L", 0);

/* =============================
   TABLA PRODUCTOS
============================= */
$pdf->SetY(104);

$pdf->SetFont("helvetica", "B", 10);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor($verdeR, $verdeG, $verdeB);
$pdf->SetFillColor($verdeR, $verdeG, $verdeB);

$pdf->Cell(25, 8, "CANTIDAD", 1, 0, "C", 1);
$pdf->Cell(85, 8, "DESCRIPCION", 1, 0, "C", 1);
$pdf->Cell(35, 8, "PRECIO UNIT.", 1, 0, "C", 1);
$pdf->Cell(35, 8, "TOTAL", 1, 1, "C", 1);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(190, 190, 190);
$pdf->SetFont("helvetica", "", 10);

$totalCalculado = 0;

if(is_array($detalle) && count($detalle) > 0){

    foreach($detalle as $item){

        $cantidad = isset($item["cantidad"]) ? (float)$item["cantidad"] : 0;
        $descripcion = isset($item["descripcion_item"]) ? $item["descripcion_item"] : "";
        $precioUnitario = isset($item["precio_unitario"]) ? (float)$item["precio_unitario"] : 0;
        $subtotal = isset($item["subtotal"]) ? (float)$item["subtotal"] : 0;

        $totalCalculado += $subtotal;

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->MultiCell(25, 10, number_format($cantidad, 0), 1, "C", 0, 0);
        $pdf->MultiCell(85, 10, $descripcion, 1, "L", 0, 0);
        $pdf->MultiCell(35, 10, "Q ".number_format($precioUnitario, 2), 1, "C", 0, 0);
        $pdf->MultiCell(35, 10, "Q ".number_format($subtotal, 2), 1, "C", 0, 1);
    }

} else {

    $pdf->Cell(25, 10, "", 1, 0, "C");
    $pdf->Cell(85, 10, "", 1, 0, "L");
    $pdf->Cell(35, 10, "", 1, 0, "C");
    $pdf->Cell(35, 10, "", 1, 1, "C");
}

if($totalGeneral <= 0){
    $totalGeneral = $totalCalculado;
}

/* =============================
   TOTAL
============================= */
$pdf->Ln(12);
$pdf->SetDrawColor($verdeR, $verdeG, $verdeB);
$pdf->SetLineWidth(0.8);
$pdf->SetFont("helvetica", "B", 12);

$pdf->Cell(110, 10, "TOTAL", 1, 0, "C");
$pdf->Cell(70, 10, "Q ".number_format($totalGeneral, 2), 1, 1, "C");

/* =============================
   OBSERVACIONES
============================= */
$pdf->Ln(10);
$pdf->SetFont("helvetica", "B", 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(40, 6, "Observaciones:", 0, 1, "L");

/* =============================
   FIRMAS
============================= */
$pdf->Ln(24);
$pdf->SetDrawColor(70, 70, 70);
$pdf->SetLineWidth(0.4);

$yFirmas = $pdf->GetY();

$pdf->Line(34, $yFirmas, 84, $yFirmas);
$pdf->Line(122, $yFirmas, 172, $yFirmas);

$pdf->Ln(3);
$pdf->SetFont("helvetica", "B", 9);
$pdf->Cell(78, 6, "sistema pos", 0, 0, "C");
$pdf->Cell(10, 6, "", 0, 0, "C");
$pdf->Cell(78, 6, "RECIBIDO POR CLIENTE", 0, 1, "C");

/* =============================
   NOTA FINAL
============================= */
$pdf->Ln(10);
$pdf->SetFont("helvetica", "I", 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 6, "SOMOS ASESORIA TECNICA RESPONSABLE...", 0, 1, "C");

if(ob_get_length()){
    ob_end_clean();
}
$pdf->Output("cotizacion_".$codigo.".pdf", "I");
