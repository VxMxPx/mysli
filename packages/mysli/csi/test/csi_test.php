<?php

namespace Mysli;

include(__DIR__.'/../csi.php');    // Include self
include(__DIR__.'/../../core/core.php'); // CORE is needed!
new \Mysli\Core(
    realpath(__DIR__.'/dummy'),
    realpath(__DIR__.'/dummy')
);

class CSITest extends \PHPUnit_Framework_TestCase
{
    protected function new_instance($id)
    {
        return new \Mysli\CSI($id);
    }

    public function test_get_id()
    {
        $csi = $this->new_instance('mysli/test/method');
        $this->assertEquals(
            'mysli_test_method',
            $csi->get_id()
        );
    }

    public function test_get_id_messy()
    {
        $csi = $this->new_instance('Mysli/"test"/method!   ');
        $this->assertEquals(
            'mysli_test_method',
            $csi->get_id()
        );
    }

    public function test_validate_value_assignation()
    {
        $csi = new \Mysli\CSI('mysli/test');
        $csi->input('email');
        $csi->input('name');
        $csi->validate([
            'csi_mysli_test_email' => 'm@mysli.io',
            'csi_mysli_test_name'  => 'Marko',
            'csi_mysli_test_x'     => 'X',
            'y'                => 'Y'
        ]);
        $this->assertEquals(
            [
                'email' => 'm@mysli.io',
                'name'  => 'Marko'
            ],
            $csi->get_values()
        );
    }

    public function test_validate_callback()
    {
        $csi = new \Mysli\CSI('mysli/test');
        $csi->input('email');
        $csi->input('name');
        $csi->on_validate(function (&$fields) {
            $fields['email']['value'] = 'i@mysli.io';
            $fields['name']['value']  = 'Inna';
            return true;
        });

        $csi->validate([
            'csi_mysli_test_email' => 'm@mysli.io',
            'csi_mysli_test_name'  => 'Marko',
            'csi_mysli_test_x'     => 'X',
            'y'                => 'Y'
        ]);
        $this->assertEquals(
            [
                'email' => 'i@mysli.io',
                'name'  => 'Inna'
            ],
            $csi->get_values()
        );
    }

    public function test_validate_modify_status()
    {
        $csi = new \Mysli\CSI('mysli/test');

        // DEFAULT
        // $csi->on_validate(function () { });
        // $csi->validate();
        // $this->assertEquals('none', $csi->status());

        // TRUE
        $csi->on_validate(function () { return true; });
        $csi->validate();
        $this->assertEquals('success', $csi->status());

        // FALSE
        $csi->on_validate(function () { return false; });
        $csi->validate();
        $this->assertEquals('failed', $csi->status());
    }

    public function test_validate_field_callback()
    {
        $csi = new \Mysli\CSI('mysli/test');
        $csi->input('email', '', '', function (&$field) {
            $field['value'] = 'modified@mysli.io';
        });

        $csi->validate([
            'csi_mysli_test_email' => 'm@mysli.io',
        ]);

        $this->assertEquals(
            'modified@mysli.io',
            $csi->get('email')
        );
    }
}
