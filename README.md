# Certificate Manager Plugin

Este plugin permite crear certificados a través de un formulario y gestionarlos mediante un flujo de aprobación.

## Funcionalidades
- Custom Post Type `certificate` para almacenar certificados.
- Metabox con campos: nombre, apellido, cargo, curso y código.
- Generación de PDF utilizando una plantilla ubicada en `templates/certificate-template.php` (requiere DOMPDF).
- Formulario mediante shortcode `[certificate_form]` para que los usuarios creen certificados.
- Botón de "Enviar aprobación" para cambiar el estado a `pending`.
- Los administradores pueden aprobar certificados cambiando su estado a `publish`.
- Shortcode `[certificate_lookup]` para buscar certificados por código y descargar el PDF.

## Instalación
1. Copiar la carpeta `certificate-manager` en el directorio `wp-content/plugins`.
2. Instalar la librería **DOMPDF** (por ejemplo mediante Composer) dentro de la carpeta del plugin.
3. Activar el plugin desde el panel de administración de WordPress.
4. Crear una página y agregar el shortcode `[certificate_form]` para permitir la creación de certificados.
5. Crear otra página con el shortcode `[certificate_lookup]` para consulta pública.

