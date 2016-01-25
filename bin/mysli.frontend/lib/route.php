<?php

namespace mysli\frontend; class route
{
    const __use = '
        .{ frontend }
        mysli.toolkit.{ output, response }
    ';

    static function error($n)
    {
        if ($n !== 404)
        {
            return false;
        }

        response::set_status(response::status_404_not_found);
        frontend::render([ 'error_404', 'mysli.frontend/error_404' ]);

        return true;
    }
}
