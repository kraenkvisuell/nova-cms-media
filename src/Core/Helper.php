<?php

namespace Kraenkvisuell\NovaCmsMedia\Core;

use Illuminate\Support\Facades\Storage;
use Kraenkvisuell\NovaCmsMedia\API;

class Helper
{
    public static function storage()
    {
        return Storage::disk(config('nova-cms-media.disk', 'public'));
    }

    public static function upload($path, $file, $private)
    {
        if (config('nova-cms-media.s3.upload_using_presigned_url')) {
            $adapter = Storage::getAdapter(); // Get the filesystem adapter
            $client = $adapter->getClient(); // Get the aws client
            $bucket = $adapter->getBucket(); // Get the current bucket
            // Make a PutObject command
            $cmd = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => 'aaa-tmp',
            ]);
            // Get the presigned request
            $request = $client->createPresignedRequest($cmd, '+20 minutes');
            // Get the actual URL to make the request to
            $presignedUrl = (string) $request->getUri();

            return static::storage()->put(
                $presignedUrl,
                $file,
                static::visibility($private)
            );
        } else {
            return static::storage()->put(
                static::folder($path),
                $file,
                static::visibility($private)
            );
        }
    }

    public static function directories()
    {
        $len = strlen(substr(self::folder(), 1));
        $array = [];

        foreach (self::storage()->allDirectories(config('nova-cms-media.folder')) as $item) {
            if ('nml_temp' == $item) {
                continue;
            }
            $path = str_replace('/', '.', substr($item, $len));
            if ($path) {
                data_set($array, $path, 0);
            }
        }

        return $array;
    }

    public static function replace($str)
    {
        return preg_replace('/(\/)\\1+/', '$1', str_replace('\\', '/', $str));
    }

    public static function folder($path = '')
    {
        return self::replace('/'.(string) config('nova-cms-media.folder', '').'/'.$path);
    }

    public static function size($bytes)
    {
        if ($bytes / 1073741824 >= 1) {
            return round($bytes / 1073741824, 2).' '.__('gb');
        }

        if ($bytes / 1048576 >= 1) {
            return round($bytes / 1048576, 2).' '.__('mb');
        }

        if ($bytes / 1024 >= 1) {
            return round($bytes / 1024, 2).' '.__('kb');
        }

        return $bytes.' '.__('b');
    }

    public static function isPrivate($folder)
    {
        $disk = config('nova-cms-media.disk');
        $private = false;

        if ('s3' == $disk) {
            $private = config('nova-cms-media.private') ?? false;
        } elseif ('local' == $disk) {
            $private = '/public/' != substr(self::folder($folder), 0, 8);
        }

        return $private;
    }

    public static function visibility($bool)
    {
        return $bool ? 'private' : 'public';
    }

    public static function preview($item, $size)
    {
        if (! in_array($size, data_get($item, 'options.img_sizes', []))) {
            return;
        }

        $url = data_get($item, 'url');

        return data_get($item, 'private') ? $url.'&img_size='.$size : API::getImageSize($url, $size);
    }

    public static function localPublic($folder, $private)
    {
        return
            'local' == config('nova-cms-media.disk') and
            ! $private and
            '/public/' == substr(self::folder($folder), 0, 8);
    }
}
