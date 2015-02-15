<?php

namespace mysli\util\tplp;

__use(__namespace__, '
    mysli.framework.pkgm
    mysli.framework.fs
');

class util {
    /**
     * Get default paths, define in mysli.pkg.ym
     * @param  string $package
     * @return array  [source, dest]
     */
    static function get_default_paths($package) {
        $meta = pkgm::meta($package);

        $source = fs::pkgpath($package, 'tplp');
        $dest   = fs::datpath(
                    'mysli/util/tplp/cache',
                    str_replace('/', '.', $package));

        if (isset($meta['tplp'])) {
            if (isset($meta['tplp']['source'])) {
                $source = fs::pkgpath($package, $meta['tplp']['source']);
            }
            if (isset($meta['tplp']['destination'])) {
                $dest = fs::pkgpath($package, $meta['tplp']['destination']);
            }
        }

        return [$source, $dest];
    }
}
