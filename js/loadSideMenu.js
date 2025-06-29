// js/loadSideMenu.js

$(document).ready(function() {
    console.log("loadSideMenu.js: Documento listo. Iniciando carga del menú lateral.");
    loadSideMenu(); // Llama a la función para cargar el menú al cargar la página
});

function loadSideMenu() {
    console.log("loadSideMenu(): Realizando llamada AJAX a phpAjaxMenu.inc.php");
    $.ajax({
        url: 'php_libs/soporte/phpAjaxMenu.inc.php', // ASEGÚRATE de que esta ruta es ABSOLUTAMENTE CORRECTA
        type: 'POST', // O 'GET' si tu script PHP está configurado para GET
        dataType: 'json',
        success: function(response) {
            console.log("loadSideMenu(): Respuesta AJAX recibida:", response);
            const $sideMenuContainer = $('#sideMenuContainer');
            
            // Si el contenedor no existe, loguear un error
            if ($sideMenuContainer.length === 0) {
                console.error("loadSideMenu(): Contenedor #sideMenuContainer no encontrado en el DOM.");
                return;
            }

            $sideMenuContainer.empty(); // Limpiar el mensaje de "Cargando menú..."

            if (response.success && response.menu && response.menu.length > 0) {
                // Función para construir recursivamente el HTML del menú
                function buildMenuHtml(items) {
                    let html = '';
                    $.each(items, function(index, item) {
                        const hasChildren = item.children && item.children.length > 0;
                        const itemUrl = item.url && item.url !== '#' ? item.url : '#';

                        html += `<li class="nav-item ${hasChildren ? 'has-treeview' : ''}">`;
                        html += `<a href="${itemUrl}" class="nav-link">`;
                        html += `<i class="nav-icon ${item.icon || 'fas fa-circle'}"></i>`; // Icono por defecto si no hay
                        html += `<p>${item.text}`;
                        if (hasChildren) {
                            html += `<i class="right fas fa-angle-left"></i>`;
                        }
                        html += `</p></a>`;

                        if (hasChildren) {
                            html += `<ul class="nav nav-treeview">`;
                            html += buildMenuHtml(item.children); // Llamada recursiva para sub-menús
                            html += `</ul>`;
                        }
                        html += `</li>`;
                    });
                    return html;
                }

                $sideMenuContainer.html(buildMenuHtml(response.menu));
                console.log("loadSideMenu(): HTML del menú insertado en el DOM.");

                // *****************************************************************
                // *** PASO CLAVE: Re-inicializar el Treeview de AdminLTE ***
                // *****************************************************************
                // Esto es crucial para que el clic funcione en los elementos cargados dinámicamente.
                // Asegúrate de que AdminLTE v3 esté correctamente cargado.
                try {
                    $('[data-widget="treeview"]').Treeview('init');
                    console.log("loadSideMenu(): AdminLTE Treeview re-inicializado.");
                } catch (e) {
                    console.warn("loadSideMenu(): No se pudo re-inicializar AdminLTE Treeview. Asegúrate de que AdminLTE.js está cargado y que 'Treeview' es un método válido para tu versión. Error:", e);
                }

            } else {
                $sideMenuContainer.html('<li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fas fa-exclamation-triangle"></i><p>Error al cargar el menú: ' + (response.message || 'Datos vacíos o inválidos.') + '</p></a></li>');
                console.error("loadSideMenu(): La respuesta del servidor no fue exitosa o no contenía datos de menú válidos.", response);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $sideMenuContainer.html('<li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fas fa-times-circle"></i><p>Error de red o del servidor al cargar el menú.</p></a></li>');
            console.error("loadSideMenu(): Error en la solicitud AJAX para cargar el menú lateral:", textStatus, errorThrown, jqXHR.responseText);
        }
    });
}