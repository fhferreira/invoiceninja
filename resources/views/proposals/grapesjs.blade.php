<script type="text/javascript">

$(function() {

    window.grapesjsEditor = grapesjs.init({
        container : '#gjs',
        components: {!! json_encode($entity ? $entity->html : '') !!},
        style: {!! json_encode($entity ? $entity->css : '') !!},
        showDevices: false,
        plugins: ['gjs-preset-newsletter'],
        pluginsOpts: {
            'gjs-preset-newsletter': {
                'categoryLabel': "{{ trans('texts.standard') }}"
            }
        },
        storageManager: {
            type: 'none'
        },
        assetManager: {
            assets: {!! json_encode($documents) !!},
            noAssets: "{{ trans('texts.no_assets') }}",
            addBtnText: "{{ trans('texts.add_image') }}",
            modalTitle: "{{ trans('texts.select_image') }}",
            @if (Utils::isSelfHost() || $account->isEnterprise())
                upload: {!! json_encode(url('/documents')) !!},
                uploadText: "{{ trans('texts.dropzone_default_message') }}",
            @else
                upload: false,
                uploadText: "{{ trans('texts.upgrade_to_upload_images') }}",
            @endif
            uploadName: 'files',
            params: {
                '_token': '{{ Session::token() }}',
                'grapesjs': true,
            }
        }
    });

    var blockManager = grapesjsEditor.BlockManager;

    @foreach ($snippets as $snippet)
        blockManager.add("h{{ ($loop->index + 1) }}-block", {
            label: '{{ $snippet->name }}',
            category: '{{ $snippet->proposal_category ? $snippet->proposal_category->name : trans('texts.custom') }}',
            content: {!! json_encode($snippet->html) !!},
            style: {!! json_encode($snippet->css) !!},
            attributes: {
                title: {!! json_encode($snippet->private_notes) !!},
                class:'fa fa-{{ $snippet->icon ?: 'font' }}'
            }
        });
    @endforeach

    @if (count($snippets))
        var blockCategories = blockManager.getCategories();
        for (var i=0; i<blockCategories.models.length; i++) {
            var blockCategory = blockCategories.models[i];
            blockCategory.set('open', false);
        }
    @endif

    grapesjsEditor.on('component:update', function(a, b) {
        NINJA.formIsChanged = true;
    });

    grapesjsEditor.on('asset:remove', function(asset) {
        sweetConfirm(function() {
            $.ajax({
                url: "{{ url('/documents') }}/" + asset.attributes.public_id,
                type: 'DELETE',
                success: function(result) {
                    console.log('result: %s', result);
                }
            });
        }, "{{ trans('texts.delete_image_help') }}", "{{ trans('texts.delete_image') }}", function() {
            var assetManager = grapesjsEditor.AssetManager;
            assetManager.add([asset.attributes]);
        });
    });

});

</script>
