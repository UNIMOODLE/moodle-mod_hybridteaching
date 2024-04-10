# Test unitarios HybridTeaching
- **hybridteaching_create_activity_test.php** => Crea actividad [DONE]
- **hybridteaching_create_session_test.php** => Crea sesiones del plugin [DONE]
- **hybridteaching_delete_session_test.php** => Elimina sesiones [DONE]
- **hybridteaching_delete_all_sessions_test.php** => Elimina todas las sesiones [DONE]
- **hybridteaching_update_session_test.php** => Actualiza sesiones [DONE]
- **hybridteaching_finish_session_test.php** => Finaliza sesiones del plugin [DONE]
- **hybridteaching_get_next_session_test.php** => Obtiene la siguiente sesión (si no hay, devuelve false) [DONE]
- **hybridteaching_check_session_started_test.php** => Comprueba si la sesión se ha inicializado [DONE]
- **hybridteaching_check_session_finished_test.php** => Comprueba si la sesión se ha finalizado [DONE]
- **hybridteaching_generate_passwords_test.php** => Genera las contraseñas de una sesión [DONE]
- **hybridteaching_get_own_attendance_test.php** => Genera información de tu asistencia y el total de asistencia de una sesión [DONE]
- **hybridteaching_set_attendance_test.php** => Añade la asistencia una sesión [DONE]
- **hybridteaching_load_attendance_test.php** => Carga las asistencias una sesión [DONE]
- **hybridteaching_update_attendance_test.php** => Actualiza las asistencias una sesión [TODO]
- **hybridteaching_set_visibility_record_session_test.php** => Cambia la visibilidad de los registros de una sesión [DONE]
- **hybridteaching_print_session_info_test.php** => Devuelve la información de la sesión [DONE]
- **hybridteaching_set_attendance_log_test.php** => Añade log de la asistencia creada [DONE]
- **hybridteaching_set_vc_create_session_test.php** => Crea sessión y meeting [TODO]
- **hybridteaching_create_config_test.php** => Crea configuración del plugin[DONE]
- **hybridteaching_configure_subplugin_test.php** => Crea configuracion del subplugin (ej: bbb)[FIX]
- **hybridteaching_attendance_table_test.php** => Render de las attendance [FIX]
- **hybridteaching_session_render_test.php** => Render de las sessiones [FIX]
- **hybridteaching_event_test.php** => Activa los eventos del plugin [DONE]
- **hybridteaching_notifys_test.php** => Activa notificaciones del plugin [DONE]
- **hybridteaching_roles_test.php** => Gestiona roles del plugin [DONE]
- **hybridteaching_custom_completion_test.php** => Gestiona los estados/reglas del modulo [DONE]
- **hybridteaching_session_filter_test.php** => Gestiona filtros de las sesiones [FIX]

## Documentacion 
-- Instalacion phpunit:     https://moodledev.io/general/development/tools/phpunit
-- Phpunit general: https://docs.moodle.org/dev/Writing_PHPUnit_tests
-- Assertions: https://docs.phpunit.de/en/10.5/assertions.html
        
-- Ejemplo comando ejecución (docker)
    vendor/bin/phpunit mod/hybridteaching/tests/phpunit/hybridteaching_create_session_test.php --testdox

