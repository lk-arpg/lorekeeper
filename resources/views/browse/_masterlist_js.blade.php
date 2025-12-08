<script>
    $(document).ready(function() {
        $('.userselectize').selectize();

        var $gridButton = $('.grid-view-button');
        var $gridView = $('#gridView');
        var $listButton = $('.list-view-button');
        var $listView = $('#listView');

        var view = null;

        initView();

        $gridButton.on('click', function(e) {
            e.preventDefault();
            setView('grid');
        });
        $listButton.on('click', function(e) {
            e.preventDefault();
            setView('list');
        });

        function initView() {
            view = window.localStorage.getItem('lorekeeper_masterlist_view');
            if (!view) view = 'grid';
            setView(view);
        }

        function setView(status) {
            view = status;

            if (view == 'grid') {
                $gridView.removeClass('hide');
                $gridButton.addClass('active');
                $listView.addClass('hide');
                $listButton.removeClass('active');
                window.localStorage.setItem('lorekeeper_masterlist_view', 'grid');
            } else if (view == 'list') {
                $listView.removeClass('hide');
                $listButton.addClass('active');
                $gridView.addClass('hide');
                $gridButton.removeClass('active');
                window.localStorage.setItem('lorekeeper_masterlist_view', 'list');
            }
        }


        var $featureSelect = $('.feature-select');

        @if (config('lorekeeper.extensions.organised_traits_dropdown.enable'))
            let renderOptions = {};
            @if (config('lorekeeper.extensions.organised_traits_dropdown.rarity.enable'))
                renderOptions = {
                    option: featureOptionRender,
                    item: featureSelectedRender
                }
            @else
                renderOptions = {
                    item: featureSelectedRender
                }
            @endif
            $featureSelect.selectize({
                render: renderOptions
            });
        @else
            $featureSelect.selectize();
        @endif

        function featureOptionRender(item, escape) {
            return '<div class="option"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + (item["text"].trim()) + '</span></div>';
        }

        function featureSelectedRender(item, escape) {
            @if (config('lorekeeper.extensions.organised_traits_dropdown.rarity.enable'))
                return '<div><span>' + (item["text"].trim()) + ' (' + (item["optgroup"].trim()) + ')' + '</span></div>';
            @endif
            return '<div><span>' + escape(item["text"].trim()) + ' (' + escape(item["optgroup"].trim()) + ')' + '</span></div>';
        }

    });
</script>
