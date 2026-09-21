$(document).ready(function() {
    loadDynamicMenu();

    // Función para cargar y construir el menú dinámicamente
function loadDynamicMenu() {
    $.ajax({
        url: 'php_libs/soporte/phpAjaxMenu.inc.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.menu.length > 0) {
                const menuContainer = $('#dynamic-sidebar-menu');
                menuContainer.empty();

                const ulMenu = $('<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false"></ul>');

                response.menu.forEach(item => {
                    const hasChildren = item.children && item.children.length > 0;
                    let navItemClass = 'nav-item';
                    let navLinkClass = 'nav-link';
                    let navIconHtml = `<i class="nav-icon ${item.icon}"></i>`;
                    let navTextHtml = `<p>${item.text}</p>`;
                    let arrowHtml = '';

                    if (hasChildren) {
                        navItemClass += ' has-treeview';
                        navLinkClass += '';
                        arrowHtml = '<i class="fas fa-angle-left right"></i>';
                    }

                    const listItem = $(`<li class="${navItemClass}"></li>`);
                    const linkItem = $(`<a href="${item.url || '#'}" class="${navLinkClass}">
                                            ${navIconHtml}
                                            ${navTextHtml}
                                            ${arrowHtml}
                                        </a>`);
                    
                    listItem.append(linkItem);

                    if (hasChildren) {
                        const subMenuUl = $('<ul class="nav nav-treeview"></ul>');
                        item.children.forEach(subItem => {
                            const subNavLinkHtml = `<a href="${subItem.url}" class="nav-link">
                                                        <i class="nav-icon ${subItem.icon}"></i>
                                                        <p>${subItem.text}</p>
                                                    </a>`;
                            const subMenuItem = $(`<li class="nav-item"></li>`).append(subNavLinkHtml);
                            subMenuUl.append(subMenuItem);
                        });
                        listItem.append(subMenuUl);
                    }
                    ulMenu.append(listItem);
                });

                menuContainer.append(ulMenu);

                // --- INICIALIZACIÓN DEL WIDGET TREEVIEW DE ADMINLTE ---
                if ($.fn.Treeview) {
                    ulMenu.Treeview('init');
                }

                // Ajustar estado activo de los enlaces
                setActiveMenuItem();

            } else {
                $('#dynamic-sidebar-menu').html('<p class="text-danger p-3">No se pudo cargar el menú o no hay elementos.</p>');
                console.error("Error al cargar el menú:", response.message || "Respuesta inválida del servidor.");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            let errorMessage = "Error al cargar el menú: ";
            if (jqXHR.status === 0) {
                errorMessage += "No hay conexión al servidor.";
            } else if (jqXHR.status == 404) {
                errorMessage += "Endpoint del menú no encontrado [404].";
            } else if (jqXHR.status == 500) {
                errorMessage += "Error interno del servidor [500].";
            } else {
                errorMessage += textStatus + " - " + errorThrown;
            }
            $('#dynamic-sidebar-menu').html(`<p class="text-danger p-3">${errorMessage}</p>`);
            console.error(errorMessage, jqXHR);
        }
    });
}

// Función auxiliar para marcar el menú activo e iluminarlo adecuadamente
function setActiveMenuItem() {
    const currentPath = window.location.pathname.split('/').pop();
    $('#dynamic-sidebar-menu a.nav-link').each(function() {
        const linkPath = $(this).attr('href').split('/').pop();
        if (linkPath === currentPath && linkPath !== '' && linkPath !== '#') {
            $(this).addClass('active');$(this).parents('.nav-treeview').prev('.nav-link').addClass('active').parents('.nav-item.has-treeview').addClass('menu-open');
        }
    });
}

    // Opcional: Manejar el estado "activo" del menú basado en la URL actual
    // Esta lógica debe ir DESPUÉS de que el menú se haya cargado.
    setTimeout(function() { // Pequeño retardo para asegurar que el menú esté completamente renderizado
        const currentPath = window.location.pathname.split('/').pop(); // Obtiene el nombre del archivo actual (ej. index.php)
        $('a.nav-link').each(function() {
            const linkPath = $(this).attr('href').split('/').pop();
            if (linkPath === currentPath) {
                $(this).addClass('active');
                // Si es un submenú, expandir también el padre
                $(this).parents('.nav-treeview').prev('.nav-link').addClass('active').parents('.nav-item.has-treeview').addClass('menu-open');
            } else {
                $(this).removeClass('active');
            }
        });
    }, 100); // 100ms de retardo, ajusta si es necesario
});