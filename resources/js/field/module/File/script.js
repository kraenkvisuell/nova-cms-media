import Library from '../Library'
import Mixin from '../../../_mixin'

export default {
    props: ['field', 'handler'],
    mixins: [Mixin],
    components: { Library },
    data() {
        return {
            config: window.Nova.config.novaMediaLibrary,
            popup: false,
            isForm: this.$parent.$parent.isFormField === true,
            item: this.field.value,
            files: [],
            uploading: false,
        }
    },
    methods: {
        changeFile(item) {
            this.item = item;
            if ( this.handler ) this.handler(item);
        },
        selectFiles(input) {
            if ( !input.target.files.length ) return;
            
            this.files = Object.assign({}, input.target.files);
            this.uploadFile(0);
            
            document.getElementById('nml_upload_'+this.field.attribute).value = null;
        },
        uploadFile(i) {
            let file = this.files[i];
            if ( !file ) return;
            
            this.uploading = true;
            
            let config = { headers: { 'Content-Type': 'multipart/form-data' } };
            let data = new FormData();
            data.append('file', file);
            data.append('folder', null);
            
            Nova.request().post('/nova-vendor/nova-cms-media/upload', data, config).then(r => {
                this.uploading = false;
                this.changeFile(r.data);
            }).catch(e => {
                this.uploading = false;
                window.nmlToastHook(e);
            });
        }
    },
    created() {
        Nova.$on(`nmlSelectFiles[${this.field.attribute}]`, array => {
            this.popup = false;
            console.log(array[0]);
            this.changeFile(array[0]);
        });
    },
    beforeDestroy() {
        Nova.$off(`nmlSelectFiles[${this.field.attribute}]`);
    }
}
