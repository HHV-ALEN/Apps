<?php
// Inicio de sesión
ini_set('session.gc_maxlifetime', 3600); // 1 hour
session_set_cookie_params(3600); // Set cookie lifetime to match
session_start();
include("../../Back/config/config.php"); // --> Conexión con Base de datos
/// Habilitar la muestra de errores:

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de  datos

if (isset($_SESSION['alerta_estado'])) {
    echo "<div id='alertaBanner' class='alerta fade-in'>
              {$_SESSION['alerta_estado']}
            </div>";
    unset($_SESSION['alerta_estado']); // Limpiar mensaje para que no se repita
}


$conn = connectMySQLi();

//print_r($_SESSION);
$CarpetaContenedora = '../Back/Files/'; // Carpeta contenedora de los archivoss
$rutaWeb = "../Back/Files/img/";

$Nombre_Completo = $_SESSION['Name'];
$Tipo_Usuario = $_SESSION['Departamento'];

$id_salida = $_GET['id'];
$Id_Salida_Original = $id_salida;
//echo "<br> Id Salida: " . $id_salida;

//echo "<strong>Salida No. </strong>" . $id_salida;
//echo "<br> ****************************************************************** <br>";

/// Consulta de la Tabla "salida"
$Salida_query = "SELECT * FROM salidas WHERE Id = '$id_salida'";
$Salida_result = mysqli_query($conn, $Salida_query);
$Salida = mysqli_fetch_array($Salida_result);
$id_salida = $Salida['Id'];
$id_cliente = $Salida['Id_Cliente'];
$Nombre_Cliente = $Salida['Nombre_Cliente'];
$id_status = $Salida['Id_Status'];
$Estado = $Salida['Estado'];
$Estado_Original_DeSalida = $Salida['Estado'];
$Id_Sucursal = $Salida['Id_Sucursal'];
$Sucursal = $Salida['Sucursal'];
//echo "<strong>TABLA SALIDA:</strong>";
//echo "<br> - Cliente: " . $id_cliente;
//echo "<br> - Status: " . $id_status;
//echo "<br> - Nombre: " . $Nombre_Cliente ;
//echo "<br> - Fecha: " . $fecha;
//echo "<br> - Comentario: " . $comentario;
//echo "<br> - Sucursal: " . $id_sucursal;
//echo "<br> - Estado: " . $Estado;

// Consulta en la tabla "cliente"
//echo "<br> Id Cliente: " . $id_cliente;
$Cliente_query = "SELECT * FROM clientes WHERE Id = $id_cliente";
$Cliente_result = mysqli_query($conn, $Cliente_query);
$Cliente = mysqli_fetch_array($Cliente_result);
$nombre_cliente = $Cliente['Nombre'] ?? 'N/A';
$rfc = $Cliente['RFC'] ?? 'N/A';
$clave_sap = $Cliente['Clave_Sap'] ?? 'N/A';

//echo "<br> en minusculas: nombre_cliente: " . $nombre_cliente;


/// Consulta en la tabla "entrega_factura" para obtener el folio de la orden `entrega_factura_refactor`
$Orden_query = "SELECT * FROM entregas WHERE Id_Salida = '$id_salida'";
$Orden_result = mysqli_query($conn, $Orden_query);
// Iterar para obtener todos los registros
$Facturas = array();
//echo "<br><br><strong>ORDEN DE FACTURACION:</strong>";
while ($Orden = mysqli_fetch_array($Orden_result)) {
    $Id_Salida = $Orden['Id_Salida'];
    $OrdenDeVenta = $Orden['Id_Orden_Venta'];
    $Id_Entrega = $Orden['Id_Entrega'];
    $Partida = $Orden['Partida'];
    $Id_Factura = $Orden['Id_Factura'];
    $Id_Cliente = $Orden['Id_Cliente'];
    $Cliente_Nombre = $Orden['Cliente_Nombre'];
    $Archivo = $Orden['Archivo'];
    $Facturas[$Id_Salida] = $Archivo;
    //echo "<br> - Orden de Venta: " . $OrdenDeVenta;
    //echo "<br> - Folio Factura: " . $Id_Factura;
    //echo "<br> - Folio Entrega: " . $Id_Entrega;
    //echo "<br> - Archivo: " . $Archivo;

}
//echo "<br>..................................................................";

$rutaWeb_Factura = "C:/xampp/htdocs/archivos/pdf/";
// MOSTRAR FACTURAS Y EN EL NOMBRE USAR UN LINK PARA DESCARGAR EL ARCHIVO
foreach ($Facturas as $Factura) {
    /// Si el archivo existe 
    if (file_exists($rutaWeb_Factura . $Factura)) {
        /// Verificar que el archivo no exista ya en la carpeta contenedora
        if (file_exists($CarpetaContenedora . 'Facturas/' . $Factura)) {
            //echo "<br>*** El archivo " . $Factura . " ya existe en la carpeta contenedora  ***<br>";
        } else {
            //echo "<br> El archivo " . $Factura . " no existe en la carpeta contenedora";
            // Copiar la imagen en la Carpeta Archivos
            copy($rutaWeb_Factura . $Factura, $CarpetaContenedora . 'Facturas/' . $Factura);
            if (file_exists($CarpetaContenedora . 'Facturas/' . $Factura)) {
                //echo "<br> Se ha copiado el archivo " . $Factura . "<br>";
            } else {
                //echo "<br> No se pudo copiar el archivo " . $Factura;
            }
        }
        /// Mostar las imagenes en la página
        //echo "<a href='" . $CarpetaContenedora . 'Facturas/' . $Factura . "' download>Descargar Factura</a>";
    } else {
        //echo "<br> No existe";
    }
}

//echo "<br> ------------------------------------------------------------------ ";
//echo "<br> Id Salida: " . $id_salida;
// Consultar los procesos de la salida en la tabla "actualizaciones_estados"
$Procesos_query = "SELECT * FROM bitacora WHERE Id_Salida = '$id_salida' ORDER BY Fecha DESC";
$Procesos_result = mysqli_query($conn, $Procesos_query);
/// Iterar para obtener todos los registros
//echo "<br><br><strong>(BITACORA) PROCESOS DE LA SALIDA:</strong>";
$Procesos_Array = array();
while ($Procesos = mysqli_fetch_array($Procesos_result)) {
    $De_A_Procesos = $Procesos['Accion'];
    $Fecha = $Procesos['Fecha'];
    $Usuario_Responsable = $Procesos['Responsable'];
    $Procesos_Array[] = array('De_A' => $De_A_Procesos, 'Fecha' => $Fecha, 'Usuario_Responsable' => $Usuario_Responsable);
    //echo "<br> - De_A: " . $De_A_Procesos;
    //echo "<br> - Fecha: " . $Fecha;
    //echo "<br> - Usuario Responsable: " . $Usuario_Responsable;
    //echo "<br>";
}

$Folios_Anidados = array();
$Folios_Anidados_query = "SELECT * FROM fusion_etiquetas WHERE Salida_Base = '$id_salida'";
// consultar la tabla "imagen" para obtener las imagenes de la salida
$Imagen_query = "SELECT * FROM imagen WHERE id_salida = '$id_salida'";
$Imagen_result = mysqli_query($conn, $Imagen_query);
/// Iterar para obtener todos los registros relacionados a esa id_salida
//echo "<br><br><strong>Imagenes:</strong>";
$Imagenes = array();
$rutaWeb = "../Back/Files/img/";
// Back\Files\img\Claude-monet-landscape-near-montecarlo.jpg
// Mostrar detalles de imágenes
while ($Imagen = mysqli_fetch_array($Imagen_result)) {
    $id_imagen = $Imagen['Id_imagen'];
    $nombre_imagen = $Imagen['nombre'];
    /// Arreglo [id_imagen] = nombre_imagen
    $Imagenes[$id_imagen] = $nombre_imagen;
    //echo "<br> - ID de la imagen: " . $id_imagen;
    //echo "<br> - Nombre de la imagen: " . $nombre_imagen;
    //echo "<br>";
}

// Mostrar cada imagen en la página
foreach ($Imagenes as $imagen) {
    /// Si el archivo existe 
    if (file_exists($rutaWeb . $imagen)) {
        //echo "<br> Archivo: " . $imagen . "<br>";
        /// Verificar que el archivo no exista ya en la carpeta contenedora
        if (file_exists($CarpetaContenedora . 'img/' . $imagen)) {
            //echo "<br>*** El archivo ya existe en la carpeta contenedora  ***<br>";
        } else {
            //echo "<br> El archivo no existe en la carpeta contenedora";
            // Copiar la imagen en la Carpeta Archivos
            copy($rutaWeb . $imagen, $CarpetaContenedora . 'img/' . $imagen);
            if (file_exists($CarpetaContenedora . 'img/' . $imagen)) {
                //echo "<br> Archivo copiado<br>";
            } else {
                //echo "<br> No se pudo copiar el archivo";
            }
        }
        /// Mostar las imagenes en la página
        //echo "<img src='" . $CarpetaContenedora . 'Imagenes/' . $imagen . "' width='200' height='200'>";
    } else {
        //echo "<br> No existe";
    }
}
// Reiniciar el puntero de la consulta para volver a recorrerlo
mysqli_data_seek($Imagen_result, 0);
//echo "<br> ****************************************************************** <br>";

$target_dir = "../Back/Files/img/"; // Carpeta donde se guardará la imagen
$isGerente = ($_SESSION['User_Id'] == 34 || $_SESSION['User_Id'] == 1 || $_SESSION['User_Id'] == 33 || $_SESSION['User_Id'] == 10);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles - Salida No. <?php echo $id_salida; ?></title>
    <link rel="icon" type="image/png" href="../../Front/Img/Icono-A.png" />
    <!-- FontAwesome 6 (Última versión estable) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/detalles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

</head>
<?php require_once("../../Front/navbar.php"); ?>


<!-- Contenido de la página -->
<div class="container my-4">
    <!-- Title Section -->
    <div class="text-center mb-5">
        <h2 class="display-4 font-weight-bold" style="font-family: 'Roboto', sans-serif; color: #2c3e50;">
            Salida No. <?php echo $id_salida; ?> - <?php echo $Estado; ?>
        </h2>
        <!-- <p class="lead text-muted" style="font-family: 'Roboto', sans-serif;">
            Estado actual: <span class="badge bg-primary"><?php echo $Estado; ?></span>
        </p> -->
    </div>

    <!-- Contenedor con Scroll -->
    <div class="timeline-container">
        <div class="timeline">
            <!-- Progress Bar -->
            <div class="progress-bar" id="progressBar"></div>
            <div class="step" id="step1">
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="label">Entrega</div>
            </div>
            <div class="step" id="step2">
                <div class="icon"><i class="fas fa-box"></i></div>
                <div class="label">Empaque</div>
            </div>
            <div class="step" id="step3">
                <div class="icon"><i class="fas fa-qrcode"></i></div>
                <div class="label">Facturación</div>
            </div>
            <div class="step" id="step4">
                <div class="icon"><i class="fas fa-truck"></i></div>
                <div class="label">Logística</div>
            </div>
            <div class="step" id="step5">
                <div class="icon"><i class="fas fa-route"></i></div>
                <div class="label">Ruta</div>
            </div>
            <div class="step" id="step6">
                <div class="icon"><i class="fas fa-envelope-open-text"></i></div>
                <div class="label">Envíos</div>
            </div>
            <div class="step" id="step7">
                <div class="icon"><i class="fas fa-check"></i></div>
                <div class="label">Completado</div>
            </div>
        </div>
    </div>
    <?php
    // Definir el mapeo de estados
    $estadoMap = [
        21 => 1,
        22 => 2,
        23 => 3,
        24 => 4,
        25 => 5,
        26 => 6,
        27 => 7
    ];
    // Asegurar que el id_status existe y tiene un mapeo
    $estadoActual = isset($estadoMap[$id_status]) ? $estadoMap[$id_status] : 1;
    //echo "Estado Actual: " . $estadoActual;
    ?>

    <hr>
    <!-- Botón para Entrega si aplica -->
    <?php
    if ($_SESSION['Departamento'] == "Chofer" && $Estado == 'A Ruta') { ?>
        <div class="text-center">
            <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#etiquetaModal">
                <i class="fas fa-check-circle"></i> Entregar Pedido
            </button>
        </div>

        <!-- Modal de entrega de pedido -->
        <div class="modal fade" id="etiquetaModal" tabindex="-1" aria-labelledby="etiquetaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="etiquetaModalLabel">Etiqueta de Salida</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- FORMULARIO EN EL LUGAR CORRECTO -->
                    <form action="../Back/Entregas/entregaChofer.php" method="POST" id="form_agregar_empaque">
                        <input type="hidden" name="Id_Salida" value="<?php echo $id_salida; ?>">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="FolioSalida" class="form-label">Folio:</label>
                                    <input type="text" class="form-control" id="FolioSalida" name="Folio" value="<?php echo $id_salida; ?>" readonly>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="Entrega" class="form-label">Estado de la Entrega:</label>
                                    <select class="form-select" name="EstadoEntrega" id="Entrega" style="cursor: pointer;">
                                        <option value="Entrega A Cliente">Entrega A Cliente</option>
                                        <option value="Entrega A Transporte">Entrega A Transporte</option>
                                        <option value="No Entregado">No Entregado</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="Comentario" class="form-label">Comentario:</label>
                                    <input type="text" class="form-control" id="Comentario" name="Comentario" placeholder="Agrega un comentario...">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php } ?>
    <br>
    <!-- Información De la preguia -->
    <div class="container">
        <div class="col-md-12">
            <div class="card-header text-white d-flex justify-content-between align-items-center py-3"
                style="background-color: #0099ff; border-radius: 10px 10px 1px 1px;">

                <!-- Título centrado -->
                <div class="flex-grow-1 text-center">
                    <h5 class="mb-0">Información de la Pre-guía</h5>
                </div>

                <!-- Botón a la derecha, que se muestra solo en el estado Envíos -->
                <?php if ($Estado == 'Envios' && $_SESSION['Departamento'] == 'Logistica' || $Estado == 'Envíos' && $_SESSION['Departamento'] == 'Logistica') { ?>
                    <div>
                        <?php
                        // Botón para registrar Ruta de envio [1]
                        /// Verificar la tabla doc_preguia

                        $DocPreguia_query = "SELECT * FROM doc_preguia WHERE Id_Salida = '$id_salida'";
                        $DocPreguia_result = mysqli_query($conn, $DocPreguia_query);
                        if (mysqli_num_rows($DocPreguia_result) > 0) {
                            $DocPreguia = mysqli_fetch_array($DocPreguia_result);
                            $Folio_Doc = $DocPreguia['Folio_Doc'] ?? 'N/A';
                            $Id_Preguia = $DocPreguia['Id_Preguia'] ?? 'N/A';
                        ?>
                            <!-- Boton para abrir modal de registrar ruta de envio -->
                            <button class="btn btn-light btn-sm me-3" data-bs-toggle="modal" data-bs-target="#modalEnvios2">
                                <i class="bi bi-send"></i> Registrar Ruta de Envío
                            </button>
                        <?php
                        }
                        ?>


                    </div>
                <?php
                }
                ?>
            </div>

            <?php
            $Chofer_query = "SELECT * FROM preguia WHERE Id_Salida = '$id_salida'";
            $Chofer_result = mysqli_query($conn, $Chofer_query);
            $Chofer = mysqli_fetch_array($Chofer_result);
            $Id_Salida = $Chofer['Id_Salida'] ?? 'N/A';
            $Cliente = $Chofer['Cliente'] ?? 'N/A';
            $Cliente_Intermedio = $Chofer['Cliente_Intermedio'] ?? 'N/A';
            $Paqueteria = $Chofer['Paqueteria'] ?? 'N/A';
            $ChoferNombre = $Chofer['Chofer'] ?? 'N/A';
            $Tipo_Flete = $Chofer['Tipo_Flete'] ?? 'N/A';
            $Metodo_Pago = $Chofer['Metodo_Pago'] ?? 'N/A';
            $Tipo_Doc = $Chofer['Tipo_Doc'] ?? 'N/A';
            $Fecha_Preguia = $Chofer['Fecha'] ?? 'N/A';
            $Fecha_Entregado = $Chofer['Fecha_Entregado'] ?? 'N/A';
            ?>

            <div class="card-body p-4 text-center">
                <div class="row">
                    <!-- Información Principal -->
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-user"></i> Cliente</h6>
                        <p class="mb-1"><strong>Nombre:</strong> <?php echo $Cliente; ?></p>
                        <p class="mb-1"><strong>RFC:</strong> <?php echo $rfc; ?></p>
                        <p class="mb-1"><strong>Clave SAP:</strong> <?php echo $clave_sap; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-truck"></i> Transporte</h6>

                        <?php
                        $OtrasOpciones = [
                            "Cliente Pasa",
                            "Entregado por Vendedor",
                            "Proveedor Recolecta"
                        ];
                        if (in_array($ChoferNombre, $OtrasOpciones)) {
                        ?>
                            <p class="mb-1"><strong>Envio:</strong> <?php echo $ChoferNombre; ?></p>
                            <p class="mb-1"><strong>Fecha Entregado:</strong> <?php echo $Fecha_Entregado; ?></p>
                        <?php
                        } else {
                        ?>
                            <p class="mb-1"><strong>Chofer:</strong> <?php echo $ChoferNombre; ?></p>
                            <p class="mb-1"><strong>Tipo de Flete:</strong> <?php echo $Tipo_Flete; ?></p>
                            <p class="mb-1"><strong>Paquetería:</strong> <?php echo $Paqueteria; ?></p>
                        <?php
                        }

                        ?>
                    </div>
                </div>
                <hr>
                <!-- Información Adicional -->
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-file-alt"></i> Documento</h6>
                        <p class="mb-1"><strong>Tipo:</strong> <?php echo $Tipo_Doc; ?></p>
                        <?php
                        /// Traer Información de la tabla "envios"
                        // Id_Salida, Costo, Folio_Guia, Tipo, Responsable
                        $Envios_query = "SELECT * FROM envios WHERE Id_Salida = '$id_salida'";
                        $Envios_result = mysqli_query($conn, $Envios_query);
                        $Envios = mysqli_fetch_array($Envios_result);
                        if (mysqli_num_rows($Envios_result) > 0) {

                            $Costo = $Envios['Costo'] ?? 'N/A';
                            $Folio_Guia = $Envios['Folio_Guia'] ?? 'N/A';
                            $Tipo_Envio = $Envios['Tipo'] ?? 'N/A';
                            $Fecha_Envio = $Envios['Fecha'] ?? 'N/A';

                            if ($Fecha_Envio != 'N/A') {
                                $Fecha_Envio = date('d-m-Y', strtotime($Fecha_Envio));
                                echo "<p class='mb-1'><strong>Fecha de Envío:</strong> $Fecha_Envio</p>";
                            } else {
                                echo "<p class='mb-1'><strong>Costo de Envío:</strong> $ $Costo</p>";
                                echo "<p class='mb-1'><strong>Folio de Guía:</strong> $Folio_Guia</p>";
                                echo "<p class='mb-1'><strong>Tipo de Envío:</strong> $Tipo_Envio</p>";
                            }
                        } else {
                            // Consultar la tabla doc_preguia
                            $DocPreguia_query = "SELECT * FROM doc_preguia WHERE Id_Salida = '$id_salida'";
                            $DocPreguia_result = mysqli_query($conn, $DocPreguia_query);
                            if (mysqli_num_rows($DocPreguia_result) > 0) {
                                $DocPreguia = mysqli_fetch_array($DocPreguia_result);

                                $Tipo_Doc = $DocPreguia['Tipo_Doc'];
                                $Folio_Doc_Guia = $DocPreguia['Folio_Doc'] ?? 'N/A';
                                $Id_Preguia = $DocPreguia['Id_Preguia'] ?? 'N/A';
                                $Fecha_Envio = $DocPreguia['Fecha'] ?? 'N/A';
                                $Costo = $DocPreguia['Costo_Directo'] ?? 'N/A';
                                $Fecha_Final = $DocPreguia['Fecha_Final'] ?? 'N/A';
                                $Guia_Reembarque = $DocPreguia['Guia_Reembarque'] ?? 'N/A';
                                $Costo_Reembarque = $DocPreguia['Costo_Reembarque'] ?? 'N/A';

                                if ($Tipo_Doc == 'Reembarque') {
                                    echo "<p class='mb-1'><strong>Folio de Guía:</strong> $Folio_Doc_Guia</p>";
                                    echo "<p class='mb-1'><strong>Id Preguia:</strong> $Id_Preguia</p>";
                                    echo "<p class='mb-1'><strong>Fecha de Envío:</strong> $Fecha_Envio</p>";
                                    echo "<p class='mb-1'><strong>Folio de Guía Reembarque:</strong> $Guia_Reembarque</p>";
                                    echo "<p class='mb-1'><strong>Costo de Envío Reembarque:</strong> $ $Costo_Reembarque</p>";
                                    echo "<p class='mb-1'><strong>Fecha de Envío Reembarque:</strong> $Fecha_Final</p>";
                                } elseif ($Tipo_Doc == 'Directo') {
                                    echo "<p class='mb-1'><strong>Folio de Guía:</strong> $Folio_Doc_Guia</p>";
                                    echo "<p class='mb-1'><strong>Id Preguia:</strong> $Id_Preguia</p>";
                                    echo "<p class='mb-1'><strong>Fecha de Envío:</strong> $Fecha_Envio</p>";
                                    echo "<p class='mb-1'><strong>Costo de Envío:</strong> $ $Costo</p>";
                                    echo "<p class='mb-1'><strong>Fecha de Envío:</strong> $Fecha_Final</p>";
                                } elseif ($Tipo_Doc == 'Ruta') {
                                    echo "<p class='mb-1'><strong>Fecha de Envío:</strong> $Fecha_Envio</p>";
                                }
                            }
                        }

                        ?>
                        <br><br><br>

                        <?php



                        $CarpetaContenedora = '../Back/Entregas/Files/Docs/';
                        // Consultar la tabla "entregas" en el Atributo DocumentoDeEntrega, Si existe, mostrar botón para descargar
                        $DocEntrega_query = "SELECT * FROM entregas WHERE Id_Salida = '$id_salida'";
                        $DocEntregaResult = mysqli_query($conn, $DocEntrega_query);
                        if (mysqli_num_rows($DocEntregaResult) > 0) {
                            $DocEntrega = mysqli_fetch_array($DocEntregaResult);
                            $DocumentoDeEntrega = $DocEntrega["DocumentoDeEntrega"];

                            if (!empty($DocumentoDeEntrega) && $DocumentoDeEntrega != "N/A") {
                                // Verificar si el archivo existe en la carpeta contenedora
                                if (file_exists($CarpetaContenedora . $DocumentoDeEntrega)) {
                                    echo "<p class='mb-1'><strong>Documento de Entrega:</strong></p>";
                                    echo "<a href='" . $CarpetaContenedora . $DocumentoDeEntrega . "' download class='btn btn-success btn-sm'>";
                                    echo "<i class='fas fa-file-download'></i> Descargar Documento";
                                    echo "</a>";
                                } else {
                                    echo "<p class='mb-1'>No se encontró el documento de entrega.</p>";
                                }
                            } else {
                                echo "<p class='mb-1'>No se ha registrado un documento de entrega.</p>";

                                if ($isGerente) {
                        ?>
                                    <p class="mb-1"><strong>Documento de Entrega:</strong></p>
                                    <form action="../Back/Entregas/Files/DocumentoDeEntrega.php?id_salida=<?php echo $id_salida; ?>" method="POST" enctype="multipart/form-data">
                                        <input type="file" name="DocumentoDeEntrega" class="form-control mb-2" required>
                                        <input type="hidden" name="Id_Salida" value="<?php echo $id_salida; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-upload"></i> Subir Documento
                                        </button>
                                    </form>
                        <?php
                                }
                            }
                        }
                        ?>

                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-store"></i> Sucursal</h6>
                        <p class="mb-1"><strong>Status:</strong> <?php echo $Estado; ?></p>
                        <p class="mb-1"><strong>Sucursal:</strong> <?php echo $Sucursal; ?></p>
                        <p class="mb-1"><strong>Método de Pago:</strong> <?php echo $Metodo_Pago; ?></p>

                        <hr>

                        <!-- Botón para Descargar el formato de Pre-Guía -->
                        <div class="row">
                            <p class="mb-1 center-text"><strong>Formatos de Descarga:</strong> </p>
                            <div class="col-md-6">
                                <?php
                                /// Verificar que cuando se encuentre el archivo registrado, aparezca el botón de descargar
                                // Consulta en la tabla "preguia_refactor"
                                $Formato_query = "SELECT * FROM preguia WHERE Id_Salida = '$id_salida'";
                                $Formato_result = mysqli_query($conn, $Formato_query);
                                /// Si no hay registro, Mostrar "No Disponible"
                                if (mysqli_num_rows($Formato_result) > 0) {
                                ?>
                                    <a href="../Back/download_formato.php?Id_Salida=<?php echo $id_salida; ?>&Tipo_Doc=<?php echo $Tipo_Doc; ?>"
                                        class="btn btn-success btn-sm">
                                        <i class="fas fa-file-download"></i> Descargar Formato de Pre-Guía
                                    </a>
                                <?php
                                } else {
                                    echo "No disponible";
                                }
                                ?>
                            </div>
                            <?php
                            /// Mostrar Detalles del envio solo hasta que se haya registrado la preguia
                            // si la consulta a la tabla preguia tiene resultados, mostrar el boton, si no, no mostrarlo
                            $Preguia_Consult = "SELECT * FROM preguia WHERE Id_Salida = $id_salida";
                            $Preguia_Consult_result = mysqli_query($conn, $Preguia_Consult);
                            if (mysqli_num_rows($DocPreguia_result) > 0) {
                            ?>
                                <div class="col-md-6">
                                    <a href="../Back/Etiqueta_Detalle.php?Id_Salida=<?php echo $id_salida; ?>"
                                        class="btn btn-success btn-sm">
                                        <i class="fas fa-file-download"></i> Formato Detalles de Envio
                                    </a>
                                </div>

                            <?php

                            } else {
                            ?>
                                <div class="col-md-6">
                                    <a
                                        class="btn btn-success btn-sm disabled">
                                        <i class="fas fa-file-download"></i> Formato Detalles de Envio
                                    </a>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <hr>
            </div>
        </div>
    </div>

    <!-- Modal para registrar ruta de envio -->
    <div class="modal fade" id="modalEnvios2" tabindex="-1" aria-labelledby="modalEnviosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnviosLabel">Registrar Ruta de Envío</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="../Back/Preguia/segundoRegistro.php?id_salida=<?php echo $id_salida; ?>">
                    <div class="modal-body">

                        <!-- Hidden Inputs -->
                        <?php
                        echo "<br><strong>Tipo de documento: </strong>" . $Tipo_Doc;
                        ?>
                        <input type="hidden" name="Id_Salida" value="<?php echo $id_salida; ?>">
                        <input type="hidden" name="Tipo_Doc" value="<?php echo $Tipo_Doc; ?>">

                        <?php
                        /// Mostrar campos segun el Tipo_Doc
                        if ($Tipo_Doc == 'Directo') {
                        ?>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="Tipo_doc" class="form-label">Tipo de Documento: </label>
                                    <input type="text" class="form-control" id="Tipo_doc" name="Tipo_doc" value="<?php echo $Tipo_Doc; ?>" readonly>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="folio_doc" class="form-label">Folio de la Guia:</label>
                                    <input type="text" class="form-control" id="folio_doc" name="folio_doc">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="costo" class="form-label">Costo: </label>
                                    <input type="numeric" class="form-control" id="costo" name="costo">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="FechaFinal" class="form-label">Fecha Final:</label>
                                    <input type="date" class="form-control" id="FechaFinal" name="FechaFinal">
                                </div>
                            </div>
                        <?php
                        } elseif ($Tipo_Doc == 'Reembarque') {
                        ?>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="Tipo_doc" class="form-label">Tipo de Documento: </label>
                                    <input type="text" class="form-control" id="Tipo_doc" name="Tipo_doc" value="<?php echo $Tipo_Doc; ?>" readonly>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="folio_doc" class="form-label">Folio de la Guia: </label>
                                    <input type="text" class="form-control" id="folio_doc" name="folio_doc">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="costo_reembarque" class="form-label">Costo Reembarque: </label>
                                    <input type="numeric" class="form-control" id="costo" name="costo">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="GuiaReembarque" class="form-label">Guía Reembarque:</label>
                                    <input type="text" class="form-control" id="GuiaReembarque" name="GuiaReembarque">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label for="FechaFinal" class="form-label">Fecha Final:</label>
                                    <input type="date" class="form-control" id="FechaFinal" name="FechaFinal">
                                </div>
                            </div>

                        <?php
                        } elseif ($Tipo_Doc == 'Ruta') {
                        ?>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label for="FechaFinal" class="form-label">Fecha Final:</label>
                                    <input type="date" class="form-control" id="FechaFinal" name="FechaFinal">
                                </div>
                            </div>
                        <?php
                        }
                        ?>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Registrar Ruta de Envío</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <br>

    <!-- Información General y Comentarios -->
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <!-- Información del Cliente -->
                <div class="card mb-3">
                    <div class="text-center card-header bg-primary text-white">
                        <h5>Información General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> <?php echo $nombre_cliente; ?></p>
                                <p><strong>RFC:</strong> <?php echo $rfc; ?></p>

                            </div>
                            <div class="col-md-6">
                                <p><strong>Sucursal:</strong> <?php echo $Sucursal; ?></p>
                                <p><strong>Clave SAP:</strong> <?php echo $clave_sap; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comentarios -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="text-center card-header bg-warning text-black">
                        <h5>Comentarios</h5>
                    </div>
                    <div class="card-body" style="max-height: 100px; overflow-y: scroll;" id="comentariosContainer">
                        <!-- Aquí se mostrarán los comentarios dinámicamente -->
                    </div>
                    <div class="card-footer d-flex">
                        <input type="text" id="nuevoComentario" class="form-control me-2"
                            placeholder="Escribe un comentario...">
                        <button class="btn btn-primary" onclick="agregarComentario()">Comentar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Etiquetas -->
    <div class="container mb-3">
        <!-- Card for the Table -->
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h5>Etiquetas</h5>

                <?php
                /// Solo el tipo "Empaque" puede hacer uso de estos botones:
                if ($Tipo_Usuario == 'Empaque' || $isGerente) {
                ?>
                    <div class="row justify-content-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <!-- Button to Open Modal for Fusionar Etiquetas -->
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalFusionEtiquetas">
                                <i class="bi bi-plus-lg"></i> Fusionar Etiquetas
                            </button>

                        </div>
                        <div class="col-md-6">
                            <!-- Button to Open Modal for Consolidar Etiquetas -->
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalConsolidarEtiquetas">
                                <i class="bi bi-plus-square"></i> Consolidar Etiquetas
                            </button>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <?php
            $Arreglo_OrdenVenta_Registrados = array();
            ?>
            <div class="card-body">
                <div class="row">

                    <!-- Table for Etiqueta Base -->
                    <div class="col-md-12">
                        <h3 class="text-center">Etiqueta Base</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="text-center">
                                        <th>Orden de Venta</th>
                                        <th>Entrega</th>
                                        <th>Partida</th>
                                        <th>Cliente</th>
                                        <th>Factura</th>
                                        <th>Archivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Query to display Etiqueta Base information
                                    $entrega_query = "SELECT * FROM entregas WHERE Id_Salida = '$Id_Salida_Original'";
                                    $entrega_result = mysqli_query($conn, $entrega_query);
                                    while ($entrega = mysqli_fetch_array($entrega_result)) {
                                        $Id_Contenido = $entrega['Id'];
                                        $Id_Salida_B = $entrega['Id_Salida'];
                                        $Id_Orden_Venta_B = $entrega['Id_Orden_Venta'];
                                        array_push($Arreglo_OrdenVenta_Registrados, $Id_Orden_Venta_B);
                                        $Id_Entrega_B = $entrega['Id_Entrega'];
                                        $Partida_B = $entrega['Partida'];
                                        $Id_Factura_B = $entrega['Id_Factura'];
                                        $Archivo = $entrega['Archivo'];
                                        $Cliente_Nombre_B = $entrega['Cliente_Nombre'];
                                    ?>
                                        <tr>
                                            <td><?php echo $Id_Orden_Venta_B; ?></td>
                                            <td><?php echo $Id_Entrega_B; ?></td>
                                            <td><?php echo $Partida_B; ?></td>
                                            <td><?php echo $Cliente_Nombre_B; ?></td>
                                            <td><?php
                                                if ($Archivo == '0') {
                                                    echo " - Sin Factura";
                                                } elseif ($Id_Factura == '1') {
                                                    echo "<strong>Remisión</strong>";
                                                } else {
                                                    echo $Id_Factura;
                                                }
                                                ?></td>
                                            <td>

                                                <?php
                                                if ($Archivo != '0') {
                                                ?>
                                                    <div class="row">
                                                        <div class="col-xs-6 text-left">
                                                            <a href="../Back/Files/Facturas/<?php echo $Archivo; ?>"
                                                                class="btn btn-success btn-sm"
                                                                download>
                                                                <i class="bi bi-cloud-arrow-down"></i> Descargar Factura
                                                            </a>
                                                        </div>
                                                        <?php
                                                    }

                                                    if ($Estado_Original_DeSalida == 'Facturación') {
                                                        /// Se muestra boton de agregar Factura solo si esta en el estado de facturación
                                                        if ($_SESSION['Departamento'] == 'Facturación' || $isGerente) {
                                                            if ($Id_Factura_B == '0' && $Archivo == '0') {
                                                        ?>
                                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                                    data-bs-target="#modalFacturacion_BASE<?php echo $Id_Salida_B; ?>">
                                                                    <i class="fas fa-file-pdf"></i> Asignar Factura
                                                                </button>
                                                                <a href="../Back/Facturas/addRemision.php?Id_Salida=<?php echo $Id_Salida_B; ?>&Parametro=Base" class="btn btn-outline-primary btn-sm">Agregar Remisión</a>

                                                            <?php
                                                            } elseif ($Id_Factura_B == '1' && $Archivo == 'REMISION') {
                                                            ?>
                                                                <a href="../Back/Facturas/deleteRemision.php?Id_Salida=<?php echo $Id_Salida_B; ?>&Parametro=Base"
                                                                    class="btn btn-danger btn-sm">
                                                                    <i class="bi bi-file-earmark-x"></i> Eliminar Remisión
                                                                </a>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <!-- Eliminar Factura -->
                                                                <div class="col-xs-6 text-right">
                                                                    <a href="../Back/Facturas/deleteFactura.php?Id_Salida=<?php echo $Id_Salida_B; ?>&Id_Factura=<?php echo $Id_Factura_B; ?>&Archivo=<?php echo $Archivo; ?>&Parametro=Base"
                                                                        class="btn btn-danger btn-sm">
                                                                        <i class="bi bi-file-earmark-x"></i> Eliminar Factura
                                                                    </a>
                                                                </div>
                                                    <?php
                                                            }
                                                        }
                                                    }
                                                    ?>


                                            </td>
                                            <td>
                                                <?php
                                                // Permitir Edición al area de Entrega Y Surtido, Empaque y A Raque
                                                if ($isGerente || $_SESSION['Departamento'] == 'Empaque' || $_SESSION['Departamento'] == 'Entrega y Surtido') {
                                                ?>
                                                    <!-- Botón para abrir modal para editar la información de la etiqueta Base -->
                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarEtiquetaBase<?php echo $Id_Contenido; ?>">
                                                        <i class="fas fa-edit"></i> Editar Etiqueta Base
                                                    </button>

                                                    <!-- Botón para eliminar con SweetAlert -->
                                                    <button class="btn btn-danger btn-sm eliminar-etiqueta-base"
                                                        data-id="<?php echo $Id_Contenido; ?>">
                                                        <i class="fas fa-trash-alt"></i> Eliminar
                                                    </button>
                                                <?php
                                                }
                                                ?>

                                                <!-- Modal para editar la información de la etiqueta Base -->
                                                <div class="modal fade "
                                                    id="modalEditarEtiquetaBase<?php echo $Id_Contenido; ?>" tabindex="-1"
                                                    aria-labelledby="modalEditarEtiquetaBaseLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalEditarEtiquetaBaseLabel">Editar
                                                                    Etiqueta Base <?php echo $Id_Contenido; ?> </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="../Back/Etiquetas/editEtiqueta.php?Id_Contenido=<?php echo $Id_Contenido; ?>&Id_Salida=<?php echo $id_salida; ?>" method="POST">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="Folio_Orden" class="form-label">Folio Orden de Venta:</label>
                                                                                <input type="text" class="form-control" id="Folio_Orden" name="Folio_Orden" value="<?php echo $Id_Orden_Venta_B; ?>" required>
                                                                            </div>

                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="Folio_Entrega" class="form-label">Folio Entrega:</label>
                                                                                <input type="text" class="form-control" id="Folio_Entrega" name="Folio_Entrega" value="<?php echo $Id_Entrega_B; ?>" required>
                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="Partida" class="form-label">Partida:</label>
                                                                        <input type="text" class="form-control" id="Partida" name="Partida" value="<?php echo $Partida_B; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <?php
                                                                        // Obtener nombres únicos de la tabla clientes
                                                                        $queryClientes = "SELECT DISTINCT Nombre FROM clientes ORDER BY Nombre ASC";
                                                                        $resultClientes = mysqli_query($conn, $queryClientes);
                                                                        ?>

                                                                        <div class="mb-3">
                                                                            <label for="Cliente_Nombre" class="form-label">Cliente Nombre:</label>
                                                                            <select class="form-select" id="Cliente_Nombre" name="Cliente_Nombre" required>
                                                                                <option value="">Seleccione un cliente</option>
                                                                                <?php
                                                                                while ($row = mysqli_fetch_assoc($resultClientes)) {
                                                                                    $nombre = $row['Nombre'];
                                                                                    $selected = ($nombre === $Cliente_Nombre_B) ? 'selected' : '';
                                                                                    echo "<option value=\"$nombre\" $selected>$nombre</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                    <?php
                                    } // End of loop
                                    ?>
                                </tbody>
                            </table>

                            <!-- Button trigger modal -->
                            <div class="mt-6">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarEntrega">
                                    ➕ Agregar Nueva Entrega
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="modalAgregarEntrega" aria-labelledby="modalAgregarEntregaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <form action="../Back/Entregas/Agregar_Entrega.php" method="POST">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="modalAgregarEntregaLabel">Agregar Nueva Etiqueta</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>

                                    </div>

                                    <div class="modal-body">
                                        <!-- Campo oculto para enviar el Id_Salida_Original -->
                                        <input type="hidden" name="id_salida_original" value="<?php echo $Id_Salida_Original; ?>">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_salida">Id Salida</label>
                                                    <input type="text" class="form-control" id="id_salida" name="id_salida" value="<?php echo $Id_Salida_Original; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_orden_venta">Id Orden de Venta</label>
                                                    <input type="text" class="form-control" id="id_orden_venta" name="id_orden_venta" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="id_entrega">Id Entrega</label>
                                                    <input type="text" class="form-control" id="id_entrega" name="id_entrega" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="partida" class="form-label">Partida</label>
                                                <div class="d-flex align-items-center">
                                                    <input type="number" class="form-control me-2" id="partida1" name="partida1" min="1"
                                                        max="99" step="1" style="width: 80px;" value="1" readonly>
                                                    <span>-</span>
                                                    <input type="number" class="form-control ms-2" id="partida2" name="partida2" min="1"
                                                        max="99" step="1" style="width: 80px;" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cliente">Cliente</label>
                                            <input type="text" class="form-control" id="cliente" value="<?php echo htmlspecialchars($nombre_cliente); ?>" disabled>
                                            <input type="hidden" name="cliente" value="<?php echo htmlspecialchars($nombre_cliente); ?>">
                                            <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($id_cliente); ?>">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-success">Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Etiquetas Fusionadas -->
    <div class="container mb-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Table for Etiquetas Fusionadas -->
                    <div class="col-md-12">
                        <h3 class="text-center">Etiquetas Fusionadas</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Orden de Venta</th>
                                        <th>Entrega</th>
                                        <th>Partida</th>
                                        <th>Cliente</th>
                                        <th>Factura</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $ids_excluidos = [];
                                    // Query to display Etiquetas Fusionadas information
                                    $fusion_query = "SELECT * FROM etiquetas_fusionadas WHERE Salida_Base = '$id_salida'";
                                    $fusion_result = mysqli_query($conn, $fusion_query);
                                    while ($fusion = mysqli_fetch_array($fusion_result)) {
                                        $Id_Relacion = $fusion['Id_Relacion_Salida'];
                                        $ids_excluidos[] = $fusion['Id_Relacion_Salida'];
                                        $Id_Orden_Venta_Fusion = $fusion['Orden_Venta'];
                                        $Id_Entrega_Fusion = $fusion['Entrega'];
                                        $Partida_Fusion = $fusion['Partida'];
                                        $Id_Factura_Fusion = $fusion['Id_Factura'];
                                        $Archivo_Fusion = $fusion['Archivo'];
                                        $Cliente_Nombre_Fusion = $fusion['Cliente_Nombre'];

                                    ?>
                                        <tr>
                                            <td><?php echo $Id_Orden_Venta_Fusion; ?></td>
                                            <td><?php echo $Id_Entrega_Fusion; ?></td>
                                            <td><?php echo $Partida_Fusion; ?></td>
                                            <td><?php echo $Cliente_Nombre_Fusion; ?></td>
                                            <td><?php

                                                if ($Id_Factura_Fusion == '0') {
                                                    echo "Sin Factura";
                                                } elseif ($Id_Factura_Fusion == '1') {
                                                    echo " Remisión";
                                                } else {
                                                    echo $Id_Factura_Fusion;
                                                }
                                                ?></td>
                                            <td>
                                                <?php
                                                if ($Estado_Original_DeSalida == 'Facturación' && $_SESSION['Departamento'] == 'Facturación') {
                                                    if ($Id_Factura_Fusion == '0' && $Archivo_Fusion == '0') {
                                                ?>
                                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                            data-bs-target="#modalFacturacion<?php echo $Id_Relacion; ?>">
                                                            <i class="fas fa-file-pdf"></i> Asignar Factura
                                                        </button>
                                                        <a href="../Back/Facturas/addRemision.php?Id_Salida_Relacion=<?php echo $Id_Relacion; ?>&Parametro=Fusion&Id_Salida=<?php echo $id_salida; ?>"
                                                            class="btn btn-outline-primary btn-sm">Agregar Remisión</a>
                                                    <?php
                                                    } elseif ($Id_Factura_Fusion == '1' && $Archivo_Fusion == 'REMISION') {
                                                        // Boton para eliminar remision
                                                    ?>
                                                        <a href="../Back/Facturas/deleteRemision.php?Id_Salida_Relacion=<?php echo $Id_Relacion; ?>&Id_Salida=<?php echo $id_salida; ?>&Parametro=Fusion" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-file-earmark-x"></i> Eliminar Remisión
                                                        </a>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <a href="../Back/Files/Facturas/<?php echo $Archivo_Fusion; ?>"
                                                            class="btn btn-success btn-sm" download>
                                                            <i class="bi bi-cloud-arrow-down"></i> Descargar Factura
                                                        </a>
                                                        <a href="../Back/Facturas/deleteFactura.php?Id_Salida_Relacion=<?php echo $Id_Relacion; ?>&Id_Factura=<?php echo $Id_Factura_Fusion; ?>&Archivo=<?php echo $Archivo_Fusion; ?>&Parametro=Fusion&Id_Salida=<?php echo $id_salida; ?>"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="bi bi-file-earmark-x"></i> Eliminar Factura
                                                        </a>
                                                <?php
                                                    }
                                                }
                                                ?>
                                                <?php
                                                if ($isGerente || $_SESSION['Departamento'] == 'Empaque' || $_SESSION['Departamento'] == 'Entrega y Surtido') {
                                                ?>
                                                    <!-- Botón para abrir modal para editar la información de la etiqueta Fusionada -->
                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarEtiquetaFusionada<?php echo $Id_Relacion; ?>">
                                                        <i class="fas fa-edit"></i> Editar Etiqueta Fusionada
                                                    </button>
                                                    <!-- Botón para un sweetalert para eliminar la etiqueta Fusionada -->
                                                    <!-- Botón para eliminar con SweetAlert -->
                                                    <button class="btn btn-danger btn-sm eliminar-etiqueta-fusionada"
                                                        data-id="<?php echo $Id_Relacion; ?>">
                                                        <i class="fas fa-trash-alt"></i> Eliminar Fusionada
                                                    </button>
                                                <?php
                                                }
                                                ?>

                                                <!-- Modal para editar la información de la etiqueta fusionada -->
                                                <div class="modal fade "
                                                    id="modalEditarEtiquetaFusionada<?php echo $Id_Relacion; ?>" tabindex="-1"
                                                    aria-labelledby="modalEditarEtiquetaFusionadaLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalEditarEtiquetaFusionadaLabel">Editar
                                                                    Etiqueta Fusionada <?php echo $Id_Relacion; ?> </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body ">
                                                                <form action="../Back/Etiquetas/editEtiquetaFusionada.php?Id_Relacion_Salida=<?php echo $Id_Relacion; ?>&Id_Salida=<?php echo $id_salida; ?>" method="POST">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="Folio_Orden" class="form-label">Folio Orden de Venta:</label>
                                                                                <input type="text" class="form-control" id="Folio_Orden" name="Folio_Orden" value="<?php echo $Id_Orden_Venta_Fusion; ?>" required>
                                                                            </div>

                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="Folio_Entrega" class="form-label">Folio Entrega:</label>
                                                                                <input type="text" class="form-control" id="Folio_Entrega" name="Folio_Entrega" value="<?php echo $Id_Entrega_Fusion; ?>" required>
                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="Partida" class="form-label">Partida:</label>
                                                                        <input type="text" class="form-control" id="Partida" name="Partida" value="<?php echo $Partida_Fusion; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <?php
                                                                        // Obtener nombres únicos de la tabla clientes
                                                                        $queryClientes = "SELECT DISTINCT Nombre FROM clientes ORDER BY Nombre ASC";
                                                                        $resultClientes = mysqli_query($conn, $queryClientes);
                                                                        ?>

                                                                        <div class="mb-3">
                                                                            <label for="Cliente_Nombre" class="form-label">Cliente Nombre:</label>
                                                                            <select class="form-select" id="Cliente_Nombre" name="Cliente_Nombre" required>
                                                                                <option value="">Seleccione un cliente</option>
                                                                                <?php
                                                                                while ($row = mysqli_fetch_assoc($resultClientes)) {
                                                                                    $nombre = $row['Nombre'];
                                                                                    $selected = ($nombre === $Cliente_Nombre_Fusion) ? 'selected' : '';
                                                                                    echo "<option value=\"$nombre\" $selected>$nombre</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                        <!-- Modal para esta fila -->
                                        <div class="modal fade" id="modalFacturacion<?php echo $Id_Relacion; ?>" tabindex="-1" aria-labelledby="modalFacturacion<?php echo $Id_Relacion; ?>Label" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="modalFacturacion<?php echo $Id_Relacion; ?>Label">
                                                            Asignar Factura a la Etiqueta Fusionada
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="../Back/Facturas/addFactura.php?id_salida_Relacion=<?php echo $Id_Relacion; ?>&Parametro=Fusion&id_salida=<?php echo $id_salida; ?>" method="POST" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="Folio_Orden_<?php echo $Id_Relacion; ?>" class="form-label">Folio Orden:</label>
                                                                    <input type="text" class="form-control" id="Folio_Orden_<?php echo $Id_Relacion; ?>" name="Folio_Orden" value="<?php echo $Id_Orden_Venta_Fusion; ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="Folio_Entrega_<?php echo $Id_Relacion; ?>" class="form-label">Folio Entrega:</label>
                                                                    <input type="text" class="form-control" id="Folio_Entrega_<?php echo $Id_Relacion; ?>" name="Folio_Entrega" value="<?php echo $Id_Entrega_Fusion; ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="archivo_<?php echo $Id_Relacion; ?>" class="form-label">Seleccione una Factura:</label>
                                                                    <input type="file" class="form-control" name="archivo" id="archivo_<?php echo $Id_Relacion; ?>" accept="application/pdf" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="FolioFactura_<?php echo $Id_Relacion; ?>" class="form-label">Folio Factura:</label>
                                                                    <input type="text" class="form-control" id="FolioFactura_<?php echo $Id_Relacion; ?>" name="FolioFactura" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <button type="submit" class="btn btn-primary">Subir Factura</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php
                                    } // End of loop
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Etiquetas Consolidadas -->
    <div class="container mb-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Table for Etiquetas Consolidadas -->
                    <div class="col-md-12">
                        <h3 class="text-center">Etiquetas Consolidadas</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="text-center">
                                        <th>Orden de venta</th>
                                        <th>Entrega</th>
                                        <th>Partida</th>
                                        <th>Destino</th>
                                        <th>Cliente</th>
                                        <th>Factura</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    /// Query to display Consolidados
                                    $Consolidados_Query = "SELECT * FROM consolidados WHERE Id_Base = '$id_salida'";
                                    $Consolidados_result = mysqli_query($conn, $Consolidados_Query);
                                    while ($Consolidados = mysqli_fetch_array($Consolidados_result)) {
                                        $Id_Consolidado = $Consolidados['Id'];
                                        $Id_Salida_Consolidada = $Consolidados['Id_salida_consolidada'];
                                        $Destino_Consolidado = $Consolidados['Destino'];
                                        $Nombre_Cliente_Consolidado = $Consolidados['Nombre_Cliente'];
                                        $Entrega_Consolidado = $Consolidados['Id_Entrega'];
                                        $Orden_Venta_Consolidado = $Consolidados['Orden_Venta'];
                                        $Partida_Consolidado = $Consolidados['Partida'];
                                        $Id_Factura_Consolidado = $Consolidados['Id_Factura'];
                                        $Archivo_Consolidado = $Consolidados['Archivo'];
                                    ?>
                                        <tr>
                                            <td><?php echo $Orden_Venta_Consolidado; ?></td>
                                            <td><?php echo $Entrega_Consolidado; ?></td>
                                            <td><?php echo $Partida_Consolidado; ?></td>
                                            <td><?php echo $Destino_Consolidado; ?></td>
                                            <td><?php echo $Nombre_Cliente_Consolidado; ?></td>
                                            <td><?php
                                                if ($Id_Factura_Consolidado == '0') {
                                                    echo "Sin Factura";
                                                } elseif ($Id_Factura_Consolidado == '1') {
                                                    echo " Remisión";
                                                } else {
                                                    echo $Id_Factura_Consolidado;
                                                }
                                                ?></td>
                                            <td>
                                                <?php
                                                if ($Estado_Original_DeSalida == 'Facturación' && $_SESSION['Departamento'] == 'Facturación') {
                                                    if ($Id_Factura_Consolidado == '0' && $Archivo_Consolidado == '0') {
                                                ?>
                                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                            data-bs-target="#modalFacturacionConsolidada<?php echo $Id_Consolidado; ?>">
                                                            <i class="fas fa-file-pdf"></i> Asignar Factura
                                                        </button>
                                                        <a href="../Back/Facturas/addRemision.php?Id_Salida=<?php echo $id_salida; ?>&Parametro=Consolidado&Id_Salida_Relacion=<?php echo $Id_Consolidado; ?>" class="btn btn-outline-primary btn-sm">Agregar Remisión</a>
                                                    <?php
                                                    } elseif ($Id_Factura_Consolidado == '1' && $Archivo_Consolidado == 'REMISION') {
                                                        // Boton para eliminar remision
                                                    ?>
                                                        <a href="../Back/Facturas/deleteRemision.php?Id_Salida=<?php echo $id_salida; ?>&Parametro=Consolidado&Id_Salida_Relacion=<?php echo $Id_Consolidado; ?>" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-file-earmark-x"></i> Eliminar Remisión
                                                        </a>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <a href="../Back/Files/Facturas/<?php echo $Archivo_Consolidado; ?>"
                                                            class="btn btn-success btn-sm" download>
                                                            <i class="bi bi-cloud-arrow-down"></i> Descargar Factura
                                                        </a>
                                                        <a href="../Back/Facturas/deleteFactura.php?Id_Salida_consolidado=<?php echo $Id_Consolidado; ?>&Parametro=Consolidado&Id_Salida=<?php echo $id_salida; ?>&Id_Factura=<?php echo $Id_Factura_Consolidado; ?>" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-file-earmark-x"></i> Eliminar Factura
                                                        </a>
                                                <?php
                                                    }
                                                }
                                                ?>
                                                <?php
                                                if ($isGerente || $_SESSION['Departamento'] == 'Empaque' || $_SESSION['Departamento'] == 'Entrega y Surtido') {
                                                ?>
                                                    <!-- Botón para abrir modal para editar la información de la etiqueta Consolidada -->
                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarEtiquetaConsolidada<?php echo $Id_Consolidado; ?>">
                                                        <i class="fas fa-edit"></i> Editar Etiqueta Consolidada
                                                    </button>

                                                    <!-- Botón para abrir sweet alert para eliminar etiqueta Consolidada -->
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm eliminar-etiqueta-consolidada"
                                                        data-id="<?php echo $Id_Consolidado; ?>">
                                                        <i class="fas fa-trash-alt"></i> Eliminar Etiqueta Consolidada
                                                    </button>
                                                <?php
                                                }
                                                ?>

                                                <!-- Modal para editar la información de la etiqueta consolidada -->
                                                <div class="modal fade "
                                                    id="modalEditarEtiquetaConsolidada<?php echo $Id_Consolidado; ?>" tabindex="-1"
                                                    aria-labelledby="modalEditarEtiquetaConsolidadaLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalEditarEtiquetaConsolidadaLabel">Editar
                                                                    Etiqueta Consolidada <?php echo $Id_Salida_Consolidada; ?> </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="../Back/Etiquetas/editEtiquetaConsolidada.php?Id_Relacion_Salida=<?php echo $Id_Salida_Consolidada; ?>&Id_Salida=<?php echo $id_salida; ?>" method="POST">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="Folio_Orden" class="form-label">Folio Orden de Venta:</label>
                                                                                <input type="text" class="form-control" id="Folio_Orden" name="Folio_Orden" value="<?php echo $Orden_Venta_Consolidado; ?>" required>
                                                                            </div>

                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="mb-3">
                                                                                <label for="Folio_Entrega" class="form-label">Folio Entrega:</label>
                                                                                <input type="text" class="form-control" id="Folio_Entrega" name="Folio_Entrega" value="<?php echo $Entrega_Consolidado; ?>" required>
                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="Partida" class="form-label">Partida:</label>
                                                                        <input type="text" class="form-control" id="Partida" name="Partida" value="<?php echo $Partida_Consolidado; ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <?php
                                                                        // Obtener nombres únicos de la tabla clientes
                                                                        $queryClientes = "SELECT DISTINCT Nombre FROM clientes ORDER BY Nombre ASC";
                                                                        $resultClientes = mysqli_query($conn, $queryClientes);
                                                                        ?>

                                                                        <div class="mb-3">
                                                                            <label for="Cliente_Nombre" class="form-label">Cliente Nombre:</label>
                                                                            <select class="form-select" id="Cliente_Nombre" name="Cliente_Nombre" required>
                                                                                <option value="">Seleccione un cliente</option>
                                                                                <?php
                                                                                while ($row = mysqli_fetch_assoc($resultClientes)) {
                                                                                    $nombre = $row['Nombre'];
                                                                                    $selected = ($nombre === $Nombre_Cliente_Consolidado) ? 'selected' : '';
                                                                                    echo "<option value=\"$nombre\" $selected>$nombre</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                        <!-- Modal para esta fila -->
                                        <div class="modal fade" id="modalFacturacionConsolidada<?php echo $Id_Consolidado; ?>" tabindex="-1" aria-labelledby="modalFacturacionConsolidada<?php echo $Id_Consolidado; ?>Label" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="modalFacturacionConsolidada<?php echo $Id_Consolidado; ?>Label">
                                                            Asignar Factura a la Etiqueta Consolidada <?php echo $Id_Salida_Consolidada; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="../Back/Facturas/addFactura.php?id_salida_Relacion=<?php echo $Id_Consolidado; ?>&Parametro=Consolidado&id_salida=<?php echo $id_salida; ?>" method="POST" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="Folio_Orden_<?php echo $Id_Salida_Consolidada; ?>" class="form-label">Folio Salida Consolidada:</label>
                                                                    <input type="text" class="form-control" id="Folio_Orden_<?php echo $Id_Salida_Consolidada; ?>" name="Folio_Orden" value="<?php echo $Id_Salida_Consolidada; ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="Folio_Entrega_<?php echo $Id_Consolidado; ?>" class="form-label">Folio Entrega:</label>
                                                                    <input type="text" class="form-control" id="Folio_Entrega_<?php echo $Id_Consolidado; ?>" name="Folio_Entrega" value="<?php echo $Entrega_Consolidado; ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="archivo_<?php echo $Id_Consolidado; ?>" class="form-label">Seleccione una Factura:</label>
                                                                    <input type="file" class="form-control" name="archivo" id="archivo_<?php echo $Id_Consolidado; ?>" accept="application/pdf" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="FolioFactura_<?php echo $Id_Consolidado; ?>" class="form-label">Folio Factura:</label>
                                                                    <input type="text" class="form-control" id="FolioFactura_<?php echo $Id_Consolidado; ?>" name="FolioFactura" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <button type="submit" class="btn btn-primary">Subir Factura</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de registros de compra con la misma Orden de Venta 

    <div class="container mt-4">
        <div class="card mb-4 shadow">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0">🧾 Registros Articulos de Compras</h5>
            </div>

            <div class="card-body px-4">
                <?php
                $lista_ordenes = implode(',', array_map('intval', $Arreglo_OrdenVenta_Registrados));
                $sql = "SELECT * FROM supply_compras WHERE OrdenVenta IN ($lista_ordenes)";
                $resultado = $conn->query($sql);
                ?>

                <?php if ($resultado->num_rows > 0): ?>
                    <div class="row gx-4 gy-4">
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card h-100 border-primary shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">🛒 Orden de Compra: <?= htmlspecialchars($row['OrdenCompra']) ?></h6>
                                        <p class="card-text mb-1">👤 <strong>Cliente:</strong> <?= htmlspecialchars($row['NombreCliente']) ?></p>
                                        <p class="card-text mb-1">📅 <strong>Fecha:</strong> <?= date("d/m/Y", strtotime($row['FechaEntregaCliente'])) ?></p>
                                        <p class="card-text">📦 <strong>Número De Articulo:</strong> <?= htmlspecialchars($row['NoDeArticulo']) ?></p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <span class="badge bg-info text-dark">🔁 OV: <?= htmlspecialchars($row['OrdenVenta']) ?></span>
                                            </div>
                                            <div class="col-md-6">
                                                BOTON PARA VER DETALLES LOS DETALLES DEL REGISTRO EN OTRA VENTANA 
                                                <a href="detallesCompra.php?id=<?= htmlspecialchars($row['Id']) ?>">Detalles del Articulo</a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mt-3">❌ No se encontraron registros relacionados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
                -->

    <!-- Información del Empaque -->
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="text-center card-header bg-secondary text-white align-items-center">
                <h5>Información del Empaque</h5>
                <?php
                /// Solo el tipo "Empaque" puede hacer uso de estos botones:
                if ($Tipo_Usuario == 'Empaque') {
                ?>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarEmpaque">
                        <i class="fas fa-plus"></i> Agregar Empaque
                    </button>
                    <a href="../Back/printEtiqueta.php?Id_Salida=<?php echo $id_salida; ?>"
                        class="btn btn-primary btn-sm"
                        target="_blank">
                        <i class="fas fa-print"></i> Imprimir Etiqueta
                    </a>
                <?php
                }
                ?>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <?php
                    $Empaque_query = "SELECT * FROM contenido WHERE Id_Salida = '$id_salida'";
                    $Empaque_result = mysqli_query($conn, $Empaque_query);
                    while ($Empaque = mysqli_fetch_array($Empaque_result)) {
                        $Id_Contenido = $Empaque['Id'];
                        $Contenedor = $Empaque['Contenedor'];
                        $Cantidad = $Empaque['Cantidad'];
                    ?>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-center"><?php echo $Contenedor; ?></h6>
                                    <p class="card-text"><strong>Cantidad:</strong> <?php echo $Cantidad; ?></p>
                                    <hr>
                                    <div class="text-center">
                                        <a href="../Back/Empaque/deleteEmpaqueAnidado.php?Id_Salida=<?php echo $id_salida; ?>&Id_Contenido=<?php echo $Id_Contenido; ?>"
                                            class="btn btn-danger btn-sm">Eliminar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>

                <div class="row text-center">
                    <?php
                    // Consultar para mostrar información de los empaques de registros Anidados
                    $Anidados = "SELECT * FROM etiquetas_fusionadas WHERE Salida_Base = '$id_salida'";
                    $Anidados_Result = mysqli_query($conn, $Anidados);
                    $Ids_Relacionados = array();

                    while ($Anidado = mysqli_fetch_array($Anidados_Result)) {
                        $Id_Relacion = $Anidado['Id_Relacion_Salida'];
                        $Ids_Relacionados[] = $Id_Relacion;
                    }

                    // Consultar la tabla de contenido para obtener los registros relacionados
                    foreach ($Ids_Relacionados as $Id_Relacion) {
                        $Empaque_query = "SELECT * FROM contenido WHERE Id_Salida = '$Id_Relacion'";
                        $Empaque_result = mysqli_query($conn, $Empaque_query);

                        while ($Empaque = mysqli_fetch_array($Empaque_result)) {
                            $Id_Contenido = $Empaque['Id'];
                            $Contenedor = $Empaque['Contenedor'];
                            $Cantidad = $Empaque['Cantidad'];
                            $Id_Salida = $Empaque['Id_Salida'];
                    ?>
                            <div class="col-12 col-sm-6 col-lg-3 mb-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-center"><?php echo $Contenedor; ?></h6>
                                        <p class="card-text"><strong>Cantidad:</strong> <?php echo $Cantidad; ?></p>
                                        <hr>
                                        <a href="Back/deleteEmpaqueAnidado.php?Id_Salida=<?php echo $id_salida; ?>&Id_Contenido=<?php echo $Id_Contenido; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                        <hr>
                                        <small class="text-muted">Fusionado de la Salida: <strong><?php echo $Id_Salida; ?></strong></small>
                                    </div>
                                </div>
                            </div>
                    <?php
                        } // Fin del ciclo
                    }

                    if (empty($Ids_Relacionados)) {
                        echo "<p class='text-center'>No se encuentran Registros</p>";
                    }
                    ?>
                </div>
                <br>
                <div class="row text-center">
                    <?php
                    $Ids_Relacionados = [];
                    // Consultar para mostrar información de los empaques de registros Anidados
                    $Anidados = "SELECT * FROM consolidados WHERE Id_Base = '$id_salida'";
                    $Anidados_Result = mysqli_query($conn, $Anidados);

                    while ($Anidado = mysqli_fetch_array($Anidados_Result)) {
                        $Id_Relacion = $Anidado['Id_salida_consolidada'];
                        $Ids_Relacionados[] = $Id_Relacion;
                    }
                    // Consultar la tabla de contenido para obtener los registros relacionados
                    foreach ($Ids_Relacionados as $Id_Relacion) {
                        $Empaque_query = "SELECT * FROM contenido WHERE Id_Salida = '$Id_Relacion'";
                        $Empaque_result = mysqli_query($conn, $Empaque_query);
                        while ($Empaque = mysqli_fetch_array($Empaque_result)) {
                            $Id_Contenido = $Empaque['Id'];
                            $Contenedor = $Empaque['Contenedor'];
                            $Cantidad = $Empaque['Cantidad'];

                    ?>
                            <div class="col-12 col-sm-6 col-lg-3 mb-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-center"><?php echo $Contenedor; ?></h6>
                                        <p class="card-text"><strong>Cantidad:</strong> <?php echo $Cantidad; ?></p>
                                        <hr>
                                        <a href="../Back/Empaque/deleteEmpaque.php?Id_Salida=<?php echo $id_salida; ?>&Id_Contenido=<?php echo $Id_Contenido; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                        <hr>
                                        <small class="text-muted">Consolidado de la Salida: <strong><?php echo $Id_Relacion; ?></strong></small>
                                    </div>
                                </div>
                            </div>

                    <?php
                        }
                    }
                    ?>
                </div>


            </div>
        </div>
    </div>

    <!-- Bitacora -->
    <div class="row">
        <!-- Bitácora del Folio Base -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="text-center card-header bg-info text-white">
                    <h5>Bitácora del Folio Base</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <ul class="list-group">

                        <?php while ($Procesos = array_shift($Procesos_Array)): ?>
                            <li class="list-group-item">
                                <strong>De_A:</strong> <?php echo $Procesos['De_A']; ?> <br>
                                <strong>Fecha:</strong> <?php echo $Procesos['Fecha']; ?> <br>
                                <strong>Usuario Responsable:</strong>
                                <?php echo $Procesos['Usuario_Responsable']; ?>
                            </li>
                        <?php endwhile; ?>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Galería de Imágenes -->
    <div class="card mb-3">
        <div class="card-header bg-dark text-white text-center d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Galería de Imágenes</h5>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalSubirImagen">
                <i class="fas fa-upload"></i> Subir Imagen
            </button>
        </div>

        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            <!-- Sección de Imágenes -->
            <?php function mostrarImagenes($imagenes, $target_dir)
            { ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($imagenes as $Imagen) {
                        $ruta_imagen = $target_dir . $Imagen['nombre']; ?>
                        <div class="col">
                            <div class="card h-100">

                                <!-- Si el nombre del archivo termina en .zip Mostrar un icono de Zip y no la foto-->

                                <?php if (pathinfo($Imagen['nombre'], PATHINFO_EXTENSION) == 'zip') { ?>
                                    <img src="/Front/Img/raricon.png" class="card-img-top img-thumbnail expandable-image"
                                        alt="<?php echo $Imagen['nombre']; ?>"
                                        style="height: 200px; object-fit: cover;">

                                <?php } else { ?>

                                    <img src="<?php echo $ruta_imagen; ?>"
                                        class="card-img-top img-thumbnail expandable-image"
                                        alt="<?php echo $Imagen['nombre']; ?>"
                                        style="height: 200px; object-fit: cover;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal"
                                        onclick="expandImage(this)">


                                <?php } ?>

                                <div class="card-body text-center">
                                    <h6 class="card-title text-truncate"> <?php echo $Imagen['nombre']; ?> </h6>

                                    <a href="<?php echo $ruta_imagen; ?>" download class="btn btn-primary btn-sm w-100 mt-2">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                    <a href="../Back/Imagenes/deleteImage.php?id_salida=<?php echo $Imagen['id_salida']; ?>&Nombre_Archivo=<?php echo $Imagen['nombre']; ?>"
                                        class="btn btn-danger btn-sm w-100 mt-2">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php
            function obtenerImagenes($conn, $query)
            {
                return mysqli_fetch_all(mysqli_query($conn, $query), MYSQLI_ASSOC);
            }

            // Obtener imágenes de la salida principal
            $imagenes_salida = obtenerImagenes($conn, "SELECT * FROM imagen WHERE id_salida = '$id_salida'");

            // Obtener imágenes fusionadas
            $Ids_Relacionados = obtenerImagenes($conn, "SELECT Id_Relacion_Salida FROM etiquetas_fusionadas WHERE Salida_Base = '$id_salida'");
            $imagenes_anidadas = [];
            foreach ($Ids_Relacionados as $Id) {
                $imagenes_anidadas = array_merge($imagenes_anidadas, obtenerImagenes($conn, "SELECT * FROM imagen WHERE id_salida = '{$Id['Id_Relacion_Salida']}'"));
            }

            // Obtener imágenes consolidadas
            $Ids_Consolidados = obtenerImagenes($conn, "SELECT Id_salida_consolidada FROM consolidados WHERE Id_Base = '$id_salida'");
            $imagenes_consolidadas = [];
            foreach ($Ids_Consolidados as $Id) {
                $imagenes_consolidadas = array_merge($imagenes_consolidadas, obtenerImagenes($conn, "SELECT * FROM imagen WHERE id_salida = '{$Id['Id_salida_consolidada']}'"));
            }

            // Si no hay imágenes en ninguna categoría, mostrar mensaje general
            if (empty($imagenes_salida) && empty($imagenes_anidadas) && empty($imagenes_consolidadas)) {
                echo "<p class='text-center'>No hay imágenes disponibles</p>";
            } else {
                if (!empty($imagenes_salida)) {
                    echo "<h4 class='text-center mt-3'>Imágenes Principales</h4>";
                    mostrarImagenes($imagenes_salida, $target_dir);
                }

                if (!empty($imagenes_anidadas)) {
                    echo "<h4 class='text-center mt-4'>Imágenes Fusionadas</h4>";
                    mostrarImagenes($imagenes_anidadas, $target_dir);
                }

                if (!empty($imagenes_consolidadas)) {
                    echo "<h4 class='text-center mt-4'>Imágenes Consolidadas</h4>";
                    mostrarImagenes($imagenes_consolidadas, $target_dir);
                }
            }
            ?>
        </div>
    </div>
</div>

<!--- Zona de Modales -->
<!-- Modal para Asignar Factura a la Etiqueta Base -->
<div class="modal fade" id="modalFacturacion_BASE<?php echo $Id_Salida_B; ?>" tabindex="-1"
    aria-labelledby="modalFacturacion_BASE<?php echo $Id_Salida_B; ?>Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFacturacion_BASE<?php echo $Id_Salida_B; ?>Label">
                    Asignar Factura a la Etiqueta Base
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="../Back/Facturas/addFactura.php?id_salida=<?php echo $Id_Salida_B; ?>&Parametro=Base" method="POST"
                    enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Folio_Orden" class="form-label">Folio Orden:</label>
                            <input type="text" class="form-control" id="Folio_Orden" name="Folio_Orden"
                                placeholder="Folio de la Orden" value="<?php echo $Id_Orden_Venta_B; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Folio_Entrega" class="form-label">Folio Entrega:</label>
                            <input type="text" class="form-control" id="Folio_Entrega" name="Folio_Entrega"
                                placeholder="Folio de la Entrega" value="<?php echo $Id_Entrega_B; ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="archivo" class="form-label">Seleccione una Factura:</label>
                            <input type="file" class="form-control" name="archivo" accept="application/pdf">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="FolioFactura" class="form-label">Folio Factura:</label>
                            <input type="text" class="form-control" id="FolioFactura" name="FolioFactura"
                                placeholder="Ingrese el folio de la factura..." required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Subir Factura</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar imagen expandida -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-body text-center">
                <img id="expandedImage" src="" class="img-fluid" alt="Expanded view">
            </div>
        </div>
    </div>
</div>

<!-- Modal para fusionar etiquetas -->
<div class="modal fade" id="modalFusionEtiquetas" tabindex="-1" aria-labelledby="modalFusionEtiquetasLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFusionEtiquetasLabel">Fusionar Etiquetas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../Back/Etiquetas/fusionEtiquetas.php?id_salida=<?php echo $id_salida; ?>" method="POST"
                    id="form_fusion" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <!-- Cliente Actual -->
                            <div class="mb-3">
                                <label for="Cliente_Actual" class="form-label">Cliente Actual</label>
                                <input type="text" class="form-control" id="Cliente_Actual" name="Cliente_Actual"
                                    value="<?php echo $nombre_cliente; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <!-- Input for Current Id_Salida -->
                            <div class="mb-3">
                                <label for="id_salida_actual" class="form-label">Id_Salida
                                    Actual</label>
                                <input type="text" class="form-control" id="id_salida_actual"
                                    name="id_salida_actual" value="<?php echo $id_salida; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <?php
                        ?>
                        <label for="Salida_Anidada_Id" class="form-label">Seleccione una opción:</label>

                        <!-- Seleccionar Unicamente registros de la tabla salida_refactor que tengan el mismo Cliente -->
                        <?php
                        $nombre_cliente = trim($nombre_cliente);
                        $id_salida = intval($id_salida);

                        $Sql_Same_Clients = "SELECT * FROM salidas WHERE Id != $id_salida 
  AND Nombre_Cliente = '" . mysqli_real_escape_string($conn, $nombre_cliente) . "'
  AND Id NOT IN (
    SELECT Id_Relacion_Salida FROM etiquetas_fusionadas
  )";
                        $Result_Same_Clients = mysqli_query($conn, $Sql_Same_Clients);
                        ?>


                        <select class="form-select" id="Salida_Anidada_Id" name="Salida_Anidada_Id" required>
                            <option value="">Selecciona una Etiqueta</option>
                            <?php
                            if (mysqli_num_rows($Result_Same_Clients) > 0) {
                                while ($row = mysqli_fetch_assoc($Result_Same_Clients)) {
                                    $Id_Salida = $row['Id'];
                                    $Nombre_Cliente = $row['Nombre_Cliente'];
                                    echo "<option value='$Id_Salida'>$Id_Salida - $Nombre_Cliente</option>";
                                }
                            } else {
                                echo "<option disabled>No hay otras etiquetas del mismo cliente</option>";
                            }
                            ?>
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="form_fusion" class="btn btn-primary">Fusionar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Subir Imagen -->
<div class="modal fade" id="modalSubirImagen" tabindex="-1" aria-labelledby="modalSubirImagenLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalSubirImagenLabel">Subir Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" action="../Back/Imagenes/addImagen.php?id_salida=<?php echo $_GET['id']; ?>"
                    method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="imagenes" class="form-label">Seleccionar Archivos</label>
                        <input type="file" class="form-control" id="imagenes" name="imagenes[]" multiple required>
                        <small class="form-text text-muted">Puedes seleccionar múltiples imágenes</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Subir Imágenes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Consolidar Etiquetas -->
<div class="modal fade" id="modalConsolidarEtiquetas" tabindex="-1" aria-labelledby="modalConsolidarEtiquetasLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalConsolidarEtiquetasLabel">Consolidar Etiquetas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../Back/Etiquetas/consolidarEtiqueta.php?id_salida=<?php echo $id_salida; ?>
                        " method="POST" id="form_consolidar">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="Destino" class="form-label">Destino</label>
                            <select class="form-select" name="Destino" id="Destino" required>
                                <option value="">Selecciona un destino</option>
                                <option value="ELECT IND ALEN SUC TIJUANA"> ELECT IND ALEN SUC TIJUANA
                                </option>
                                <option value="ELECT IND ALEN SUC VERACRUZ"> ELECT IND ALEN SUC VERACRUZ
                                </option>
                                <option value="PERSONAL ALEN INTELLIGENT"> PERSONAL ALEN INTELLIGENT
                                </option>
                                <option value="ELECT IND ALEN BODEGA"> ELECT IND ALEN BODEGA </option>
                                <option value="ELECT IND ALEN SUC MEXICO"> ELECT IND ALEN SUC MEXICO
                                </option>
                                <option value="ELECTRICA INDUSTRIAL ALEN"> ELECTRICA INDUSTRIAL ALEN
                                </option>
                                <option value="ALEN AUTOMATIZACION"> ALEN AUTOMATIZACION </option>
                                <option value="ALEN DEL NORTE"> ALEN DEL NORTE </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <?php
                            // Select con las opciones de las salidas
                            /// Excluir los Id que estan fusionados o el mismo Id
                            $Ids_Excluidos_para_consolidar = array();
                            $Ids_Excluidos_para_consolidar[] = $id_salida;
                            $fusion_query = "SELECT * FROM etiquetas_fusionadas WHERE Salida_Base = '$id_salida'";
                            $fusion_result = mysqli_query($conn, $fusion_query);
                            while ($fusion = mysqli_fetch_array($fusion_result)) {
                                $Id_Relacion = $fusion['Id_Relacion_Salida'];
                                $Ids_Excluidos_para_consolidar[] = $fusion['Id_Relacion_Salida'];
                            }

                            // Asegurarse de que el arreglo no esté vacío
                            if (!empty($Ids_Excluidos_para_consolidar)) {
                                $Ids_Excluidos_str = implode(",", $Ids_Excluidos_para_consolidar);
                                $Salidas_query = "SELECT * FROM salidas WHERE Id NOT IN ($Ids_Excluidos_str)";
                            } else {
                                $Salidas_query = "SELECT * FROM salidas"; // Si no hay IDs para excluir, selecciona todo
                            }

                            $Salidas_result = mysqli_query($conn, $Salidas_query);
                            ?>
                            <label for="Salida_Destino" class="form-label">Selecciona una Salida</label>
                            <select class="form-select" name="Salida_Destino" id="Salida_Destino" required>
                                <option value="">Selecciona una salida</option>
                                <?php
                                while ($Salida = mysqli_fetch_array($Salidas_result)) {
                                    $Id_Salida = $Salida['Id'];
                                    $Nombre_Cliente = $Salida['Nombre_Cliente'];
                                    echo "<option value='$Id_Salida'>$Id_Salida - $Nombre_Cliente</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <table class="table table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>ID Orden Venta</th>
                                    <th>ID Entrega</th>
                                    <th>Partida</th>
                                    <th>ID Factura</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_entregas">
                                <tr>
                                    <td colspan="4" class="text-center">Selecciona una salida para ver
                                        los
                                        datos</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">Consolidar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Información del Empaque -->
<div class="modal fade " id="modalAgregarEmpaque" tabindex="-1" aria-labelledby="modalAgregarEmpaqueLabel"
    aria-hidden="true">
    <form action="../Back/Empaque/addEmpaque.php?id_salida=<?php echo $id_salida; ?>" method="POST" id="form_agregar_empaque">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarEmpaqueLabel">Agregar Información del
                        Empaque
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body text-center">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="FolioSalida" class="form-label">Folio:</label>
                            <input type="text" class="form-control" id="Id_Salida" name="Id_Salida"
                                value="<?php echo $id_salida; ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="Cliente" class="form-label">Cliente:</label>
                            <input type="text" class="form-control" id="Cliente" name="Cliente"
                                value="<?php echo $nombre_cliente; ?>" readonly>
                        </div>
                    </div>

                    <!-- Contenedores Section -->
                    <div id="contenedores">
                        <div class="contenedor-item mb-3">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="id_contenedor" class="form-label">Contenedor</label>
                                    <select class="form-select contenedor-select" name="id_contenedor[]" required>
                                        <option value="">Selecciona un Contenedor</option>
                                        <option value="Caja">Caja</option>
                                        <option value="Paquete">Paquete</option>
                                        <option value="Rollo">Rollo</option>
                                        <option value="Carrete">Carrete</option>
                                        <option value="Tarima">Tarima</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <!-- Campo oculto para "Otro" -->
                                <div class="col-md-5 mb-3 contenedor-otro" style="display: none;">
                                    <label for="otro_contenedor" class="form-label">Especificar otro
                                        contenedor</label>
                                    <input type="text" class="form-control" name="otro_contenedor[]">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="Cantidad_contenedores" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" name="Cantidad_contenedores[]"
                                        required>
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <!-- No delete button for the first row -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Contenedor Button -->
                    <button type="button" class="btn btn-success" id="btnAgregarContenedor">Agregar
                        Contenedor</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
    </form>
</div>


<!-- Modal para la Ruta -->
<div class="modal fade" id="modalRuta" tabindex="-1" aria-labelledby="modalRutaLabel" aria-hidden="true">
    <form action="Back/addRuta.php?id_salida=<?php echo $id_salida; ?>" method="POST">
        <!-- Ensure modal is centered -->
        <div class="modal-dialog modal-dialog-centered"> <!-- Add modal-dialog-centered -->
            <div class="modal-content">
                <!-- Customized modal header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalRutaLabel">Estado del Envio:</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <select class="form-select" name="Estado" id="Estado" required>
                            <option value="">Selecciona un estado</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Entregado">Entregado</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <!-- Hidden field for extra information -->
                    <div class="mb-3" id="extraInfoField" hidden>
                        <label for="ExtraInfo" class="form-label">Agregar Estado del Envio:</label>
                        <input type="text" class="form-control" id="ExtraInfo" name="ExtraInfo">
                    </div>
                    <div class="mb-3">
                        <label for="Comentario" class="form-label">Comentario</label>
                        <input type="text" class="form-control" id="Comentario" name="Comentario">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Bundle JS (incluye Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Eliminar Etiqueta base con sweet alert -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.eliminar-etiqueta-base').forEach(btn => {
            btn.addEventListener('click', function() {
                const idContenido = this.getAttribute('data-id');
                const idSalida = <?= $id_salida ?>
                console.log("iD Salida: ", idSalida);

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Se eliminará la etiqueta base con ID ${idContenido}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirigir al archivo del back con el ID
                        window.location.href = `../Back/Etiquetas/eliminar_etiqueta.php?id=${idContenido}&id_salida=${idSalida}&Tipo=Base`;
                    }
                });
            });
        });
    });
</script>

<!-- Eliminar Etiqueta Fusionada -->
<script>
    $(document).on('click', '.eliminar-etiqueta-fusionada', function(e) {
        e.preventDefault(); // evita submit accidental
        const idRelacion = $(this).data('id');
        const idSalida = <?= $id_salida ?>

        console.log("iD Salida: ", idSalida);
        console.log("Id Relacionada:", idRelacion);

        Swal.fire({
            title: '¿Eliminar etiqueta?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../Back/Etiquetas/eliminar_etiqueta.php?idfusionada=${idRelacion}&id_salida=${idSalida}&Tipo=Fusionada`;
            }
        });
    });

    $(document).on('click', '.eliminar-etiqueta-consolidada', function(e) {
        e.preventDefault(); // evita submit accidental
        const idConsolidado = $(this).data('id');
        const idSalida = <?= $id_salida ?>

        console.log("Id Consolidado: ", idConsolidado);
        console.log("Id Salida: ", idSalida);

        Swal.fire({
            title: '¿Eliminar etiqueta?',
            text: 'Eliminar Id: ' + idConsolidado + ' (Salida Base: ' + idSalida + ')',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../Back/Etiquetas/eliminar_etiqueta.php?idfusionada=${idConsolidado}&id_salida=${idSalida}&Tipo=Consolidada`;
            }
        });

    });
</script>


<script>
    /// Animación de salida para el mensaje:
    window.addEventListener('DOMContentLoaded', () => {
        const alerta = document.getElementById("alertaBanner");
        if (alerta) {
            setTimeout(() => {
                alerta.style.opacity = '0';
                setTimeout(() => {
                    alerta.remove();
                }, 500);
            }, 4000); // Desaparece después de 4 segundos
        }
    });


    function scrollToCurrentStep(estado) {
        const timelineContainer = document.querySelector(".timeline-container");
        const currentStep = document.querySelector(`#step${estado}`);

        if (!timelineContainer || !currentStep) return;

        // Si la pantalla es menor a 768px (modo móvil)
        if (window.innerWidth <= 768) {
            // Obtener el ancho del contenedor
            const containerWidth = timelineContainer.offsetWidth;
            // Obtener la posición del estado actual
            const stepPosition = currentStep.offsetLeft;
            // Obtener el ancho del estado actual
            const stepWidth = currentStep.offsetWidth;
            // Calcular la posición para centrar el estado actual
            const scrollPosition = stepPosition - containerWidth / 2 + stepWidth / 2;

            // Aplicar desplazamiento suave
            timelineContainer.scrollTo({
                left: scrollPosition,
                behavior: "smooth"
            });
        }
    }

    // 🚀 Modificamos la función para llamar a `scrollToCurrentStep`
    function actualizarTimeline(estado) {
        console.log("Estado recibido:", estado);
        const steps = document.querySelectorAll(".step");
        const progressBar = document.getElementById("progressBar");

        if (!progressBar) {
            console.error("No se encontró el elemento #progressBar");
            return;
        }

        steps.forEach((step, index) => {
            step.classList.remove("completed", "current");
            if (index + 1 < estado) {
                step.classList.add("completed");
            } else if (index + 1 === estado) {
                step.classList.add("current");
            }
        });

        // Llamamos a la función mejorada de scroll
        scrollToCurrentStep(estado);
    }


    // Codigo para mostrar el modal para agregar un nuevo contenedor
    function expandImage(imgElement) {
        const imageUrl = imgElement.src;
        document.getElementById('expandedImage').src = imageUrl;
    }

    /// Codigo para extender el tiempo de la sesion activa :
    setInterval(function() {
        fetch('extender_sesion.php'); // Llama al script cada 5 minutos
    }, 900000); // 300,000 ms = 5 minutos

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".contenedor-select").forEach(select => {
            select.addEventListener("change", function() {
                let contenedorOtro = this.closest(".row").querySelector(".contenedor-otro");
                if (this.value === "Otro") {
                    contenedorOtro.style.display = "block";
                    contenedorOtro.querySelector("input").setAttribute("required", "true");
                } else {
                    contenedorOtro.style.display = "none";
                    contenedorOtro.querySelector("input").removeAttribute("required");
                }
            });
        });
    });


    // ==== Global Variables ====
    const estadoActual = <?php echo $estadoActual; ?>; // Current state from PHP
    const id_salida = "<?php echo $id_salida; ?>"; // Current shipment ID from PHP
    const usuario = "<?php echo $Nombre_Completo; ?>"; // Current user from PHP



    // Initialize timeline
    actualizarTimeline(estadoActual);

    document.addEventListener("DOMContentLoaded", function() {
        const selectSalida = document.getElementById("Salida_Destino");
        const tablaEntregas = document.getElementById("tabla_entregas");

        // 1. Verificar que los elementos existen
        console.log("Select element:", selectSalida);
        console.log("Table element:", tablaEntregas);

        selectSalida.addEventListener("change", function() {
            const idSalida = this.value;
            console.log("Selected value:", idSalida); // 2. Verificar valor seleccionado

            if (idSalida) {
                console.log("Making fetch request...");

                // 3. Crear objeto FormData para enviar los datos
                const formData = new FormData();
                formData.append('id_salida', idSalida);

                fetch("get_entregas.php", {
                        method: "POST",
                        body: formData // Cambiamos a FormData que es más robusto
                    })
                    .then(response => {
                        console.log("Response status:", response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Received data:", data); // 4. Verificar datos recibidos
                        actualizarTabla(data);
                    })
                    .catch(error => {
                        console.error("Error en la petición:", error);
                        // Mostrar error al usuario
                        tablaEntregas.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error al cargar datos</td></tr>`;
                    });
            } else {
                // Limpiar tabla si no hay selección
                tablaEntregas.innerHTML = `<tr><td colspan="4" class="text-center">Seleccione una salida</td></tr>`;
            }
        });

        function actualizarTabla(data) {
            console.log("Updating table with:", data);
            let contenido = '';

            if (data && data.length > 0) {
                contenido = data.map(row => `
                <tr>
                    <td>${row.Id_Orden_Venta || 'N/A'}</td>
                    <td>${row.Id_Entrega || 'N/A'}</td>
                    <td>${row.Partida || 'N/A'}</td>
                    <td>${row.Id_Factura || 'N/A'}</td>
                </tr>`).join("");
            } else {
                contenido = `<tr><td colspan="4" class="text-center">No hay datos disponibles</td></tr>`;
            }

            tablaEntregas.innerHTML = contenido;
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const btnAgregar = document.getElementById("btnAgregarContenedor");
        const contenedoresDiv = document.getElementById("contenedores");
        const primerContenedor = document.querySelector(".contenedor-item");

        // Evento para agregar contenedores dinámicos
        btnAgregar.addEventListener("click", function() {
            const nuevoContenedor = primerContenedor.cloneNode(true);

            // Limpiar inputs y selects en el nuevo contenedor
            nuevoContenedor.querySelector("select").value = "";
            nuevoContenedor.querySelectorAll("input").forEach(input => input.value = "");

            // Mostrar el campo "Otro" si está seleccionado
            nuevoContenedor.querySelector(".contenedor-otro").style.display = "none";

            // Agregar botón de eliminar
            const btnEliminar = document.createElement("button");
            btnEliminar.type = "button";
            btnEliminar.classList.add("btn", "btn-danger", "btnEliminarContenedor");
            btnEliminar.textContent = "Eliminar";
            btnEliminar.addEventListener("click", function() {
                nuevoContenedor.remove();
            });

            nuevoContenedor.querySelector(".col-md-2.mb-3").appendChild(btnEliminar);
            contenedoresDiv.appendChild(nuevoContenedor);
        });

        // Evento delegado para manejar el select de todos los contenedores, incluso los nuevos
        document.addEventListener("change", function(e) {
            if (e.target.classList.contains("contenedor-select")) {
                const contenedorOtro = e.target.closest(".row").querySelector(".contenedor-otro");

                if (e.target.value === "Otro") {
                    contenedorOtro.style.display = "block";
                    contenedorOtro.querySelector("input").setAttribute("required", "true");
                } else {
                    contenedorOtro.style.display = "none";
                    contenedorOtro.querySelector("input").removeAttribute("required");
                }
            }
        });
    });

    // ==== Comments Management ====
    function cargarComentarios() {
        fetch("../Back/Comentarios/obtener_comentarios.php?id_salida=" + id_salida)
            .then(response => response.text())
            .then(data => {
                const contenedor = document.getElementById("comentariosContainer");
                contenedor.innerHTML = data;
                contenedor.scrollTop = 0; // Scroll to the top
            });
    }

    function agregarComentario() {

        const comentario = document.getElementById("nuevoComentario").value.trim();
        console.log(id_salida);


        if (!comentario) {
            alert("El comentario no puede estar vacío");
            return;
        }
        console.log("Comentario: " + comentario);
        console.log("Id Salida: " + id_salida);
        console.log("Usuario" + usuario);

        fetch("../Back/Comentarios/agregar_comentario.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `id_salida=${id_salida}&usuario=${usuario}&comentario=${encodeURIComponent(comentario)}`
            })
            .then(response => response.text())
            .then(() => {
                document.getElementById("nuevoComentario").value = "";
                cargarComentarios(); // Reload comments
            });
    }

    // Load comments on page load and refresh every 5 seconds
    cargarComentarios();
    setInterval(cargarComentarios, 5000);

    // ==== Dynamic Field Display ====
    document.addEventListener("DOMContentLoaded", function() {
        const estadoDropdown = document.getElementById("Estado");
        const extraInfoField = document.getElementById("extraInfoField");

        estadoDropdown.addEventListener("change", function() {
            if (this.value === "Otro") {
                extraInfoField.removeAttribute("hidden");
            } else {
                extraInfoField.setAttribute("hidden", true);
                document.getElementById("ExtraInfo").value = ""; // Clear the input field
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".eliminar-etiqueta").forEach(function(btn) {
            btn.addEventListener("click", function() {
                const id = this.getAttribute("data-id");
                const id_salida = <? ?>

                Swal.fire({
                    title: "¿Estás seguro?",
                    text: "Esta acción eliminará la etiqueta consolidada.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar",
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirige al backend
                        window.location.href = `../Back/Etiquetas/eliminar_consolidado.php?id_consolidado=${id}`;
                    }
                });
            });
        });
    });
</script>

</body>

</html>