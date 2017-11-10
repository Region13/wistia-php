<?php
namespace Automattic\Wistia\Traits;

use BadMethodCallException;

/**
 * Trait ApiMethodsTrait
 * @package Automattic\Wistia\Traits
 *
 * Add code-complete hints for psudo-methods.
 * @method list_projects(array $param = []) Accepts page and per_page params as array. API defaults to 100 results.
 * @method show_project(string $project_hashed_id)
 * @method create_project(array $project_data)
 * @method update_project(string $project_hashed_id,array $project_data)
 * @method delete_project(string $project_hashed_id)
 * @method copy_project(string $project_hashed_id)
 *
 * @method list_sharings(string $project_hashed_id)
 * @method show_sharing(string $project_hashed_id, int $sharing_id)
 * @method create_sharing(string $project_hashed_id)
 * @method update_sharing(string $project_hashed_id, int $sharing_id, array $sharing_data)
 * @method delete_sharing(string $project_hashed_id, int $sharing_id)
 *
 * @method list_medias(array $param = []) Accepts "page" and "per_page" params.
 * @method show_media(string  $media_hashed_id)
 * @method create_media(string $file_path, array $media_data)
 * @method update_media(string $media_hashed_id, array $media_data)
 * @method delete_media(string $media_hashed_id)
 * @method copy_media(string $media_hashed_id)
 * @method stats_media(string $media_hashed_id)
 *
 * @method show_account(array $param = []) Accepts "page" and "per_page" params.
 *
 * @method show_customizations(string $media_hashed_id)
 * @method create_customizations(string $media_hashed_id, array $customizations_data)
 * @method update_customizations(string $media_hashed_id, array $customization_data)
 * @method delete_customizations(string $media_hashed_id)
 *
 * @method list_captions(string $media_hashed_id)
 * @method show_captions(string $media_hashed_id, string $language_code)
 * @method create_captions(string $media_hashed_id, array $captions_data)
 * @method update_captions(string $media_hashed_id, array $captions_data)
 * @method delete_captions(string $media_hashed_id , string $language_code)
 *
 */
trait ApiMethodsTrait {

    /**
     * Methods allowed for this Trait
     * @var array
     */
    protected $_methods = [
        // Projects
        'list_projects'         => [ 'get', 'projects' ],
        'show_project'          => [ 'get', 'projects/%s' ],
        'create_project'        => [ 'post', 'projects' ],
        'update_project'        => [ 'put', 'projects/%s' ],
        'delete_project'        => [ 'delete', 'projects/%s' ],
        'copy_project'          => [ 'post', 'projects/%s/copy' ],

        // Project Sharings
        'list_sharings'         => [ 'get', 'projects/%s/sharings' ],
        'show_sharing'          => [ 'get', 'projects/%s/sharings/%d' ],
        'create_sharing'        => [ 'post', 'projects/%s/sharings' ],
        'update_sharing'        => [ 'put', 'projects/%s/sharings/%d' ],
        'delete_sharing'        => [ 'delete', 'projects/%s/sharings/%d' ],

        // Medias
        'list_medias'           => [ 'get', 'medias' ],
        'show_media'            => [ 'get', 'medias/%s' ],
        'update_media'          => [ 'put', 'medias/%s' ],
        'delete_media'          => [ 'delete', 'medias/%s' ],
        'copy_media'            => [ 'post', 'medias/%s/copy' ],
        'stats_media'           => [ 'get', 'medias/%s/stats' ],

        // Account
        'show_account'          => [ 'get', 'account' ],

        // Media Customizations
        'show_customizations'   => [ 'get', 'medias/%s/customizations' ],
        'create_customizations' => [ 'post', 'medias/%s/customizations' ],
        'update_customizations' => [ 'put', 'medias/%s/customizations' ],
        'delete_customizations' => [ 'delete', 'medias/%s/customizations' ],

        // Media Captions
        'list_captions'         => [ 'get', 'medias/%s/captions' ],
        'show_captions'         => [ 'get', 'medias/%s/captions/%s' ],
        'create_captions'       => [ 'post', 'medias/%s/captions' ],
        'update_captions'       => [ 'put', 'medias/%s/captions/%s' ],
        'delete_captions'       => [ 'delete', 'medias/%s/captions/%s' ],
    ];

	/**
	 * @return mixed
	 */
    abstract public function get_client();

    /**
     * Call a defined method
     *
     * @param  string $method
     * @param  array $params
     * @return array
     */
    public function __call( $method, $params ) {
        if ( null === $signature = $this->_get_method_signature( $method ) ) {
            throw new BadMethodCallException( 'Method ' . $method . ' not found on ' . get_class() . '.', 500 );
        }

        preg_match_all( '/\%/', $signature[1], $replacements );

        $replacement_count = isset( $replacements[0] ) ? count( $replacements[0] ) : 0;
        $replacement_params = array_splice( $params, 0, $replacement_count );
        array_unshift( $replacement_params, $signature[1] );

        $path = call_user_func_array( 'sprintf', $replacement_params );
        array_unshift( $params, $path );

        return call_user_func_array( [ $this->get_client(), $signature[0] ], $params );
    }

    /**
     * Check if a method exists and return its name and params
     *
     * @param  string $method
     * @return array|null
     * @access protected
     */
    protected function _get_method_signature( $method ) {
        $valid_method = isset( $this->_methods[ $method ] ) &&
                        is_array( $this->_methods[ $method ] ) &&
                        count( $this->_methods[ $method ] ) >= 2;

        if ( $valid_method ) {
            return $this->_methods[ $method ];
        }

        return null;
    }
}