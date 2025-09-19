jQuery(document).ready(function ($) {

    // Guardar cambios en la edición en línea de la tabla de cursos
    $("#guardar-cambios").on("click", function () {
        let cursos = [];

        $("table.widefat tbody tr").each(function () {
            let celdas = $(this).find("td");
            // Prevenir filas vacías
            if (celdas.length >= 5) {
                cursos.push({
                    nombre: celdas.eq(1).text().trim(),
                    fecha: celdas.eq(2).text().trim(),
                    horario: celdas.eq(3).text().trim(),
                    precio: celdas.eq(4).text().trim()
                });
            }
        });

        let data = {
            action: "guardar_cambios_cursos",
            cursos: cursos,
            security: datos_cursos_ajax_obj.security
        };

        $.post(datos_cursos_ajax_obj.ajax_url, data, function (response) {
            var $mensaje = $("#mensaje-actualizacion");
            if (response.success) {
                $mensaje.text(response.data).css("color", "green");
            } else {
                $mensaje.text('Error: ' + response.data).css("color", "red");
            }
            $mensaje.fadeIn().delay(2500).fadeOut();
        });
    });

    // Eliminar curso (confirmación incluida)
    $(document).on("click", ".eliminar-curso", function () {
        if (!confirm("¿Seguro que deseas eliminar este curso?")) return;
        var cursoId = $(this).data("curso-id");
        var data = {
            action: "eliminar_curso",
            security: datos_cursos_ajax_obj.security,
            curso_id: cursoId
        };

        $.post(datos_cursos_ajax_obj.ajax_url, data, function (response) {
            if (response.success) {
                // Eliminado correctamente, recargar la página
                location.reload();
            } else {
                alert("Error: " + response.data);
            }
        });
    });

});
