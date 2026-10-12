// Alertas y validaciones en formularios
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ajax-form').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault(); // Prevenir envío normal del formulario

            const formData = new FormData(form); // Crear objeto FormData
            const action = form.getAttribute('action'); // Obtener la URL de destino

            try {
                const response = await fetch(action, {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json(); // Procesar respuesta JSON

                if (result.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: result.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                    }).then(() => {
                        window.location.reload(); // Recargar página (opcional)
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: result.message,
                        icon: 'error',
                        confirmButtonText: 'Corregir',
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error inesperado',
                    text: 'No se pudo enviar el formulario. Intente nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            }
        });
    });
});
// Eliminacion de talleres y eventos
document.addEventListener('DOMContentLoaded', function () {
    // Manejar botones de eliminación
    document.querySelectorAll('.delete-button').forEach((button) => {
        button.addEventListener('click', async (e) => {
            e.preventDefault(); // Evitar comportamiento predeterminado del enlace o botón
            
            const action = button.getAttribute('data-action'); // Obtener la URL de destino

            // Confirmación con SweetAlert
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(action, {
                            method: 'POST', // Método POST para eliminar
                        });

                        const result = await response.json(); // Procesar respuesta JSON

                        if (result.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: result.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar',
                            }).then(() => {
                                window.location.reload(); // Recargar la página (opcional)
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: result.message,
                                icon: 'error',
                                confirmButtonText: 'Corregir',
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error inesperado',
                            text: 'No se pudo completar la eliminación. Intente nuevamente.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                        });
                    }
                }
            });
        });
    });
});

// Cambiar estados de talleres y eventos 
function cambiarEstado(url, id, estadoActual, entidad, isChecked) {
    const nuevoEstado = isChecked ? 'activo' : 'inactivo';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id: id,
            estado: nuevoEstado,
        },
        success: function () {
            Swal.fire({
                position: "center",
                icon: "success",
                title: `${entidad} ${nuevoEstado === "activo" ? "activo" : "inactivo"}`,
                showConfirmButton: false,
                timer: 800,
            });
        },
        error: function (xhr, status, error) {
            console.error(`Error al actualizar el estado de ${entidad}:`, error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: `Error al cambiar el estado de ${entidad}`,
                text: "Inténtalo nuevamente.",
                showConfirmButton: true,
                confirmButtonText: "Aceptar",
            });
        }
    });
}

// cerrar sesion

function cerrarSesion(url) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas cerrar la sesión?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar la solicitud AJAX
            fetch(url, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        title: '¡Sesión cerrada!',
                        text: result.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Redirigir al login después de cerrar sesión
                        window.location.href = './?controlador=paginas&accion=login';
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo cerrar la sesión. Intenta nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
                console.error('Error:', error);
            });
        }
    });
}



