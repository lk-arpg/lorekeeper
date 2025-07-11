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
    });
</script>
