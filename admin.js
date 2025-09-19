jQuery(document).ready(function ($) {

    $("#guardar-cambios").click(function () {
    
        let cursos = [];

        $("table tr").each(function (index) {
        
            if (index > 0) {
                let curso = {
                    nombre: $(this).find("td:nth-child(2)").text(),
                    fecha: $(this).find("td:nth-child(3)").text(),
                    horario: $(this).find("td:nth-child(4)").text(),
                    precio: $(this).find("td:nth-child(5)").text(),
                };
                cursos.push(curso);
            }
            
        });

        let data = {
        
            action: "guardar_cambios_cursos",
            cursos: cursos,
            security: datos_cursos_ajax_obj.security,
            
        };

		$.post(datos_cursos_ajax_obj.ajax_url, data, function (response) {
		
		    var mensajeActualizacion = $("#mensaje-actualizacion");
		
		    if (response.success) {
		    
		        mensajeActualizacion.text(response.data);
		        mensajeActualizacion.css("color", "green");
		        
		    } else {
		    
		        mensajeActualizacion.text('Error: ' + response.data);
		        mensajeActualizacion.css("color", "red");
		    }
		
		    mensajeActualizacion.fadeIn().delay(3000).fadeOut();
		});
		
    });
    
	  $(document).on("click", ".eliminar-curso", function () {
	    var cursoId = $(this).data("curso-id");
	    var data = {
	      action: "eliminar_curso",
	      security: datos_cursos_ajax_obj.security,
	      curso_id: cursoId,
	    };
	
	    $.post(datos_cursos_ajax_obj.ajax_url, data, function (response) {
	      if (response.success) {
	        location.reload();
	      } else {
	        alert(response.data);
	      }
	    });
	});
	
});