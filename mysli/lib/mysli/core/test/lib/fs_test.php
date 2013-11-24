<?php

namespace Mysli\Core\Lib;

include(__DIR__.'/../../core.php');
\Mysli\Core::init(
    __DIR__.'/public_dummy',
    __DIR__.'/libraries_dummy',
    __DIR__.'/data_dummy'
);

class FsTest extends \PHPUnit_Framework_TestCase
{
    public function test_format_size()
    {
        $this->assertEquals(
            [100, 'bytes'],
            \FS::format_size(100)
        );
        $this->assertEquals(
            [1.9531, 'KB'],
            \FS::format_size(2000)
        );
        $this->assertEquals(
            [3.8147, 'MB'],
            \FS::format_size(4000000)
        );
        $this->assertEquals(
            [7.4506, 'GB'],
            \FS::format_size(8000000000)
        );
    }

    public function test_rename_simple()
    {
        $oldfilename = datpath('fs/lorem.txt');
        $newfilename = datpath('fs/lorem.renamed.txt');

        // This file must exists before we can test rename!
        $this->assertFileExists($oldfilename);
        $this->assertEquals(
            1,
            \FS::rename(
                $oldfilename,
                $newfilename
            )
        );
        $this->assertFileExists($newfilename);
        $this->assertEquals(
            1,
            \FS::rename(
                $newfilename,
                $oldfilename
            )
        );
        $this->assertFileExists($oldfilename);
    }

    public function test_rename_array()
    {
        $old1 = datpath('fs/lorem.txt');
        $old2 = datpath('fs/ipsum.txt');
        $new1 = datpath('fs/lorem.renamed.txt');
        $new2 = datpath('fs/ipsum.renamed.txt');

        // This file must exists before we can test rename!
        $this->assertFileExists($old1);
        $this->assertFileExists($old2);
        $this->assertEquals(
            2,
            \FS::rename([
                $old1 => $new1,
                $old2 => $new2,
            ])
        );
        $this->assertFileExists($new1);
        $this->assertFileExists($new2);
        $this->assertEquals(
            2,
            \FS::rename([
                $new1 => $old1,
                $new2 => $old2,
            ])
        );
        $this->assertFileExists($old1);
        $this->assertFileExists($old2);
    }

    public function test_unique_prefix()
    {
        $file1 = \FS::unique_prefix(datpath('fs/lorem.txt'));
        $file2 = \FS::unique_prefix(datpath('fs/lorem/lorem.txt'));

        $this->assertRegExp(
            '/^[a-fA-F\d]{32}_lorem\.txt$/',
            $file1
        );

        $this->assertNotEquals($file1, $file2);
    }

    public function test_file_extension()
    {
        $this->assertEquals(
            'txt',
            \FS::file_extension('lorem.txt')
        );
        $this->assertEquals(
            'gz',
            \FS::file_extension('file.tar.gz')
        );
    }

    public function test_file_get_name()
    {
        $this->assertEquals(
            'lorem.txt',
            \FS::file_get_name(datpath('fs/lorem.txt'), true)
        );
        $this->assertEquals(
            'lorem',
            \FS::file_get_name(datpath('fs/lorem.txt'), false)
        );
    }

    public function test_file_get_name_folder()
    {
        $this->assertEquals(
            'lorem',
            \FS::file_get_name(datpath('fs/lorem'), false)
        );
    }

    public function test_file_unique_name()
    {
        $this->assertFileExists(datpath('fs/lorem.txt'));
        $this->assertFileExists(datpath('fs/lorem_2.txt'));
        $this->assertEquals(
            datpath('fs/lorem_3.txt'),
            \FS::file_unique_name(datpath('fs/lorem.txt'))
        );
    }

    public function test_file_unique_name_non_existing()
    {
        $filename = datpath('fs/non.txt');

        $this->assertFalse(file_exists($filename));
        $this->assertEquals(
            $filename,
            \FS::file_unique_name($filename)
        );
    }

    public function test_file_read()
    {
        $this->assertEquals(
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
            trim(\FS::file_read(datpath('fs/ipsum.txt')))
        );
    }

    public function test_file_create()
    {
        $filename = datpath('fs/test_file.txt');

        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->assertFalse(file_exists($filename));
        $this->assertTrue(
            \FS::file_create($filename)
        );
        $this->assertFileExists($filename);
        unlink($filename);
    }

    public function test_file_create_file_exists_empty()
    {
        $filename = datpath('fs/test_file.txt');

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertFileExists($filename);
        $this->assertTrue(
            \FS::file_create($filename, true)
        );
        $this->assertEquals(
            '',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_append_existing()
    {
        $filename = datpath('fs/test_file.txt');

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertGreaterThan(
            0,
            \FS::file_append($filename, '67890')
        );
        $this->assertEquals(
            '1234567890',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_append_non_existing()
    {
        $filename = datpath('fs/test_file.txt');

        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->assertFalse(file_exists($filename));
        $this->assertGreaterThan(
            0,
            \FS::file_append($filename, '1234', true)
        );
        $this->assertEquals(
            '1234',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_prepend_existing()
    {
        $filename = datpath('fs/test_file.txt');

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertGreaterThan(
            0,
            \FS::file_prepend($filename, '67890')
        );
        $this->assertEquals(
            '6789012345',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_prepend_non_existing()
    {
        $filename = datpath('fs/test_file.txt');

        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->assertFalse(file_exists($filename));
        $this->assertGreaterThan(
            0,
            \FS::file_prepend($filename, '0123456', true)
        );
        $this->assertEquals(
            '0123456',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_replace_existing()
    {
        $filename = datpath('fs/test_file.txt');

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertGreaterThan(
            0,
            \FS::file_replace($filename, 'abcd')
        );
        $this->assertEquals(
            'abcd',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_replace_non_existing()
    {
        $filename = datpath('fs/test_file.txt');

        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->assertFalse(file_exists($filename));
        $this->assertGreaterThan(
            0,
            \FS::file_replace($filename, 'abcdef', true)
        );
        $this->assertEquals(
            'abcdef',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_empty_existing()
    {
        $filename = datpath('fs/test_file.txt');

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertTrue(
            \FS::file_empty($filename)
        );
        $this->assertEquals(
            '',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_remove()
    {
        $files = [
            datpath('fs/remove_file_1'),
            datpath('fs/remove_file_2'),
            datpath('fs/remove_file_3'),
            datpath('fs/remove_file_4'),
        ];

        foreach ($files as $file) {
            $this->assertTrue(touch($file));
        }

        $this->assertEquals(
            4,
            \FS::file_remove($files)
        );

        foreach ($files as $file) {
            $this->assertFalse(file_exists($file));
        }
    }
}
