<template>
    <div>
        <template v-if="!item && isForm">   
            <template v-if="field.nmlUploadOnly">
                <label 
                    class="block card border border-lg border-50 max-w-xs p-8 text-center cursor-pointer max-w-xs"
                    :class="{
                        'pointer-events-none text-red' : uploading
                    }"
                >
                    <input
                        :id="'nml_upload_'+field.attribute"
                        v-if="field.nmlUploadOnly"
                        class="form-file-input"
                        type="file"
                        :accept="config.accept"
                        @change="selectFiles"
                    />
                    <template v-if="!uploading">
                        {{ __('Upload') }}
                    </template>

                    <template v-if="uploading">
                        <em>{{ __('Uploading') }}...</em>
                    </template>
                </label>
            </template>

            <template v-if="!field.nmlUploadOnly">
                <div 
                    class="card border border-lg border-50 max-w-xs p-8 text-center cursor-pointer max-w-xs"  
                    @click="popup = true"
                >
                    {{ __('Select File') }}
                </div>
            </template>
        </template>

        <a v-else-if="item" :href="item.url" target="_blank" class="no-underline">
            <img class="inline-block rounded-lg shadow-md max-w-xs"
            style="max-height: 200px"
            v-if="`image` === mime(item)"
            :src="item.preview || item.url"
            :alt="__('This file could not be found')" />

            <div class="nml-display-list" v-else>
                <div class="nml-item relative mb-2 cursor-pointer" :title="item.title || item.name">
                    <div :class="'icon rounded-lg shadow-md nml-icon-'+mime(item)" :style="bg(item)" />

                    <div class="title truncate" v-text="item.title || item.name" />
                </div>
            </div>
        </a>


        <div class="mt-4" v-if="isForm && item">
            <a 
                v-if="!field.nmlUploadOnly"
                class="cursor-pointer dim inline-block text-primary font-bold mr-8" 
                @click="popup = true"
            >
                {{ __('Media Library') }}
            </a>

            <a class="cursor-pointer dim inline-block text-danger font-bold" @click="changeFile(null)">
                {{ __('Clear') }}
            </a>
        </div>


        <transition name="fade" mode="out-in">
            <Library v-if="popup && !field.nmlUploadOnly" :field="field" />
        </transition>
    </div>
</template>

<script src="./script.js"></script>
