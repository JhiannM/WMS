/* 
Link de roboflow: https://app.roboflow.com/join/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ3b3Jrc3Bh
Y2VJZCI6IjFkSlVQVU1GWHdmZWJsT1ZQNWdvbDNqek5tZjIiLCJyb2xlIjoib3duZXIiLCJpbnZpdGVyI
joicDM3Njg2MjhAZ21haWwuY29tIiwiaWF0IjoxNzUwOTU4NDQzfQ.AYNCJEI5gxaCLyk900Ksj8ltJJOO02ZDeS66DRld1no
*/

let contadorFilas = 1;
let nombres = "";

/** Agregar filas de forma dinámica */
$(document).ready(function () {
  $(".js-example-basic-single").select2();

  /** Mensaje de cargando */
  Swal.fire({
    title: "Cargando...",
    text: "Por favor, espere un momento mientras se termina de cargar toda la información.",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  /* ocultar swal de cargando */
  setTimeout(() => {
    Swal.close(); 
  }, 6000);

  /* Contar las filas */
  var filas = 0;

  $(".fila").each(function () {
    filas++;
  });

  $("#contador").html(filas);

  /** Agregar nueva fila */
  $("#agregar").on("click", function () {
    agregarFila();
  });

  /** Asignar evento para enviar la informacion */
  $("#form").on("submit", function (event) {
    event.preventDefault(); // Evitar el envío del formulario por defecto

    enviarDatos();
  });

  /** Consultar Artículos, Proyectos y Jefes */
  setTimeout(() => {
    consultarArtiuculos();
    consultarProyectos();
    consultarJefeAprueba();
  }, 1000);

  const input = document.getElementById("fechaInput");
  const hoy = new Date();
  const fechaMinima = agregarDiasHabiles(hoy, 5);

  // Formato YYYY-MM-DD
  const fechaMinimaStr = fechaMinima.toISOString().split('T')[0];
  input.min = fechaMinimaStr;

});

/** Asignar datos al select artículos */
function consultarArtiuculos() {
  $(".textoArticulo").select2({
    ajax: {
      url: "../Controlador/consultarInfo.php?action=articulos",
      type: "POST",
      dataType: "json",
      delay: 250,
      data: function (params) {
        return { search: params.term };
      },
      processResults: function (data) {
        let results = [];
        if (data.success === false || !data.data || data.data.length === 0) {
          // Si no hay resultados, muestra la opción "nuevo"
          results = [{ id: "nuevo", text: "nuevo" }];

          // Evento para detectar la selección de "nuevo"
          $(".textoArticulo").on("select2:select", function (e) {
            const selectedId = e.params.data.id;
            if (selectedId === "nuevo") {
              otraDescripcion();
            }
          });
        } else {
          results = data.data.map(function (item) {
            return {
              id: item[0],
              text: item[0],
            };
          });
        }
        return { results: results };
      },
    },
    minimumInputLength: 5,
    width: "100%",
    language: "es",
    placeholder: "Buscar por nombre del artículo",
  });
}

/** Asignar datos al select proyectos */
function consultarProyectos() {
  $.ajax({
    url: "../Controlador/consultarInfo.php?action=proyectos",
    type: "POST",
    dataType: "json",
    success: function (response) {
      if (response && response.data.length > 0) {
        llenarSelectProyectos(response.data);
      } else {
        Swal.fire({
          title: "No hay artículos disponibles",
          text: "Por favor, agrega artículos a la base de datos.",
          icon: "info",
          confirmButtonColor: "#1d8348",
        });
      }
    },
    error: function (xhr, status, error) {
      console.error("Error al consultar los artículos:", xhr);

      Swal.fire({
        title: "Error",
        text: "No se pudieron cargar los artículos. Inténtalo de nuevo.",
        icon: "error",
        confirmButtonColor: "#d33",
      });
    },
  });
}

/** Asignar datos al select aprueba */
function consultarJefeAprueba() {
  $.ajax({
    url: "../Controlador/consultarInfo.php?action=jefes",
    type: "POST",
    dataType: "json",
    success: function (response) {
      if (response && response.data.length > 0) {
        llenarSelectAprueba(response.data);
      } else {
        Swal.fire({
          title: "No hay artículos disponibles",
          text: "Por favor, agrega artículos a la base de datos.",
          icon: "info",
          confirmButtonColor: "#1d8348",
        });
      }
    },
    error: function (xhr, status, error) {
      console.error("Error al consultar los artículos:", xhr);

      Swal.fire({
        title: "Error",
        text: "No se pudieron cargar los artículos. Inténtalo de nuevo.",
        icon: "error",
        confirmButtonColor: "#d33",
      });
    },
  });
   
}

/** Recuperar la info del form y llamar a la función
 *  que los guarda en la hoja de calculo
*/
function enviarDatos() {
  // Crear un objeto FormData para enviar los datos
  var btn = $("#btnEnviar");
  btn.prop("disabled", true);
  btn.val("Enviando...");

  var nombre = $("#nombre").val();
  var correo = $("#correo").val();
  var proyecto = $("#proyecto").val();

  var formData = new FormData();
  formData.append("nombre", nombre);
  formData.append("correo", correo);
  formData.append("proyecto", proyecto);

  // Recoger los datos de las filas
  var filas = [];
  $("#cuerpoTabla tr").each(function (i) {
    var fila = {
      articulo: $(this).find(".textoArticulo").val(),
      otro: $(this).find(".otro").val(),
      cantidad: $(this).find(".cantidad").val(),
      fecha: $(this).find(".fecha").val(),
      aprueba: $(this).find(".aprueba").val(),
      observaciones: $(this).find(".observaciones").val(),
      // No incluir el archivo aquí
    };
    filas.push(fila);

    // Adjuntar el archivo al FormData con un nombre único
    var archivo = $(this).find(".archivo")[0]?.files[0];
    if (archivo) {
      formData.append("archivo_" + i, archivo);
    }
  });

  // Añadir las filas (sin archivos) al FormData
  formData.append("filas", JSON.stringify(filas));

  // Enviar los datos al servidor
  $.ajax({
    url: "../Controlador/enviarDatos.php?action=guardarCotizacion",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      // Si la respuesta es un string, conviértela a objeto
      if (typeof response === "string") {
        try {
          response = JSON.parse(response);
        } catch (e) {
          console.error("No se pudo parsear la respuesta:", e);
          Swal.fire({
            title: "Error",
            text: "Respuesta inesperada del servidor.",
            icon: "error",
            confirmButtonColor: "#d33",
          });
          btn.prop("disabled", false);
          btn.val("Enviar Solicitud");
          return;
        }
      }

      Swal.fire({
        title: "Consecutivo N°: " + response.consecutivo,
        text:
          response.mensaje +
          ", N° artículos registrados: " +
          $("#contador").text(),
        icon: response.success ? "success" : "error",
        confirmButtonColor: "#1d8348",
      });

      btn.prop("disabled", false); // Habilitar el botón nuevamente
      btn.val("Enviar Solicitud");
      limpiarFilas();
    },
    error: function (xhr, status, error) {
      console.error("Error al enviar los datos:", error);

      Swal.fire({
        title: "Error",
        text: "No se pudo enviar la solicitud de cotización. Inténtalo de nuevo.",
        icon: "error",
        confirmButtonColor: "#d33",
      });

      btn.prop("disabled", false); // Habilitar el botón nuevamente
      btn.val("Enviar Solicitud");
    },
  });
}

/** Llenar el select de las personas que aprueban las
 *  cotizaciones
*/
function llenarSelectAprueba(nombreJefes) {
  const sltAprueba = document.getElementById("aprueba");
  nombres = []

  for (var i = 0; i < nombreJefes.length; i++) {
    var opciones = nombreJefes[i];

    if (opciones != "") {
      nombres.push(opciones); 
      
      var option = document.createElement("option");
      option.value = opciones;
      option.text = opciones;
      sltAprueba.append(option);

      /* Organizar alfabeticamente */
      $("#aprueba").each(function () {
        const $select = $(this);
        const $options = $select.find("option").slice(1); // omite el primero

        $options.sort(function (a, b) {
          return a.text.localeCompare(b.text);
        });

        $select.append($options);
      });
    }
  }
}

/** Llenar el select de las personas que proyectos */
function llenarSelectProyectos(proyectos) {
  const sltProyecto = document.getElementById("proyecto");
  for (var i = 1; i < proyectos.length; i++) {
    var opciones = proyectos[i];

    if (opciones != "") {
      var option = document.createElement("option");
      option.value = opciones;
      option.text = opciones;
      sltProyecto.append(option); // Usa appendChild en lugar de add

      /* Organizar alfabeticamente */
      $("#proyecto").each(function () {
        const $select = $(this);
        const $options = $select.find("option").slice(1); // omite el primero

        $options.sort(function (a, b) {
          return a.text.localeCompare(b.text);
        });

        $select.append($options);
      });
    }
  }
}

/** Agregar una nueva fila a la tabla */
function agregarFila() {
  let opciones = "";
 
  nombres.forEach((Element) => {
    opciones += `<option value="${Element}">${Element}</option>`;
  });

  let fila = `
      <tr class="fila">
        <td id="colArticulo">                
          <select class="js-example-basic-single textoArticulo" name="articulo[]"  onchange="obtenerFila(this)" required>
            <option selected disabled value="">Seleccione...</option>
          </select>
          <input type="text" class="form-control text-uppercase otro" onchange="this.value=(this.value).toUpperCase()" placeholder="Ingrese el nuevo artículo" hidden>
        </td>
        <td>
          <input type="number" class="form-control cantidad"  required>
        </td>
        <td>
          <input type="date" class="form-control fecha" required>
        </td>
        <td>
          <select class="form-select aprueba" id="aprueba" required>
            <option selected disabled value="">Seleccione...</option>
            ${opciones}
          </select>
        </td>
        <td>
          <input type="file" class="form-control archivo" accept=".pdf, .jpg, .png, .JPEG" style="display: none;">
          <button type="button" class="btn btn-outline-secondary" onclick="adjuntarArchivo(this)" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
          Adjuntar
          </button>
        </td>
        <td>
          <textarea class="form-control text-uppercase observaciones" onchange="this.value=(this.value).toUpperCase()" rows="2"></textarea>
        </td>
        <td id="botones">
          <button type="button" class="btn btn-outline-danger btn-sm btnEliminar"><ion-icon name="trash-outline"></ion-icon></button>
        </td>
      </tr>
  `;

  $("#cuerpoTabla").append(fila);

  contadorFilas++;
  $("#contador").html(contadorFilas);

  /** Selecionar la fila agregada */
  const nuevaFila = $("#cuerpoTabla tr").last();

  nuevaFila.find(".btnEliminar").on("click", function () {
    $(this).closest("tr").remove();

    contadorFilas--;
    $("#contador").html(contadorFilas);
  });

  $("#cuerpoTabla .textoArticulo")
  .not(".select2-hidden-accessible")
  .select2({
      ajax: {
        url: "../Controlador/consultarInfo.php?action=articulos",
        type: "POST",
        dataType: "json",
        delay: 250,
        data: function (params) {
          return { search: params.term };
        },
        processResults: function (data) {
          let respt = [];
          if (data.success == false) {
            respt = [{ id: "nuevo", text: "nuevo" }];

            // Evento para detectar la selección de "nuevo"
            $(".textoArticulo").on("select2:select", function (e) {
              const selectedId = e.params.data.id;
              if (selectedId === "nuevo") {
                otraDescripcion();
              }
            });

            return { results: respt };
          }

          return {
            results: data.data.map(function (item) {
              return {
                id: item[0], // Ajusta según tu estructura de datos
                text: item[0],
              };
            }),
          };
        },
      },
      minimumInputLength: 5,
      width: "100%",
      language: "es",
      placeholder: "Buscar por nombre del artículo",
  });

  /* Organizar alfabeticamente */
  $(".aprueba").each(function () {
    const $select = $(this);
    const $options = $select.find("option").slice(1); // omite el primero

    $options.sort(function (a, b) {
      return a.text.localeCompare(b.text);
    });

    $select.append($options);
  });
}

let filaActiva = null;
function obtenerFila(boton) {
  /** Obtener la fila en la cual se presiono el botòn */
  filaActiva = boton.closest("tr");
}

/* Optener la fila en la cual se dio click */
function adjuntarArchivo(boton) {
  /** Obtener la fila en la cual se presiono el botòn */
  filaActiva = boton.closest("tr");
}

/* Abrir modal para cargar archivo */
function dispararInputArchivo() {
  if (filaActiva) {
    const input = filaActiva.querySelector(".archivo");

    if (input) {
      input.click();
      input.onchange = () => {
        const modalElement = document.getElementById("staticBackdrop");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);

        if (modalInstance) {
          modalInstance.hide();
          Swal.fire({
            title: "Archivo Adjuntado",
            text: "Su archivo se adjunto correctamente.",
            icon: "success",
            confirmButtonColor: "#1d8348",
          });
        }
      };
    }
  }
}

/** Habilitar el inputs de otro artículo */
function otraDescripcion() {
  const inputOtro = filaActiva.querySelector(".otro");
  inputOtro.removeAttribute("hidden");

  const inputTexto = filaActiva.querySelector(".textoArticulo");
  inputTexto.setAttribute("hidden", "true");
  // Suponiendo que inputTexto es el select
  $(inputTexto).next(".select2-container").hide();

  /* Ocultar modal */
  const modalElement = document.getElementById("exampleModal");
  const modalInstance = bootstrap.Modal.getInstance(modalElement);
  if (modalInstance) {
    modalInstance.hide();
  }
}

/** Limpiar todos los campos */
function limpiarFilas() {
  const filas = $("#cuerpoTabla tr");

  // Recorremos todas las filas
  filas.each(function (index) {
    document.getElementById("nombre").value = "";
    document.getElementById("correo").value = "";
    document.getElementById("proyecto").value = "";

    if (index === 0) {
      // Limpiar inputs y selects de la primera fila
      $(this)
        .find(
          "input[type='text'], input[type='number'], input[type='date'], input[type='file'], textarea, select"
        )
        .val("");
      $(this).find(".js-example-basic-single").val("").trigger("change");
      $(this).find(".otro").attr("hidden", "true");

    } else {
      // Eliminar todas las demás filas
      $(this).remove();
    }
  });

  /** Reiniciar el contador */
  contadorFilas = 1;
  $("#contador").html(contadorFilas);
}

function agregarDiasHabiles(fecha, diasHabiles) {
  let resultado = new Date(fecha);
  let agregados = 0;
  while (agregados < diasHabiles) {
    resultado.setDate(resultado.getDate() + 1);
    // 0 = Domingo, 6 = Sábado
    if (resultado.getDay() !== 0 && resultado.getDay() !== 6) {
      agregados++;
    }
  }
  return resultado;
}


