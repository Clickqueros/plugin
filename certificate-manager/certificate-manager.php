<?php
/**
 * Plugin Name: Certificate Manager
 * Description: Allows users to create certificates and manage approvals.
 * Version: 1.0.0
 * Author: Eddier Acosta
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CM_Certificate_Manager {

    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_certificate', array( $this, 'save_certificate_meta' ) );
        add_shortcode( 'certificate_form', array( $this, 'certificate_form_shortcode' ) );
        add_shortcode( 'certificate_lookup', array( $this, 'certificate_lookup_shortcode' ) );
        add_action( 'wp_loaded', array( $this, 'handle_form_submission' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    public function activate() {
        $this->register_post_type();
        flush_rewrite_rules();
        // Capabilities
        $roles = array( 'administrator', 'editor' );
        foreach ( $roles as $role_name ) {
            $role = get_role( $role_name );
            if ( $role ) {
                $role->add_cap( 'read_certificate' );
                $role->add_cap( 'edit_certificate' );
                $role->add_cap( 'edit_certificates' );
                $role->add_cap( 'edit_others_certificates' );
                $role->add_cap( 'publish_certificates' );
                $role->add_cap( 'read_private_certificates' );
            }
        }
    }

    public function register_post_type() {
        $labels = array(
            'name'               => 'Certificados',
            'singular_name'      => 'Certificado',
            'add_new'            => 'Agregar Nuevo',
            'add_new_item'       => 'Agregar Certificado',
            'edit_item'          => 'Editar Certificado',
            'new_item'           => 'Nuevo Certificado',
            'all_items'          => 'Todos los Certificados',
            'view_item'          => 'Ver Certificado',
            'search_items'       => 'Buscar Certificados',
            'not_found'          => 'No se encontraron certificados',
            'not_found_in_trash' => 'No se encontraron certificados en la papelera',
            'menu_name'          => 'Certificados'
        );
        $caps = array(
            'edit_post'          => 'edit_certificate',
            'read_post'          => 'read_certificate',
            'delete_post'        => 'delete_certificate',
            'edit_posts'         => 'edit_certificates',
            'edit_others_posts'  => 'edit_others_certificates',
            'publish_posts'      => 'publish_certificates',
            'read_private_posts' => 'read_private_certificates',
        );
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'capability_type'    => 'certificate',
            'capabilities'       => $caps,
            'map_meta_cap'       => true,
            'supports'           => array( 'title' ),
        );
        register_post_type( 'certificate', $args );
    }

    public function add_meta_boxes() {
        add_meta_box( 'certificate_details', 'Detalles del Certificado', array( $this, 'render_meta_box' ), 'certificate', 'normal', 'default' );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'save_certificate_meta', 'certificate_meta_nonce' );
        $first_name = get_post_meta( $post->ID, '_cm_first_name', true );
        $last_name  = get_post_meta( $post->ID, '_cm_last_name', true );
        $position   = get_post_meta( $post->ID, '_cm_position', true );
        $course     = get_post_meta( $post->ID, '_cm_course', true );
        $code       = get_post_meta( $post->ID, '_cm_code', true );
        echo '<p><label>Nombre:<br /><input type="text" name="cm_first_name" value="' . esc_attr( $first_name ) . '" /></label></p>';
        echo '<p><label>Apellido:<br /><input type="text" name="cm_last_name" value="' . esc_attr( $last_name ) . '" /></label></p>';
        echo '<p><label>Cargo:<br /><input type="text" name="cm_position" value="' . esc_attr( $position ) . '" /></label></p>';
        echo '<p><label>Curso:<br /><input type="text" name="cm_course" value="' . esc_attr( $course ) . '" /></label></p>';
        echo '<p><label>Código del Certificado:<br /><input type="text" name="cm_code" value="' . esc_attr( $code ) . '" /></label></p>';
    }

    public function save_certificate_meta( $post_id ) {
        if ( ! isset( $_POST['certificate_meta_nonce'] ) || ! wp_verify_nonce( $_POST['certificate_meta_nonce'], 'save_certificate_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['cm_first_name'] ) ) {
            update_post_meta( $post_id, '_cm_first_name', sanitize_text_field( $_POST['cm_first_name'] ) );
        }
        if ( isset( $_POST['cm_last_name'] ) ) {
            update_post_meta( $post_id, '_cm_last_name', sanitize_text_field( $_POST['cm_last_name'] ) );
        }
        if ( isset( $_POST['cm_position'] ) ) {
            update_post_meta( $post_id, '_cm_position', sanitize_text_field( $_POST['cm_position'] ) );
        }
        if ( isset( $_POST['cm_course'] ) ) {
            update_post_meta( $post_id, '_cm_course', sanitize_text_field( $_POST['cm_course'] ) );
        }
        if ( isset( $_POST['cm_code'] ) ) {
            update_post_meta( $post_id, '_cm_code', sanitize_text_field( $_POST['cm_code'] ) );
        }
    }

    public function certificate_form_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>Debes iniciar sesión para crear certificados.</p>';
        }
        ob_start();
        ?>
        <form method="post">
            <?php wp_nonce_field( 'cm_create_certificate', 'cm_certificate_nonce' ); ?>
            <p><label>Nombre:<br /><input type="text" name="cm_first_name" required /></label></p>
            <p><label>Apellido:<br /><input type="text" name="cm_last_name" required /></label></p>
            <p><label>Cargo:<br /><input type="text" name="cm_position" required /></label></p>
            <p><label>Curso:<br /><input type="text" name="cm_course" required /></label></p>
            <p><label>Código del Certificado:<br /><input type="text" name="cm_code" required /></label></p>
            <p><input type="submit" name="cm_create_certificate" value="Crear Certificado" /></p>
        </form>
        <?php
        return ob_get_clean();
    }

    public function handle_form_submission() {
        if ( isset( $_POST['cm_create_certificate'] ) && isset( $_POST['cm_certificate_nonce'] ) && wp_verify_nonce( $_POST['cm_certificate_nonce'], 'cm_create_certificate' ) ) {
            $first = sanitize_text_field( $_POST['cm_first_name'] );
            $last  = sanitize_text_field( $_POST['cm_last_name'] );
            $pos   = sanitize_text_field( $_POST['cm_position'] );
            $course= sanitize_text_field( $_POST['cm_course'] );
            $code  = sanitize_text_field( $_POST['cm_code'] );
            $post_id = wp_insert_post( array(
                'post_type'   => 'certificate',
                'post_status' => 'draft',
                'post_title'  => $code,
                'post_author' => get_current_user_id(),
            ) );
            if ( $post_id ) {
                update_post_meta( $post_id, '_cm_first_name', $first );
                update_post_meta( $post_id, '_cm_last_name', $last );
                update_post_meta( $post_id, '_cm_position', $pos );
                update_post_meta( $post_id, '_cm_course', $course );
                update_post_meta( $post_id, '_cm_code', $code );
                $pdf = $this->generate_pdf( $first, $last, $pos, $course, $code );
                if ( $pdf ) {
                    update_post_meta( $post_id, '_cm_pdf', $pdf );
                }
                wp_redirect( add_query_arg( array( 'cm_created' => 1 ) ) );
                exit;
            }
        }
        if ( isset( $_GET['cm_send_for_approval'] ) ) {
            $id = intval( $_GET['cm_send_for_approval'] );
            $post = get_post( $id );
            if ( $post && $post->post_type === 'certificate' && $post->post_author == get_current_user_id() ) {
                wp_update_post( array( 'ID' => $id, 'post_status' => 'pending' ) );
                wp_redirect( remove_query_arg( 'cm_send_for_approval' ) );
                exit;
            }
        }
        if ( isset( $_GET['cm_approve'] ) && current_user_can( 'publish_certificates' ) ) {
            $id = intval( $_GET['cm_approve'] );
            $post = get_post( $id );
            if ( $post && $post->post_type === 'certificate' ) {
                wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
                wp_redirect( remove_query_arg( 'cm_approve' ) );
                exit;
            }
        }
    }

    private function generate_pdf( $first, $last, $position, $course, $code ) {
        if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
            return false;
        }
        $dompdf = new \Dompdf\Dompdf();
        $settings = get_option( 'cm_settings', array() );
        $bg_image   = isset( $settings['image'] ) ? $settings['image'] : '';
        $name_x     = isset( $settings['name_x'] ) ? $settings['name_x'] : 0;
        $name_y     = isset( $settings['name_y'] ) ? $settings['name_y'] : 0;
        $position_x = isset( $settings['position_x'] ) ? $settings['position_x'] : 0;
        $position_y = isset( $settings['position_y'] ) ? $settings['position_y'] : 0;
        $course_x   = isset( $settings['course_x'] ) ? $settings['course_x'] : 0;
        $course_y   = isset( $settings['course_y'] ) ? $settings['course_y'] : 0;
        $code_x     = isset( $settings['code_x'] ) ? $settings['code_x'] : 0;
        $code_y     = isset( $settings['code_y'] ) ? $settings['code_y'] : 0;
        ob_start();
        include plugin_dir_path( __FILE__ ) . 'templates/certificate-template.php';
        $html = ob_get_clean();
        $dompdf->loadHtml( $html );
        $dompdf->setPaper( 'A4', 'landscape' );
        $dompdf->render();
        $upload = wp_upload_dir();
        $dir = trailingslashit( $upload['basedir'] ) . 'certificates';
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }
        $file = trailingslashit( $dir ) . $code . '.pdf';
        file_put_contents( $file, $dompdf->output() );
        return $file;
    }

    public function certificate_lookup_shortcode() {
        ob_start();
        ?>
        <form method="get">
            <p><label>Código:<br /><input type="text" name="cm_search_code" /></label></p>
            <p><input type="submit" value="Buscar" /></p>
        </form>
        <?php
        if ( isset( $_GET['cm_search_code'] ) ) {
            $code = sanitize_text_field( $_GET['cm_search_code'] );
            $query = new WP_Query( array(
                'post_type'  => 'certificate',
                'post_status'=> 'publish',
                'meta_key'   => '_cm_code',
                'meta_value' => $code,
                'posts_per_page' => 1,
            ) );
            if ( $query->have_posts() ) {
                $query->the_post();
                echo '<h3>' . esc_html( get_post_meta( get_the_ID(), '_cm_first_name', true ) ) . ' ' . esc_html( get_post_meta( get_the_ID(), '_cm_last_name', true ) ) . '</h3>';
                echo '<p>Cargo: ' . esc_html( get_post_meta( get_the_ID(), '_cm_position', true ) ) . '</p>';
                echo '<p>Curso: ' . esc_html( get_post_meta( get_the_ID(), '_cm_course', true ) ) . '</p>';
                $pdf = get_post_meta( get_the_ID(), '_cm_pdf', true );
                if ( $pdf ) {
                    $url = str_replace( WP_CONTENT_DIR, content_url(), $pdf );
                    echo '<p><a href="' . esc_url( $url ) . '" download>Descargar PDF</a></p>';
                }
            } else {
                echo '<p>No encontrado.</p>';
            }
            wp_reset_postdata();
        }
        return ob_get_clean();
    }

    public function add_settings_page() {
        add_options_page( 'Certificate Settings', 'Certificate Settings', 'manage_options', 'cm-settings', array( $this, 'render_settings_page' ) );
    }

    public function register_settings() {
        register_setting( 'cm_settings_group', 'cm_settings' );
    }

    public function enqueue_admin_scripts( $hook ) {
        if ( $hook === 'settings_page_cm-settings' ) {
            wp_enqueue_media();
        }
    }

    public function render_settings_page() {
        $settings = get_option( 'cm_settings', array() );
        $defaults = array(
            'image'      => '',
            'name_x'     => 100,
            'name_y'     => 100,
            'position_x' => 100,
            'position_y' => 150,
            'course_x'   => 100,
            'course_y'   => 200,
            'code_x'     => 100,
            'code_y'     => 250,
        );
        $settings = wp_parse_args( $settings, $defaults );
        ?>
        <div class="wrap">
            <h1>Certificate Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'cm_settings_group' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Background Image</th>
                        <td>
                            <input type="text" name="cm_settings[image]" id="cm_settings_image" value="<?php echo esc_attr( $settings['image'] ); ?>" class="regular-text" />
                            <input type="button" class="button" id="cm_settings_image_button" value="Upload" />
                            <p class="description">Select an image to use as the certificate background.</p>
                        </td>
                    </tr>
                    <tr><th scope="row">Name X</th><td><input type="number" name="cm_settings[name_x]" value="<?php echo esc_attr( $settings['name_x'] ); ?>" /></td></tr>
                    <tr><th scope="row">Name Y</th><td><input type="number" name="cm_settings[name_y]" value="<?php echo esc_attr( $settings['name_y'] ); ?>" /></td></tr>
                    <tr><th scope="row">Position X</th><td><input type="number" name="cm_settings[position_x]" value="<?php echo esc_attr( $settings['position_x'] ); ?>" /></td></tr>
                    <tr><th scope="row">Position Y</th><td><input type="number" name="cm_settings[position_y]" value="<?php echo esc_attr( $settings['position_y'] ); ?>" /></td></tr>
                    <tr><th scope="row">Course X</th><td><input type="number" name="cm_settings[course_x]" value="<?php echo esc_attr( $settings['course_x'] ); ?>" /></td></tr>
                    <tr><th scope="row">Course Y</th><td><input type="number" name="cm_settings[course_y]" value="<?php echo esc_attr( $settings['course_y'] ); ?>" /></td></tr>
                    <tr><th scope="row">Code X</th><td><input type="number" name="cm_settings[code_x]" value="<?php echo esc_attr( $settings['code_x'] ); ?>" /></td></tr>
                    <tr><th scope="row">Code Y</th><td><input type="number" name="cm_settings[code_y]" value="<?php echo esc_attr( $settings['code_y'] ); ?>" /></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('#cm_settings_image_button').on('click', function(e){
                e.preventDefault();
                var frame = wp.media({title:'Select or Upload Image', button:{text:'Use this image'}, multiple:false});
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#cm_settings_image').val(attachment.url);
                });
                frame.open();
            });
        });
        </script>
        <?php
    }
}

new CM_Certificate_Manager();
