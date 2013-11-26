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
    public function __construct()
    {
        $dir = datpath('fs');
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

    // Test Format Size --------------------------------------------------------
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

    // Test Rename -------------------------------------------------------------
    public function test_rename_simple()
    {
        $oldfilename = datpath('fs/lorem.txt');
        $newfilename = datpath('fs/lorem.renamed.txt');

        // Create file
        file_put_contents($oldfilename, 'Lorem ipsum dolor sit amet.');
        unlink($newfilename);

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
        unlink($newfilename);
    }

    public function test_rename_array()
    {
        $old1 = datpath('fs/lorem.txt');
        $old2 = datpath('fs/ipsum.txt');
        $new1 = datpath('fs/lorem.renamed.txt');
        $new2 = datpath('fs/ipsum.renamed.txt');

        file_put_contents($old1, 'Lorem.');
        file_put_contents($old2, 'Lorem.');
        unlink($new1);
        unlink($new2);

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

        unlink($new1);
        unlink($new2);
    }

    // Test Unique Prefix ------------------------------------------------------
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

    // Test File Extension -----------------------------------------------------
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

    // Test File Get Name ------------------------------------------------------
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

    // Test File Unqiue Name ---------------------------------------------------
    public function test_unique_name()
    {
        $file1 = datpath('fs/lorem.txt');
        $file2 = datpath('fs/lorem_2.txt');
        file_put_contents($file1, 'Lorem.');
        file_put_contents($file2, 'Lorem.');

        $this->assertFileExists($file1);
        $this->assertFileExists($file2);
        $this->assertEquals(
            datpath('fs/lorem_3.txt'),
            \FS::unique_name($file1)
        );

        unlink($file1);
        unlink($file2);
    }

    public function test_unique_name_directory()
    {
        $dir1 = datpath('fs/lorem.txt');
        $dir2 = datpath('fs/lorem_2.txt');
        mkdir($dir1);
        mkdir($dir2);

        $this->assertFileExists($dir1);
        $this->assertFileExists($dir2);
        $this->assertEquals(
            datpath('fs/lorem_3.txt'),
            \FS::unique_name($dir1)
        );

        rmdir($dir1);
        rmdir($dir2);
    }

    public function test_unique_name_non_existing()
    {
        $filename = datpath('fs/non.txt');

        $this->assertFalse(file_exists($filename));
        $this->assertEquals(
            $filename,
            \FS::unique_name($filename)
        );
    }

    // Test File Read ----------------------------------------------------------
    public function test_file_read()
    {
        $file = datpath('fs/lorem.txt');
        file_put_contents($file, 'Lorem ipsum dolor.');

        $this->assertEquals(
            'Lorem ipsum dolor.',
            \FS::file_read($file)
        );

        unlink($file);
    }

    // Test File Create --------------------------------------------------------
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
        if (file_exists($filename)) {
            unlink($filename);
        }

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

    // Test File Append --------------------------------------------------------
    public function test_file_append_existing()
    {
        $filename = datpath('fs/test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

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

    // Test File Prepend -------------------------------------------------------
    public function test_file_prepend_existing()
    {
        $filename = datpath('fs/test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

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

    // File Replace ------------------------------------------------------------
    public function test_file_replace_existing()
    {
        $filename = datpath('fs/test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

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

    // File Empty ------------------------------------------------------------
    public function test_file_empty_existing()
    {
        $filename = datpath('fs/test_file.txt');
        if (file_exists($filename)) {
            unlink($filename);
        }

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

    // File Remove -------------------------------------------------------------
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

    // File Copy ---------------------------------------------------------------
    public function test_file_copy()
    {
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        unlink($dest);

        $this->assertFalse(file_exists($dest));
        $this->assertFileExists($file);

        $this->assertEquals(
            1,
            \FS::file_copy($file, $dest)
        );

        $this->assertFileExists($dest);
        unlink($file);
        unlink($dest);
    }

    public function test_file_copy_array()
    {
        $file1 = datpath('fs/lorem.txt');
        $file2 = datpath('fs/ipsum.txt');
        $dest1 = datpath('fs/lorem_copy.txt');
        $dest2 = datpath('fs.ipsum_copy.txt');
        file_put_contents($file1, 'Lorem.');
        file_put_contents($file2, 'Lorem.');
        unlink($dest1);
        unlink($dest2);

        $this->assertFalse(file_exists($dest1));
        $this->assertFalse(file_exists($dest2));
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);

        $this->assertEquals(
            2,
            \FS::file_copy([
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \FS::file_copy($file, $dest, \FS::EXISTS_REPLACE)
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        $renamed = datpath('fs/lorem_copy_2.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \FS::file_copy($file, $dest, \FS::EXISTS_RENAME)
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            0,
            \FS::file_copy($file, $dest, \FS::EXISTS_IGNORE)
        );

        $this->assertEquals(
            'Ipsum.',
            file_get_contents($dest)
        );
        unlink($file);
        unlink($dest);
    }

    /**
     * @expectedException   \Mysli\Core\FileSystemException
     * @expectedExceptionCode 10
     */
    public function test_file_copy_exists_error()
    {
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \FS::file_copy($file, $dest, \FS::EXISTS_ERROR)
            );
        } catch (\Mysli\Core\FileSystemException $e) {
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \FS::file_copy($file, $dest, 'Invalid')
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
        $file = datpath('fs/non_existant.txt');
        if (file_exists($file)) {
            unlink($file);
        }
        $this->assertFalse(file_exists($file));
        $this->assertEquals(
            0,
            \FS::file_copy($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 11
     */
    public function test_file_copy_source_is_dir()
    {
        $file = datpath('fs');
        $this->assertFileExists($file);
        $this->assertEquals(
            0,
            \FS::file_copy($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 12
     */
    public function test_file_copy_dest_not_dir()
    {
        $file = datpath('fs/lorem.txt');
        file_put_contents($file, 'Lorem.');
        $this->assertFileExists($file);
        try {
            $this->assertEquals(
                0,
                \FS::file_copy($file, null)
            );
        } catch (\Mysli\Core\ValueException $e) {
            unlink($file);
            throw $e;
        }
    }

    // File Move ---------------------------------------------------------------
    public function test_file_move()
    {
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_moved.txt');
        file_put_contents($file, 'Lorem.');
        unlink($dest);

        $this->assertFalse(file_exists($dest));
        $this->assertFileExists($file);

        $this->assertEquals(
            1,
            \FS::file_move($file, $dest)
        );

        $this->assertFalse(file_exists($file));
        $this->assertFileExists($dest);
        unlink($dest);
    }

    public function test_file_move_array()
    {
        $file1 = datpath('fs/lorem.txt');
        $file2 = datpath('fs/ipsum.txt');
        $dest1 = datpath('fs/lorem_moved.txt');
        $dest2 = datpath('fs.ipsum_moved.txt');
        file_put_contents($file1, 'Lorem.');
        file_put_contents($file2, 'Lorem.');
        unlink($dest1);
        unlink($dest2);

        $this->assertFalse(file_exists($dest1));
        $this->assertFalse(file_exists($dest2));
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);

        $this->assertEquals(
            2,
            \FS::file_move([
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \FS::file_move($file, $dest, \FS::EXISTS_REPLACE)
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        $renamed = datpath('fs/lorem_copy_2.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            1,
            \FS::file_move($file, $dest, \FS::EXISTS_RENAME)
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        $this->assertEquals(
            0,
            \FS::file_move($file, $dest, \FS::EXISTS_IGNORE)
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
     * @expectedException   \Mysli\Core\FileSystemException
     * @expectedExceptionCode 10
     */
    public function test_file_move_exists_error()
    {
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \FS::file_move($file, $dest, \FS::EXISTS_ERROR)
            );
        } catch (\Mysli\Core\FileSystemException $e) {
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
        $file = datpath('fs/lorem.txt');
        $dest = datpath('fs/lorem_copy.txt');
        file_put_contents($file, 'Lorem.');
        file_put_contents($dest, 'Ipsum.');

        $this->assertFileExists($file);
        $this->assertFileExists($dest);

        try {
            $this->assertEquals(
                0,
                \FS::file_move($file, $dest, 'Invalid')
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
        $file = datpath('fs/non_existant.txt');
        if (file_exists($file)) {
            unlink($file);
        }
        $this->assertFalse(file_exists($file));
        $this->assertEquals(
            0,
            \FS::file_move($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 11
     */
    public function test_file_move_source_is_dir()
    {
        $file = datpath('fs');
        $this->assertFileExists($file);
        $this->assertEquals(
            0,
            \FS::file_move($file, null)
        );
    }

    /**
     * @expectedException   \Mysli\Core\ValueException
     * @expectedExceptionCode 12
     */
    public function test_file_move_dest_not_dir()
    {
        $file = datpath('fs/lorem.txt');
        file_put_contents($file, 'Lorem.');
        $this->assertFileExists($file);
        try {
            $this->assertEquals(
                0,
                \FS::file_move($file, null)
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
        mkdir(datpath('fs/test'));
        foreach ($files as $file) {
            $file_sub = datpath('fs/test/' . $file);
            $file = datpath('fs/' . $file);
            file_put_contents($file, 'null');
            file_put_contents($file_sub, 'null');
            $this->assertFileExists($file);
        }
        $results =  \FS::file_search(
            datpath('fs'),
            '/(.*?)\.jpe?g/i'
        );
        // Remove path information
        foreach ($results as &$result) {
            $result = substr($result, strlen(datpath('fs')) + 1);
        }

        foreach ($endings as $file) {
            $this->assertTrue(in_array($file, $results), 'File not found: ' . $file);
        }

        foreach ($files as $file) {
            $file_sub = datpath('fs/test/' . $file);
            $file = datpath('fs/' . $file);
            unlink($file);
            unlink($file_sub);
        }
        rmdir(datpath('fs/test'));
    }

    // File Signature ----------------------------------------------------------
    public function test_file_signature()
    {
        $file1 = datpath('fs/test1.txt');
        $file2 = datpath('fs/test2.txt');
        file_put_contents($file1, 'Lorem ipsum.');
        file_put_contents($file2, '12345 67890.');
        $this->assertEquals(
            [
                '4e9a74ac6861b061fd45db860c6247ca',
                '7c33b1ed131f9e3e7d6f7583b4556df8'
            ],
            \FS::file_signature([$file1, $file2])
        );
        unlink($file1);
        unlink($file2);
    }

    // File Is Public ----------------------------------------------------------
    public function test_file_is_public_not()
    {
        $file = datpath('fs/my-file.txt');
        file_put_contents($file, '1234');
        $this->assertFalse(\FS::file_is_public($file));
        unlink($file);
    }

    public function test_file_is_public_true()
    {
        $file = pubpath('test.txt');
        file_put_contents($file, '1234');
        $this->assertTrue(\FS::file_is_public($file));
        unlink($file);
    }

    // File Get Uri ------------------------------------------------------------
    public function test_file_get_uri()
    {
        $file = pubpath('test.txt');
        file_put_contents($file, '1234');
        $this->assertEquals(
            'test.txt',
            \FS::file_get_uri($file)
        );
        unlink($file);
    }

    // File Get Url ------------------------------------------------------------
    public function test_file_get_url()
    {
        $file = pubpath('test.txt');
        file_put_contents($file, '1234');
        $this->assertEquals(
            // This url is set in data_dummy/core/cfg.json
            'http:://localhost/test.txt',
            \FS::file_get_url($file)
        );
        unlink($file);
    }

    // Dir Get Signatures
    public function test_dir_signatures()
    {
        $file1 = datpath('fs/lorem_1.txt');
        $file2 = datpath('fs/lorem_2.txt');
        file_put_contents($file1, 'Lorem ipsum.');
        file_put_contents($file2, '12345 67890.');
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);
        $this->assertEquals(
            '4e9a74ac6861b061fd45db860c6247ca.7c33b1ed131f9e3e7d6f7583b4556df8',
            implode('.', \FS::dir_signatures(datpath('fs')))
        );
        unlink($file1);
        unlink($file2);
    }

    public function test_dir_signatures_sub_dir()
    {
        $file1 = datpath('fs/lorem_1.txt');
        $dir   = datpath('fs/dir1');
        $file2 = datpath('fs/dir1/lorem_2.txt');
        mkdir($dir);
        file_put_contents($file1, 'Lorem ipsum.');
        file_put_contents($file2, '12345 67890.');
        $this->assertFileExists($file1);
        $this->assertFileExists($file2);
        $this->assertEquals(
            '7c33b1ed131f9e3e7d6f7583b4556df8.4e9a74ac6861b061fd45db860c6247ca',
            implode('.', \FS::dir_signatures(datpath('fs', true)))
        );
        unlink($file1);
        unlink($file2);
        rmdir($dir);
    }

    // Dir Is Empry ------------------------------------------------------------
    public function test_dir_is_empty()
    {
        $file = datpath('fs/file.txt');
        file_put_contents($file, 'Lorem.');
        $this->assertFalse(\FS::dir_is_empty(datpath('fs')));
        unlink($file);
    }

    public function test_dir_is_empty_true()
    {
        $this->assertTrue(
            \FS::dir_is_empty(datpath('fs')),
            'Check if the directory is empty: ' . datpath('fs')
        );
    }

    // Dir Remove --------------------------------------------------------------
    public function test_dir_remove()
    {
        mkdir(datpath('fs/dir1/sub/1'), 0777, true);
        mkdir(datpath('fs/dir1/sub/2'), 0777, true);
        file_put_contents(datpath('fs/dir1/file'), 'Lorem ipsum.');
        file_put_contents(datpath('fs/dir1/sub/file'), 'Another ipsum.');
        file_put_contents(datpath('fs/dir1/sub/1/file'), '3rd ipsum.');
        file_put_contents(datpath('fs/dir1/sub/2/file'), '4th ipsum.');

        $this->assertTrue(\FS::dir_remove(datpath('fs/dir1')));
        $this->assertFalse(file_exists(datpath('fs/dir1')));
    }

    // Dir Copy ----------------------------------------------------------------
    // public function test_dir_copy()
    // {
    //     mkdir(datpath('fs/dir1/sub/1'), 0777, true);
    //     mkdir(datpath('fs/dir1/sub/2'), 0777, true);
    //     file_put_contents(datpath('fs/dir1/file'), 'Lorem ipsum.');
    //     file_put_contents(datpath('fs/dir1/sub/file'), 'Another ipsum.');
    //     file_put_contents(datpath('fs/dir1/sub/1/file'), '3rd ipsum.');
    //     file_put_contents(datpath('fs/dir1/sub/2/file'), '4th ipsum.');

    //     \FS::dir_copy(datpath('fs/dir1'), datpath('fs/dir2'));
    // }
}
