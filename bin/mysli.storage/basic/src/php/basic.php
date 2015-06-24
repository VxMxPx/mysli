<?php

namespace mysli\storage\basic;

__use(__namespace__, '
    mysli.framework.fs/fs,dir,file
');

class basic
{
    /**
     * Select package, or data for particular package.
     * @param  string  $package i.e. mysli.content.blog/posts
     * @param  string  $field   optional `field` or `field_a = ?? AND field_b < ??`
     * @param  mixed   $value   either string or array, see $field
     * @param  boolean $limit   limit results to one
     * @return array
     */
    static function select($package, $field=null, $value=null, $limit=false)
    {
    }
    /**
     * Insert data to particular _table_.
     * @param  string $package
     * @param  array  $data to be inserted
     * @return boolean
     */
    static function insert($package, array $data)
    {
    }
    /**
     * Update date from particular _table_.
     * @param  string $package
     * @param  array  $where primary key equals to...
     * @param  array  $data
     * @return boolean
     */
    static function update($package, $where, array $data)
    {
    }
    /**
     * Delete data from particular _table_.
     * @param  string $package
     * @param  array  $where primary key equals to...
     * @return boolean
     */
    static function delete($package, $where)
    {
    }

    /**
     * Create a new table for particular package.
     * @param  string $package
     * @param  array  $format
     * @return boolean
     */
    static function create($package, array $format)
    {
    }
    /**
     * Drop data for particular package.
     * Either: vendor.package or vendor.package/table
     * @param  string $package
     * @return boolean
     */
    static function drop($package)
    {
    }
}

/*
<?php

define('table', 'mysli.web.users/users');

db::create(table, [
    'id'         => md('m@gaj.st'),
    'mail'       => 'm@gaj.st',
    'password'   => hash::create($password),
    'created_on' => date(),
    'updated_on' => date(),
    'active'     => true,
    'type'       => 2
]);

db::update(table, ['mail' => 'm@gaj.st'], ['mail' => 'marko@gaj.st']);

db::select(table, 'mail = ?? AND active = ??', ['m@gaj.st', true]);

db::create(table, [
    'id'         => 'VARCHAR(40)  REQUIRED PRIMARY KEY',
    'mail'       => 'VARCHAR(256) REQUIRED INDEXED',
    'password'   => 'VARCHAR(40)  REQUIRED',
    'created_on' => 'INTEGER(14)  REQUIRED',
    'updated_on' => 'INTEGER(14)  REQUIRED',
    'active'     => 'INTEGER(1)   DEFAULT true',
    'type'       => 'INTEGER(2)   DEFAULT 4'
]);
 */
