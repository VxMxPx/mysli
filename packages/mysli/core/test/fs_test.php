<?php

namespace Mysli\Core\Util;

// Exceptions, etc..
include(__DIR__.'/../core.php');
$core = new \Mysli\Core(__DIR__.'/dummy', __DIR__.'/dummy');

class FsTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $dir = $this->data();
        if (!file_exists($dir) || !is_dir($dir)) {
            trigger_error(
                'Expecting following directory to exists: ' . $dir,
                E_USER_ERROR
            );
        }
        $files = scandir($dir);
        if (count($files) > 2) {
            trigger_error(
                'Expecting testing directory to be empty: '. $dir,
                E_USER_ERROR
            );
        }
    }

    // Get full absolute data path...
    protected function data($segment = null)
    {
        return $segment ? ds(__DIR__, 'dummy', $segment) : ds(__DIR__, 'dummy');
    }

    // Test Format Size --------------------------------------------------------
    public function test_format_size()
    {
        $this->assertEquals(
            [100, 'bytes'],
            \Core\FS::format_size(100)
        );
        $this->assertEquals(
            [1.9531, 'KB'],
            \Core\FS::format_size(2000)
        );
        $this->assertEquals(
            [3.8147, 'MB'],
            \Core\FS::format_size(4000000)
        );
        $this->assertEquals(
            [7.4506, 'GB'],
            \Core\FS::format_size(8000000000)
        );
    }

    // Test Rename -------------------------------------------------------------
    public function test_rename_simple()
    {
        $oldfilename = $this->data('lorem.txt');
        $newfilename = $this->data('lorem.renamed.txt');

        // Create file
        file_put_contents($oldfilename, 'Lorem ipsum dolor sit amet.');
        //unlink($newfilename);

        // This file must exists before we can test rename!
        $this->assertFileExists($oldfilename);
        $this->assertEquals(
            1,
            \Core\FS::rename(
                $oldfilename,
                $newfilename
            )
        );
        $this->assertFileExists($newfilename);
        unlink($newfilename);
    }

    public function test_rename_array()
    {
        $old1 = $this->data('lorem.txt');
        $old2 = $this->data('ipsum.txt');
        $new1 = $this->data('lorem.renamed.txt');
        $new2 = $this->data('ipsum.renamed.txt');

        file_put_contents($old1, 'Lorem.');
        file_put_contents($old2, 'Lorem.');
        // unlink($new1);
        // unlink($new2);

        // This file must exists before we can test rename!
        $this->assertFileExists($old1);
        $this->assertFileExists($old2);
        $this->assertEquals(
            2,
            \Core\FS::rename([
                $old1 => $new1,
                $old2 => $new2,
            ])
        );

        unlink($new1);
        unlink($new2);
    }

    // Test Unique Prefix ------------------------------------------------------
    public function test_unique_prefix()
    {
        $file1 = \Core\FS::unique_prefix($this->data('lorem.txt'));
        $file2 = \Core\FS::unique_prefix($this->data('lorem/lorem.txt'));

        $this->assertRegExp(
            '/^[a-fA-F\d]{32}_lorem\.txt$/',
            $file1
        );

        $this->assertNotEquals($file1, $file2);
    }

    // Test File Extension -----------------------------------------------------
    public function test_file_extension()
    {
        $this->assertEquals(
            'txt',
            \Core\FS::file_extension('lorem.txt')
        );
        $this->assertEquals(
            'gz',
            \Core\FS::file_extension('file.tar.gz')
        );
    }

    // Test File Get Name ------------------------------------------------------
    public function test_file_get_name()
    {
        $this->assertEquals(
            'lorem.txt',
            \Core\FS::file_get_name($this->data('lorem.txt'), true)
        );
        $this->assertEquals(
            'lorem',
            \Core\FS::file_get_name($this->data('lorem.txt'), false)
        );
    }

    public function test_file_get_name_folder()
    {
        $this->assertEquals(
            'lorem',
            \Core\FS::file_get_name($this->data('lorem'), false)
        );
    }

    // Test File Unqiue Name ---------------------------------------------------
    public function test_unique_name()
    {
        $file1 = $this->data('lorem.txt');
        $file2 = $this->data('lorem_2.txt');
        file_put_contents($file1, 'Lorem.');
        file_put_contents($file2, 'Lorem.');

        $this->assertFileExists($file1);
        $this->assertFileExists($file2);
        $this->assertEquals(
            $this->data('lorem_3.txt'),
            \Core\FS::unique_name($file1)
        );

        unlink($file1);
        unlink($file2);
    }

    public function test_unique_name_directory()
    {
        $dir1 = $this->data('lorem.txt');
        $dir2 = $this->data('lorem_2.txt');
        mkdir($dir1);
        mkdir($dir2);

        $this->assertFileExists($dir1);
        $this->assertFileExists($dir2);
        $this->assertEquals(
            $this->data('lorem_3.txt'),
            \Core\FS::unique_name($dir1)
        );

        rmdir($dir1);
        rmdir($dir2);
    }

    public function test_unique_name_non_existing()
    {
        $filename = $this->data('non.txt');

        $this->assertFalse(file_exists($filename));
        $this->assertEquals(
            $filename,
            \Core\FS::unique_name($filename)
        );
    }

    // Test File Read ----------------------------------------------------------
    public function test_file_read()
    {
        $file = $this->data('lorem.txt');
        file_put_contents($file, 'Lorem ipsum dolor.');

        $this->assertEquals(
            'Lorem ipsum dolor.',
            \Core\FS::file_read($file)
        );

        unlink($file);
    }

    // Test File Create --------------------------------------------------------
    public function test_file_create()
    {
        $filename = $this->data('test_file.txt');

        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->assertFalse(file_exists($filename));
        $this->assertTrue(
            \Core\FS::file_create($filename)
        );
        $this->assertFileExists($filename);
        unlink($filename);
    }

    public function test_file_create_file_exists_empty()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertFileExists($filename);
        $this->assertTrue(
            \Core\FS::file_create($filename, true)
        );
        $this->assertEquals(
            '',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    // Test File Append --------------------------------------------------------
    public function test_file_append_existing()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertGreaterThan(
            0,
            \Core\FS::file_append($filename, '67890')
        );
        $this->assertEquals(
            '1234567890',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_append_non_existing()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertFalse(file_exists($filename));
        $this->assertGreaterThan(
            0,
            \Core\FS::file_append($filename, '1234', true)
        );
        $this->assertEquals(
            '1234',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    // Test File Prepend -------------------------------------------------------
    public function test_file_prepend_existing()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertGreaterThan(
            0,
            \Core\FS::file_prepend($filename, '67890')
        );
        $this->assertEquals(
            '6789012345',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_prepend_non_existing()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertFalse(file_exists($filename));
        $this->assertGreaterThan(
            0,
            \Core\FS::file_prepend($filename, '0123456', true)
        );
        $this->assertEquals(
            '0123456',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    // File Replace ------------------------------------------------------------
    public function test_file_replace_existing()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertGreaterThan(
            0,
            \Core\FS::file_replace($filename, 'abcd')
        );
        $this->assertEquals(
            'abcd',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    public function test_file_replace_non_existing()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertFalse(file_exists($filename));
        $this->assertGreaterThan(
            0,
            \Core\FS::file_replace($filename, 'abcdef', true)
        );
        $this->assertEquals(
            'abcdef',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    // File Empty ------------------------------------------------------------
    public function test_file_empty_existing()
    {
        $filename = $this->data('test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

        $this->assertEquals(
            5,
            file_put_contents($filename, '12345')
        );
        $this->assertTrue(
            \Core\FS::file_empty($filename)
        );
        $this->assertEquals(
            '',
            file_get_contents($filename)
        );
        unlink($filename);
    }

    // File Remove -------------------------------------------------------------
    public function test_file_remove()
    {
        $files = [
            $this->data('remove_file_1'),
            $this->data('remove_file_2'),
            $this->data('remove_file_3'),
            $this->data('remove_file_4'),
        ];

        foreach ($files as $file) {
            $this->assertTrue(touch($file));
        }

        $this->assertEquals(
            4,
            \Core\FS::file_remove($files)
        );

        foreach ($files as $file) {
            $this->assertFalse(file_exists($file));
        }
    }

    // File Copy ---------------------------------------------------------------
    public function test_file_copy()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        //unlink($dest);

        $this->assertFalse(file_exists($dest));
        $this->assertFileExists($file);

        $this->assertEquals(
            1,
            \Core\FS::file_copy($file, $dest)
        );

        $this->assertFileExists($dest);
        unlink($file);
        unlink($dest);
    }

    public function test_file_copy_array()
    {
        $file1 = $this->data('lorem.txt');
        $file2 = $this->data('ipsum.txt');
        $dest1 = $this->data('lorem_copy.txt');
        $dest2 = $this->data('fs.ipsum_copy.txt');
        file_put_contents($file1, 'Lorem.');
        file_put_contents($file2, 'Lorem.');
        //unlink($dest1);
        //unlink($dest2);

        $this->assertFalse(file_exists($dest1));
        $this->assertFalse(file_exists($dest2));
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);

        $this->assertEquals(
            2,
            \Core\FS::file_copy([
                $file1 => $dest1,
                $file2 => $dest2,
            ])
        );

        $this->assertFileExists($dest1);
        $this->assertFileExists($dest2);
        unlink($file1);
        unlink($file2);
        unlink($dest1);
        unlink($dest2);
    }

    public function test_file_copy_exists_replace()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \Core\FS::file_copy($file, $dest, \Core\FS::EXISTS_REPLACE)
        );

        $this->assertFileExists($dest);
        $this->assertEquals(
            'Lorem.',
            file_get_contents($dest)
        );
        unlink($file);
        unlink($dest);
    }

    public function test_file_copy_exists_rename()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        $renamed = $this->data('lorem_copy_2.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \Core\FS::file_copy($file, $dest, \Core\FS::EXISTS_RENAME)
        );

        $this->assertFileExists($renamed);
        $this->assertEquals(
            'Lorem.',
            file_get_contents($renamed)
        );
        unlink($file);
        unlink($dest);
        unlink($renamed);
    }

    public function test_file_copy_exists_ignore()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            0,
            \Core\FS::file_copy($file, $dest, \Core\FS::EXISTS_IGNORE)
        );

        $this->assertEquals(
            'Ipsum.',
            file_get_contents($dest)
        );
        unlink($file);
        unlink($dest);
    }

    /**
     * @expectedException   \Mysli\Core\FSException
     * @expectedExceptionCode 10
     */
    public function test_file_copy_exists_error()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \Core\FS::file_copy($file, $dest, \Core\FS::EXISTS_ERROR)
            );
        } catch (\Mysli\Core\FSException $e) {
            unlink($file);
            unlink($dest);
            throw $e;
        }
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 20
     */
    public function test_file_copy_invalid_exists_value()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \Core\FS::file_copy($file, $dest, 'Invalid')
            );
        } catch (\Mysli\Core\ValueException $e) {
            unlink($file);
            unlink($dest);
            throw $e;
        }
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 10
     */
    public function test_file_copy_source_not_found()
    {
        $file = $this->data('non_existant.txt');
        if (file_exists($file)) {
            unlink($file);
        }
        $this->assertFalse(file_exists($file));
        $this->assertEquals(
            0,
            \Core\FS::file_copy($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 11
     */
    public function test_file_copy_source_is_dir()
    {
        $file = $this->data();
        $this->assertFileExists($file);
        $this->assertEquals(
            0,
            \Core\FS::file_copy($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 12
     */
    public function test_file_copy_dest_not_dir()
    {
        $file = $this->data('lorem.txt');
        file_put_contents($file, 'Lorem.');
        $this->assertFileExists($file);
        try {
            $this->assertEquals(
                0,
                \Core\FS::file_copy($file, null)
            );
        } catch (\Mysli\Core\ValueException $e) {
            unlink($file);
            throw $e;
        }
    }

    // File Move ---------------------------------------------------------------
    public function test_file_move()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_moved.txt');
        file_put_contents($file, 'Lorem.');
        //unlink($dest);

        $this->assertFalse(file_exists($dest));
        $this->assertFileExists($file);

        $this->assertEquals(
            1,
            \Core\FS::file_move($file, $dest)
        );

        $this->assertFalse(file_exists($file));
        $this->assertFileExists($dest);
        unlink($dest);
    }

    public function test_file_move_array()
    {
        $file1 = $this->data('lorem.txt');
        $file2 = $this->data('ipsum.txt');
        $dest1 = $this->data('lorem_moved.txt');
        $dest2 = $this->data('fs.ipsum_moved.txt');
        file_put_contents($file1, 'Lorem.');
        file_put_contents($file2, 'Lorem.');
        //unlink($dest1);
        //unlink($dest2);

        $this->assertFalse(file_exists($dest1));
        $this->assertFalse(file_exists($dest2));
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);

        $this->assertEquals(
            2,
            \Core\FS::file_move([
                $file1 => $dest1,
                $file2 => $dest2,
            ])
        );

        $this->assertFileExists($dest1);
        $this->assertFileExists($dest2);
        $this->assertFalse(file_exists($file1));
        $this->assertFalse(file_exists($file2));
        unlink($dest1);
        unlink($dest2);
    }

    public function test_file_move_exists_replace()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \Core\FS::file_move($file, $dest, \Core\FS::EXISTS_REPLACE)
        );

        $this->assertFalse(file_exists($file));
        $this->assertFileExists($dest);
        $this->assertEquals(
            'Lorem.',
            file_get_contents($dest)
        );
        unlink($dest);
    }

    public function test_file_move_exists_rename()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        $renamed = $this->data('lorem_copy_2.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \Core\FS::file_move($file, $dest, \Core\FS::EXISTS_RENAME)
        );

        $this->assertFalse(file_exists($file));
        $this->assertFileExists($renamed);
        $this->assertEquals(
            'Lorem.',
            file_get_contents($renamed)
        );
        unlink($dest);
        unlink($renamed);
    }

    public function test_file_move_exists_ignore()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            0,
            \Core\FS::file_move($file, $dest, \Core\FS::EXISTS_IGNORE)
        );

        $this->assertFileExists($file);
        $this->assertEquals(
            'Ipsum.',
            file_get_contents($dest)
        );
        unlink($file);
        unlink($dest);
    }

    /**
     * @expectedException   \Mysli\Core\FSException
     * @expectedExceptionCode 10
     */
    public function test_file_move_exists_error()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \Core\FS::file_move($file, $dest, \Core\FS::EXISTS_ERROR)
            );
        } catch (\Mysli\Core\FSException $e) {
            unlink($file);
            unlink($dest);
            throw $e;
        }
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 20
     */
    public function test_file_move_invalid_exists_value()
    {
        $file = $this->data('lorem.txt');
        $dest = $this->data('lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \Core\FS::file_move($file, $dest, 'Invalid')
            );
        } catch (\Mysli\Core\ValueException $e) {
            unlink($file);
            unlink($dest);
            throw $e;
        }
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 10
     */
    public function test_file_move_source_not_found()
    {
        $file = $this->data('non_existant.txt');
        if (file_exists($file)) {
            unlink($file);
        }
        $this->assertFalse(file_exists($file));
        $this->assertEquals(
            0,
            \Core\FS::file_move($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 11
     */
    public function test_file_move_source_is_dir()
    {
        $file = $this->data();
        $this->assertFileExists($file);
        $this->assertEquals(
            0,
            \Core\FS::file_move($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 12
     */
    public function test_file_move_dest_not_dir()
    {
        $file = $this->data('lorem.txt');
        file_put_contents($file, 'Lorem.');
        $this->assertFileExists($file);
        try {
            $this->assertEquals(
                0,
                \Core\FS::file_move($file, null)
            );
        } catch (\Mysli\Core\ValueException $e) {
            unlink($file);
            throw $e;
        }
    }

    // File Search -------------------------------------------------------------
    public function test_file_search()
    {
        $files = [
            'file1.txt', 'file2.txt', 'file_2.jpg', 'picture.jpg', 'pic.JPG',
            'DC1234.JPEG', 'file'
        ];
        $endings = [
            'test/DC1234.JPEG', 'test/file_2.jpg', 'test/pic.JPG', 'test/picture.jpg',
            'DC1234.JPEG', 'file_2.jpg', 'pic.JPG', 'picture.jpg',
        ];
        mkdir($this->data('test'));
        foreach ($files as $file) {
            $file_sub = $this->data('test/' . $file);
            $file = $this->data('' . $file);
            file_put_contents($file, 'null');
            file_put_contents($file_sub, 'null');
            $this->assertFileExists($file);
        }
        $results =  \Core\FS::file_search(
            $this->data(),
            '/(.*?)\.jpe?g/i'
        );
        // Remove path information
        foreach ($results as &$result) {
            $result = substr($result, strlen($this->data()) + 1);
        }

        foreach ($endings as $file) {
            $this->assertTrue(in_array($file, $results), 'File not found: ' . $file);
        }

        foreach ($files as $file) {
            $file_sub = $this->data('test/' . $file);
            $file = $this->data('' . $file);
            unlink($file);
            unlink($file_sub);
        }
        rmdir($this->data('test'));
    }

    // File Signature ----------------------------------------------------------
    public function test_file_signature()
    {
        $file1 = $this->data('test1.txt');
        $file2 = $this->data('test2.txt');
        file_put_contents($file1, 'Lorem ipsum.');
        file_put_contents($file2, '12345 67890.');
        $this->assertEquals(
            [
                '4e9a74ac6861b061fd45db860c6247ca',
                '7c33b1ed131f9e3e7d6f7583b4556df8'
            ],
            \Core\FS::file_signature([$file1, $file2])
        );
        unlink($file1);
        unlink($file2);
    }

    // Is Public ---------------------------------------------------------------
    // public function test_is_public_not()
    // {
    //     $file = $this->data('my-file.txt');
    //     file_put_contents($file, '1234');
    //     $this->assertFalse(\Core\FS::is_public($file));
    //     unlink($file);
    // }

    // public function test_is_public_true()
    // {
    //     $file = pubpath('test.txt');
    //     file_put_contents($file, '1234');
    //     $this->assertTrue(\Core\FS::is_public($file));
    //     unlink($file);
    // }

    // File Get Uri ------------------------------------------------------------
    // public function test_get_uri()
    // {
    //     $file = pubpath('test.txt');
    //     file_put_contents($file, '1234');
    //     $this->assertEquals(
    //         'test.txt',
    //         \Core\FS::get_uri($file)
    //     );
    //     unlink($file);
    // }

    // File Get Url ------------------------------------------------------------
    // NOTE: Note part of the FS class anymore!
    // public function test_get_url()
    // {
    //     $file = pubpath('test.txt');
    //     file_put_contents($file, '1234');
    //     $this->assertEquals(
    //         // This url is set in data_dummy/core/cfg.json
    //         'http:://localhost/test.txt',
    //         \Core\FS::get_url($file)
    //     );
    //     unlink($file);
    // }

    // Dir Get Signatures
    public function test_dir_signatures()
    {
        $file1 = $this->data('lorem_1.txt');
        $file2 = $this->data('lorem_2.txt');
        file_put_contents($file1, 'Lorem ipsum.');
        file_put_contents($file2, '12345 67890.');
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);
        $this->assertEquals(
            '4e9a74ac6861b061fd45db860c6247ca.7c33b1ed131f9e3e7d6f7583b4556df8',
            implode('.', \Core\FS::dir_signatures($this->data()))
        );
        unlink($file1);
        unlink($file2);
    }

    public function test_dir_signatures_sub_dir()
    {
        $file1 = $this->data('lorem_1.txt');
        $dir   = $this->data('dir1');
        $file2 = $this->data('dir1/lorem_2.txt');
        mkdir($dir);
        file_put_contents($file1, 'Lorem ipsum.');
        file_put_contents($file2, '12345 67890.');
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);
        $this->assertEquals(
            '7c33b1ed131f9e3e7d6f7583b4556df8.4e9a74ac6861b061fd45db860c6247ca',
            implode('.', \Core\FS::dir_signatures($this->data()))
        );
        unlink($file1);
        unlink($file2);
        rmdir($dir);
    }

    // Dir Is Empry ------------------------------------------------------------
    public function test_dir_is_empty()
    {
        $file = $this->data('file.txt');
        file_put_contents($file, 'Lorem.');
        $this->assertFalse(\Core\FS::dir_is_empty($this->data()));
        unlink($file);
    }

    public function test_dir_is_empty_true()
    {
        $this->assertTrue(
            \Core\FS::dir_is_empty($this->data()),
            'Check if the directory is empty: ' . $this->data()
        );
    }

    // Dir Remove --------------------------------------------------------------
    public function test_dir_remove()
    {
        mkdir($this->data('dir1/sub/1'), 0777, true);
        mkdir($this->data('dir1/sub/2'), 0777, true);
        file_put_contents($this->data('dir1/file'), 'Lorem ipsum.');
        file_put_contents($this->data('dir1/sub/file'), 'Another ipsum.');
        file_put_contents($this->data('dir1/sub/1/file'), '3rd ipsum.');
        file_put_contents($this->data('dir1/sub/2/file'), '4th ipsum.');

        $this->assertTrue(\Core\FS::dir_remove($this->data('dir1')));
        $this->assertFalse(file_exists($this->data('dir1')));
    }

    // Dir Copy ----------------------------------------------------------------
    public function test_dir_copy()
    {
        $src = $this->data('dir1');
        $dest = $this->data('dir2');

        mkdir(ds($src, 'sub/1'), 0777, true);
        mkdir(ds($src, 'sub/2'), 0777, true);
        file_put_contents(ds($src, 'file'), 'Lorem ipsum.');
        file_put_contents(ds($src, 'sub/file'), 'Another ipsum.');
        file_put_contents(ds($src, 'sub/1/file'), '3rd ipsum.');
        file_put_contents(ds($src, 'sub/2/file'), '4th ipsum.');

        $this->assertTrue(
            \Core\FS::dir_copy(
                $src,
                $dest
            )
        );

        $this->assertFileExists($dest);
        $this->assertEquals(
            file_get_contents(ds($src, 'sub/2/file')),
            file_get_contents(ds($dest, 'sub/2/file'))
        );

        \Core\FS::dir_remove($src);
        \Core\FS::dir_remove($dest);
    }

    public function test_dir_copy_merge()
    {
        $src = $this->data('dir1');
        $dest = $this->data('dir2');

        mkdir(ds($src, 'sub/1'), 0777, true);
        mkdir(ds($src, 'sub/2'), 0777, true);
        mkdir(ds($dest, 'sub/1'), 0777, true);
        mkdir(ds($dest, 'sub/2'), 0777, true);
        file_put_contents(ds($src, 'file'), 'Lorem ipsum.');
        file_put_contents(ds($dest, 'file'), 'Ipsum lorem.');
        file_put_contents(ds($src, 'sub/file'), 'Another ipsum.');
        file_put_contents(ds($src, 'sub/1/file'), '3rd ipsum.');
        file_put_contents(ds($src, 'sub/2/file'), '4th ipsum.');

        $this->assertTrue(
            \Core\FS::dir_copy(
                $src,
                $dest,
                \Core\FS::EXISTS_MERGE
            )
        );

        $this->assertFileExists($dest);
        $this->assertEquals(
            file_get_contents(ds($src, 'file')),
            file_get_contents(ds($dest, 'file'))
        );

        \Core\FS::dir_remove($src);
        \Core\FS::dir_remove($dest);
    }

    public function test_dir_copy_rename()
    {
        $src = $this->data('dir1');
        $dest = $this->data('dir2');
        $dest_rn = $this->data('dir2_2');

        mkdir(ds($src, 'sub/1'), 0777, true);
        mkdir(ds($src, 'sub/2'), 0777, true);
        mkdir(ds($dest, 'sub/1'), 0777, true);
        file_put_contents(ds($src, 'file'), 'Lorem ipsum.');
        file_put_contents(ds($src, 'sub/file'), 'Another ipsum.');
        file_put_contents(ds($src, 'sub/1/file'), '3rd ipsum.');
        file_put_contents(ds($src, 'sub/2/file'), '4th ipsum.');

        $this->assertTrue(
            \Core\FS::dir_copy(
                $src,
                $dest,
                \Core\FS::EXISTS_RENAME
            )
        );

        $this->assertFileExists($dest_rn);
        $this->assertEquals(
            file_get_contents(ds($src, 'file')),
            file_get_contents(ds($dest_rn, 'file'))
        );

        \Core\FS::dir_remove($src);
        \Core\FS::dir_remove($dest);
        \Core\FS::dir_remove($dest_rn);
    }

    public function test_dir_copy_replace()
    {
        $src = $this->data('dir1');
        $dest = $this->data('dir2');

        mkdir(ds($src, 'sub/1'), 0777, true);
        mkdir(ds($src, 'sub/2'), 0777, true);
        mkdir(ds($dest, 'sub/1'), 0777, true);
        file_put_contents(ds($dest, 'file_special'), 'Ipsum lorem.');
        file_put_contents(ds($src, 'file'), 'Lorem ipsum.');
        file_put_contents(ds($src, 'sub/file'), 'Another ipsum.');
        file_put_contents(ds($src, 'sub/1/file'), '3rd ipsum.');
        file_put_contents(ds($src, 'sub/2/file'), '4th ipsum.');

        $this->assertTrue(
            \Core\FS::dir_copy(
                $src,
                $dest,
                \Core\FS::EXISTS_REPLACE
            )
        );

        $this->assertFileExists($dest);
        $this->assertFalse(file_exists(ds($dest, 'file_special')));
        $this->assertEquals(
            file_get_contents(ds($src, 'file')),
            file_get_contents(ds($dest, 'file'))
        );

        \Core\FS::dir_remove($src);
        \Core\FS::dir_remove($dest);
    }

    // Dir Create --------------------------------------------------------------
    public function test_dir_create()
    {
        $dest = $this->data('dir1');
        $this->assertFalse(file_exists($dest));
        $this->assertEquals(
            $dest,
            \Core\FS::dir_create($dest)
        );
        $this->assertFileExists($dest);
        \Core\FS::dir_remove($dest);
    }
    public function test_dir_create_rename()
    {
        $dest = $this->data('dir1');
        $destr = $this->data('dir1_2');
        mkdir($dest);
        $this->assertFileExists($dest);

        $this->assertEquals(
            $destr,
            \Core\FS::dir_create(
                $dest,
                \Core\FS::EXISTS_RENAME
            )
        );
        $this->assertFileExists($destr);

        \Core\FS::dir_remove($dest);
        \Core\FS::dir_remove($destr);
    }
}
