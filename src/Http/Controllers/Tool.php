<?php

namespace Kraenkvisuell\NovaCmsMedia\Http\Controllers;

use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsMedia\Core\Crop;
use Kraenkvisuell\NovaCmsMedia\Core\Helper;
use Kraenkvisuell\NovaCmsMedia\Core\Model;
use Kraenkvisuell\NovaCmsMedia\Core\Upload;
use Kraenkvisuell\NovaCmsMedia\Http\Requests\CropRequest;
use Kraenkvisuell\NovaCmsMedia\Http\Requests\DeleteFolderRequest;
use Kraenkvisuell\NovaCmsMedia\Http\Requests\DeleteRequest;
use Kraenkvisuell\NovaCmsMedia\Http\Requests\GetRequest;
use Kraenkvisuell\NovaCmsMedia\Http\Requests\NewFolderRequest;
use Kraenkvisuell\NovaCmsMedia\Http\Requests\UpdateRequest;
use Kraenkvisuell\NovaCmsMedia\Http\Requests\UploadRequest;

class Tool
{
    public function get(GetRequest $fr)
    {
        $preview = config('nova-cms-media.resize.preview');

        $data = (new Model)->search();
        $data['array'] = collect($data['array'])->map(function ($item) use ($preview) {
            if (! $item->url) {
                $item = $item->toArray();
                $item['url'] = route('nml-private-file-admin', ['id' => $item['id']]);
            }
            $item['preview'] = Helper::preview($item, $preview);

            return $item;
        });

        return $data;
    }

    public function private()
    {
        $item = Model::find(request('id'));
        $size = request('img_size');

        if (! $item or ! $item->path) {
            return response()->noContent(404);
        }

        if (! in_array($size, data_get($item, 'options.img_sizes', []))) {
            $size = null;
        }

        return API::getPrivateFile($item->path, $size);
    }

    public function upload(UploadRequest $fr)
    {
        $file = request()->file('file');
        $file_name = " ({$file->getClientOriginalName()})";

        $upload = new Upload($file);

        if (! $upload->setType()) {
            abort(422, __('Forbidden file format'));
        }

        $upload->setWH();

        $upload->setFolder(request('folder'));

        $upload->setPrivate();

        $upload->setFile();

        if (! $upload->checkSize()) {
            abort(422, __('File size limit exceeded').$file_name);
        }

        $item = $upload->save();
        if ($item) {
            Crop::createSizes($item);
            if ($upload->noResize) {
                // abort(200, __('Unsupported image type for resizing, only the original is uploaded') . $file_name);
            }
            $item['preview'] = Helper::preview($item, 'thumb');

            return $item;
        }

        abort(422, __('The file was not downloaded for unknown reasons').$file_name);
    }

    public function delete(DeleteRequest $fr)
    {
        $get = Model::find(request('ids'));
        $delete = Model::whereIn('id', request('ids'))->delete();

        if (count($get) > 0) {
            $array = [];
            foreach ($get as $key) {
                $sizes = data_get($key, 'options.img_sizes', []);
                $array[] = Helper::folder($key->folder).$key->name;

                if ($sizes) {
                    foreach ($sizes as $size) {
                        $name = explode('.', $key->name);
                        $array[] = Helper::folder($key->folder.implode('-'.$size.'.', $name));
                    }
                }
            }

            Helper::storage()->delete($array);
        }

        return ['status' => (bool) $delete];
    }

    public function update(UpdateRequest $fr)
    {
        $item = Model::find(request('id'));
        if (! $item) {
            abort(422, __('Invalid id'));
        }

        $item->title = request('title');
        $img_sizes = data_get($item->options, 'img_sizes', []);

        if (request()->has('private') and 's3' === config('nova-cms-media.disk')) {
            $item->private = (bool) request('private');
            $visibility = Helper::visibility($item->private);

            Helper::storage()->setVisibility($item->path, $visibility);
            foreach ($img_sizes as $key) {
                Helper::storage()->setVisibility(API::getImageSize($item->path, $key), $visibility);
            }
        }

        $folder = request('folder');
        if ($folder and 'folders' === config('nova-cms-media.store') and $folder !== $item->folder) {
            $private = Helper::isPrivate($folder);
            $array = [[$item->path, Helper::folder($folder.$item->name)]];

            foreach ($img_sizes as $key) {
                $name = API::getImageSize($item->name, $key);
                $array[] = [Helper::folder($item->folder.$name), Helper::folder($folder.$name)];
            }

            foreach ($array as $key) {
                Helper::storage()->move($key[0], $key[1]);
                if ($private != $item->private) {
                    Helper::storage()->setVisibility($key[1], Helper::visibility($private));
                }
            }

            $item->private = $private;
            $item->folder = Helper::replace('/'.$folder.'/');
            $item->lp = Helper::localPublic($item->folder, $private);
        }

        $item->save();

        return $item;
    }

    public function crop(CropRequest $fr)
    {
        $crop = new Crop(request()->toArray());
        if (! $crop->form) {
            abort(422, __('Crop module disabled'));
        }

        if (! $crop->image) {
            abort(422, __('Invalid request data'));
        }

        $crop->make();

        if ($crop->save()) {
            return;
        }

        abort(422, __('The file was not downloaded for unknown reasons'));
    }

    public function folderNew(NewFolderRequest $fr)
    {
        if (Helper::storage()->makeDirectory(Helper::folder(request('base').request('folder').'/'))) {
            return ['folders' => Helper::directories()];
        }

        abort(422, __('Cannot manage folders'));
    }

    public function folderDel(DeleteFolderRequest $fr)
    {
        if (Helper::storage()->deleteDirectory(Helper::folder(request('folder')))) {
            return ['folders' => Helper::directories()];
        }

        abort(422, __('Cannot manage folders'));
    }

    public function folders()
    {
        return Helper::directories();
    }
}
