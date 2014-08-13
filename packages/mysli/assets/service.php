<?php

namespace Mysli\Assets;

class Service
{
    use \Mysli\Core\Pkg\Singleton;

    private $assets;

    public function __construct(Assets $assets)
    {
        $this->assets = $assets;
    }

    /**
     * Register template's global functions.
     * --
     * @param  object $tplp mysli/tplp
     * --
     * @return null
     */
    public function register($tplp)
    {
        $tplp->register_function('css', function ($list) {
            return $this->assets->get_tags('css', $list);
        });
        $tplp->register_function('javascript', function ($list) {
            return $this->assets->get_tags('js', $list);
        });
    }
}
