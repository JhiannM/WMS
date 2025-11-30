<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="https://contactamos.co/index2/logos/ICONO-01.png">
    <title>APP Cotizaciones</title>

    <!-- CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- JavaScript de Bootstrap (con Popper incluido) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript de Swal Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Iconos -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="../public/css/estilos.css">
    <script type="text/javascript" src="../public/js/script.js?v=<?= rand() ?>" defer></script>

    <!-- DATARANGEPICKER -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style id="spinner-custom-style">
        .swal2-loader {
        border-color: #28a745 transparent #28a745 transparent !important;
        }
    </style>

</head>

<body>

    <form id="form" method="POST" enctype="multipart/form-data">
        <div class="caja-titulo bg-success">
            <h4>Solicitud de Cotizaciones</h4>
        </div>

        <div class="row gy-2 gx-3">
            <div class="mb-3">
                <label for="proveedor" class="form-label">Nombre del solicitante:</label>
                <input type="text" class="form-control text-uppercase" id="nombre" onchange="this.value=(this.value).toUpperCase()" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo del solicitante:</label>
                <input type="email" class="form-control text-lowercase" id="correo" required>
            </div>
            <div class="mb-3">
                <label for="proyecto" class="form-label">Proyecto:</label>
                <select class="form-select" id="proyecto">
                    <option selected="" disabled="" value="">Seleccione una opción...</option>
                </select>
            </div>
        </div>
        <div class="row gy-2 gx-3 mt-3">
            <div class="caja-subtitulo">
                <h4>Artículos Requeridos (<small id="contador">1</small>)</h4>
            </div>

            <div>
                <input class="btn btn-outline-secondary float-end add-row" type="button" value="Agregar" id="agregar">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="tablaRequerimientos">
                    <thead>
                        <tr>
                            <th>Artículo <span>*</span></th>
                            <th>Cantidad <span>*</span></th>
                            <th>Fecha Tentativa <span>*</span></th>
                            <th>Aprueba <span>*</span></th>
                            <th>Adjunto</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla">
                        <tr class="fila">
                            <td id="colArticulo">
                                <select class="js-example-basic-single textoArticulo" onchange="obtenerFila(this)" name="state" id="articulo" required>
                                    <option selected disabled value="">Seleccione...</option>
                                    <!-- Las opciones se llenarán con JS -->
                                </select>
                                <input type="text" class="form-control text-uppercase otro" onchange="this.value=(this.value).toUpperCase()" placeholder="Ingrese el nuevo artículo"  hidden>
                            </td>
                            <td>
                                <input type="number" class="form-control cantidad" required>
                            </td>
                            <td>
                                <input type="date" class="form-control fecha" id="fechaInput" required>
                            </td>
                            <td>
                                <select class="form-select aprueba" id="aprueba" required>
                                    <option selected disabled value="">Seleccione...</option>
                                </select>
                            </td>
                            <td id="botones">
                                <input type="file" class="form-control d-none archivo" id="dropzone-file" accept=".pdf, .jpg, .png, .JPEG">
                                <button type="button" class="btn btn-outline-secondary" onclick="adjuntarArchivo(this)" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                    Adjuntar
                                </button>
                            </td>
                            <td>
                                <textarea class="form-control text-uppercase observaciones" onchange="this.value=(this.value).toUpperCase()" rows="2"></textarea>
                            </td>
                            <td id="botones">
                                <button type="button" class="btn btn-outline-danger btn-sm" disabled><ion-icon name="trash-outline"></ion-icon></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="nota">
            <p>
                <b>Importante: </b> Verifica todos los ítems de la tabla antes de enviar la información. <br>
                <b id="nota*">* </b> Campos Obligatorios. <br>
                <b>Nota: </b> El campo de fecha tentativa es la fecha para la cual se necesita el artículo
                y debe ser mínimo 5 días posterior al envío de la solicitud.
            </p>
        </div>

        <!-- Modal file -->
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">
                            Adjuntar Archivo
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-4">
                            <div class="border border-secondary rounded p-4 text-center" style="cursor: pointer;" onclick="dispararInputArchivo()">
                                <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16" width="90px" height="50px">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"></path>
                                </svg>
                                <p class="text-muted mb-1">Haz clic para subir o arrastra aquí tu archivo</p>
                                <small class="text-muted">Formatos permitidos: PNG, JPG, PDF JPEG</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal file End -->
         
        <div class="boton">
            <input class="btn btn-outline-success" type="submit" id="btnEnviar" value="Enviar Solicitud">
        </div>
    </form>

</body>
<footer class="text-center">
    <p>
        © APP Cotizaciones - Todos los derechos reservados <br>
        <small>Designed Jesús Orozco</small>
    </p>
</footer>

</html>