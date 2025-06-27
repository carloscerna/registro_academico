<?php
session_name('demoUI');
session_start();

// Asegurarse de que el usuario está logueado
if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autenticado.']);
    exit();
}

// Incluir la conexión a la base de datos si es necesaria (por ahora no, luego sí)
// include($_SESSION['path_root'] . "/registro_academico/includes/mainFunctions_.php"); // Si necesitas DB

$codigo_perfil = $_SESSION['codigo_perfil'] ?? null; // Obtener el código de perfil de la sesión

$menuItems = [];

// Definición de la estructura del menú por perfiles
// Cada elemento tiene:
// 'text': Texto a mostrar en el menú
// 'icon': Clase de icono (Font Awesome)
// 'url': URL a la que enlaza
// 'children': Un array de submenús (si los hay)
// 'profiles': Array de códigos de perfil que pueden ver este elemento

// Estructura general de todos los elementos del menú disponibles
$fullMenuStructure = [
    // Ejemplo: Tablero (visible para 04, 05)
    [
        'id' => 'dashboard',
        'text' => 'Tablero',
        'icon' => 'fas fa-school',
        'url' => 'index.php',
        'profiles' => ['04', '05', '01'], // Añadido 01 para Administrador
        'children' => []
    ],
    // Ejemplo: Estudiante (visible para 04, 05)
    [
        'id' => 'estudiante',
        'text' => 'Estudiante',
        'icon' => 'fas fa-user',
        'url' => '#', // No tiene URL directa, es un padre
        'profiles' => ['04', '05', '01'],
        'children' => [
            ['id' => 'est_matricula', 'text' => 'Matrícula', 'icon' => 'far fa-pen-square', 'url' => 'matricula.php', 'profiles' => ['04', '05', '01']],
            ['id' => 'est_registro_notas', 'text' => 'Registro Notas', 'icon' => 'far fa-pen-square', 'url' => 'registro_notas.php', 'profiles' => ['04', '05', '01']],
        ]
    ],
    // Ejemplo: Catálogo (visible para 04, 05)
    [
        'id' => 'catalogo',
        'text' => 'Catálogo',
        'icon' => 'fas fa-book',
        'url' => '#',
        'profiles' => ['04', '05', '01'],
        'children' => [
            ['id' => 'cat_area', 'text' => 'Área', 'icon' => 'far fa-pen-square', 'url' => 'area.php', 'profiles' => ['04', '05', '01']],
            // ['id' => 'cat_reporte', 'text' => 'Reporte', 'icon' => 'far fa-pen-square', 'url' => 'reporte.php', 'profiles' => ['04', '05', '01']], // Tuve que adivinar el nombre
            ['id' => 'cat_periodos', 'text' => 'Períodos', 'icon' => 'far fa-pen-square', 'url' => 'Periodos.php', 'profiles' => ['04', '05', '01']],
        ]
    ],
    // PERFIL ADMINISTRADOR (01)
    [
        'id' => 'admin_gestion_usuarios',
        'text' => 'Gestión Usuarios',
        'icon' => 'fas fa-users-cog',
        'url' => 'gestion_usuarios.php', // URL hipotética
        'profiles' => ['01'],
        'children' => []
    ],
    // PERFIL DIRECTOR O SUBDIRECTOR (02, 03)
    [
        'id' => 'dir_informes',
        'text' => 'Informes Dirección',
        'icon' => 'fas fa-chart-line',
        'url' => 'informes_direccion.php', // URL hipotética
        'profiles' => ['02', '03', '01'],
        'children' => []
    ],
    // PERFIL DE MATRICULA (06)
    [
        'id' => 'matricula_only',
        'text' => 'Solo Matrícula',
        'icon' => 'fas fa-user-plus',
        'url' => 'matricula_simple.php', // URL hipotética
        'profiles' => ['06', '01'],
        'children' => []
    ],
    // PERFIL ROOT (99)
    [
        'id' => 'root_mantenimiento',
        'text' => 'Mantenimiento del Sistema',
        'icon' => 'fas fa-cogs',
        'url' => 'mantenimiento_root.php', // URL hipotética
        'profiles' => ['99'],
        'children' => [
            ['id' => 'root_crear_usuarios', 'text' => 'Crear Usuarios', 'icon' => 'fas fa-user-plus', 'url' => 'crear_usuarios.php', 'profiles' => ['99']],
            ['id' => 'root_personal', 'text' => 'Mantenimiento Personal', 'icon' => 'fas fa-id-card', 'url' => 'mantenimiento_personal.php', 'profiles' => ['99']],
        ]
    ]
];

// Construir el menú para el perfil actual
foreach ($fullMenuStructure as $mainItem) {
    // Si el perfil actual está en la lista de perfiles permitidos para este elemento principal
    if (in_array($codigo_perfil, $mainItem['profiles'])) {
        $currentItem = [
            'id' => $mainItem['id'],
            'text' => $mainItem['text'],
            'icon' => $mainItem['icon'],
            'url' => $mainItem['url'],
            'children' => []
        ];

        // Procesar submenús
        foreach ($mainItem['children'] as $subItem) {
            if (in_array($codigo_perfil, $subItem['profiles'])) {
                $currentItem['children'][] = [
                    'id' => $subItem['id'],
                    'text' => $subItem['text'],
                    'icon' => $subItem['icon'],
                    'url' => $subItem['url']
                ];
            }
        }
        // Solo añadir el elemento principal si tiene submenús o si es un elemento de nivel superior sin hijos.
        // O si después de filtrar los hijos, quedan hijos (para evitar menús padres vacíos).
        if (empty($mainItem['children']) || !empty($currentItem['children'])) {
             $menuItems[] = $currentItem;
        }
    }
}

// Devolver la respuesta JSON
header('Content-Type: application/json;charset=utf-8');
echo json_encode(['success' => true, 'menu' => $menuItems]);

?>