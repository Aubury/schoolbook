<?php

add_action('et_builder_ready', 'aws_divi_register_modules');
add_action('divi_module_library_modules_dependency_tree', 'aws_divi5_register_modules');

function aws_divi_is_readiness_request() {
    return is_admin() &&
        wp_doing_ajax() &&
        isset( $_POST['action'] ) &&
        strpos( sanitize_text_field( wp_unslash( $_POST['action'] ) ), 'et_d5_readiness_' ) === 0;
}

function aws_divi_remove_from_third_party_modules() {
    if ( ! class_exists( 'ET_Builder_Element' ) ) {
        return;
    }

    try {
        $reflection = new ReflectionClass( 'ET_Builder_Element' );
        if ( ! $reflection->hasProperty( '_third_party_modules' ) ) {
            return;
        }

        $property = $reflection->getProperty( '_third_party_modules' );
        $property->setAccessible( true );
        $modules = $property->getValue();

        if ( is_array( $modules ) && isset( $modules['aws'] ) ) {
            unset( $modules['aws'] );
            $property->setValue( null, $modules );
        }
    } catch ( Exception $e ) {
        return;
    }
}

function aws_divi5_register_modules( $dependency_tree ) {

    if (
        ! interface_exists( '\ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface' ) ||
        ! class_exists( '\ET\Builder\Packages\ModuleLibrary\ModuleRegistration' )
    ) {
        return;
    }

    if ( ! class_exists( '\AWSPro\Divi5\Modules\AwsSearch\AwsSearch' ) ) {
        require_once AWS_PRO_DIR . '/includes/modules/divi/divi-5/server/Modules/AwsSearch/AwsSearch.php';
    }

    if ( class_exists( '\AWSPro\Divi5\Modules\AwsSearch\AwsSearch' ) ) {
        $dependency_tree->add_dependency( new \AWSPro\Divi5\Modules\AwsSearch\AwsSearch() );
    }
}

function aws_divi_register_modules() {

    if ( aws_divi_is_readiness_request() ) {
        aws_divi_remove_from_third_party_modules();
    }

    if ( class_exists( 'ET_Builder_Module' ) && ! class_exists( 'Divi_AWS_Module' ) ):

        class Divi_AWS_Module extends ET_Builder_Module {

            public $slug       = 'aws';
            public $vb_support = 'partial';

            public function init() {
                $this->name = esc_html__( 'Advanced Woo Search', 'advanced-woo-search' );
            }

            public function get_fields() {

                $plugin_options = get_option( 'aws_pro_settings' );
                $form_ids = array();
                foreach ( $plugin_options as $instance_id => $instance_options ) {
                    $form_ids[strval($instance_id)] = strval( $instance_id );
                }

                wp_enqueue_style(
                    'aws-divi',
                    AWS_PRO_URL . 'includes/modules/divi/divi.css', array(), AWS_PRO_VERSION
                );

                return array(
                    'placeholder'     => array(
                        'label'           => esc_html__( 'Placeholder', 'advanced-woo-search' ),
                        'type'            => 'text',
                        'option_category' => 'basic_option',
                        'description'     => esc_html__( 'Add placeholder text or leave empty to use default.', 'advanced-woo-search' ),
                        'toggle_slug'     => 'main_content',
                    ),
                    'form_id' => array(
                        'label'             => esc_html__( 'Form ID:', 'advanced-woo-search' ),
                        'type'              => 'select',
                        'option_category'   => 'basic_option',
                        'options'           => $form_ids,
                        'default'           => '1',
                        'toggle_slug'       => 'main_content',
                        'description'       => esc_html__( 'Choose form id', 'advanced-woo-search' ),
                        'mobile_options'    => true,
                    ),
                );
            }

            public function render( $unprocessed_props, $content = null, $render_slug = null ) {
                if ( function_exists( 'aws_get_search_form' ) ) {
                    $form_id = ( isset( $this->props['form_id'] ) && $this->props['form_id'] ) ? $this->props['form_id'] : 1;
                    $args = array();
                    $args['id'] = $form_id;
                    if ( $this->props['placeholder'] ) {
                        $args['placeholder'] = $this->props['placeholder'];
                    }
                    $search_form = aws_get_search_form( false, $args );
                    return $search_form;
                }
                return '';
            }

        }

        new Divi_AWS_Module;

    endif;

}
